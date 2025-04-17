<?php
// pages/login.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header("Location: index.php?page=home");
        exit;
    } else {
        $error = "Nieprawidłowe dane logowania.";
    }
}

ob_start();
?>
<div class="login-form">
  <h1 class="mb-4 text-center">Logowanie</h1>
  <?php if(isset($error)): ?>
    <div class="alert alert-danger" role="alert">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php endif; ?>
  <form method="post" action="">
    <label for="username">Nazwa użytkownika</label>
    <input type="text" name="username" id="username" required>
    
    <label for="password">Hasło</label>
    <input type="password" name="password" id="password" required>
    
    <button type="submit">Zaloguj się</button>
  </form>
  <p class="text-center mt-3">
    <a href="index.php?page=register">Nie masz konta? Zarejestruj się</a>
  </p>
</div>
<?php
// Zawartość login.php jest przekazywana do layout.php jako $content
echo $content = ob_get_clean();
?>
