<?php
require_once 'lib/bootstrap.php';
require_once 'lib/thumbs.php';
require_once 'lib/db_utilities.php';

$titolo = $_GET['titolo'] ?? '';
$post_id = $_GET['post_id'] ?? 0;
$cartella = $_GET['cartella'] ?? '';
$sfondo = $_GET['sfondo'] ?? ($_POST['sfondo'] ?? '');

// Recupera cartella salvata nel DB se non fornita
if (!$cartella && $post_id) {
  $row = db_select_row("SELECT cartella FROM post WHERE id = ?", [$post_id]);
  if ($row && !empty($row['cartella'])) $cartella = $row['cartella'];
}

// Normalizza path
$base_path = '/srv/http/leNostre';
$abs_cartella = realpath("$base_path/$cartella");
if (!$abs_cartella || !str_starts_with($abs_cartella, $base_path)) {
  die("Accesso non consentito");
}

// Galleria immagini
$thumbs_path = "$base_path/thumbs";
$immagini = glob("$abs_cartella/*.{jpg,jpeg,png,JPG,JPEG,PNG}", GLOB_BRACE);
$thumbs = [];
foreach ($immagini as $img) {
  $thumbs[] = generaThumbnailSeNecessario($img, $thumbs_path);
}

// Navigazione cartelle sorelle
// Se siamo nella root (leNostre), mostra le sottocartelle (anni)
$rel_cartella = isset($cartella) ? trim($cartella) : '';
if ($rel_cartella === '' || $rel_cartella === '.') {
  // Siamo nella root leNostre
  $cartella_padre = '';
  $abs_cartella = $base_path;
  $abs_padre = $base_path;
} else {
  $cartella_padre = dirname($rel_cartella ?? '');
  $abs_cartella = "$base_path/$rel_cartella";
  $abs_padre = "$base_path/$cartella_padre";
}
debug_log("📌 rel_cartella = '$rel_cartella' | cartella_padre = '$cartella_padre' | abs_cartella = '$abs_cartella'", 'debug');

$cartelle_sorelle = [];

$abs_cartella = "$base_path/$cartella";
debug_log("📂 Cartella attiva: $cartella", 'info');

// Prima: tentiamo di ottenere sottocartelle con immagini
$sottocartelle = [];
if (is_dir($abs_cartella)) {
  $entries = scandir($abs_cartella);
  foreach ($entries as $entry) {
    if ($entry === '.' || $entry === '..') continue;
    $sub_path = "$abs_cartella/$entry";
    if (is_dir($sub_path) && contiene_immagini($sub_path)) {
      $rel = ($cartella ? "$cartella/" : '') . $entry;
      $sottocartelle[$rel] = $entry;
      // debug_log("✅ Sottocartella '$entry' accettata (immagini trovate)", 'debug');
    }
  }
}

// Se abbiamo sottocartelle → le usiamo
if (!empty($sottocartelle)) {
  $cartelle_sorelle = $sottocartelle;
  // $cartella_padre = $cartella; // serve per ".. (su)" qua era un errore
  debug_log("🔽 Visualizzazione sottocartelle di '$cartella', cartella padre = $cartella_padre", 'debug');
} else {
  // Altrimenti: visualizziamo le sorelle (come prima)
  $rel_cartella = $cartella;
  $cartella_padre = dirname($rel_cartella);
  $abs_padre = "$base_path/$cartella_padre";
  // debug_log("🔼 Nessuna sottocartella utile → risalgo a cartella_padre: '$cartella_padre'", 'debug');

  if (is_dir($abs_padre)) {
    $dirs = scandir($abs_padre);
    foreach ($dirs as $entry) {
      if ($entry === '.' || $entry === '..') continue;
      $full = "$abs_padre/$entry";
      if (is_dir($full)) {
        if (contiene_immagini($full)) {
          $rel = ($cartella_padre ? "$cartella_padre/" : '') . $entry;
          $cartelle_sorelle[$rel] = $entry;
          // debug_log("✅ Cartella sorella '$entry' accettata (immagini trovate)", 'debug');
        } else {
          debug_log("❌ Cartella sorella '$entry' esclusa (nessuna immagine)", 'debug');
        }
      }
    }
  }
}

ksort($cartelle_sorelle, SORT_NATURAL | SORT_FLAG_CASE);

?>
<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8">
  <title>VittExplorer</title>
  <link rel="stylesheet" href="css/theme-default.css?v=4">
  <style>
    body {
      font-family: sans-serif;
      background: #f5f5f5;
      margin: 0;
    }

    .popup-box {
      max-width: 96%;
      margin: 2em auto;
      background: #fff;
      padding: 1em;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    }

    .media-layout {
      display: flex;
      gap: 1em;
    }

    .col-sx {
      width: 250px;
      font-size: 0.9em;
    }

    .col-dx {
      flex: 1;
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }

    .thumb-container img.thumb {
      width: 160px;
      height: auto;
      border-radius: 6px;
    }

    .thumb-container.selezionato {
      outline: 3px solid #4CAF50;
      border-radius: 6px;
    }

    .col-dx {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      justify-content: flex-start;
      align-items: flex-start;
    }

    .thumb-container img.thumb {
      width: 180px;
      height: 130px;
      object-fit: cover;
      border-radius: 6px;
    }

    .thumb-container img.thumb {
      width: 180px;
      aspect-ratio: 16 / 9;
      object-fit: cover;
    }

    .attiva a {
      font-weight: bold;
      background: #dff0ff;
      padding: 2px 4px;
      border-radius: 5px;
      display: block;
    }

    ul.lista-cartelle {
      list-style: none;
      padding-left: 0;
    }

    .btn {
      display: block;
      margin: 0.5em 0;
      padding: 0.5em 0.8em;
      background: #0077aa;
      color: white;
      border-radius: 6px;
      text-align: center;
      text-decoration: none;
    }

    .btn:hover {
      background: #005f88;
    }
  </style>
</head>

<body>
  <div class="popup-box">
    <h2>🧭 VittExplorer: <?= htmlspecialchars($titolo) ?> <small style="font-weight:normal;color:#555;">→ <?= $rel_cartella ?></small></h2>
    <div class="media-layout">
      <div class="col-sx">
        <ul class="lista-cartelle">
          <!-- <?php if ($rel_cartella !== '' && $rel_cartella !== '.'): ?> -->
          <li>
            <a href="?azione=galleria&post_id=<?= $post_id ?>&titolo=<?= urlencode($titolo) ?>&cartella=<?= urlencode($cartella_padre) ?>">⬆️ .. (su)</a>
          </li>
          <!-- <?php endif; ?> -->

          <?php foreach ($cartelle_sorelle as $path => $nome): ?>
            <?php $classe = ($path === $rel_cartella) ? 'attiva' : ''; ?>
            <li class="<?= $classe ?>">
              <a href="?azione=galleria&post_id=<?= $post_id ?>&titolo=<?= urlencode($titolo) ?>&cartella=<?= urlencode($path) ?>">
                <?= htmlspecialchars($nome) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>

        <!--         <ul class="lista-cartelle">
          <?php if ($rel_cartella !== '' && $rel_cartella !== '.'): ?>
            <li>
              <a href="?azione=galleria&post_id=<?= $post_id ?>&titolo=<?= urlencode($titolo) ?>&cartella=<?= urlencode($cartella_padre) ?>">
                ⬆️ .. (su)
              </a>
            </li>
          <?php endif; ?>
          <?php foreach ($cartelle_sorelle as $rel => $nome): ?>
            <li class="<?= ($rel === $rel_cartella) ? 'attiva' : '' ?>">
              <a href="?azione=galleria&post_id=<?= $post_id ?>&titolo=<?= urlencode($titolo) ?>&cartella=<?= urlencode($rel) ?>"><?= htmlspecialchars($nome) ?></a>
            </li>
          <?php endforeach; ?>
        </ul> -->

        <hr>
        <a class="btn" onclick="inserisciImmagine()">🖼 Inserisci</a>
        <a class="btn" style="background:#888;" href="media_popup.php?post_id=<?= $post_id ?>&titolo=<?= urlencode($titolo) ?>">⬅ Torna</a>
        <a class="btn" style="background:#c44;" onclick="window.close()">❌ Chiudi</a>
      </div>
      <div class="col-dx">
        <?php foreach ($thumbs as $thumb):
          $rel = str_replace('/srv/http/leNostre/', '', $thumb); ?>
          <div class="thumb-container" onclick="selezionaThumb(this)">
            <img src="<?= str_replace('/srv/http', '', $thumb) ?>" class="thumb" data-relpath="<?= htmlspecialchars($rel) ?>">
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <script>
    function selezionaThumb(div) {
      document.querySelectorAll('.thumb-container').forEach(el => el.classList.remove('selezionato'));
      div.classList.add('selezionato');
    }

    function inserisciImmagine() {
      const img = document.querySelector('.thumb-container.selezionato img');
      if (!img) return alert("Seleziona un'immagine!");

      const relpath = img.getAttribute("data-relpath");
      const sfondo = document.querySelector("#sfondo")?.value || '';
      console.log("📤 Inviando sfondo:", sfondo);

      fetch('ajax/handler_ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body: new URLSearchParams({
            relpath,
            post_id: <?= (int)$post_id ?>,
            sfondo
          })
        })
        .then(r => r.json())
        .then(data => {
          if (!data.success) return alert("Errore: " + data.error);
          if (window.opener?.tinymce?.activeEditor) {
            window.opener.tinymce.activeEditor.insertContent(`<img src="${data.url}" alt="" style="max-width:100%;">`);
          }
        });
    }
  </script>
  <input type="hidden" id="sfondo" value="<?= htmlspecialchars($sfondo) ?>">

</body>

</html>