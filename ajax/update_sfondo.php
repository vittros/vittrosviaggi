<?php
define('AJAX_MODE', true);
require_once '../lib/bootstrap.php';
require_once '../lib/db_utilities.php';

header('Content-Type: application/json');

$post_id = $_POST['post_id'] ?? 0;
$sfondo = $_POST['sfondo'] ?? '';

if (!$post_id) {
    echo json_encode(['success' => false, 'error' => 'ID post mancante']);
    exit;
}

$query = "UPDATE post SET sfondo = ?, data_modifica = NOW() WHERE id = ?";
if (db_update($query, [$sfondo, $post_id])) {
    debug_log("✅ sfondo aggiornato: post_id=$post_id, sfondo='$sfondo'", "info");
    echo json_encode(['success' => true]);
} else {
    debug_log("❌ Errore aggiornamento sfondo: post_id=$post_id", "error");
    echo json_encode(['success' => false, 'error' => 'Update fallito']);
}
