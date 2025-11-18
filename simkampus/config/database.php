<?php
/**
 * File Konfigurasi Database
 * Koneksi menggunakan PDO (PHP Data Objects)
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'simkampus1');
define('DB_USER', 'root');
define('DB_PASS', '');

// Fungsi untuk membuat koneksi database
function getConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        // Log error ke file atau tampilkan pesan user-friendly
        die("Koneksi database gagal: " . $e->getMessage());
    }
}

// Buat koneksi global
$pdo = getConnection();
?>