<?php
/**
 * Database Configuration — hagz_clinic_ai
 * Uses PDO with UTF-8 support for Arabic text
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'hagz_clinic_ai');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log error and show generic message
            error_log("Database Connection Error: " . $e->getMessage());
            die(json_encode(['success' => false, 'message' => 'خطأ في الاتصال بقاعدة البيانات. يرجى المحاولة لاحقاً.']));
        }
    }
    return $pdo;
}
