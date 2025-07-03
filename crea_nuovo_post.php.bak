<?php
session_start();
require_once 'lib/functions.php';

// ⚠️ Solo per debug!
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $azione = $_POST['azione'] ?? '';

    if ($azione === 'annulla') {
        header("Location: index.php");
        exit;
    }

    $titolo = trim($_POST['titolo'] ?? '');
    $commento = trim($_POST['commento'] ?? '');

    if ($titolo !== '') {
        $stmt = $pdo->prepare('INSERT INTO post (titolo, contenuto, autore_id, bozza) VALUES (?, ?, ?, 1)');
        $stmt->execute([$titolo, $commento, $_SESSION['user_id'] ?? 1]);

        $post_id = $pdo->lastInsertId();
        header("Location: modifica_post.php?id=$post_id");
        exit;
    }

    $errore = "Il titolo è obbligatorio.";
}


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nuovo Post - vittrosviaggi</title>
    <link rel="stylesheet" href="css/stile.css">
</head>
<body>
    <h1>Nuovo Post (bozza)</h1>

    <?php if (!empty($errore)): ?>
        <p style="color:red"><?= htmlspecialchars($errore) ?></p>
    <?php endif; ?>

<form method="post">
    <label>Titolo:</label><br>
    <input type="text" name="titolo" size="60" required><br><br>

    <label>Commento iniziale:</label><br>
    <textarea name="commento" rows="5" cols="60"></textarea><br><br>

    <button type="submit" name="azione" value="crea">Crea e modifica</button>
    <button type="submit" name="azione" value="annulla">Annulla</button>
</form>

</body>
</html>

