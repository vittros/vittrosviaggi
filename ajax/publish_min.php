<?php
declare(strict_types=1);
define('AJAX_MODE', true);
require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/db_utilities.php';

header('Content-Type: application/json; charset=utf-8');

function return_json(array $payload, int $code = 200): void {
  http_response_code($code);
  if (function_exists('ob_get_level')) { while (ob_get_level() > 0) { ob_end_clean(); } }
  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  flush();
  exit;
}

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return_json(['success' => false, 'error' => 'Metodo non consentito'], 405);
  }

  $id = (int)($_POST['id'] ?? $_POST['post_id'] ?? 0);
  if ($id <= 0) {
    return_json(['success' => false, 'error' => 'ID mancante o non valido'], 400);
  }

  // (opzionale) controlli utente/permessi qui

  // Marca pubblicato coerente col tuo schema
  $rows = db_exec("UPDATE post SET bozza=0, data_modifica=NOW() WHERE id=?", [$id]);

  // Snapshot published? Se vuoi tenerlo come il vecchio:
  // $post = db_select_row("SELECT * FROM post WHERE id=?", [$id]);
  // if ($post) {
  //   db_insert(
  //     "INSERT INTO post_versions (post_id, autore_id, titolo, contenuto, sfondo, stato_snapshot, created_at)
  //      VALUES (?,?,?,?,?, 'published', NOW())",
  //     [$id, current_user_id(), $post['titolo'], $post['contenuto'], $post['sfondo']]
  //   );
  //   // conserva ultime 10
  //   db_update("
  //     DELETE FROM post_versions
  //     WHERE post_id=? AND id NOT IN (
  //       SELECT id FROM (SELECT id FROM post_versions WHERE post_id=? ORDER BY id DESC LIMIT 10) t
  //     )", [$id, $id]
  //   );
  // }

  // Rileggi stato sintetico
  $row = db_select_row("SELECT id, bozza, data_modifica FROM post WHERE id=?", [$id]);

  return_json([
    'success' => true,
    'id' => $id,
    'affected_rows' => (int)$rows,
    'post' => $row,
    'note' => ($rows === 0 ? 'Nessuna riga modificata (già pubblicato?)' : 'Aggiornato')
  ]);
} catch (Throwable $e) {
  error_log('publish_min.php EX: ' . $e->getMessage());
  return_json(['success' => false, 'error' => $e->getMessage()], 500);
}
