<?php
debug_log("✅ Inizio script");


require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/ridimensiona_funzioni.php';

$path = $_GET['path'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

debug_log("🔍 ridimensiona.php chiamato: id=$id, path=$path", "info");

if ($path === '' || $id <= 0) {
    debug_log("❌ Parametri mancanti o non validi", "info");
    http_response_code(400);
    echo "Parametri non validi";
    exit;
}

$url = ridimensiona_per_post($path, $id);

if ($url) {
    debug_log("✅ Immagine ridotta salvata: $url", "info");
    echo $url;
} else {
    debug_log("❌ Errore durante il ridimensionamento", "info");
    http_response_code(500);
    echo "Errore interno";
}
