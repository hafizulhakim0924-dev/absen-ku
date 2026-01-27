<?php
/**
 * Initialization File
 * Include file ini di awal setiap halaman PHP untuk memuat konfigurasi
 * 
 * Usage:
 * require_once __DIR__ . '/init.php';
 */

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load application configuration
if (file_exists(__DIR__ . '/app_config.php')) {
    require_once __DIR__ . '/app_config.php';
} else {
    // Fallback jika app_config.php tidak ada
    date_default_timezone_set('Asia/Jakarta');
    define('BASE_PATH', __DIR__);
    define('UPLOAD_DIR', BASE_PATH . '/upload/');
    define('CONFIG_FILE', BASE_PATH . '/config.json');
    define('SCHEDULES_FILE', BASE_PATH . '/schedules.json');
    define('IZIN_FILE', BASE_PATH . '/izin.json');
    define('BASE_URL', 'https://ypi-khairaummah.sch.id/absen');
    
    // Create upload directory if not exists
    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
}

// Load database configuration if needed
if (file_exists(__DIR__ . '/db_config.php')) {
    require_once __DIR__ . '/db_config.php';
}

/**
 * Helper function to load config.json
 */
if (!function_exists('loadConfig')) {
    function loadConfig() {
        if (!file_exists(CONFIG_FILE)) {
            return null;
        }
        $content = file_get_contents(CONFIG_FILE);
        return json_decode($content, true);
    }
}

/**
 * Helper function to save config.json
 */
if (!function_exists('saveConfig')) {
    function saveConfig($data) {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return file_put_contents(CONFIG_FILE, $jsonContent) !== false;
    }
}

?>

