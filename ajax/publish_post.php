<?php
// ajax/publish_min.php
declare(strict_types=1);
define('AJAX_MODE', true);
require_once __DIR__ . '/../lib/bootstrap.php';   // niente output in bootstrap!
require_once __DIR__ . '/../lib/db_utilities.php';

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Metodo non consentito');
    }

    // Accetta sia id che post_id
    $id = (int)($_POST['id'] ?? $_POST['post_id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        throw new Exception('ID mancante o non valido');
    }

    // (opzionale) verifica permessi utente qui

    // Pubblica il post
    // Adatta i nomi colonna alla tua tabella (stato/published_at/updated_at...)
    db_execute(
        'UPDATE post SET stato = ?, published_at = NOW(), updated_at = NOW() WHERE id = ?',
        ['pubblicato', $id]
    );

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    // Log tecnico e risposta pulita
    error_log('publish_min.php: ' . $e->getMessage());
    $msg = $e->getMessage();
    echo json_encode(['success' => false, 'error' => $msg]);
}
