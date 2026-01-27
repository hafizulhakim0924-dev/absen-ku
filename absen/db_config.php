<?php
/**
 * Database Configuration File
 * File ini berisi konfigurasi koneksi database
 * 
 * INSTRUKSI:
 * 1. Sesuaikan nilai-nilai di bawah ini dengan konfigurasi database Anda
 * 2. Pastikan file ini tidak di-commit ke repository publik (tambahkan ke .gitignore)
 */

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'ypikhair_admin');
define('DB_PASS', 'hakim123123123');
define('DB_NAME', 'ypikhair_datautama');
define('DB_CHARSET', 'utf8mb4');

/**
 * Fungsi untuk membuat koneksi database
 * @return mysqli|false Koneksi database atau false jika gagal
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return false;
        }
        
        // Set charset
        $conn->set_charset(DB_CHARSET);
    }
    
    return $conn;
}

/**
 * Fungsi untuk menutup koneksi database
 */
function closeDBConnection() {
    global $conn;
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

// Test koneksi saat file di-load (opsional, bisa di-comment jika tidak diperlukan)
// $test_conn = getDBConnection();
// if ($test_conn) {
//     error_log("Database connection successful");
// }
?>

