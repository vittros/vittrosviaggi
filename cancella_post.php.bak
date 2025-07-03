<?php
session_start();
require_once 'lib/config.php';  // Assumi che qui ci sia $pdo PDO

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$ruolo = $_SESSION['ruolo'] ?? 'ospite';
$user_id = $_SESSION['user_id'];

// Prendi ID post da POST e validalo
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    die("ID post non valido.");
}

// Controlla che il post esista e prendi autore
$stmt = $pdo->prepare("SELECT autore_id FROM post WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    die("Post non trovato.");
}

// Controllo permessi: può cancellare solo admin o autore
if ($ruolo !== 'admin' && $user_id != $post['autore_id']) {
    die("Non hai i permessi per cancellare questo post.");
}

// Cancella il post
$stmt = $pdo->prepare("DELETE FROM post WHERE id = ?");
$stmt->execute([$id]);

// Redirect a lista post con messaggio (opzionale)
header('Location: diario_lista.php?msg=Post+eliminato+con+successo');
exit;

