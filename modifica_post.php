<?php
session_start();
require_once 'lib/functions.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("ID post non valido.");
}

$stmt = $pdo->prepare("SELECT * FROM post WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    die("Post non trovato.");
}

$base_path = '/srv/http/leNostre';
$rel_path = $post['cartella'] ?? '';
$full_path = realpath($base_path . '/' . $rel_path);
if (!$full_path || strpos($full_path, realpath($base_path)) !== 0) {
    $full_path = realpath($base_path);
    $rel_path = '';
}

$cartelle = suggerisci_cartelle($post['titolo'], $base_path);
?>

<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Modifica post</title>
  <link rel="stylesheet" href="css/form_post.css">
  <!-- Carica TinyMCE da CDN --> 
  <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script> 
</head>
<body>
  <h1>Modifica post</h1>

  <?php mostra_form_post($post, $cartelle, $rel_path); ?>

</body>
</html>

