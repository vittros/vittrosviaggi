<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'lib/functions.php';
require_once 'lib/mostra_form.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();

// ID post
$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);
if ($id <= 0) die("ID post non valido.");

$stmt = $pdo->prepare("SELECT * FROM post WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) die("Post non trovato.");

// Salvataggio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $azione = $_POST['azione'] ?? '';
    if ($azione === 'annulla') {
        header("Location: diario_lista.php");
        exit;
    } elseif ($azione === 'salva') {
        $titolo = trim($_POST['titolo'] ?? '');
        $contenuto = $_POST['contenuto'] ?? '';
        $cartella = $_POST['cartella_foto'] ?? '';
        $musica = trim($_POST['musica'] ?? '');
        $sfondo = trim($_POST['sfondo'] ?? '');
        if ($titolo === '') die("Titolo mancante.");

        $stmt = $pdo->prepare("UPDATE post SET titolo = ?, contenuto = ?, cartella = ?, musica = ?, sfondo = ? WHERE id = ?");
        $stmt->execute([$titolo, $contenuto, $cartella, $musica, $sfondo, $id]);
        header("Location: diario_lista.php?msg=" . urlencode("Post aggiornato"));
        exit;
    }
}

// --- CONFIG ---
$base_path = '/srv/http/leNostre';
$rel_path = '';

// 1. Se c'è un path nella GET, lo usiamo direttamente
if (!empty($_GET['path'])) {
    $rel_path = $_GET['path'];
}
if (empty($rel_path)) {
    $suggerite = suggerisci_cartelle($post['titolo'], $base_path);
    $cartelle = array_keys($suggerite);
    $rel_path = reset($cartelle) ?? '';
}

// 3. Altrimenti usiamo quella già salvata nel post
elseif (!empty($post['cartella'])) {
    $rel_path = $post['cartella'];
}

// Verifica e normalizza il path
$full_path = realpath($base_path . '/' . $rel_path);
if (!$full_path || strpos($full_path, realpath($base_path)) !== 0) {
    $rel_path = '';
    $full_path = realpath($base_path);
}


// --- NORMALIZZA E VERIFICA ---
$full_path = realpath($base_path . '/' . $rel_path);
if (!$full_path || strpos($full_path, realpath($base_path)) !== 0) {
    $rel_path = '';
    $full_path = realpath($base_path);
}



// Protezione
if (!$full_path || strpos($full_path, realpath($base_path)) !== 0) {
    $rel_path = '';
    $full_path = realpath($base_path);
}

// Trova sottocartelle
$cartelle = [];
if (is_dir($full_path)) {
    foreach (scandir($full_path) as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        if (is_dir("$full_path/$entry")) {
            $cartelle[] = $entry;
        }
    }
}

$post['cartella'] = $rel_path;

?><!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Modifica post</title>
  <link rel="stylesheet" href="/vittrosviaggi/css/content.css">
  <?php caricaTinyMCE(); ?>
</head>
<body>
  <h1>Modifica post</h1>
  
  <?php 
   $suggerite = suggerisci_cartelle($post['titolo'], $base_path);
   mostra_form_post($post, $cartelle, $rel_path, $suggerite);
  ?>
</body>
</html>
