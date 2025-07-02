<?php
$targetDir = "uploads/";

if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$file = $_FILES['file'];
$filename = basename($file['name']);
$targetFile = $targetDir . $filename;

if (move_uploaded_file($file['tmp_name'], $targetFile)) {
    echo json_encode(['location' => $targetFile]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Errore nel caricamento']);
}
?>

