<?php
// /srv/http/vittrosviaggi_1.1/ajax/salva_post.php
declare(strict_types=1);

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/functions.php';
header('Content-Type: application/json; charset=utf-8');

try {
  if (function_exists('require_login')) require_login();

  $post_id   = (int)($_POST['post_id'] ?? 0);
  $titolo    = trim($_POST['titolo'] ?? '');
  $contenuto = $_POST['contenuto'] ?? '';
  $sfondo    = trim($_POST['sfondo'] ?? '');

  if ($post_id <= 0) throw new RuntimeException('post_id mancante');

  $db = getPDO();
  $db->beginTransaction();
  $db->prepare("UPDATE post SET titolo=?, contenuto=?, sfondo=?, updated_at=NOW() WHERE id=?")
     ->execute([$titolo, $contenuto, $sfondo, $post_id]);
  // Se hai una colonna 'status' o 'pubblicato', abilita la riga seguente adattandola:
  // $db->prepare("UPDATE post SET status='pubblicato', data_pubblicazione=NOW() WHERE id=?")->execute([$post_id]);
  $db->commit();

  echo json_encode(['success' => true]);
} catch (Throwable $e) {
  if (isset($db) && $db->inTransaction()) $db->rollBack();
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
