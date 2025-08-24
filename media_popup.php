<?php

declare(strict_types=1);
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/lib/db_utilities.php';

// --- helpers fallback (come da tuo snippet) ---
if (!function_exists('has_images_in_dir')) {
  function has_images_in_dir(string $dir, array $exts = ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG']): bool
  {
    if (!is_dir($dir)) return false;
    foreach (new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS) as $f) {
      if ($f->isFile()) {
        $ext = pathinfo($f->getFilename(), PATHINFO_EXTENSION);
        if ($ext && in_array($ext, $exts, true)) return true;
      }
    }
    return false;
  }
}
if (!function_exists('elenco_cartelle_con_immagini')) {
  function elenco_cartelle_con_immagini(string $base_path, array $exclude_roots = ['thumbs', 'web_wallpaper']): array
  {
    $out = [];
    $rii = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($base_path, FilesystemIterator::SKIP_DOTS),
      RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($rii as $spl) {
      if (!$spl->isDir()) continue;
      $abs = $spl->getPathname();
      $rel = ltrim(str_replace($base_path, '', $abs), '/');
      if ($rel === '') continue;
      foreach ($exclude_roots as $ex) {
        if ($rel === $ex || str_starts_with($rel, $ex . '/')) continue 2;
      }
      if (has_images_in_dir($abs)) $out[$rel] = $rel;
    }
    ksort($out, SORT_NATURAL | SORT_FLAG_CASE);
    return $out;
  }
}
if (!function_exists('suggerisci_cartelle')) {
  function suggerisci_cartelle(string $titolo, string $base_path): array
  {
    preg_match_all('/\b\w+\b/u', mb_strtolower($titolo, 'UTF-8'), $m);
    $parole = array_filter($m[0] ?? [], fn($w) => mb_strlen($w, 'UTF-8') > 3);
    if (!$parole) return [];
    $out = [];
    $rii = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($base_path, FilesystemIterator::SKIP_DOTS),
      RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($rii as $spl) {
      if (!$spl->isDir()) continue;
      $abs = $spl->getPathname();
      $rel = ltrim(str_replace($base_path, '', $abs), '/');
      if ($rel === '') continue;
      if (str_starts_with($rel, 'thumbs') || str_starts_with($rel, 'web_wallpaper')) continue;
      if (!has_images_in_dir($abs)) continue;
      $base = mb_strtolower(basename($abs), 'UTF-8');
      foreach ($parole as $p) {
        if (strpos($base, $p) !== false) {
          $out[$rel] = $rel;
          break;
        }
      }
    }
    ksort($out, SORT_NATURAL | SORT_FLAG_CASE);
    return $out;
  }
}
// --- fine helpers ---

$post_id = (int)($_GET['post_id'] ?? 0);
$titolo  = (string)($_GET['titolo']  ?? '');
$cartella = (string)($_GET['cartella'] ?? '');

$BASE_URL = defined('BASE_URL') ? BASE_URL : '/vittrosviaggi_1.1/';
$base_path = '/srv/http/leNostre';

// Cartella da DB (robusta e senza check immagini)
$cartella_db = '';
if ($post_id > 0) {
  $row = db_select_row("SELECT cartella FROM post WHERE id = ?", [$post_id]);
  $cartella_db = trim($row['cartella'] ?? '');
}
$cartella_attiva = $cartella ?: $cartella_db;
debug_log("✅ cartella attiva: " . ($cartella_attiva ?: '(vuota)'), 'info');

$suggerite = suggerisci_cartelle($titolo, $base_path);
$tutte     = elenco_cartelle_con_immagini($base_path);
if ($cartella_db) {
  unset($suggerite[$cartella_db], $tutte[$cartella_db]);
}
foreach (array_keys($suggerite) as $rel) unset($tutte[$rel]);

if (!$cartella_attiva) {
  if ($cartella_db) $cartella_attiva = $cartella_db;
  elseif ($suggerite) $cartella_attiva = array_key_first($suggerite);
}

function renderThumbs(string $base_path, ?string $rel): string
{
  if (!$rel) return '<div class="hint">Scegli una cartella a sinistra per visualizzare le immagini.</div>';
  $abs = realpath($base_path . '/' . $rel);
  if (!$abs || !str_starts_with($abs, $base_path) || !is_dir($abs)) return '<div class="hint">Cartella non accessibile.</div>';
  $imgs = glob($abs . '/*.{jpg,jpeg,png,JPG,JPEG,PNG}', GLOB_BRACE) ?: [];
  sort($imgs, SORT_NATURAL | SORT_FLAG_CASE);
  if (!$imgs) return '<div class="hint">Nessuna immagine in questa cartella.</div>';

  ob_start(); ?>
  <div class="grid">
    <?php foreach ($imgs as $path):
      $relImg = ltrim(str_replace($base_path . '/', '', $path), '/'); ?>
      <a class="thumb" href="#" data-relpath="<?= htmlspecialchars($relImg) ?>">
        <img src="/leNostre/<?= htmlspecialchars($relImg) ?>" alt="">
        <div class="fname"><?= htmlspecialchars(basename($path)) ?></div>
      </a>
    <?php endforeach; ?>
  </div>
<?php return ob_get_clean();
}

// --- SOLO GRIGLIA (risposta parziale) ---------------------------------
// Accettiamo sia header X-Partial: thumbs (come nel tuo JS) che ?partial=thumbs
$isPartial = (($_SERVER['HTTP_X_PARTIAL'] ?? '') === 'thumbs')
  || (($_GET['partial'] ?? '') === 'thumbs');

if ($isPartial) {
  header('Content-Type: text/html; charset=UTF-8');
  echo renderThumbs($base_path, $cartella_attiva);
  exit; // IMPORTANTISSIMO: evita che venga stampata tutta la pagina
}
?>
<!doctype html>
<html lang="it">

<head>
  <meta charset="utf-8">
  <title>VitExplorer — <?= htmlspecialchars($titolo) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="<?= $BASE_URL ?>css/theme-<?= TEMA_ATTIVO ?>.css?v=1">
  <style>
    :root {
      --ve-accent: #f4d441;
      --ve-accent-ink: #2b2b2b;
    }

    body.theme-default.media-popup {
      background: transparent;
      color: #111;
      font-family: system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }

    header.toolbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 14px;
      border-bottom: 1px solid rgba(0, 0, 0, .08);
      backdrop-filter: blur(2px);
      background: rgba(255, 255, 255, .35);
    }

    header.toolbar .actions .btn {
      appearance: none;
      border: 0;
      border-radius: 10px;
      padding: 8px 12px;
      margin-left: 8px;
      cursor: pointer;
      background: var(--ve-accent);
      color: var(--ve-accent-ink);
      font-weight: 600;
      box-shadow: 0 1px 3px rgba(0, 0, 0, .12);
    }

    header.toolbar .actions .btn.secondary {
      background: rgba(255, 255, 255, .7);
      color: #333;
      font-weight: 500;
    }

    .wrap {
      display: grid;
      grid-template-columns: 300px 1fr;
      height: calc(100vh - 56px);
    }

    .sx {
      border-right: 1px solid rgba(0, 0, 0, .06);
      background: rgba(244, 212, 65, .18);
      /* giallino contenitore */
      backdrop-filter: blur(1px);
      display: flex;
      flex-direction: column;
    }

    .lista {
      padding: 10px 12px;
      overflow: auto;
    }

    .lista h3 {
      margin: 8px 0;
      font-size: 13px;
      color: #222;
      letter-spacing: .2px;
    }

    .lista ul {
      list-style: none;
      margin: 0;
      padding: 0;
    }

    .lista li {
      padding: 7px 9px;
      border-radius: 8px;
      cursor: pointer;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      color: #111;
    }

    .lista li:hover {
      background: rgba(244, 212, 65, .35);
    }

    .lista li.active {
      background: rgba(244, 212, 65, .55);
      font-weight: 700;
    }

    .dx {
      padding: 14px;
      overflow: auto;
    }

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 10px;
    }

    .thumb {
      border: 1px solid rgba(0, 0, 0, .08);
      border-radius: 10px;
      padding: 6px;
      display: block;
      text-align: center;
      text-decoration: none;
      background: rgba(255, 255, 255, .65);
    }

    .thumb img {
      max-width: 100%;
      max-height: 140px;
      display: block;
      margin: 0 auto;
      border-radius: 6px;
    }

    .thumb .fname {
      font-size: 12px;
      opacity: .85;
      margin-top: 6px;
    }

    .thumb.sel {
      outline: 3px solid var(--ve-accent);
    }

    .hint {
      opacity: .7;
      font-size: 13px;
      padding: 8px 12px;
    }

    .sec {
      border-bottom: 1px dashed rgba(0, 0, 0, .08);
    }

    @media (max-width: 640px) {
      .wrap {
        grid-template-columns: 1fr;
      }

      .sx {
        height: 35vh;
      }

      .dx {
        height: 65vh;
      }
    }

    header.toolbar {
      gap: 12px;
    }

    header.toolbar .actions {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    header.toolbar .actions .controls {
      display: flex;
      gap: 8px;
    }

    header.toolbar .actions .btn,
    header.toolbar .actions .pill {
      min-height: 36px;
      display: inline-flex;
      align-items: center;
      padding: 6px 12px;
      border-radius: 10px;
    }

    header.toolbar .actions .pill {
      background: rgba(255, 255, 255, .85);
      border: 1px solid rgba(0, 0, 0, .08);
      font-weight: 600;
      color: #2b2b2b;
    }

    header.toolbar .actions .userbox {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 4px;
    }

    header.toolbar .actions .btn.tiny {
      padding: 4px 10px;
      min-height: 30px;
      font-size: 0.9em;
    }

    /* niente badge flottante nel popup */
    .media-popup .user-badge {
      display: none !important;
    }

    header.toolbar {
      gap: 12px;
      z-index: 5;
    }

    header.toolbar .actions {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    /* bottoni principali */
    header.toolbar .actions .btn {
      appearance: none;
      border: 0;
      border-radius: 10px;
      padding: 8px 12px;
      cursor: pointer;
      font-weight: 600;
      box-shadow: 0 1px 3px rgba(0, 0, 0, .12);
      min-height: 36px;
      display: inline-flex;
      align-items: center;
    }

    header.toolbar .actions .btn.secondary {
      background: rgba(255, 255, 255, .7);
      color: #333;
      font-weight: 500;
    }

    /* PILL utente con Logout dentro */
    .user-pill {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      background: rgba(255, 255, 255, .85);
      border: 1px solid rgba(0, 0, 0, .08);
      border-radius: 10px;
      padding: 6px 10px;
      min-height: 36px;
      color: #2b2b2b;
      font-weight: 600;
      white-space: nowrap;
    }

    .user-pill small {
      font-weight: 500;
      opacity: .9;
    }

    /* Logout come mini-bottone dentro la pill */
    .user-pill .logout {
      display: inline-block;
      text-decoration: none;
      padding: 4px 10px;
      border-radius: 8px;
      font-size: .9em;
      background: rgba(0, 0, 0, .06);
      color: #2b2b2b;
      border: 1px solid rgba(0, 0, 0, .08);
    }

    .user-pill .logout:hover {
      background: rgba(0, 0, 0, .12);
    }

    /* Nasconde il vecchio badge flottante solo nel popup */
    body.media-popup>div[style*="position: absolute"][style*="right: 10px"] {
      display: none !important;
    }
  </style>
</head>

<body class="theme-default media-popup">

  <header class="toolbar">
    <div class="ttl">VitExplorer — <?= htmlspecialchars($titolo) ?></div>
    <div class="actions">
      <div class="controls">
        <button class="btn btn-inserisci">🖼 Inserisci</button>
        <button class="btn secondary" onclick="window.close()">✖ Chiudi</button>
      </div>

      <!-- Toolbar -->
      <div class="pill user-pill">
        👑 <strong><?= htmlspecialchars($_SESSION['username'] ?? '') ?></strong>
        <?php if (!empty($_SESSION['ruolo'])): ?>
          <small>(<?= htmlspecialchars($_SESSION['ruolo']) ?>)</small>
        <?php endif; ?>
        <button class="logout" type="button" title="Esci">Logout</button>
      </div>

    </div>
  </header>

  <div class="wrap">
    <aside class="sx">
      <div class="lista sec">
        <h3>1) Cartella salvata</h3>
        <ul>
          <?php if ($cartella_db): ?>
            <li data-rel="<?= htmlspecialchars($cartella_db) ?>" class="<?= $cartella_attiva === $cartella_db ? 'active' : '' ?>"><?= htmlspecialchars($cartella_db) ?></li>
          <?php else: ?>
            <li class="hint">— nessuna —</li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="lista sec" style="flex:1 1 auto;">
        <h3>2) Suggerite (<?= count($suggerite) ?>)</h3>
        <ul>
          <?php if ($suggerite): foreach ($suggerite as $rel): ?>
              <li data-rel="<?= htmlspecialchars($rel) ?>" class="<?= $cartella_attiva === $rel ? 'active' : '' ?>"><?= htmlspecialchars($rel) ?></li>
            <?php endforeach;
          else: ?>
            <li class="hint">— nessuna corrispondenza —</li>
          <?php endif; ?>
        </ul>
      </div>

      <div class="lista" style="flex:1 1 auto;">
        <h3>3) Archivio completo (<?= count($tutte) ?>)</h3>
        <ul>
          <?php foreach ($tutte as $rel): ?>
            <li data-rel="<?= htmlspecialchars($rel) ?>" class="<?= $cartella_attiva === $rel ? 'active' : '' ?>"><?= htmlspecialchars($rel) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </aside>

    <main class="dx">
      <?= renderThumbs($base_path, $cartella_attiva) ?>
    </main>
  </div>

  <script>
    const BASE_URL = <?= json_encode($BASE_URL) ?>;
    const POST_ID = <?= (int)$post_id ?>;

    // cambio cartella
    document.addEventListener('click', (e) => {
      const li = e.target.closest('.sx li[data-rel]');
      if (!li) return;
      const rel = li.getAttribute('data-rel');
      // selezione visuale a sinistra
      document.querySelectorAll('.sx li').forEach(x => x.classList.remove('active'));
      li.classList.add('active');
      // ricarica griglia dx (parziale)
      const url = new URL(window.location.href);
      url.searchParams.set('cartella', rel);
      fetch(url.toString(), {
          headers: {
            'X-Partial': 'thumbs'
          }
        })
        .then(r => r.text())
        .then(html => {
          document.querySelector('.dx').innerHTML = html;
        })
        .catch(() => {
          window.location.href = url.toString();
        });
    });

    // selezione thumb
    document.addEventListener('click', (e) => {
      const a = e.target.closest('.thumb');
      if (!a) return;
      document.querySelectorAll('.thumb').forEach(x => x.classList.remove('sel'));
      a.classList.add('sel');
      e.preventDefault();
    });

    // doppio click = inserisci
    document.addEventListener('dblclick', (e) => {
      const a = e.target.closest('.thumb');
      if (!a) return;
      document.querySelector('.btn-inserisci')?.click();
    });

    // Inserisci = resize server + inserzione in TinyMCE
    document.querySelector('.btn-inserisci')?.addEventListener('click', () => {
      const a = document.querySelector('.thumb.sel') || document.querySelector('.thumb');
      if (!a) {
        alert('Seleziona un’immagine');
        return;
      }
      const relpath = a.getAttribute('data-relpath');

      const body = new URLSearchParams({
        relpath,
        post_id: POST_ID,
        max_w: 1400,
        quality: 82
      });

      fetch(BASE_URL + 'ajax/handler_ajax.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          },
          body
        })
        .then(async r => {
          const txt = await r.text();
          let j = null;
          try {
            j = JSON.parse(txt);
          } catch {}
          if (!r.ok) throw new Error('HTTP ' + r.status + ': ' + txt.slice(0, 500));
          if (!j?.success) throw new Error(j?.error || txt.slice(0, 500));
          const url = j.url;

          // Inserisci in TinyMCE se presente
          if (window.opener?.tinymce?.activeEditor) {
            window.opener.tinymce.activeEditor.insertContent(
              `<img src="${url}" alt="" style="max-width:100%; height:auto;">`
            );
          } else if (window.opener?.handleImgSelect) {
            window.opener.handleImgSelect(POST_ID, relpath);
          }
        })
        .catch(err => alert('Errore: ' + err.message));
    });
    // LOGOUT dal popup: esegui sul server, poi sincronizza l'opener e chiudi il popup
    document.querySelector('.logout')?.addEventListener('click', async (e) => {
      e.preventDefault();
      try {
        // usa POST; se il tuo logout è GET, cambia in GET
        await fetch(BASE_URL + 'logout.php?ajax=1', {
          method: 'POST',
          credentials: 'include'
        });
      } catch (_) {}

      // se esiste, manda la finestra principale alla login
      if (window.opener && !window.opener.closed) {
        try {
          window.opener.location.href = BASE_URL + 'login.php?bye=1';
        } catch (_) {}
      }

      // chiudi il popup (funziona se aperto via window.open)
      window.close();

      // fallback: se non si chiude, vai comunque alla login nel popup
      setTimeout(() => {
        location.href = BASE_URL + 'login.php?bye=1';
      }, 300);
    });
  </script>
</body>

</html>