<?php
/**
 * ============================================
 * EXPORT DATA VOTING KE CSV
 * ============================================
 * 
 * Cara pakai:
 *   export.php?format=hasil     → Hasil perolehan suara per kandidat
 *   export.php?format=riwayat   → Riwayat voting lengkap (dengan IP + waktu)
 * 
 * Hanya bisa diakses oleh admin yang sudah login.
 * ============================================
 */

session_start();

// ============================================
// Cek login admin
// ============================================
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// ============================================
// Koneksi database
// ============================================
require_once __DIR__ . '/../config/database.php';
$db = getDB();

// ============================================
// Tentukan format dari query string
// ============================================
$format = $_GET['format'] ?? 'hasil';

// Validasi format
if (!in_array($format, ['hasil', 'riwayat'])) {
    $format = 'hasil';
}

// ============================================
// Nama file dengan timestamp
// ============================================
date_default_timezone_set('Asia/Jakarta');
$tanggal = date('Y-m-d_H-i-s');

if ($format === 'riwayat') {
    $filename = "riwayat_voting_{$tanggal}.csv";
} else {
    $filename = "hasil_voting_{$tanggal}.csv";
}

// ============================================
// Header HTTP untuk download file CSV
// ============================================
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

// ============================================
// Buka output stream ke browser
// ============================================
$output = fopen('php://output', 'w');

// Tambahkan BOM UTF-8 supaya Excel bisa baca karakter Indonesia
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

// ============================================
// FORMAT: HASIL (perolehan suara per kandidat)
// ============================================
if ($format === 'hasil') {

    // Query data hasil
    $stmt = $db->query(
        "SELECT 
            k.id, 
            k.nama, 
            k.kelas, 
            k.visi, 
            COUNT(s.id)::int AS suara
         FROM kandidat k
         LEFT JOIN suara s ON s.kandidat_id = k.id
         GROUP BY k.id, k.nama, k.kelas, k.visi
         ORDER BY suara DESC, k.id ASC"
    );
    $data  = $stmt->fetchAll();
    $total = array_sum(array_column($data, 'suara'));

    // ============ Header Laporan ============
    fputcsv($output, ['HASIL PEMILIHAN KETUA OSIS']);
    fputcsv($output, ['Diekspor:', date('d F Y, H:i:s') . ' WIB']);
    fputcsv($output, ['Total Suara:', $total]);
    fputcsv($output, ['Total Kandidat:', count($data)]);
    fputcsv($output, []); // baris kosong

    // ============ Header Tabel ============
    fputcsv($output, [
        'No',
        'Nama Kandidat',
        'Kelas',
        'Visi',
        'Jumlah Suara',
        'Persentase'
    ]);

    // ============ Data ============
    $no = 1;
    foreach ($data as $k) {
        $persen = $total > 0 ? round(($k['suara'] / $total) * 100, 2) : 0;

        fputcsv($output, [
            $no++,
            $k['nama'],
            $k['kelas'],
            $k['visi'] ?: '-',
            $k['suara'],
            $persen . '%'
        ]);
    }

    // ============ Baris Total ============
    fputcsv($output, []);
    fputcsv($output, [
        '',
        '',
        '',
        'TOTAL',
        $total,
        '100%'
    ]);

    // ============ Footer ============
    fputcsv($output, []);
    fputcsv($output, ['Dicetak oleh: Panitia Pemilihan OSIS']);
}

// ============================================
// FORMAT: RIWAYAT (detail per suara)
// ============================================
elseif ($format === 'riwayat') {

    // Query data riwayat
    $stmt = $db->query(
        "SELECT 
            s.id,
            s.nis,
            sw.nama   AS nama_siswa,
            sw.kelas  AS kelas_siswa,
            k.nama    AS nama_kandidat,
            k.kelas   AS kelas_kandidat,
            s.ip_address,
            s.user_agent,
            s.waktu
         FROM suara s
         JOIN siswa sw    ON sw.nis = s.nis
         JOIN kandidat k  ON k.id = s.kandidat_id
         ORDER BY s.waktu ASC"
    );
    $data = $stmt->fetchAll();

    // Hitung IP unik
    $ips = [];
    foreach ($data as $d) {
        if ($d['ip_address']) $ips[$d['ip_address']] = true;
    }

    // ============ Header Laporan ============
    fputcsv($output, ['RIWAYAT VOTING PEMILIHAN KETUA OSIS']);
    fputcsv($output, ['Diekspor:', date('d F Y, H:i:s') . ' WIB']);
    fputcsv($output, ['Total Vote:', count($data)]);
    fputcsv($output, ['Total IP Unik:', count($ips)]);
    fputcsv($output, []); // baris kosong

    // ============ Header Tabel ============
    fputcsv($output, [
        'No',
        'NIS',
        'Nama Siswa',
        'Kelas Siswa',
        'Pilihan Kandidat',
        'Kelas Kandidat',
        'IP Address',
        'Waktu Submit',
        'User Agent'
    ]);

    // ============ Data ============
    $no = 1;
    foreach ($data as $h) {
        // Format waktu: 10/10/2026 14:30:15
        $waktu = date('d/m/Y H:i:s', strtotime($h['waktu']));

        fputcsv($output, [
            $no++,
            $h['nis'],
            $h['nama_siswa'],
            $h['kelas_siswa'] ?: '-',
            $h['nama_kandidat'],
            $h['kelas_kandidat'] ?: '-',
            $h['ip_address'] ?: '-',
            $waktu,
            $h['user_agent'] ?: '-'
        ]);
    }

    // ============ Footer ============
    fputcsv($output, []);
    fputcsv($output, ['Dicetak oleh: Panitia Pemilihan OSIS']);
}

// ============================================
// Tutup output stream
// ============================================
fclose($output);
exit;