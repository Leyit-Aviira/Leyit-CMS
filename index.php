<?php
// index.php
require_once 'config.php';

if (file_exists('install.txt')) {
    die("CMS nie jest jeszcze zainstalowany. Uruchom instalator (install.php).");
}

$page = isset($_GET['page']) ? $_GET['page'] : 'home';
$pageFile = 'pages/' . basename($page) . '.php';
if (!file_exists($pageFile)) {
    $pageFile = 'pages/404.php';
}

ob_start();
include $pageFile;
$content = ob_get_clean();

include 'templates/' . TEMPLATE_FOLDER . '/layout.php';
?>
