<?php
session_start();
require_once 'lib/functions.php';

if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Percorsi base
$base_path = '/srv/http/leNostre';
$rel_path = $_GET['path'] ?? '';
$full_path = realpath($base_path . '/' . $rel_path);

if (!$full_path || strpos($full_path, realpath($base_path)) !== 0) {
    $full_path = realpath($base_path);
    $rel_path = '';
}

// Elenco sottocartelle
$cartelle = [];
$items = scandir($full_path);
foreach ($items as $item) {
    if ($item === '.' || $item === '..') continue;
    if (is_dir($full_path . '/' . $item)) {
        $cartelle[] = $item;
    }
}

$livello_superiore = dirname($rel_path);
if ($livello_superiore === '.' || $rel_path === '') {
    $livello_superiore = '';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8" />
  <title>Nuovo post - VittrosViaggi</title>
  <script src="/libs/tinymce/tinymce.min.js"></script>
  <style>
    body { font-family: sans-serif; margin: 2em; }
    .form-group { margin-bottom: 1.5em; }
    label { display: block; margin-bottom: 0.3em; }
    input[type=text], select, textarea { width: 100%; padding: 0.5em; }
    textarea { height: 300px; }
    .scroll-box {
      max-height: 250px;
      overflow-y: auto;
      border: 1px solid #ccc;
      border-radius: 8px;
      padding: 10px;
      background: #f8f9fa;
    }
    .cartella-link {
      display: block;
      padding: 5px;
      text-decoration: none;
      color: #007bff;
    }
    .cartella-link:hover {
      background: #e9ecef;
    }
    .azioni {
      margin-top: 1em;
    }
  </style>

  <script>
    tinymce.init({
      selector: '#contenuto',
      plugins: 'lists link image media code table textcolor',
      toolbar: 'undo redo | styleselect | fontfamily fontsize | bold italic underline | forecolor backcolor | alignleft aligncenter alignright | bullist numlist | link image media | code',
      images_upload_url: 'upload_tinymce.php',
      automatic_uploads: true,
      images_reuse_filename: true,
      font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Tahoma=tahoma,arial,helvetica; Verdana=verdana,geneva',
      font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
      style_formats: [
        { title: 'Sfondo azzurro', block: 'p', classes: 'bg-azzurro' },
        { title: 'Sfondo giallo', block: 'p', classes: 'bg-giallo' },
        { title: 'Sfondo Parlasco', block: 'div', classes: 'bg-sfondo-parlasco' },
        { title: 'Sfondo immagine KDE', block: 'div', classes: 'sfondo-kde' }
      ],
      content_css: '/vittrosviaggi/content.css',
      height: 400,
      language: 'it'
    });
  </script>
</head>
<body>
  <h1>Scrivi un nuovo post</h1>
  <form action="salva_post.php" method="post">

    <div class="form-group">
      <label for="titolo">Titolo del post:</label>
      <input type="text" name="titolo" id="titolo" required>
    </div>

    <div class="form-group">
      <label for="contenuto">Contenuto:</label>
      <textarea name="contenuto" id="contenuto"></textarea>
    </div>

    <div class="form-group">
      <label for="foto">Scegli una cartella di foto da includere:</label>

      <?php if ($rel_path): ?>
        <p><a href="?path=<?= urlencode($livello_superiore) ?>">🔙 Torna a: <?= htmlspecialchars($livello_superiore ?: '/') ?></a></p>
        <p><strong>📂 Cartella attuale:</strong> <?= htmlspecialchars($rel_path) ?></p>
      <?php endif; ?>

      <div class="scroll-box">
        <?php if (empty($cartelle)): ?>
          <p><em>(Nessuna sottocartella)</em></p>
        <?php else: ?>
          <?php foreach ($cartelle as $cartella): ?>
            <a class="cartella-link" href="?path=<?= urlencode(trim($rel_path . '/' . $cartella, '/')) ?>">
              📁 <?= htmlspecialchars($cartella) ?>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php if (!empty($rel_path)): ?>
        <div class="azioni">
          <input type="hidden" name="cartella_foto" id="cartella_foto" value="<?= htmlspecialchars($rel_path) ?>">
          <button type="submit" name="usa_cartella" class="btn btn-primary">✔ Usa questa cartella</button>
          <a class="btn btn-secondary" target="_blank" href="galleria.php?path=<?= urlencode($rel_path) ?>">🖼 Apri galleria</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="form-group">
      <label for="musica">Link a file musicale (opzionale):</label>
      <input type="text" name="musica" placeholder="URL o percorso file">
    </div>

    <button type="button" onclick="apriGalleria()">📷 Inserisci foto dal nostro archivio</button>

    <script>
      function apriGalleria() {
        const cartella = document.getElementById("cartella_foto").value;
        if (!cartella) {
            alert("Seleziona prima una cartella!");
            return;
        }
        const url = "galleria.php?cartella=" + encodeURIComponent(cartella);
        window.open(url, 'Galleria', 'width=1000,height=700');
      }
    </script>

    <br><br>
    <button type="button" onclick="mostraAnteprima()">👁 Anteprima</button>

    <div id="anteprima" style="margin-top: 2em; padding: 1em; border: 1px solid #ccc; background: #f9f9f9;">
      <em>L'anteprima apparirà qui...</em>
    </div>

    <button type="submit">💾 Salva post</button>
  </form>
</body>
</html>

