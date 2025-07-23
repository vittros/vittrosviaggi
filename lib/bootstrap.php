<?php
// lib/bootstrap.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
define('DEBUG_VITTROS', true);  // ← metti a false per spegnere tutto
define('DEBUG_LEVEL', 'debug');  // 'none' => 0, 'error' => 1, 'warn' => 2, 'info' => 3, 'debug' => 4
define('DEBUG_LOGFILE', '/var/log/vittrosviaggi/sessione.log');
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/functions.php';

if (!isset($_SESSION['config'])) {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM last_conf");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $_SESSION['config'] = $config ?: [];
}

$tema_attivo = $_SESSION['config']['tema_attivo'] ?? 'default';
$progetto_attivo = $_SESSION['config']['progetto'] ?? 'vittrosviaggi';
$versione_app = $_SESSION['config']['versione'] ?? '1.0';
$developper = $_SESSION['config']['sviluppatore'] ?? 'vitti';
$data_modifica = $_SESSION['config']['data_modifica'] ?? '';

// Imposta il ruolo come ospite se non è ancora definito
if (!isset($_SESSION['ruolo'])) {
    $_SESSION['ruolo'] = 'ospite';
}

// Timeout sessione
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

// Solo ora che $tema_attuale è pronto, includi l'header
if (!defined('AJAX_MODE')) {
    require_once __DIR__ . '/header.php';
}

