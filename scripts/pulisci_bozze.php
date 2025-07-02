<?php
session_start();
require_once '../lib/functions.php';

$pdo = getPDO();

// Cancella bozze vecchie di oltre 1 ora (3600 secondi)
$stmt = $pdo->prepare("DELETE FROM post WHERE bozza = 1 AND data_creazione < (NOW() - INTERVAL 1 HOUR)");
$stmt->execute();

// Opzionale: anche bozze senza titolo e senza contenuto
$stmt = $pdo->prepare("DELETE FROM post WHERE bozza = 1 AND titolo = '' AND contenuto = ''");
$stmt->execute();

// Risposta per AJAX, se vuoi
echo "Bozze pulite";

