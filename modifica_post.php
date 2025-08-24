<?php
// modifica_post.php - versione standalone completa e “pulita”

define('AJAX_MODE', true);
$pagina_corrente = 'modifica';

require_once 'lib/bootstrap.php';
require_once 'lib/db_utilities.php';

$id_post = $_GET['id'] ?? null;
if (!$id_post || !is_numeric($id_post)) {
  echo "❌ ID non valido";
  exit;
}

$row = db_select_row("SELECT * FROM post WHERE id = ?", [$id_post]);
if (!$row) {
  echo "❌ Post non trovato";
  exit;
}

$titolo     = $row['titolo']      ?? '';
$contenuto  = $row['contenuto']   ?? '';
$sfondo     = $row['sfondo']      ?? '';
$username   = $_SESSION['username'] ?? '';
$visibilita = $row['visibilita']  ?? '';

debug_log("👀 L'utente $username sta modificando il post: $id_post", "info");

?>
<!DOCTYPE html>
<html lang="it">

<head>
  <meta charset="UTF-8">
  <title>Modifica post</title>

  <!-- ✅ Bootstrap CSS rimesso -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- I tuoi CSS -->
  <link rel="stylesheet" href="css/content.css">
  <link rel="stylesheet" href="css/theme-default.css?v=1">

  <!-- TinyMCE -->
  <script src="/libs/tinymce/tinymce.min.js"></script>
  <script>
    // handler globale usato dal popup
    window.handleImgSelect = function(postId, relPath) {
      try {
        const ed = tinymce.get('contenuto');
        if (!ed) return;
        const safe = relPath.replace(/"/g, '&quot;');
        ed.insertContent('<img src="/leNostre/' + safe + '" class="img-center">');
        // facoltativo: chiudi il popup dopo l’inserimento
        if (window.mediaWin && !window.mediaWin.closed) window.mediaWin.close();
      } catch (e) {
        console.error('insert image error', e);
      }
    };
  </script>
  <script src="js/disputil_toast.js"></script>

</head>

<body class="<?= htmlspecialchars($sfondo) ?>">
  <div class="container my-4">
    <h2 class="text-primary">Modifica post</h2>
    <div id="autosave-badge" class="small text-muted mb-3">Autosave attivo…</div>

    <form id="formPost">
      <input type="hidden" name="id" id="post_id" value="<?= htmlspecialchars($id_post) ?>">
      <input type="hidden" name="sfondo" id="sfondo" value="<?= htmlspecialchars($sfondo) ?>">

      <div class="mb-3">
        <label for="titolo" class="form-label">Titolo</label>
        <input type="text" class="form-control" id="titolo" name="titolo" value="<?= htmlspecialchars($titolo) ?>" required>
      </div>

      <div class="mb-3">
        <label for="contenuto" class="form-label">Contenuto</label>
        <textarea id="contenuto" name="contenuto" class="form-control" rows="12"><?= htmlspecialchars($contenuto) ?></textarea>
      </div>

      <div class="btn-toolbar gap-2 mt-4">
        <button type="button" class="btn btn-success" onclick="forceSave()">💾 Salva</button>
        <a href="diario_lista.php" class="btn btn-secondary">⬅️ Torna</a>
        <button type="button" class="btn btn-success" id="btn-pubblica" name="publish" value="1">✅ Pubblica</button>
        <!-- <button type="button" onclick="apriMediaPopup()">📷 Scegli immagine dalla galleria</button> -->
        <!-- <button type="button" class="btn btn-info" onclick="apriPopup(<?= (int)$id_post ?>)">📷🎵 Multimedia</button> -->
        <button type="button" class="btn btn-info" onclick="apriMediaPopup()">📷 Immagine dalla Galleria</button>
        <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
        <?php
        $lastPub = db_select_row(
          "SELECT id FROM post_versions WHERE post_id=? AND stato_snapshot='published' ORDER BY id DESC LIMIT 1",
          [$id_post]
        );
        if ($lastPub):
        ?>
          <button type="button" class="btn btn-outline-primary btn-sm"
            onclick="ripristinaVersione(<?= (int)$lastPub['id'] ?>)">
            ⤴️ Riparti dall’ultima pubblicata
          </button>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- Contenitore toast “salvato” compat -->
  <div class="position-fixed top-0 end-0 p-3" style="z-index:9999">
    <div id="toastSalvataggio" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">✅ Post salvato con successo!</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Chiudi"></button>
      </div>
    </div>
  </div>

  <!-- ✅ JS SOLO qui in fondo, una volta -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/disputil_toast.js"></script>
  <script src="/libs/tinymce/tinymce.min.js"></script>
  <script src="/vittrosviaggi_1.1/js/tinymce-init.js"></script>

  <script>
    const titoloPost = <?= json_encode($titolo) ?>;
    const POST_ID = <?= (int)$id_post ?>;


    // apre il popup e conserva il riferimento
    function apriMediaPopup() {
      const q = new URLSearchParams({
        post_id: <?= (int)($_GET['id'] ?? 0) ?>,
        titolo: document.querySelector('input[name="titolo"]')?.value || '',
      }).toString();
      window.mediaWin = window.open('media_popup.php?' + q, 'vitexplorer', 'width=1200,height=800');
    }

    // handler globale chiamato dal popup quando clicchi "Inserisci"
    window.handleImgSelect = function(postId, relPath) {
      const ed = window.tinymce?.get('contenuto');
      if (!ed) return;
      const safe = relPath.replace(/"/g, '&quot;');
      ed.insertContent('<img src="/leNostre/' + safe + '" class="img-center">');
      if (window.mediaWin && !window.mediaWin.closed) window.mediaWin.close();
    };

    // --- Autosave -----------------------------------------------------------
    function debounce(fn, d = 1000) {
      let t;
      return (...a) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...a), d);
      };
    }
    // --- Autosave "silenzioso" (solo badge) ---
    function debounce(fn, d = 1000) {
      let t;
      return (...a) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...a), d);
      };
    }
    const readTitolo = () => document.querySelector('input[name="titolo"]')?.value || '';
    const readSfondo = () => document.querySelector('#sfondo')?.value || '';
    const readContenuto = () => window.tinymce?.activeEditor ? tinymce.activeEditor.getContent() :
      (document.querySelector('textarea[name="contenuto"]')?.value || '');
    let lastPayload = '';

    const doAutosave = debounce(() => {
      const body = new URLSearchParams({
        post_id: POST_ID,
        titolo: readTitolo(),
        contenuto: readContenuto(),
        sfondo: readSfondo()
      });
      const sig = body.toString();
      if (sig === lastPayload) return;
      lastPayload = sig;

      fetch('/vittrosviaggi_1.1/ajax/autosave_post.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
          },
          body
        })
        .then(r => r.ok ? r.json() : Promise.reject('HTTP ' + r.status))
        .then(j => {
          const b = document.getElementById('autosave-badge');
          if (!b) return;
          if (j?.ok) {
            b.textContent = 'Bozza salvata: ' + new Date().toLocaleTimeString();
            window.__lastSavedDraftAt = Date.now();
          } else {
            b.textContent = 'Errore salvataggio: ' + (j?.err || 'n/d');
          }
        })
        .catch(() => {
          const b = document.getElementById('autosave-badge');
          if (b) b.textContent = 'Errore salvataggio…';
        });
    }, 1200);

    // forza un salvataggio immediato
    function forceSave() {
      lastPayload = ''; // così non viene saltato per "payload identico"
      doAutosave(); // usa lo stesso canale
      DispUtil.toast('✅ Bozza salvata', 'success');
    }


    function hookAutosave() {
      document.querySelector('input[name="titolo"]')?.addEventListener('input', doAutosave);
      document.querySelector('textarea[name="contenuto"]')?.addEventListener('input', doAutosave);
      if (window.tinymce) tinymce.on('AddEditor', (e) =>
        e.editor.on('change keyup paste input SetContent', doAutosave)
      );
    }

    // --- Pubblica -----------------------------------------------------------
    function pubblicaPost() {
      fetch('ajax/publish_min.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
          },
          body: new URLSearchParams({
            id: POST_ID
          })
        })
        .then(async (r) => {
          const txt = await r.text();
          let j = null;
          try {
            j = txt ? JSON.parse(txt) : null;
          } catch {
            throw new Error('Risposta non-JSON dal server: ' + txt.slice(0, 300));
          }
          if (!r.ok || !j) throw new Error(j?.error || (r.status + ' ' + r.statusText));
          if (j.success === true || j.ok === true) {
            DispUtil.toast('✅ Post pubblicato', 'success');
            setTimeout(() => location.reload(), 700);
          } else {
            throw new Error(j.error || 'Errore sconosciuto');
          }
        })
        .catch(err => {
          DispUtil.toast('❌ Errore in pubblicazione: ' + err.message, 'danger');
          console.error('[publish] errore:', err);
        });
    }
  </script>
</body>

</html>