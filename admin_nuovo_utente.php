<?php
require_once 'lib/bootstrap.php';
require_once 'lib/functions.php';
require_once 'lib/header.php';

if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    echo "<p>Accesso negato. Solo per amministratori.</p>";
    exit;
}

$messaggio = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $ruolo = $_POST['ruolo'] ?? 'ospite';
    $email = trim($_POST['email'] ?? '');

    if ($username && $password) {
        $pdo = getPDO();

        // Verifica se esiste già
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utenti WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $messaggio = "⚠️ Username già in uso.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO utenti (username, password_hash, ruolo, email, attivo)
                                   VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$username, $password_hash, $ruolo, $email]);
            $messaggio = "✅ Utente creato con successo!";
            debug_log("🆕 Utente creato: $username ($ruolo)", "info");
        }
    } else {
        $messaggio = "⚠️ Inserire almeno username e password.";
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Crea Nuovo Utente</title>
    <link rel="stylesheet" href="css/content.css">
    <link rel="stylesheet" href="css/theme-<?= htmlspecialchars($tema_attivo) ?>.css">
</head>
<body class="page-admin theme-<?= htmlspecialchars($tema_attivo) ?>">
    <div class="admin-box">
        <h1>Crea nuovo utente</h1>
        <?php if ($messaggio): ?><p><?= htmlspecialchars($messaggio) ?></p><?php endif; ?>
        <form method="post">
            <label>Username: <input type="text" name="username" required></label><br>
            <label>Password: <input type="password" name="password" required></label><br>
            <label>Email: <input type="email" name="email"></label><br>
            <label>Ruolo:
                <select name="ruolo">
                    <option value="editor">Editor</option>
                    <option value="amico">Amico</option>
                    <option value="ospite">Ospite</option>
                </select>
            </label><br><br>
            <button type="submit">Crea utente</button>
        </form>
        <p style="margin-top: 2em;"><a href="admin_utenti.php">⬅️ Torna alla gestione utenti</a></p>
    </div>
</body>
</html>
