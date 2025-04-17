<?php
// admin.php
require_once 'config.php';

// Sprawdzenie czy użytkownik jest zalogowany i ma uprawnienia administratora (account_group == 3)
if (!isset($_SESSION['user']) || $_SESSION['user']['account_group'] != 3) {
    header("Location: index.php?page=login");
    exit;
}
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Administratora</title>
    <link rel="stylesheet" href="templates/<?php echo TEMPLATE_FOLDER; ?>/css/modern.css">
    <style>
        .admin-menu {background-color: #333; padding: 10px; margin-bottom: 20px;}
        .admin-menu a {color: #fff; margin-right: 15px; text-decoration: none; font-weight: bold;}
        .admin-content {padding: 20px; background-color: #1f1f1f; color: #fff; border-radius: 5px; }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-container">
            <div class="logo">
                <span class="logo-icon">&#127969;</span>
                <span class="logo-text">Panel Administratora</span>
            </div>
            <div class="auth-links">
                <span>Witaj, <?php echo htmlspecialchars($_SESSION['user']['username']); ?></span>
                <a href="index.php?page=logout">Wyloguj</a>
            </div>
        </div>
    </header>
    <div class="container" style="margin-top: 100px;">
        <div class="admin-menu">
            <a href="admin.php?tab=dashboard">Dashboard</a>
            <a href="admin.php?tab=settings">Ustawienia Strony</a>
            <a href="admin.php?tab=gallery">Galeria</a>
            <a href="admin.php?tab=pages">Treść Stron</a>
        </div>
        <div class="admin-content">
            <?php
            switch ($active_tab) {
                case 'settings':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['site_name'])) {
                        $newSiteName = trim($_POST['site_name']);
                        $stmt = $pdo->prepare("UPDATE settings SET site_name = ? WHERE id = 1");
                        $stmt->execute([$newSiteName]);
                        echo "<p style='color:green;'>Nazwa strony została zaktualizowana.</p>";
                    }
                    $stmt = $pdo->query("SELECT * FROM settings WHERE id = 1");
                    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <h2>Ustawienia Strony</h2>
                    <form method="POST" action="admin.php?tab=settings">
                        <label for="site_name">Nazwa strony:</label>
                        <input type="text" name="site_name" id="site_name" value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                        <button type="submit">Aktualizuj</button>
                    </form>
                    <?php
                    break;
                case 'gallery':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        if (isset($_POST['action']) && $_POST['action'] == 'add') {
                            $image_path = trim($_POST['image_path']);
                            $title = trim($_POST['title']);
                            $description = trim($_POST['description']);
                            $stmt = $pdo->prepare("INSERT INTO gallery_images (image_path, title, description) VALUES (?, ?, ?)");
                            $stmt->execute([$image_path, $title, $description]);
                            echo "<p style='color:green;'>Zdjęcie dodane.</p>";
                        }
                        if (isset($_GET['delete'])) {
                            $delete_id = intval($_GET['delete']);
                            $stmt = $pdo->prepare("DELETE FROM gallery_images WHERE id = ?");
                            $stmt->execute([$delete_id]);
                            echo "<p style='color:green;'>Zdjęcie usunięte.</p>";
                        }
                    }
                    ?>
                    <h2>Galeria – Zarządzanie zdjęciami</h2>
                    <h3>Dodaj nowe zdjęcie</h3>
                    <form method="POST" action="admin.php?tab=gallery">
                        <input type="hidden" name="action" value="add">
                        <label for="image_path">Ścieżka do zdjęcia:</label>
                        <input type="text" name="image_path" id="image_path" required placeholder="np. images/nowe.jpg">
                        
                        <label for="title">Tytuł:</label>
                        <input type="text" name="title" id="title" required>
                        
                        <label for="description">Opis:</label>
                        <textarea name="description" id="description"></textarea>
                        
                        <button type="submit">Dodaj zdjęcie</button>
                    </form>
                    <h3>Obecne zdjęcia w galerii</h3>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM gallery_images ORDER BY created_at DESC");
                    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($images):
                        echo "<ul>";
                        foreach ($images as $img) {
                            echo "<li>";
                            echo "<img src='" . htmlspecialchars($img['image_path']) . "' alt='" . htmlspecialchars($img['title']) . "' style='max-width:100px;'><br>";
                            echo "<strong>" . htmlspecialchars($img['title']) . "</strong> ";
                            echo "<a href='admin.php?tab=gallery&delete=" . $img['id'] . "' onclick=\"return confirm('Usunąć to zdjęcie?');\">[Usuń]</a>";
                            echo "</li>";
                        }
                        echo "</ul>";
                    else:
                        echo "<p>Brak zdjęć w galerii.</p>";
                    endif;
                    break;
                case 'pages':
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['page_name']) && isset($_POST['content'])) {
                        $page_name = trim($_POST['page_name']);
                        $content_post = trim($_POST['content']);
                        $stmt = $pdo->prepare("SELECT id FROM pages WHERE page_name = ?");
                        $stmt->execute([$page_name]);
                        if ($stmt->fetch()) {
                            $stmt = $pdo->prepare("UPDATE pages SET content = ? WHERE page_name = ?");
                            $stmt->execute([$content_post, $page_name]);
                            echo "<p style='color:green;'>Treść strony została zaktualizowana.</p>";
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO pages (page_name, content) VALUES (?, ?)");
                            $stmt->execute([$page_name, $content_post]);
                            echo "<p style='color:green;'>Strona została dodana.</p>";
                        }
                    }
                    ?>
                    <h2>Zarządzanie Treścią Stron</h2>
                    <form method="POST" action="admin.php?tab=pages">
                        <label for="page_name">Nazwa strony (np. home, kontakt):</label>
                        <input type="text" name="page_name" id="page_name" required>
                        
                        <label for="content">Treść:</label>
                        <textarea name="content" id="content" rows="10" required></textarea>
                        
                        <button type="submit">Zapisz</button>
                    </form>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM pages ORDER BY updated_at DESC");
                    $pagesContent = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($pagesContent):
                        echo "<h3>Aktualne strony</h3><ul>";
                        foreach ($pagesContent as $p) {
                            echo "<li><strong>" . htmlspecialchars($p['page_name']) . ":</strong> " . substr(strip_tags($p['content']), 0, 100) . "...</li>";
                        }
                        echo "</ul>";
                    else:
                        echo "<p>Brak dynamicznych stron.</p>";
                    endif;
                    break;
                case 'dashboard':
                default:
                    echo "<h2>Dashboard</h2>";
                    echo "<p>Witaj w panelu administratora. Wybierz jedną z opcji z menu powyżej.</p>";
                    break;
            }
            ?>
        </div>
    </div>
</body>
</html>
