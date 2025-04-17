<?php
// pages/register.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username         = trim($_POST['username']);
    $email            = trim($_POST['email']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "Hasła nie są zgodne.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Nazwa użytkownika lub email już istnieje.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $pdo->prepare("INSERT INTO users (username, email, password, account_group) VALUES (?, ?, ?, ?)");
            if ($insert->execute([$username, $email, $hashed_password, 1])) {
                $success = "Rejestracja zakończona, możesz się teraz zalogować.";
            } else {
                $error = "Błąd rejestracji.";
            }
        }
    }
}

ob_start();
?>
<div class="register-form">
  <h1 class="mb-4 text-center">Rejestracja</h1>
  <?php if(isset($error)): ?>
    <div class="alert alert-danger" role="alert">
      <?php echo htmlspecialchars($error); ?>
    </div>
  <?php elseif(isset($success)): ?>
    <div class="alert alert-success" role="alert">
      <?php echo htmlspecialchars($success); ?>
    </div>
    <p class="text-center">
      <a href="index.php?page=login" class="btn btn-link">Przejdź do logowania</a>
    </p>
  <?php endif; ?>
  <form method="post" action="">
    <label for="username">Nazwa użytkownika</label>
    <input type="text" name="username" id="username" required>
    
    <label for="email">Email</label>
    <input type="email" name="email" id="email" required>
    
    <label for="password">Hasło</label>
    <input type="password" name="password" id="password" required>
    
    <label for="confirm_password">Powtórz hasło</label>
    <input type="password" name="confirm_password" id="confirm_password" required>
    
    <button type="submit">Zarejestruj się</button>
  </form>
  <p class="text-center mt-3">
    <a href="index.php?page=login">Masz już konto? Zaloguj się</a>
  </p>
</div>
<?php
// Przekazujemy zawartość do layout.php
echo $content = ob_get_clean();
?>
