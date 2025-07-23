<?php
// ajax/handler_ajax.php - riceve path immagine, ridimensiona e restituisce URL ridotta
file_put_contents('/tmp/handler.log', "[".date('c')."] INIZIO handler_ajax.php\n", FILE_APPEND);

define('AJAX_MODE', true);
require_once '../lib/bootstrap.php';
require_once '../lib/ridimensiona_lib.php';

header('Content-Type: application/json');
file_put_contents("/tmp/debug_ajax.log", "🟡 handler_ajax.php avviato\n", FILE_APPEND);

$post_id = $_POST['post_id'] ?? 0;
$relpath = $_POST['relpath'] ?? '';

file_put_contents("/tmp/debug_ajax.log", "📦 post_id=$post_id, relpath=$relpath\n", FILE_APPEND);

if (!$post_id || !$relpath) {
    debug_log("❌ Parametri mancanti nella richiesta AJAX", "debug");
    file_put_contents('/tmp/handler.log', "[".date('c')."] post_id=$post_id, relpath=$relpath\n", FILE_APPEND);

    echo json_encode(['success' => false, 'error' => 'Parametri mancanti']);
    exit;
}

// Fix per evitare thumbs nel path
if (strpos($relpath, 'thumbs/') === 0) {
    $relpath = substr($relpath, 7);
    file_put_contents('/tmp/handler.log', "[".date('c')."] post_id=$post_id, relpath=$relpath\n", FILE_APPEND);
}

debug_log("🖼️ JS: selezionata immagine con path: $relpath", "info");

$origine = "/srv/http/leNostre/" . $relpath;
file_put_contents('/tmp/handler.log', "[".date('c')."] post_id=$post_id, relpath=$relpath\n", FILE_APPEND);

$url_finale = generaImmagineRidotta($origine, $post_id);
file_put_contents('/tmp/handler.log', "[".date('c')."] post_id=$post_id, relpath=$relpath\n", FILE_APPEND);


if ($url_finale) {
    file_put_contents("/tmp/debug_ajax.log", "✅ URL finale generato: $url_finale\n", FILE_APPEND);
    echo json_encode(['success' => true, 'url' => $url_finale]);
} else {
    file_put_contents("/tmp/debug_ajax.log", "❌ Errore nel ridimensionamento\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Errore nel ridimensionamento']);
}


?>
