<?php
// modifica_post.php - versione standalone completa

// Evita HTML duplicato se bootstrap.php include header.php
define('AJAX_MODE', true);

$pagina_corrente = 'modifica';
require_once 'lib/bootstrap.php';
require_once 'lib/db_utilities.php';

$id_post = $_GET['id'] ?? null;
if (!$id_post || !is_numeric($id_post)) {
  echo "❌ ID non valido";
  exit;
}

// Carica i dati del post
$row = db_select_row("SELECT * FROM post WHERE id = ?", [$id_post]);
if (!$row) {
  echo "❌ Post non trovato";
  exit;
}

$titolo = $row['titolo'] ?? '';
$contenuto = $row['contenuto'] ?? '';
$sfondo = $row['sfondo'] ?? '';

?>
<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8">
  <title>Modifica post</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/content.css">
  <link rel="stylesheet" href="css/theme-default.css?v=1">
  <script src="/libs/tinymce/tinymce.min.js"></script>
  <script>
    const titoloPost = <?= json_encode($titolo) ?>;
    const idPost = <?= (int)$id_post ?>;

    document.addEventListener('DOMContentLoaded', function() {
      tinymce.init({
        selector: '#contenuto',
        height: 600,
        menubar: 'edit view insert format tools table',
        plugins: 'lists link image media code table fullscreen',
        toolbar: 'undo redo | styleselect fontfamily fontsize | bold italic underline | forecolor backcolor | align | bullist numlist | link image media | sfondoSelect | code fullscreen',
        content_css: '/vittrosviaggi/css/content.css',

        setup: function(editor) {
          const sfondi = {
            '': 'Predefinito',
            'bg-azzurro': 'Azzurro',
            'bg-giallo': 'Giallo',
            'bg-verde': 'Verde',
            'bg-rosa': 'Rosa',
            'bg-arancio': 'Arancio'
          };

          editor.ui.registry.addMenuButton('sfondoSelect', {
            text: 'Sfondo',
            fetch: function(callback) {
              const items = Object.entries(sfondi).map(([val, label]) => ({
                type: 'menuitem',
                text: label,
                onAction: function() {
                  document.body.className = val;
                  document.getElementById('sfondo').value = val;
                  fetch('ajax/update_sfondo.php', {
                      method: 'POST',
                      headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                      },
                      body: `post_id=${idPost}&sfondo=${encodeURIComponent(val)}`
                    })
                    .then(r => r.json())
                    .then(d => console.log('Sfondo aggiornato', d));
                }
              }));
              callback(items);
            }
          });
        }
      });
    });

    function apriPopup(postId) {
      const sfondo = document.getElementById('sfondo')?.value || '';
      const url = `media_popup.php?post_id=${postId}&titolo=${encodeURIComponent(titoloPost)}&sfondo=${encodeURIComponent(sfondo)}`;
      window.open(url, 'popupMultimedia', 'width=850,height=600,resizable=yes,scrollbars=yes');
    }

    function salvaPost() {
      tinymce.triggerSave();
      const formData = new FormData(document.getElementById('formPost'));

      fetch('ajax/handle_salva_post.php', {
          method: 'POST',
          body: formData // <-- questa è la chiave
        })
        .then(res => res.json())
        .then(data => {
          if (!data.success) {
            alert("❌ Errore: " + data.error);
          }
        })

        .catch(err => alert("❌ Errore di rete"));
    }
  </script>
</head>

<body class="<?= htmlspecialchars($sfondo) ?>">
  <div class="container my-4">
    <h2 class="text-primary">Modifica post</h2>
    <form id="formPost">
      <input type="hidden" name="id" id="post_id" value="<?= htmlspecialchars($id_post) ?>">
      <input type="hidden" name="sfondo" id="sfondo" value="<?= htmlspecialchars($sfondo) ?>">

      <div class="mb-3">
        <label for="titolo" class="form-label">Titolo</label>
        <input type="text" class="form-control" id="titolo" name="titolo" value="<?= htmlspecialchars($titolo) ?>" required>
      </div>

      <label for="visibilita">Visibilità:</label>
      <select name="visibilita" id="visibilita" class="form-control mb-3">
        <option value="pubblico" <?= $visibilita === 'pubblico' ? 'selected' : '' ?>>🌍 Pubblico</option>
        <option value="nat" <?= $visibilita === 'nat' ? 'selected' : '' ?>>🌿 Amik Nat</option>
        <option value="privato" <?= $visibilita === 'privato' ? 'selected' : '' ?>>🔒 Privato</option>
      </select>

      <div class="mb-3">
        <label for="contenuto" class="form-label">Contenuto</label>
        <textarea id="contenuto" name="contenuto" class="form-control" rows="10"><?= htmlspecialchars($contenuto) ?></textarea>
      </div>

      <div class="btn-toolbar mt-4">
        <button type="button" class="btn btn-success" onclick="salvaPost()">💾 Salva</button>
        <a href="index.php" class="btn btn-secondary">❌ Annulla</a>
        <button type="button" class="btn btn-info" onclick="apriPopup(<?= $id_post ?>)">📷🎵 Multimedia</button>
        <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
      </div>
    </form>
  </div>
</body>

</html>