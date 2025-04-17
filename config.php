<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!defined('SKIP_INSTALL_CHECK') && file_exists('install.txt')) {
    $visitor_ip = $_SERVER['REMOTE_ADDR'];
    $allowed_ip = trim(file_get_contents('install.txt'));
    if ($visitor_ip !== $allowed_ip) {
        die("CMS is not installed. Please run install.php to install the CMS. Your IP is: " . $visitor_ip . ". Please add this IP to install.txt to proceed.");
    }
}
define('CMS_INSTALLED', true);
define('TEMPLATE_FOLDER', 'default');
define('DB_HOST', '137.74.204.177');
define('DB_NAME', 'dbname');
define('DB_USER', 'dbuser');
define('DB_PASS', 'dbpass');
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}
?>
