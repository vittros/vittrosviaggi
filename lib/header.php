<?php
// Da includere subito dopo session_start() e require dei file
if (!isset($pagina_corrente)) {
    $pagina_corrente = 'home';
}

require_once __DIR__ . '/functions.php';

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
