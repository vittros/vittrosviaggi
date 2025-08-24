<?php
// ajax/handle_salva_post.php
define('AJAX_MODE', true);
require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/db_utilities.php';
if (file_exists(__DIR__ . '/../lib/functions.php')) require_once __DIR__ . '/../lib/functions.php';

header('Content-Type: application/json; charset=utf-8');

try {
  // Log iniziale
  debug_log("💾 handle_salva_post POST: " . print_r($_POST, true), 'debug');

  // id o post_id
  $id = isset($_POST['id']) ? (int)$_POST['id']
    : (isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0);
  $titolo    = $_POST['titolo']    ?? '';
  $contenuto = $_POST['contenuto'] ?? '';
  $sfondo    = $_POST['sfondo']    ?? '';
  $publish   = !empty($_POST['publish']);

  if ($id <= 0) throw new \Exception('ID post mancante', 400);

  // permessi
  $post = db_select_row("SELECT id, autore_id FROM post WHERE id=?", [$id]);
  if (!$post) throw new \Exception('Post non trovato', 404);
  if (!user_can_edit_post($post['autore_id'])) throw new \Exception('forbidden', 403);

  // salvataggio
  $sql = "UPDATE post SET titolo=?, contenuto=?, sfondo=?, data_modifica=NOW() WHERE id=?";
  $res = db_update($sql, [$titolo, $contenuto, $sfondo, $id]);
  if ($res === false) throw new \Exception('Errore nel salvataggio', 500);

  if ($publish) {
    // Trova autore_id valido per rispettare la FK
    $validUid = vrv_valid_user_id_for_versions($id, current_user_id());
    if ($validUid) {
      vrv_make_snapshot($id, 'published', $validUid);
    } else {
      debug_log("⚠️ publish: no valid user for snapshot (post $id): skip", 'debug');
    }

    // --- SNAPSHOT PUBLISHED SICURO ---
    try {
      // autore del post se esiste in utenti
      $validUid = 0;
      $author = db_select_row("SELECT autore_id FROM post WHERE id=?", [$id]);
      if (!empty($author['autore_id'])) {
        $u = db_select_row("SELECT id FROM utenti WHERE id=?", [(int)$author['autore_id']]);
        if ($u) $validUid = (int)$author['autore_id'];
      }
      // fallback: admin/editor
      if (!$validUid) {
        $adm = db_select_row("SELECT id FROM utenti WHERE ruolo IN ('admin','editor') ORDER BY id ASC LIMIT 1");
        if ($adm && !empty($adm['id'])) $validUid = (int)$adm['id'];
      }
      if ($validUid) {
        db_update(
          "INSERT INTO post_versions (post_id, autore_id, titolo, contenuto, sfondo, stato_snapshot, created_at)
       SELECT id, ?, titolo, contenuto, sfondo, 'published', NOW()
       FROM post WHERE id=?",
          [$validUid, $id]
        );
      }
    } catch (Throwable $e) {
      // debug_log("⚠️ snapshot published errore: ".$e->getMessage(), "debug");
    }


    // Marchia come pubblicato
    $resPub = db_update("UPDATE post SET bozza=0, data_modifica=NOW() WHERE id=?", [$id]);
    if ($resPub === false) debug_log("⚠️ set bozza=0 fallito per post $id", 'debug');

    // (Facoltativo) conserva ultime 10 versioni
    db_update(
      "
    DELETE FROM post_versions
    WHERE post_id=?
      AND id NOT IN (
        SELECT id FROM (SELECT id FROM post_versions WHERE post_id=? ORDER BY id DESC LIMIT 10) t
      )",
      [$id, $id]
    );

    echo json_encode(['success' => true, 'published' => 1]);
    exit;
  }

  echo json_encode(['success' => true, 'published' => 0]);
} catch (\Throwable $e) {
  $code = $e->getCode() ?: 500;
  debug_log("❌ handle_salva_post ERR[$code]: " . $e->getMessage(), 'debug');
  http_response_code($code);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

async function safeFetch(url, opt) {
  const r = await fetch(url, opt);
  const t = await r.text();
  if (r.status === 401 || /<title>.*Login/i.test(t)) {
    window.location.href = BASE_URL + 'login.php?expired=1';
    throw new Error('Sessione scaduta');
  }
  // se ti serve JSON:
  try { return JSON.parse(t); } catch { return t; }
}

