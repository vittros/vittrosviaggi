<?php
// Da includere subito dopo session_start() e require dei file
if (!isset($pagina_corrente)) {
    $pagina_corrente = 'home';
}

require_once __DIR__ . '/functions.php';

$info_utente = '';
if (isset($_SESSION['username'], $_SESSION['ruolo'])) {
    $emoji = emoji_ruolo($_SESSION['ruolo']);
    $username = htmlspecialchars($_SESSION['username']);
    $ruolo = $_SESSION['ruolo'];
    $info_utente = <<<HTML
    <div style="
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: rgba(255,255,255,0.50);
        border: 1px solid #ccc;
        border-radius: 10px;
        padding: 0.3em 0.7em;
        font-size: 0.8em;
        color: #444;
        box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
        z-index: 999;
    ">
        $emoji <strong>$username</strong> <small>($ruolo)</small>
    </div>
    HTML;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>VittRos Viaggi - <?= ucfirst($pagina_corrente) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/content.css">
    <link rel="stylesheet" href="css/theme-<?= htmlspecialchars($tema_attivo) ?>.css?v=1">
</head>
<body class="page-<?= $pagina_corrente ?> theme-<?= htmlspecialchars($tema_attivo) ?>">
    <?= $info_utente ?>
