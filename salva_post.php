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
$visibilita = $_POST['visibilita'] ?? 'pubblico';


$autore_id = $_SESSION['user_id'] ?? 0;
if ($autore_id == 0) {
    die("Errore: utente non autenticato.");
}

$sql = "UPDATE post SET titolo = ?, contenuto = ?, sfondo = ?, visibilita = ?, data_modifica = NOW() WHERE id = ?";
$res = db_update($sql, [$titolo, $contenuto, $sfondo, $visibilita, $id]);

$pdo = getPDO();

header("Location: diario_lista.php");
exit;
