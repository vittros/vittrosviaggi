<?php
// ajax/restore_version.php
define('AJAX_MODE', true);
require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/db_utilities.php';
require_once __DIR__ . '/../lib/functions.php';
header('Content-Type: application/json; charset=utf-8');

try {
  $ver_id = (int)($_POST['version_id'] ?? 0);
  if ($ver_id <= 0) throw new \Exception('missing_version_id', 400);

  $ver = db_select_row("SELECT * FROM post_versions WHERE id=?", [$ver_id]);
  if (!$ver) throw new \Exception('version_not_found', 404);

  $post_id = (int)$ver['post_id'];
  $post = db_select_row("SELECT id, autore_id FROM post WHERE id=?", [$post_id]);
  if (!$post) throw new \Exception('post_not_found', 404);
  if (!user_can_edit_post($post['autore_id'])) throw new \Exception('forbidden', 403);

  // Ripristina come bozza (non pubblichiamo automaticamente)
  $ok = db_update(
    "UPDATE post SET titolo=?, contenuto=?, sfondo=?, data_modifica=NOW() WHERE id=?",
    [$ver['titolo'], $ver['contenuto'], $ver['sfondo'], $post_id]
  );
  if ($ok === false) throw new \Exception('restore_failed', 500);

  echo json_encode(['ok'=>true]);

} catch (\Throwable $e) {
  $code = $e->getCode() ?: 500;
  debug_log("❌ restore_version ERR[$code]: ".$e->getMessage(), 'debug');
  http_response_code($code);
  echo json_encode(['ok'=>false, 'err'=>$e->getMessage()]);
}
