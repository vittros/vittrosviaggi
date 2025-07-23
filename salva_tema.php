<?php
// salva_tema.php - Salva il tema selezionato da admin.php
require_once 'lib/functions.php';
session_start();

if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    echo "Accesso negato.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tema'])) {
    $tema = basename($_POST['tema']); // sicurezza base
    aggiornaConfigurazione('tema_attivo', $tema);
    $_SESSION['tema_attivo'] = $tema;

    debug_log("🎨 Tema aggiornato a '$tema' da {$_SESSION['username']}", "info");
    header('Location: admin.php?ok=1');
    exit;
} else {
    echo "Richiesta non valida.";
}

