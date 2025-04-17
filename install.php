<?php
// install.php

// Definiujemy stałą, aby pominąć sprawdzanie instalatora w config.php.
define('SKIP_INSTALL_CHECK', true);
session_start();

$visitor_ip = $_SERVER['REMOTE_ADDR'];

// Jeśli flaga instalacji (installed.flag) istnieje – CMS został już zainstalowany.
if (file_exists('installed.flag')) {
    die("CMS is already installed.");
}

// Sprawdzenie istnienia pliku install.txt.
if (!file_exists('install.txt')) {
    die("The installation file (install.txt) is missing. Please create it with your IP.");
}

$allowed_ip = trim(file_get_contents('install.txt'));
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;

$error = "";
$success = "";

if ($step == 1) {
    // Step 1: Formularz podania danych bazy danych.
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $db_host = trim($_POST['db_host']);
        $db_name = trim($_POST['db_name']);
        $db_user = trim($_POST['db_user']);
        $db_pass = trim($_POST['db_pass']);

        try {
            $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8";
            $testPdo = new PDO($dsn, $db_user, $db_pass);
            $testPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Zapamiętujemy dane w sesji
            $_SESSION['db_host'] = $db_host;
            $_SESSION['db_name'] = $db_name;
            $_SESSION['db_user'] = $db_user;
            $_SESSION['db_pass'] = $db_pass;

            // Generujemy dynamicznie nowy config.php z podanymi danymi
            $configContent = "<?php\n";
            $configContent .= "if (session_status() == PHP_SESSION_NONE) { session_start(); }\n";
            $configContent .= "if (!defined('SKIP_INSTALL_CHECK') && file_exists('install.txt')) {\n";
            $configContent .= "    \$visitor_ip = \$_SERVER['REMOTE_ADDR'];\n";
            $configContent .= "    \$allowed_ip = trim(file_get_contents('install.txt'));\n";
            $configContent .= "    if (\$visitor_ip !== \$allowed_ip) {\n";
            $configContent .= "        die(\"CMS is not installed. Please run install.php to install the CMS. Your IP is: \" . \$visitor_ip . \". Please add this IP to install.txt to proceed.\");\n";
            $configContent .= "    }\n";
            $configContent .= "}\n";
            $configContent .= "define('CMS_INSTALLED', true);\n";
            $configContent .= "define('TEMPLATE_FOLDER', 'default');\n";
            $configContent .= "define('DB_HOST', '" . addslashes($db_host) . "');\n";
            $configContent .= "define('DB_NAME', '" . addslashes($db_name) . "');\n";
            $configContent .= "define('DB_USER', '" . addslashes($db_user) . "');\n";
            $configContent .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n";
            $configContent .= "try {\n";
            $configContent .= "    \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME, DB_USER, DB_PASS);\n";
            $configContent .= "    \$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
            $configContent .= "} catch (PDOException \$e) {\n";
            $configContent .= "    die(\"Database connection error: \" . \$e->getMessage());\n";
            $configContent .= "}\n";
            $configContent .= "?>\n";

            if (file_put_contents('config.php', $configContent) === false) {
                $error = "Could not write to config.php. Please check file permissions.";
            } else {
                header("Location: install.php?step=2");
                exit;
            }
        } catch (PDOException $ex) {
            $error = "Database connection failed: " . $ex->getMessage();
        }
    }
} elseif ($step == 2) {
    // Step 2: Tworzenie struktury bazy danych
    require_once 'config.php';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        try {
            $tables = [
                "CREATE TABLE IF NOT EXISTS users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    account_group INT NOT NULL DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
                "CREATE TABLE IF NOT EXISTS settings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    site_name VARCHAR(255) NOT NULL DEFAULT 'CMS - HP Domy i Garaże',
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
                "CREATE TABLE IF NOT EXISTS gallery_images (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    image_path VARCHAR(255) NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8",
                "CREATE TABLE IF NOT EXISTS pages (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    page_name VARCHAR(100) NOT NULL UNIQUE,
                    content TEXT NOT NULL,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
            ];
            foreach ($tables as $sql) {
                $pdo->exec($sql);
            }
            header("Location: install.php?step=3");
            exit;
        } catch (PDOException $ex) {
            $error = "Error creating database structure: " . $ex->getMessage();
        }
    }
} elseif ($step == 3) {
    // Step 3: Utworzenie konta administratora
    require_once 'config.php';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $admin_username         = trim($_POST['admin_username']);
        $admin_email            = trim($_POST['admin_email']);
        $admin_password         = $_POST['admin_password'];
        $admin_password_confirm = $_POST['admin_password_confirm'];
        if ($admin_password !== $admin_password_confirm) {
            $error = "Passwords do not match.";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, account_group) VALUES (?, ?, ?, ?)");
                $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
                $stmt->execute([$admin_username, $admin_email, $hashed_password, 3]);
                
                // Zapisujemy flagę instalacji – dzięki temu CMS wie, że został zainstalowany.
                file_put_contents('installed.flag', 'Installation complete');
                
                // Przenieś pliki instalacyjne do katalogu archiwum, aby użytkownik nie musiał ich usuwać ręcznie.
                $backupDir = 'install_backup';
                if (!is_dir($backupDir)) {
                    mkdir($backupDir, 0777, true);
                }
                if (file_exists('install.php')) {
                    rename('install.php', $backupDir . '/install.php');
                }
                if (file_exists('install.txt')) {
                    rename('install.txt', $backupDir . '/install.txt');
                }
                
                $success = "Admin account created successfully. Installation complete!";
            } catch (PDOException $ex) {
                $error = "Error creating admin account: " . $ex->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CMS Installation</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <?php if ($step == 1): ?>
        <div class="card">
            <div class="card-header">
                <h3>Step 1: Enter Database Credentials</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <p>Your IP is: <strong><?php echo htmlspecialchars($visitor_ip); ?></strong>. To install, please add this IP to <code>install.txt</code>.</p>
                <form method="post" action="install.php?step=1">
                    <div class="mb-3">
                        <label for="db_host" class="form-label">Database Host</label>
                        <input type="text" name="db_host" id="db_host" class="form-control" value="<?php echo isset($_POST['db_host']) ? htmlspecialchars($_POST['db_host']) : 'localhost'; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_name" class="form-label">Database Name</label>
                        <input type="text" name="db_name" id="db_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_user" class="form-label">Database User</label>
                        <input type="text" name="db_user" id="db_user" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_pass" class="form-label">Database Password</label>
                        <input type="password" name="db_pass" id="db_pass" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Next Step</button>
                </form>
            </div>
        </div>
    <?php elseif ($step == 2): ?>
        <div class="card">
            <div class="card-header">
                <h3>Step 2: Create Database Structure</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <p>Click the button below to create the required database tables.</p>
                <form method="post" action="install.php?step=2">
                    <button type="submit" class="btn btn-primary">Create Tables and Proceed</button>
                </form>
            </div>
        </div>
    <?php elseif ($step == 3): ?>
        <div class="card">
            <div class="card-header">
                <h3>Step 3: Create Admin Account</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <div class="alert alert-info">The installer files have been automatically moved to the <strong>install_backup</strong> folder. Please secure them accordingly.</div>
                <?php endif; ?>
                <?php if (!$success): ?>
                <form method="post" action="install.php?step=3">
                    <div class="mb-3">
                        <label for="admin_username" class="form-label">Admin Username</label>
                        <input type="text" name="admin_username" id="admin_username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="admin_email" class="form-label">Admin Email</label>
                        <input type="email" name="admin_email" id="admin_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="admin_password" class="form-label">Admin Password</label>
                        <input type="password" name="admin_password" id="admin_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="admin_password_confirm" class="form-label">Confirm Password</label>
                        <input type="password" name="admin_password_confirm" id="admin_password_confirm" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Admin Account</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">Invalid installation step.</div>
    <?php endif; ?>
</div>
<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
