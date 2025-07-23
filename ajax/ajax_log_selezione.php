<?php
require_once '../lib/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['path'])) {
    $path = $_POST['path'];
    debug_log("JS: selezionata immagine con path: " . $path, "info");
    echo "OK";
} else {
    http_response_code(400);
    echo "Parametri mancanti";
}
?>
