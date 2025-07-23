<?php
$pagina_corrente = 'login';
require_once 'lib/bootstrap.php'; // include già session_start, $tema_attivo, header.php, functions.php

$errore = '';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'IP sconosciuto';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = $_POST["username"] ?? '';
  $password = $_POST["password"] ?? '';

  $pdo = getPDO();
  $stmt = $pdo->prepare("SELECT id, username, password_hash, ruolo, attivo FROM utenti WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password_hash'])) {
    if (!$user['attivo']) {
      debug_log("⛔ Accesso negato per utente disattivato: {$user['username']} da IP $ip", "info");
      echo "<p>Account disattivato. Contatta l’amministratore.</p>";
      exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['ruolo'] = $user['ruolo'];
    $_SESSION['loggedin'] = true;
    $_SESSION['session_id'] = session_id();

    // LOG nel database
    $stmt = $pdo->prepare("INSERT INTO user_history (session_id, version, user_id, ip_add, action) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
      session_id(),
      $app_version ?? '1.0.0',
      $user['id'],
      $ip,
      'login'
    ]);

    debug_log("✅ Login riuscito per utente: {$user['username']} (ID {$user['id']}) da IP $ip", "info");
    header("Location: index.php");
    exit;
  }

  $errore = "Nome utente o password errati.";
  debug_log("❌ Login fallito per username: $username da IP $ip", "info");
}
?>
<main>
  <div class="container mt-5">
    <div class="login-box">
      <h2 clas="text-center mb-4">🔐 VittRosViaggi</h2>

      <?php if ($errore): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div>
      <?php endif; ?>

      <form method="post" action="login.php">
        <div class="mb-3">
          <label for="username" class="form-label">Nome utente</label>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Accedi</button>
      </form>
    </div>
  </div>
</main>
<?php include 'lib/footer.php'; ?>