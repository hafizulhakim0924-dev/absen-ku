<?php
/**
 * Application Configuration File
 * File ini berisi konfigurasi aplikasi secara keseluruhan
 */

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Base Path Configuration
define('BASE_PATH', __DIR__);
define('UPLOAD_DIR', BASE_PATH . '/upload/');
define('CONFIG_FILE', BASE_PATH . '/config.json');
define('SCHEDULES_FILE', BASE_PATH . '/schedules.json');
define('IZIN_FILE', BASE_PATH . '/izin.json');

// URL Configuration (sesuaikan dengan domain Anda)
// Untuk development lokal, gunakan: http://localhost/absen-ku/absen
// Untuk production, gunakan: https://ypi-khairaummah.sch.id/absen
define('BASE_URL', 'https://ypi-khairaummah.sch.id/absen');
// define('BASE_URL', 'http://localhost/absen-ku/absen'); // Uncomment untuk development lokal

// Application Settings
define('APP_NAME', 'Sistem Absensi Karyawan');
define('APP_VERSION', '1.0.0');
define('APP_ORGANIZATION', 'YPI Khairaummah School');

// File Upload Settings
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['json', 'xlsx', 'xls']);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 jika menggunakan HTTPS

// Error Reporting (set ke 0 untuk production)
error_reporting(E_ALL);
ini_set('display_errors', 1); // Set ke 0 untuk production

// Create necessary directories
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

/**
 * Fungsi untuk memuat config.json
 * @return array|null Data konfigurasi atau null jika gagal
 */
function loadAppConfig() {
    if (!file_exists(CONFIG_FILE)) {
        error_log("Config file not found: " . CONFIG_FILE);
        return null;
    }
    
    $content = file_get_contents(CONFIG_FILE);
    $config = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error parsing config.json: " . json_last_error_msg());
        return null;
    }
    
    return $config;
}

/**
 * Fungsi untuk menyimpan config.json
 * @param array $data Data konfigurasi
 * @return bool True jika berhasil, false jika gagal
 */
function saveAppConfig($data) {
    $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents(CONFIG_FILE, $jsonContent) !== false;
}

/**
 * Fungsi untuk mendapatkan URL lengkap
 * @param string $path Path relatif
 * @return string URL lengkap
 */
function getUrl($path = '') {
    $base = rtrim(BASE_URL, '/');
    $path = ltrim($path, '/');
    return $base . ($path ? '/' . $path : '');
}

/**
 * Fungsi untuk mendapatkan path file
 * @param string $file Nama file
 * @return string Path lengkap file
 */
function getFilePath($file) {
    return BASE_PATH . '/' . ltrim($file, '/');
}

// Include database config jika diperlukan
if (file_exists(__DIR__ . '/db_config.php')) {
    require_once __DIR__ . '/db_config.php';
}
?>

