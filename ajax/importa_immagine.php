<?php
define('AJAX_MODE', true);
require_once '../lib/bootstrap.php';  // Include sessione, log, permessi

$id_post = (int)($_POST['post_id'] ?? 0);
$src_rel = $_POST['path'] ?? '';
$src_abs = "/srv/http/leNostre/$src_rel";
$dest_dir = "/srv/http<?= BASE_URL ?>foto/post_$id_post";

if (!file_exists($src_abs) || !is_file($src_abs)) {
    debug_log("❌ Immagine sorgente non trovata: $src_abs", 'error');
    http_response_code(400);
    echo json_encode(['error' => 'File non trovato']);
    exit;
}

// Crea cartella se non esiste
if (!is_dir($dest_dir)) {
    mkdir($dest_dir, 0775, true);
    debug_log("📂 Cartella creata: $dest_dir", 'debug');
}

// Calcola destinazione finale
$basename = basename($src_rel);
$dest_file = "$dest_dir/$basename";

// Ridimensiona (sovrascrive se già esiste)
require_once '../lib/ridimensiona_lib.php';
if (ridimensiona_immagine($src_abs, $dest_file, 1280, 1024)) {
    debug_log("✅ Immagine copiata e ridotta: $dest_file", 'info');
    echo json_encode(['success' => true, 'url' => "<?= BASE_URL ?>foto/post_$id_post/$basename"]);
} else {
    debug_log("❌ Errore nel ridimensionamento", 'error');
    http_response_code(500);
    echo json_encode(['error' => 'Errore durante la copia']);
}
