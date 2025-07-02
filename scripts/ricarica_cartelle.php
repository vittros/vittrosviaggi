<?php
session_start();
require_once '../lib/functions.php';

header('Content-Type: application/json');

if (!isset($_POST['id'])) {
    echo json_encode([]);
    exit;
}

$id = (int)$_POST['id'];
$pdo = getPDO();

$stmt = $pdo->prepare("SELECT titolo FROM post WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    echo json_encode([]);
    exit;
}

$base_path = '/srv/http/leNostre'; // aggiorna se serve
$cartelle = suggerisci_cartelle($post['titolo'], $base_path);

echo json_encode(array_keys($cartelle));

