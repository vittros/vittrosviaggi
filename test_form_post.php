<?php
require_once 'lib/functions.php';

// Cartelle simulate (puoi anche lasciarle vuote se non ti servono ora)
$cartelle = ['Vacanze', '2024', '0101 Parlasco'];

// Post finto per testare modifica
$post_fake = [
  'id' => 999,
  'titolo' => 'Test post da mostra_form_post',
  'contenuto' => '<p class="bg-giallo">Questo è un contenuto di prova con sfondo giallo.</p>',
  'cartella' => '2024/0101 Parlasco',
  'musica' => 'audio/test.mp3'
];

$rel_path = $post_fake['cartella'];

?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <title>Test Form Post</title>
  <link rel="stylesheet" href="/vittrosviaggi/css/content.css">
  <?php caricaTinyMCE(); ?>
</head>
<body>
  <h1>Test Form Post</h1>
  <?php mostra_form_post($post_fake, $cartelle, $rel_path); ?>
</body>
</html>

