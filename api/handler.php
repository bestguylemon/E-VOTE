<?php
date_default_timezone_set('Asia/Jakarta');
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/discord.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $db = getDB();
    switch ($action) {

        case 'login':
            $nis  = trim($_POST['nis']  ?? '');
            $nama = trim($_POST['nama'] ?? '');

            if ($nis === '' || $nama === '') {
                respond(false, 'NIS dan Nama wajib diisi');
            }

            $stmt = $db->prepare("SELECT nis, nama FROM siswa WHERE nis = :nis");
            $stmt->execute([':nis' => $nis]);
            $siswa = $stmt->fetch();

            if (!$siswa) {
                respond(false, 'NIS tidak terdaftar');
            }

            $stmt = $db->prepare("SELECT 1 FROM suara WHERE nis = :nis");
            $stmt->execute([':nis' => $nis]);
            if ($stmt->fetch()) {
                respond(false, 'NIS ini sudah pernah memilih');
            }

            $_SESSION['nis']  = $nis;
            $_SESSION['nama'] = $siswa['nama'];

            respond(true, 'Login berhasil', [
                'nama'     => $siswa['nama'],
                'kandidat' => getKandidat($db)
            ]);
            break;

        case 'vote':
            if (!isset($_SESSION['nis'])) {
                respond(false, 'Sesi habis, silakan login ulang');
            }

            $stmt = $db->query(
                "SELECT value FROM pengaturan WHERE key = 'voting_selesai'"
            );
            $selesaiRow = $stmt->fetch();
            if ($selesaiRow && new DateTime() > new DateTime($selesaiRow['value'])) {
                respond(false, 'Waktu voting sudah berakhir');
            }

            $kandidatId = (int)($_POST['kandidat_id'] ?? 0);
            if ($kandidatId <= 0) {
                respond(false, 'Kandidat tidak valid');
            }

            $stmt = $db->prepare("SELECT 1 FROM kandidat WHERE id = :id");
            $stmt->execute([':id' => $kandidatId]);
            if (!$stmt->fetch()) {
                respond(false, 'Kandidat tidak ditemukan');
            }

            $stmt = $db->prepare("SELECT 1 FROM suara WHERE nis = :nis");
            $stmt->execute([':nis' => $_SESSION['nis']]);
            if ($stmt->fetch()) {
                respond(false, 'Anda sudah memilih sebelumnya');
            }

            $ipAddress = getClientIP();
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 255);
            $deviceId  = trim($_POST['device_id'] ?? '');
            $screenRes = trim($_POST['screen_res'] ?? '');
            $timezone  = trim($_POST['timezone'] ?? '');
            $language  = trim($_POST['language'] ?? '');

            $db->beginTransaction();
            try {
                $stmt = $db->prepare(
                    "INSERT INTO suara (nis, kandidat_id, ip_address, user_agent,
                                        device_id, screen_res, timezone, language)
                     VALUES (:nis, :kid, :ip, :ua, :dev, :screen, :tz, :lang)"
                );
                $stmt->execute([
                    ':nis'    => $_SESSION['nis'],
                    ':kid'    => $kandidatId,
                    ':ip'     => $ipAddress,
                    ':ua'     => $userAgent,
                    ':dev'    => $deviceId,
                    ':screen' => $screenRes,
                    ':tz'     => $timezone,
                    ':lang'   => $language
                ]);

                $stmt = $db->prepare(
                    "UPDATE siswa SET sudah_memilih = TRUE WHERE nis = :nis"
                );
                $stmt->execute([':nis' => $_SESSION['nis']]);

                $db->commit();
                                // Kirim notif Discord
                $stmt = $db->prepare("SELECT nama, kelas FROM siswa WHERE nis = :nis");
                $stmt->execute([':nis' => $_SESSION['nis']]);
                $siswa = $stmt->fetch();

                $stmt = $db->prepare("SELECT nama FROM kandidat WHERE id = :id");
                $stmt->execute([':id' => $kandidatId]);
                $kandidat = $stmt->fetch();

                sendDiscord(
                    "**Suara Baru Masuk** 🗳",
                    discordColor('success'),
                    [
                        ['name' => 'NIS',     'value' => $_SESSION['nis'],   'inline' => true],
                        ['name' => 'Nama',    'value' => $siswa['nama'],     'inline' => true],
                        ['name' => 'Kelas',   'value' => $siswa['kelas'],    'inline' => true],
                        ['name' => 'Pilihan', 'value' => $kandidat['nama'],  'inline' => true],
                        ['name' => 'IP',      'value' => $ipAddress,         'inline' => true],
                        ['name' => 'Device',  'value' => $deviceId ?: '-',   'inline' => true],
                    ],
                    'vote'
                );

                respond(true, 'Suara berhasil dicatat');
            } catch (Exception $e) {
                $db->rollBack();
                respond(false, 'Gagal menyimpan suara: ' . $e->getMessage());
            }
            break;

        case 'hasil':
            $stmt = $db->query(
                "SELECT k.id, k.nama, k.kelas, COUNT(s.id)::int AS suara
                 FROM kandidat k
                 LEFT JOIN suara s ON s.kandidat_id = k.id
                 GROUP BY k.id, k.nama, k.kelas
                 ORDER BY suara DESC, k.id ASC"
            );
            $data  = $stmt->fetchAll();
            $total = array_sum(array_column($data, 'suara'));

            respond(true, 'OK', [
                'total' => $total,
                'data'  => $data
            ]);
            break;

        case 'history':
            $stmt = $db->query(
                "SELECT 
                    s.id, s.nis,
                    sw.nama   AS nama_siswa,
                    sw.kelas  AS kelas_siswa,
                    k.nama    AS nama_kandidat,
                    k.kelas   AS kelas_kandidat,
                    s.ip_address, s.user_agent,
                    s.device_id, s.screen_res, s.timezone,
                    s.waktu
                 FROM suara s
                 JOIN siswa sw    ON sw.nis = s.nis
                 JOIN kandidat k  ON k.id = s.kandidat_id
                 ORDER BY s.waktu DESC"
            );
            respond(true, 'OK', ['history' => $stmt->fetchAll()]);
            break;

        case 'detail_kandidat':
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                respond(false, 'ID kandidat tidak valid');
            }

            $stmt = $db->prepare(
                "SELECT id, nama, kelas, visi, foto, misi, program, quote, ttl, motivasi
                 FROM kandidat WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch();

            if (!$data) {
                respond(false, 'Kandidat tidak ditemukan');
            }

            respond(true, 'OK', ['kandidat' => $data]);
            break;

        case 'update_kandidat':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                respond(false, 'ID kandidat tidak valid');
            }

            $nama     = trim($_POST['nama']     ?? '');
            $kelas    = trim($_POST['kelas']    ?? '');
            $visi     = trim($_POST['visi']     ?? '');
            $misi     = trim($_POST['misi']     ?? '');
            $program  = trim($_POST['program']  ?? '');
            $quote    = trim($_POST['quote']    ?? '');
            $ttl      = trim($_POST['ttl']      ?? '');
            $motivasi = trim($_POST['motivasi'] ?? '');

            if (!$nama || !$kelas) {
                respond(false, 'Nama dan kelas wajib diisi');
            }

            $stmt = $db->prepare(
                "UPDATE kandidat 
                 SET nama = :nama, kelas = :kelas, visi = :visi,
                     misi = :misi, program = :program, quote = :quote,
                     ttl = :ttl, motivasi = :motivasi
                 WHERE id = :id"
            );
            $stmt->execute([
                ':nama'     => $nama,
                ':kelas'    => $kelas,
                ':visi'     => $visi,
                ':misi'     => $misi,
                ':program'  => $program,
                ':quote'    => $quote,
                ':ttl'      => $ttl,
                ':motivasi' => $motivasi,
                ':id'       => $id
            ]);

            respond(true, 'Profil kandidat berhasil disimpan');
            break;

        case 'tambah_kandidat':
            $nama  = trim($_POST['nama']  ?? '');
            $kelas = trim($_POST['kelas'] ?? '');
            $visi  = trim($_POST['visi']  ?? '');

            if (!$nama || !$kelas) {
                respond(false, 'Nama dan Kelas wajib diisi');
            }

            $stmt = $db->prepare(
                "INSERT INTO kandidat (nama, kelas, visi)
                 VALUES (:nama, :kelas, :visi)
                 RETURNING id"
            );
            $stmt->execute([
                ':nama'  => $nama,
                ':kelas' => $kelas,
                ':visi'  => $visi
            ]);
            $new = $stmt->fetch();

            respond(true, 'Kandidat berhasil ditambahkan', [
                'id' => $new['id']
            ]);
            break;

        case 'hapus_kandidat':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                respond(false, 'ID kandidat tidak valid');
            }

            $stmt = $db->prepare("SELECT COUNT(*)::int AS c FROM suara WHERE kandidat_id = :id");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();

            if ($row['c'] > 0) {
                respond(false, 'Kandidat sudah memiliki ' . $row['c'] . ' suara, tidak bisa dihapus');
            }

            $stmt = $db->prepare("SELECT foto FROM kandidat WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $k = $stmt->fetch();

            if (!$k) {
                respond(false, 'Kandidat tidak ditemukan');
            }

            if ($k['foto'] && file_exists(__DIR__ . '/../public/' . $k['foto'])) {
                @unlink(__DIR__ . '/../public/' . $k['foto']);
            }

            $stmt = $db->prepare("DELETE FROM kandidat WHERE id = :id");
            $stmt->execute([':id' => $id]);

            respond(true, 'Kandidat berhasil dihapus');
            break;

        case 'upload_foto':
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                respond(false, 'ID kandidat tidak valid');
            }

            if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                respond(false, 'File foto tidak valid');
            }

            $file = $_FILES['foto'];
            $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed)) {
                respond(false, 'Format harus JPG, PNG, atau WEBP');
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                respond(false, 'Ukuran foto maksimal 2 MB');
            }

            $uploadDir = __DIR__ . '/../public/uploads/kandidat/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $filename = 'kandidat_' . $id . '_' . time() . '.' . $ext;
            $filepath = $uploadDir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                respond(false, 'Gagal menyimpan file');
            }

            $relativePath = 'uploads/kandidat/' . $filename;

            $stmt = $db->prepare("SELECT foto FROM kandidat WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $old = $stmt->fetch();
            if ($old && $old['foto'] && file_exists(__DIR__ . '/../public/' . $old['foto'])) {
                @unlink(__DIR__ . '/../public/' . $old['foto']);
            }

            $stmt = $db->prepare("UPDATE kandidat SET foto = :foto WHERE id = :id");
            $stmt->execute([':foto' => $relativePath, ':id' => $id]);

            respond(true, 'Foto berhasil diupload', ['foto' => $relativePath]);
            break;

        case 'pengaturan':
            $stmt = $db->query(
                "SELECT key, value FROM pengaturan 
                 WHERE key IN ('voting_mulai', 'voting_selesai')"
            );
            $config = [];
            foreach ($stmt->fetchAll() as $r) {
                $config[$r['key']] = $r['value'];
            }

            $mulai   = $config['voting_mulai']   ?? null;
            $selesai = $config['voting_selesai'] ?? null;
            $now     = new DateTime();
            $status  = 'aktif';

            if ($mulai && $selesai) {
                if ($now < new DateTime($mulai))      $status = 'belum_mulai';
                elseif ($now > new DateTime($selesai)) $status = 'selesai';
            }

            respond(true, 'OK', [
                'mulai'       => $mulai,
                'selesai'     => $selesai,
                'status'      => $status,
                'server_time' => $now->format('Y-m-d H:i:s')
            ]);
            break;

        case 'set_pengaturan':
            $mulai   = trim($_POST['mulai']   ?? '');
            $selesai = trim($_POST['selesai'] ?? '');

            if (!$mulai || !$selesai) {
                respond(false, 'Waktu mulai dan selesai wajib diisi');
            }

            $stmt = $db->prepare(
                "UPDATE pengaturan SET value = :v, updated_at = NOW() WHERE key = :k"
            );
            $stmt->execute([':v' => $mulai,   ':k' => 'voting_mulai']);
            $stmt->execute([':v' => $selesai, ':k' => 'voting_selesai']);

            respond(true, 'Pengaturan waktu berhasil disimpan');
            break;

        case 'login_admin':
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (!$username || !$password) {
                respond(false, 'Username dan password wajib diisi');
            }

            $stmt = $db->prepare(
                "SELECT id, username, password_hash, nama FROM admin WHERE username = :u"
            );
            $stmt->execute([':u' => $username]);
            $admin = $stmt->fetch();

            if (!$admin || !password_verify($password, $admin['password_hash'])) {
                respond(false, 'Username atau password salah');
            }

            $_SESSION['admin_id']   = $admin['id'];
            $_SESSION['admin_user'] = $admin['username'];
            $_SESSION['admin_nama'] = $admin['nama'] ?: $admin['username'];

            respond(true, 'Login berhasil', [
                'nama' => $_SESSION['admin_nama']
            ]);
            break;

        case 'logout_admin':
            unset($_SESSION['admin_id']);
            unset($_SESSION['admin_user']);
            unset($_SESSION['admin_nama']);
            respond(true, 'Logout berhasil');
            break;

        case 'check_admin':
            if (isset($_SESSION['admin_id'])) {
                respond(true, 'OK', [
                    'nama'     => $_SESSION['admin_nama'],
                    'username' => $_SESSION['admin_user']
                ]);
            }
            respond(false, 'Belum login');
            break;

        case 'list_siswa':
            $stmt = $db->query(
                "SELECT nis, nama, kelas, sudah_memilih 
                 FROM siswa 
                 ORDER BY nis ASC"
            );
            respond(true, 'OK', ['siswa' => $stmt->fetchAll()]);
            break;

        case 'tambah_siswa':
            $nis   = trim($_POST['nis']   ?? '');
            $nama  = trim($_POST['nama']  ?? '');
            $kelas = trim($_POST['kelas'] ?? '');

            if (!$nis || !$nama) {
                respond(false, 'NIS dan Nama wajib diisi');
            }

            if (!preg_match('/^[A-Za-z0-9]+$/', $nis)) {
                respond(false, 'NIS hanya boleh huruf dan angka');
            }

            $stmt = $db->prepare("SELECT 1 FROM siswa WHERE nis = :nis");
            $stmt->execute([':nis' => $nis]);
            if ($stmt->fetch()) {
                respond(false, 'NIS sudah terdaftar');
            }

            $stmt = $db->prepare(
                "INSERT INTO siswa (nis, nama, kelas) VALUES (:nis, :nama, :kelas)"
            );
            $stmt->execute([
                ':nis'   => $nis,
                ':nama'  => $nama,
                ':kelas' => $kelas
            ]);

            respond(true, 'Siswa berhasil ditambahkan');
            break;

        case 'update_siswa':
            $nis   = trim($_POST['nis']   ?? '');
            $nama  = trim($_POST['nama']  ?? '');
            $kelas = trim($_POST['kelas'] ?? '');

            if (!$nis || !$nama) {
                respond(false, 'NIS dan Nama wajib diisi');
            }

            $stmt = $db->prepare(
                "UPDATE siswa SET nama = :nama, kelas = :kelas WHERE nis = :nis"
            );
            $stmt->execute([
                ':nama'  => $nama,
                ':kelas' => $kelas,
                ':nis'   => $nis
            ]);

            if ($stmt->rowCount() === 0) {
                respond(false, 'Siswa tidak ditemukan');
            }

            respond(true, 'Data siswa berhasil diperbarui');
            break;

        case 'hapus_siswa':
            $nis = trim($_POST['nis'] ?? '');

            if (!$nis) {
                respond(false, 'NIS wajib diisi');
            }

            $stmt = $db->prepare("SELECT 1 FROM suara WHERE nis = :nis");
            $stmt->execute([':nis' => $nis]);
            if ($stmt->fetch()) {
                respond(false, 'Siswa sudah memilih, tidak bisa dihapus');
            }

            $stmt = $db->prepare("DELETE FROM siswa WHERE nis = :nis");
            $stmt->execute([':nis' => $nis]);

            if ($stmt->rowCount() === 0) {
                respond(false, 'Siswa tidak ditemukan');
            }

            respond(true, 'Siswa berhasil dihapus');
            break;
                    case 'reset_vote_siswa':
            $nis = trim($_POST['nis'] ?? '');

            if (!$nis) {
                respond(false, 'NIS wajib diisi');
            }

            // Cek siswa ada
            $stmt = $db->prepare("SELECT 1 FROM siswa WHERE nis = :nis");
            $stmt->execute([':nis' => $nis]);
            if (!$stmt->fetch()) {
                respond(false, 'Siswa tidak ditemukan');
            }

            // Cek apakah siswa sudah vote
            $stmt = $db->prepare("SELECT id FROM suara WHERE nis = :nis");
            $stmt->execute([':nis' => $nis]);
            if (!$stmt->fetch()) {
                respond(false, 'Siswa ini belum memilih');
            }

            // Hapus vote + reset flag
            $db->beginTransaction();
            try {
                $stmt = $db->prepare("DELETE FROM suara WHERE nis = :nis");
                $stmt->execute([':nis' => $nis]);

                $stmt = $db->prepare("UPDATE siswa SET sudah_memilih = FALSE WHERE nis = :nis");
                $stmt->execute([':nis' => $nis]);

                $db->commit();
                respond(true, 'Vote berhasil dihapus. Siswa bisa memilih ulang.');
            } catch (Exception $e) {
                $db->rollBack();
                respond(false, 'Gagal menghapus vote: ' . $e->getMessage());
            }
            break;
                // ============================================
        // WEBHOOK MANAGEMENT
        // ============================================
        case 'list_webhook':
            $stmt = $db->query(
                "SELECT id, nama, deskripsi, url, aktif, 
                        created_at, updated_at
                 FROM discord_webhook 
                 ORDER BY id ASC"
            );
            $data = $stmt->fetchAll();
            
            // Sembunyikan sebagian URL untuk keamanan
            foreach ($data as &$row) {
                $url = $row['url'];
                if (strlen($url) > 60) {
                    $row['url_preview'] = substr($url, 0, 45) . '...' . substr($url, -10);
                } else {
                    $row['url_preview'] = $url;
                }
            }
            
            respond(true, 'OK', ['webhooks' => $data]);
            break;

        case 'tambah_webhook':
            $nama      = trim($_POST['nama'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $url       = trim($_POST['url'] ?? '');

            if (!$nama || !$url) {
                respond(false, 'Nama dan URL wajib diisi');
            }

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                respond(false, 'URL tidak valid');
            }

            if (strpos($url, 'discord.com/api/webhooks/') === false) {
                respond(false, 'URL harus dari Discord webhook');
            }

            // Cek duplikat nama
            $stmt = $db->prepare("SELECT 1 FROM discord_webhook WHERE nama = :nama");
            $stmt->execute([':nama' => $nama]);
            if ($stmt->fetch()) {
                respond(false, 'Nama webhook sudah dipakai');
            }

            $stmt = $db->prepare(
                "INSERT INTO discord_webhook (nama, deskripsi, url, aktif)
                 VALUES (:nama, :deskripsi, :url, TRUE)
                 RETURNING id"
            );
            $stmt->execute([
                ':nama'      => $nama,
                ':deskripsi' => $deskripsi,
                ':url'       => $url
            ]);
            $new = $stmt->fetch();

            respond(true, 'Webhook berhasil ditambahkan', ['id' => $new['id']]);
            break;

        case 'update_webhook':
            $id        = (int)($_POST['id'] ?? 0);
            $nama      = trim($_POST['nama'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $url       = trim($_POST['url'] ?? '');

            if ($id <= 0) {
                respond(false, 'ID tidak valid');
            }

            if (!$nama || !$url) {
                respond(false, 'Nama dan URL wajib diisi');
            }

            if (strpos($url, 'discord.com/api/webhooks/') === false) {
                respond(false, 'URL harus dari Discord webhook');
            }

            // Cek nama duplikat (selain dirinya sendiri)
            $stmt = $db->prepare(
                "SELECT 1 FROM discord_webhook WHERE nama = :nama AND id != :id"
            );
            $stmt->execute([':nama' => $nama, ':id' => $id]);
            if ($stmt->fetch()) {
                respond(false, 'Nama webhook sudah dipakai');
            }

            $stmt = $db->prepare(
                "UPDATE discord_webhook 
                 SET nama = :nama, deskripsi = :deskripsi, 
                     url = :url, updated_at = NOW()
                 WHERE id = :id"
            );
            $stmt->execute([
                ':nama'      => $nama,
                ':deskripsi' => $deskripsi,
                ':url'       => $url,
                ':id'        => $id
            ]);

            respond(true, 'Webhook berhasil diperbarui');
            break;

        case 'toggle_webhook':
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                respond(false, 'ID tidak valid');
            }

            $stmt = $db->prepare(
                "UPDATE discord_webhook 
                 SET aktif = NOT aktif, updated_at = NOW()
                 WHERE id = :id
                 RETURNING aktif"
            );
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();

            if (!$row) {
                respond(false, 'Webhook tidak ditemukan');
            }

            $status = $row['aktif'] ? 'diaktifkan' : 'dinonaktifkan';
            respond(true, "Webhook berhasil $status");
            break;

        case 'hapus_webhook':
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                respond(false, 'ID tidak valid');
            }

            $stmt = $db->prepare("DELETE FROM discord_webhook WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                respond(false, 'Webhook tidak ditemukan');
            }

            respond(true, 'Webhook berhasil dihapus');
            break;

        case 'test_webhook':
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                respond(false, 'ID tidak valid');
            }

            $stmt = $db->prepare(
                "SELECT nama, url FROM discord_webhook WHERE id = :id"
            );
            $stmt->execute([':id' => $id]);
            $wh = $stmt->fetch();

            if (!$wh) {
                respond(false, 'Webhook tidak ditemukan');
            }

            // Kirim test message
            $payload = json_encode([
                'username' => 'E-Voting Bot',
                'embeds' => [[
                    'title'       => '🔔 Test Webhook',
                    'description' => "Webhook **{$wh['nama']}** berfungsi dengan baik!",
                    'color'       => 0x2e7d32,
                    'timestamp'   => date('c'),
                    'fields' => [
                        ['name' => 'Nama',  'value' => $wh['nama'], 'inline' => true],
                        ['name' => 'Waktu', 'value' => date('d M Y H:i:s'), 'inline' => true],
                        ['name' => 'Status', 'value' => '✅ Aktif', 'inline' => false],
                    ],
                    'footer' => ['text' => 'E-Voting System']
                ]]
            ], JSON_UNESCAPED_UNICODE);

            $ch = curl_init($wh['url']);
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
                respond(false, 'Koneksi gagal: ' . $curlErr);
            }

            if ($httpCode >= 200 && $httpCode < 300) {
                respond(true, "✅ Berhasil! Cek Discord channel. (HTTP $httpCode)");
            } else {
                respond(false, "Gagal kirim. HTTP Code: $httpCode");
            }
            break;

        // ============================================
        // GANTI PASSWORD (yang sudah ada)
        // ============================================
        case 'ganti_password':
            if (!isset($_SESSION['admin_id'])) {
                respond(false, 'Sesi habis, silakan login ulang');
            }

            $lama    = trim($_POST['password_lama'] ?? '');
            $baru    = trim($_POST['password_baru'] ?? '');
            $konfirm = trim($_POST['konfirmasi']    ?? '');

            if (!$lama || !$baru || !$konfirm) {
                respond(false, 'Semua field wajib diisi');
            }

            if ($baru !== $konfirm) {
                respond(false, 'Konfirmasi password tidak cocok');
            }

            if (strlen($baru) < 6) {
                respond(false, 'Password baru minimal 6 karakter');
            }

            if ($lama === $baru) {
                respond(false, 'Password baru harus berbeda dari password lama');
            }

            $stmt = $db->prepare(
                "SELECT password_hash FROM admin WHERE id = :id"
            );
            $stmt->execute([':id' => $_SESSION['admin_id']]);
            $admin = $stmt->fetch();

            if (!$admin) {
                respond(false, 'Admin tidak ditemukan');
            }

            if (!password_verify($lama, $admin['password_hash'])) {
                respond(false, 'Password lama salah');
            }

            $hashBaru = password_hash($baru, PASSWORD_DEFAULT);

            $stmt = $db->prepare(
                "UPDATE admin SET password_hash = :h WHERE id = :id"
            );
            $stmt->execute([
                ':h'  => $hashBaru,
                ':id' => $_SESSION['admin_id']
            ]);

            respond(true, 'Password berhasil diganti');
            break;

        case 'session':
            if (isset($_SESSION['nis'])) {
                respond(true, 'OK', [
                    'nama'     => $_SESSION['nama'],
                    'kandidat' => getKandidat($db)
                ]);
            }
            respond(false, 'Belum login');
            break;

        case 'logout':
            session_destroy();
            respond(true, 'Logout berhasil');
            break;

        default:
            respond(false, 'Action tidak dikenal: ' . $action);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}

function getKandidat(PDO $db): array {
    $stmt = $db->query(
        "SELECT id, nama, kelas, visi, foto FROM kandidat ORDER BY id ASC"
    );
    return $stmt->fetchAll();
}

function getClientIP(): string {
    $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            if (strpos($ip, ',') !== false) $ip = trim(explode(',', $ip)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

function respond(bool $success, string $message = '', array $extra = []): void {
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    exit;
}