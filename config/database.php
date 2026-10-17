<?php
date_default_timezone_set('Asia/Jakarta');
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'EVOTE');
define('DB_USER', 'postgres');
define('DB_PASS', '123456'); // <-- ganti dengan password PostgreSQL Anda

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        DB_HOST, DB_PORT, DB_NAME
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Koneksi database gagal: ' . $e->getMessage()
        ]);
        exit;
    }
}