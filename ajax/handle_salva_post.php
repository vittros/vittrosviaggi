<?php
// ajax/handle_salva_post.php
define('AJAX_MODE', true);
require_once '../lib/bootstrap.php';
require_once '../lib/db_utilities.php';
debug_log("💾 handle_salva_post.php chiamato con POST: " . print_r($_POST, true), 'debug');

$id = $_POST['id'] ?? null;
$titolo = $_POST['titolo'] ?? '';
$contenuto = $_POST['contenuto'] ?? '';
$sfondo = $_POST['sfondo'] ?? '';

if (!$id) {
  echo json_encode(['success' => false, 'error' => 'ID post mancante']);
  exit;
}

// Debug extra
debug_log("✏️ Scrivo nel DB il post $id con titolo='$titolo' e sfondo='$sfondo'", 'debug');

// Salvataggio
$sql = "UPDATE post SET titolo = ?, contenuto = ?, sfondo = ?, data_modifica = NOW() WHERE id = ?";
$res = db_update($sql, [$titolo, $contenuto, $sfondo, $id]);

if ($res) {
  debug_log("✅ Post $id aggiornato correttamente!", 'debug');
  echo json_encode(['success' => true]);
} else {
  debug_log("❌ Errore nell'UPDATE post $id", 'debug');
  echo json_encode(['success' => false, 'error' => 'Errore nel salvataggio']);
}

