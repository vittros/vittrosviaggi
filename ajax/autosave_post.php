<?php
// ajax/autosave_post.php
declare(strict_types=1);

// niente echo/notice in output JSON
ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/functions.php';
require_once __DIR__ . '/../lib/db_utilities.php';

// se bootstrap fa session_start() di suo, evita doppio avvio
if (session_status() !== PHP_SESSION_ACTIVE) @session_start();

header('Content-Type: application/json; charset=UTF-8');

function jexit(array $o)
{
  // pulisci qualunque output precedente (notice ecc.)
  ob_clean();
  echo json_encode($o, JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  $post_id   = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
  $titolo    = trim($_POST['titolo']    ?? '');
  $contenuto = $_POST['contenuto']      ?? '';
  $sfondo    = trim($_POST['sfondo']    ?? '');

  debug_log("✍️ autosave ricevuto (titolo=\"" . mb_substr($titolo,0,60) . "\")", 'info');

  if ($post_id <= 0) jexit(['ok' => false, 'err' => 'post_id mancante']);

  // NB: la tabella ha "data_modifica" (non updated_at)
  db_exec(
    "UPDATE post 
       SET titolo = ?, contenuto = ?, sfondo = ?, bozza = 1, data_modifica = NOW() 
     WHERE id = ?",
    [$titolo, $contenuto, $sfondo, $post_id]
  );

  // log minimale per debugging
  jexit(['ok' => true, 'ping' => 'pong']);
  debug_log("✅ autosave post = $id_post titolo = $titolo", "info");
} catch (Throwable $e) {
  @file_put_contents(
    '/tmp/vit_autosave.log',
    date('c') . " ERR: " . $e->getMessage() . PHP_EOL,
    FILE_APPEND
  );
  jexit(['ok' => false, 'err' => $e->getMessage()]);
}

