<?php
session_start();
require_once 'lib/functions.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();

$azione = $_POST['azione'] ?? '';
$id = $_POST['id'] ?? null;
$titolo = $_POST['titolo'] ?? '';
$contenuto = $_POST['contenuto'] ?? '';
$cartella = $_POST['cartella_foto'] ?? '';
$musica = $_POST['musica'] ?? '';
$user_id = $_SESSION['user_id'] ?? 0;

if ($azione === 'crea') {
    $stmt = $pdo->prepare("INSERT INTO post (titolo, contenuto, cartella, musica, autore_id, data_creazione) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$titolo, $contenuto, $cartella, $musica, $user_id]);
    header('Location: diario_lista.php');
    exit;
}

if ($azione === 'modifica' && $id) {
    $stmt = $pdo->prepare("UPDATE post SET titolo=?, contenuto=?, cartella=?, musica=? WHERE id=?");
    $stmt->execute([$titolo, $contenuto, $cartella, $musica, $id]);
    header("Location: visualizza_post.php?id=$id");
    exit;
}

if ($azione === 'cancella' && $id) {
    $stmt = $pdo->prepare("DELETE FROM post WHERE id=?");
    $stmt->execute([$id]);
    header('Location: diario_lista.php');
    exit;
}

die("Azione non valida.");
?>

