<?php
/**
 * ============================================
 * DISCORD WEBHOOK HELPER
 * ============================================
 * 
 * Fungsi untuk kirim notifikasi ke Discord.
 * 
 * Cara pakai:
 *   sendDiscord("Pesan", discordColor('success'), [
 *     ['name' => 'Field 1', 'value' => 'Isi 1', 'inline' => true]
 *   ]);
 * ============================================
 */

/**
 * Kirim notifikasi ke Discord via webhook.
 * 
 * @param string $message      Pesan utama (mendukung markdown)
 * @param int    $color        Warna embed (hex)
 * @param array  $fields       Field tambahan
 * @param string $webhookNama  Nama webhook di tabel (default: 'vote')
 * @return bool
 */
function sendDiscord(
    string $message, 
    int $color = 0x2e7d32, 
    array $fields = [], 
    string $webhookNama = 'vote'
): bool {
    try {
        $db = getDB();
        $stmt = $db->prepare(
            "SELECT url FROM discord_webhook 
             WHERE nama = :nama AND aktif = TRUE"
        );
        $stmt->execute([':nama' => $webhookNama]);
        $row = $stmt->fetch();

        if (!$row || empty($row['url'])) {
            error_log("Discord webhook '{$webhookNama}' tidak ditemukan atau nonaktif");
            return false;
        }

        $webhookUrl = $row['url'];

        $embed = [
            'title'       => 'E-Voting OSIS',
            'description' => $message,
            'color'       => $color,
            'timestamp'   => date('c'),
            'footer'      => [
                'text' => 'E-Voting System · ' . date('d M Y H:i:s')
            ]
        ];

        if (!empty($fields)) {
            $embed['fields'] = $fields;
        }

        $payload = json_encode([
            'username' => 'E-Voting Bot',
            'embeds'   => [$embed]
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            error_log('Discord webhook cURL error: ' . $curlErr);
            return false;
        }

        return $httpCode >= 200 && $httpCode < 300;

    } catch (Throwable $e) {
        error_log('Discord webhook exception: ' . $e->getMessage());
        return false;
    }
}

/**
 * Warna embed Discord.
 */
function discordColor(string $type): int {
    switch ($type) {
        case 'success': return 0x2e7d32;
        case 'warning': return 0xe65100;
        case 'danger':  return 0xc62828;
        case 'info':    return 0x1976d2;
        default:        return 0x757575;
    }
}