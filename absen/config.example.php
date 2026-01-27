<?php
/**
 * CONFIGURATION TEMPLATE
 * 
 * INSTRUKSI SETUP:
 * 1. Copy file ini dan buat file db_config.php (jika belum ada)
 * 2. Sesuaikan nilai-nilai di bawah ini dengan konfigurasi server Anda
 * 3. Pastikan file db_config.php tidak di-commit ke repository publik
 */

// ============================================
// DATABASE CONFIGURATION
// ============================================
// Sesuaikan dengan kredensial database Anda
define('DB_HOST', 'localhost');          // Host database (biasanya 'localhost')
define('DB_USER', 'your_username');      // Username database
define('DB_PASS', 'your_password');      // Password database
define('DB_NAME', 'your_database');     // Nama database
define('DB_CHARSET', 'utf8mb4');         // Charset (biasanya utf8mb4)

// ============================================
// APPLICATION CONFIGURATION
// ============================================
// Base URL aplikasi
// Untuk development lokal: 'http://localhost/absen-ku/absen'
// Untuk production: 'https://ypi-khairaummah.sch.id/absen'
define('BASE_URL', 'https://ypi-khairaummah.sch.id/absen');

// Timezone
date_default_timezone_set('Asia/Jakarta');

// ============================================
// FILE PATHS
// ============================================
define('BASE_PATH', __DIR__);
define('UPLOAD_DIR', BASE_PATH . '/upload/');
define('CONFIG_FILE', BASE_PATH . '/config.json');
define('SCHEDULES_FILE', BASE_PATH . '/schedules.json');
define('IZIN_FILE', BASE_PATH . '/izin.json');

// ============================================
// SECURITY SETTINGS
// ============================================
// Error Reporting (set ke 0 untuk production)
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set ke 0 untuk production

// Session Security
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set ke 1 jika menggunakan HTTPS

// ============================================
// UPLOAD SETTINGS
// ============================================
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['json', 'xlsx', 'xls']);

?>

