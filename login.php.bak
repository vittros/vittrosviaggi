<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'lib/config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? '';
    $password = $_POST["password"] ?? '';

    $stmt = $pdo->prepare("SELECT id, username, password_hash, ruolo FROM utenti WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['ruolo'] = $user['ruolo']; // ← QUI va messa
        $_SESSION['loggedin'] = true;

        header("Location: index.php");
        exit;
    } else {
        $errore = "Nome utente o password errati.";
    }
}


?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Login - VittrosViaggi</title>
</head>
<body>
  <h2>Login</h2>
  <?php if (!empty($errore)) echo "<p style='color:red;'>$errore</p>"; ?>
  <form method="post">
    <label>Username: <input type="text" name="username" required></label><br><br>
    <label>Password: <input type="password" name="password" required></label><br><br>
    <button type="submit">Accedi</button>
  </form>
</body>
</html>

