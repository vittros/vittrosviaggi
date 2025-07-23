<?php
require_once 'lib/bootstrap.php';
require_once 'lib/thumbs.php';
require_once 'lib/db_utilities.php';

$titolo = $_GET['titolo'] ?? '';
$post_id = $_GET['post_id'] ?? 0;
$azione = $_GET['azione'] ?? '';
$cartella = $_GET['cartella'] ?? '';

// Recupera cartella salvata nel post
if (!$cartella && $post_id) {
  $row = db_select_row("SELECT cartella FROM post WHERE id = ?", [$post_id]);
  if ($row && !empty($row['cartella'])) {
    $cartella = $row['cartella'];
  }
}

$base_path = '/srv/http/leNostre';
$thumbs_path = '/srv/http/leNostre/thumbs';

$elenco_immagini = [];
if ($azione === 'galleria' && $cartella) {
  $path = "$base_path/$cartella";
  $immagini = glob("$path/*.{jpg,jpeg,png,JPG,JPEG,PNG}", GLOB_BRACE);
  foreach ($immagini as $img) {
    $thumb = generaThumbnailSeNecessario($img, $thumbs_path);
    $elenco_immagini[] = $thumb;
  }
}

function suggerisci_cartelle_sorted($titolo, $base_path)
{
  $tutte = suggerisci_cartelle($titolo, $base_path);
  ksort($tutte, SORT_NATURAL | SORT_FLAG_CASE);
  return $tutte;
}
$suggerite = ($azione === 'galleria') ? suggerisci_cartelle_sorted($titolo, $base_path) : [];
$cartella_attiva = $cartella;
?>
<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8">
  <title>Media Popup</title>
  <link rel="stylesheet" href="css/theme-default.css?v=3">
  <style>
    body {
      margin: 0;
      font-family: sans-serif;
      background-size: cover;
    }

    .popup-box {
      max-width: 96%;
      margin: 2em auto;
      padding: 1em;
      background: rgba(255, 255, 255, 0.75);
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
    }

    h2 {
      margin-top: 0;
      font-size: 1.5em;
    }

    .media-layout {
      display: flex;
      gap: 1em;
    }

    .col-sx {
      width: 25%;
    }

    .col-dx {
      flex-grow: 1;
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
      justify-content: flex-start;
    }

    .sezione-titolo {
      font-weight: bold;
      margin-bottom: 0.5em;
      color: #222;
    }

    .lista-cartelle {
      font-size: 0.8em;
      width: 260px;
      /* era 200px? aumentiamo un po' */
      padding-right: 10px;
    }

    .lista-cartelle li {
      margin: 3px 0;
    }

    .lista-cartelle .attiva a {
      background: #cde3ff;
      font-weight: bold;
      padding: 2px 4px;
      border-radius: 5px;
    }

    .thumb-container {
      position: relative;
      border-radius: 8px;
      overflow: hidden;
      cursor: pointer;
      border: 2px solid transparent;
    }

    .thumb-container img.thumb {
      width: 170px;
      height: auto;
      aspect-ratio: 16 / 9;
      object-fit: cover;
      border-radius: 6px;
    }

    .thumb-container.selezionato {
      border: none;
      position: relative;
    }

    .thumb-container.selezionato::after {
      content: "✅";
      position: absolute;
      top: 6px;
      right: 6px;
      font-size: 0.7em;
      background: rgba(255, 255, 255, 0.7);
      border-radius: 50%;
      padding: 2px 5px;
      pointer-events: none;
    }

    .thumb-container .checkmark {
      position: absolute;
      top: 4px;
      right: 4px;
      background: #4CAF50;
      color: white;
      width: 20px;
      height: 20px;
      font-size: 14px;
      line-height: 20px;
      text-align: center;
      border-radius: 50%;
    }

    .media-buttons {
      margin-top: 1em;
    }

    .media-buttons .btn {
      display: inline-block;
      margin: 0.5em 0.3em;
      padding: 0.6em 1em;
      font-size: 1em;
      border-radius: 8px;
      text-decoration: none;
      color: white;
      background: #4da3c7;
    }
  </style>
</head>

<body>
  <div class="popup-box">
    <h2>📁 Galleria per: <?= htmlspecialchars($titolo) ?></h2>
    <?php if ($azione !== 'galleria'): ?>
      <div class="media-buttons">
        <a class="btn" href="media_popup.php?azione=galleria&post_id=<?= $post_id ?>&titolo=<?= urlencode($titolo) ?>">📸 Importa da Galleria</a>
        <a class="btn" style="background:#66cc99;" href="lib/media/upload.php?post_id=<?= $post_id ?>">📤 Carica da PC</a>
      </div>
    <?php else: ?>
      <div class="media-layout">
        <div class="col-sx">
          <strong>📂 Cartelle suggerite:</strong>
          <ul class="lista-cartelle">
            <?php foreach ($suggerite as $rel => $nome): ?>
              <li class="<?= ($rel == $cartella_attiva) ? 'attiva' : '' ?>">
                <a href="?azione=galleria&post_id=<?= $post_id ?>&titolo=<?= urlencode($titolo) ?>&cartella=<?= urlencode($rel) ?>">
                  <?= htmlspecialchars($nome) ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
          <div class="media-buttons">
            <button class="btn" onclick="inserisciImmagine()">🖼 Inserisci immagine</button><br>
            <button class="btn" style="background:#888;" onclick="window.location='media_popup.php?post_id=<?= $post_id ?>&titolo=<?= urlencode($titolo) ?>'">⬅ Torna alla scelta</button><br>
            <button class="btn" style="background:#ffb000;" onclick="window.close()">❌ Chiudi PopUp</button>
          </div>
        </div>
        <div class="col-dx">
          <?php foreach ($elenco_immagini as $thumb): ?>
            <?php $rel = str_replace('/srv/http/leNostre/', '', $thumb); ?>
            <div class="thumb-container" onclick="selezionaThumb(this)">
              <img src="<?= str_replace('/srv/http', '', $thumb) ?>" class="thumb" data-relpath="<?= htmlspecialchars($rel) ?>">
              <div class="checkmark" style="display:none;">✔</div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
  <script>
    function selezionaThumb(div) {
      document.querySelectorAll('.thumb-container').forEach(el => el.classList.remove('selezionato'));
      div.classList.add('selezionato');
    }

    function inserisciImmagine() {
      const selezionata = document.querySelector('.thumb-container.selezionato img');
      if (!selezionata) {
        alert("Seleziona un'immagine!");
        return;
      }

      const relpath = selezionata.getAttribute("data-relpath");
      const post_id = <?= (int)$post_id ?>;

      fetch('ajax/handler_ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            relpath,
            post_id
          })
        })
        .then(r => r.json())
        .then(data => {
          if (!data.success) {
            alert("Errore: " + data.error);
            return;
          }

          const url = data.url;

          // Invia anche il log (opzionale, già fatto in PHP ma teniamolo)
          fetch('ajax_log_selezione.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
              path: relpath
            })
          });

          // Inserisce l'immagine nel TinyMCE del padre
          if (window.opener && window.opener.tinymce) {
            const editor = window.opener.tinymce.activeEditor;
            editor.insertContent(`<img src="${url}" alt="" style="max-width:100%;">`);
          }

          // window.close();
        })
        .catch(err => {
          alert("Errore nella comunicazione AJAX");
          console.error(err);
        });
    }
  </script>

</body>

</html>