<?php
require_once __DIR__ . '/../lib/db_utilities.php';
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
    // Dopo aver fatto la copia...
    if (!empty($id_post) && !empty($cartella)) {
        if (db_update("UPDATE post SET cartella = ? WHERE id = ?", [$cartella, $id_post])) {
            debug_log("📂 Tabella post aggiornata con data modifica per il post: $id_post", 'info');
        } else {
            debug_log("❌ Fallito aggiornamento tabella post con data modifica per il post: $id_post", 'info');
        }
    }
} else {
    debug_log("❌ Errore durante il ridimensionamento", "info");
    http_response_code(500);
    echo "Errore interno";
}
