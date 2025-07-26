<?php
// ajax/handler_ajax.php - riceve path immagine, ridimensiona e restituisce URL ridotta
file_put_contents('/tmp/handler.log', "[" . date('c') . "] INIZIO handler_ajax.php\n", FILE_APPEND);

define('AJAX_MODE', true);
require_once '../lib/bootstrap.php';
require_once '../lib/ridimensiona_lib.php';
require_once '../lib/db_utilities.php';

header('Content-Type: application/json');
$post_id = $_POST['post_id'] ?? 0;
$relpath = $_POST['relpath'] ?? '';
$sfondo = $_POST['sfondo'] ?? null;

debug_log("📥 Ricevuti parametri: post_id=$post_id, relpath=$relpath, sfondo=$sfondo", "info");


// 🔧 Fix prima di tutto
if (strpos($relpath, 'thumbs/') === 0) {
    $relpath = substr($relpath, 7);
}

// Ora è corretto!
$cartella = dirname($relpath);


file_put_contents("/tmp/debug_ajax.log", "📦 post_id=$post_id, relpath=$relpath\n", FILE_APPEND);

if (!$post_id || !$relpath) {
    debug_log("❌ Parametri mancanti nella richiesta AJAX", "debug");
    file_put_contents('/tmp/handler.log', "[" . date('c') . "] post_id=$post_id, relpath=$relpath\n", FILE_APPEND);

    echo json_encode(['success' => false, 'error' => 'Parametri mancanti']);
    exit;
}

// Fix per evitare thumbs nel path
if (strpos($relpath, 'thumbs/') === 0) {
    $relpath = substr($relpath, 7);
    file_put_contents('/tmp/handler.log', "[" . date('c') . "] post_id=$post_id, relpath=$relpath\n", FILE_APPEND);
}

debug_log("🖼️ JS: selezionata immagine con path: $relpath", "info");

$origine = "/srv/http/leNostre/" . $relpath;
file_put_contents('/tmp/handler.log', "[" . date('c') . "] post_id=$post_id, relpath=$relpath\n", FILE_APPEND);

$url_finale = generaImmagineRidotta($relpath, $post_id);
file_put_contents('/tmp/handler.log', "[" . date('c') . "] post_id=$post_id, relpath=$relpath\n", FILE_APPEND);

// 🔁 SPOSTA TUTTO QUESTO DENTRO if ($url_finale)

if ($url_finale) {
    debug_log("✅ URL finale generato: $url_finale\n", "info");
    file_put_contents("/tmp/debug_ajax.log", "✅ URL finale generato: $url_finale\n", FILE_APPEND);
    // AGGIORNA SOLO SE L’IMMAGINE È STATA CREATA
    if (!empty($post_id) && !empty($cartella)) {
        if ($sfondo !== null) {
            $query = "UPDATE post SET cartella = ?, sfondo = ?, data_modifica = NOW() WHERE id = ?";
            $params = [$cartella, $sfondo, $post_id];
            if (db_update($query, $params)) {
                debug_log("✅ post_id=$post_id aggiornato con cartella='$cartella' e sfondo='$sfondo'", "info");
            } else {
                debug_log("❌ Fallito UPDATE per post_id=$post_id", "info");
            }
        } else {
            db_update("UPDATE post SET cartella = ? WHERE id = ?", [$cartella]);
        }
        
        if (db_update($query, $params)) {
            debug_log("✅ post_id=$post_id aggiornato con cartella='$cartella' e sfondo='$sfondo'", "info");
        } else {
            debug_log("❌ Fallito UPDATE per post_id=$post_id", "info");
        }
    }

    $json = json_encode(['success' => true, 'url' => $url_finale]);
    debug_log("📤 Risposta JSON inviata: $json", "info");
    echo $json;
} else {
    $json = json_encode(['success' => false, 'error' => 'Errore nel ridimensionamento']);
    debug_log("📤 Risposta JSON errore: $json", "info");
    http_response_code(500);
    echo $json;
}
