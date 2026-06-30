<?php
// ============================================
// DATABASE CONFIGURATION
// Update these with your Hostinger DB credentials
// Find them in hPanel → Databases → MySQL Databases
// ============================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'guide');     // Your database name
define('DB_USER', 'root');       // Your database user
define('DB_PASS', 'root');    // Your database password

define('SITE_NAME', 'MNFST Studio');
define('SITE_URL', 'http://localhost'); // Change to your domain
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Admin session name
define('SESSION_NAME', 'mnfst_admin_session');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}