<?php
session_start();
require_once '../lib/functions.php';

if (!isset($_POST['id'], $_POST['titolo'])) {
    http_response_code(400);
    exit;
}

$id = (int)$_POST['id'];
$titolo = trim($_POST['titolo']);

if ($id <= 0 || $titolo === '') {
    http_response_code(400);
    exit;
}

$pdo = getPDO();

// Aggiorna titolo e segna bozza = 1 per sicurezza
$stmt = $pdo->prepare("UPDATE post SET titolo = ?, bozza = 1 WHERE id = ?");
$stmt->execute([$titolo, $id]);

http_response_code(200);

