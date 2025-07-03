<?php
session_start();
require_once 'lib/functions.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$titolo = $_POST['titolo'] ?? '';
$cartella = $_POST['cartella_foto'] ?? '';
$contenuto = $_POST['contenuto'] ?? '';
$musica = $_POST['musica'] ?? '';

$autore_id = $_SESSION['user_id'] ?? 0;
if ($autore_id == 0) {
    die("Errore: utente non autenticato.");
}

$pdo = getPDO();

try {
    $stmt = $pdo->prepare("INSERT INTO post (titolo, cartella, contenuto, musica, autore_id, data_creazione) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$titolo, $cartella, $contenuto, $musica, $autore_id]);
} catch (PDOException $e) {
    die("Errore DB: " . $e->getMessage());
}

header("Location: diario_lista.php");
exit;
