<?php
/**
 * Database Configuration - Apotek Ananda Jadimulya
 * 
 * Koneksi ke MySQL menggunakan PDO dengan prepared statements
 * untuk mencegah SQL Injection.
 */

// =============================================
// KONFIGURASI DATABASE
// =============================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'apotek_ananda');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// =============================================
// KONFIGURASI APLIKASI
// =============================================
define('APP_NAME', 'Apotek Ananda Jadimulya');
define('APP_VERSION', '1.0.0');
define('BASE_URL', '/ApotekAnanda');

// Path untuk upload file
define('UPLOAD_DIR', __DIR__ . '/../../uploads/');
define('EXPORT_DIR', __DIR__ . '/../../exports/');
define('BUKTI_PEMBAYARAN_DIR', UPLOAD_DIR . 'bukti_pembayaran/');

// =============================================
// KONFIGURASI KEAMANAN
// =============================================
define('MAX_LOGIN_ATTEMPTS', 5);          // Maks percobaan login
define('LOGIN_LOCKOUT_TIME', 900);        // Lockout 15 menit (dalam detik)
define('SESSION_LIFETIME', 3600);         // Session 1 jam
define('CAPTCHA_ENABLED', true);          // Aktifkan CAPTCHA
define('CSRF_ENABLED', true);             // Aktifkan CSRF protection

/**
 * Membuat koneksi PDO ke database MySQL
 * 
 * @return PDO Instance koneksi database
 * @throws PDOException Jika koneksi gagal
 */
function getDBConnection(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,    // Gunakan native prepared statements
            PDO::ATTR_STRINGIFY_FETCHES  => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Jangan tampilkan detail error di production
            error_log("Database connection failed: " . $e->getMessage());
            die("Koneksi database gagal. Silakan hubungi administrator.");
        }
    }
    
    return $pdo;
}
