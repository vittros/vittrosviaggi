<?php
declare(strict_types=1);
define('AJAX_MODE', true);
require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/db_utilities.php';
require_once __DIR__ . '/../lib/functions.php';

header('Content-Type: application/json; charset=UTF-8');

try {
  $post_id = (int)($_POST['post_id'] ?? 0);
  $relpath = trim($_POST['relpath'] ?? '');
  if ($post_id <= 0 || $relpath === '') throw new Exception('Parametri mancanti');

  $SRC_ROOT  = '/srv/http/leNostre';
  $PUB_ROOT  = '/srv/http/vittrosviaggi_1.1';
  $BASE_URL  = '/vittrosviaggi_1.1';

  $src_abs = realpath($SRC_ROOT . '/' . $relpath);
  if (!$src_abs || !str_starts_with($src_abs, $SRC_ROOT.'/')) throw new Exception('Sorgente non valida');

  // Leggi dimensioni
  [$w, $h, $type] = getimagesize($src_abs);
  if (!$w || !$h) throw new Exception('Immagine non leggibile');

  // Regola 1024: landscape -> limito larghezza, portrait -> limito altezza
  $limit = 1024;
  $is_landscape = $w >= $h;
  if ($is_landscape && $w > $limit) {
    $tw = $limit;
    $th = (int)round($h * ($limit/$w));
  } elseif (!$is_landscape && $h > $limit) {
    $th = $limit;
    $tw = (int)round($w * ($limit/$h));
  } else {
    $tw = $w;
    $th = $h;
  }

  // Crea risorsa GD sorgente
  switch ($type) {
    case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($src_abs); $ext = 'jpg'; break;
    case IMAGETYPE_PNG:  $src = imagecreatefrompng($src_abs);  $ext = 'jpg'; break; // forziamo jpg
    default: throw new Exception('Formato non supportato');
  }
  if (!$src) throw new Exception('Impossibile aprire immagine');

  // Ridimensiona
  $dst = imagecreatetruecolor($tw, $th);
  imagecopyresampled($dst, $src, 0,0,0,0, $tw,$th, $w,$h);

  // Cartella di destinazione: foto/post_{ID}
  $dest_dir = $PUB_ROOT . '/foto/post_' . $post_id;
  if (!is_dir($dest_dir)) @mkdir($dest_dir, 0775, true);

  // Filename
  $base = pathinfo($relpath, PATHINFO_FILENAME);
  $suffix = $is_landscape ? ("-w".$tw) : ("-h".$th);
  $dest_abs = $dest_dir . '/' . $base . $suffix . '.jpg';

  // Salva jpg (qualità 82)
  imagejpeg($dst, $dest_abs, 82);
  imagedestroy($dst);
  imagedestroy($src);

  $public_url = $BASE_URL . '/foto/post_' . $post_id . '/' . basename($dest_abs);

  // >>> Aggiorna cartella nel post
  $cartella = trim(dirname($relpath), '/'); // es: "2023/1225 Foto ..."
  if ($cartella !== '' && $post_id > 0) {
    db_exec("UPDATE post SET cartella = ?, data_modifica = NOW() WHERE id = ?", [$cartella, $post_id]);
    debug_log("🖼 Inserita immagine '$relpath' → '$public_url' (cartella='$cartella')", 'info');
  }

  echo json_encode(['success' => true, 'url' => $public_url]);
} catch (Throwable $e) {
  debug_log('❌ handler_ajax: ' . $e->getMessage(), 'error');
  http_response_code(400);
  echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
