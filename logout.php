<?php
require_once __DIR__ . '/lib/bootstrap.php';
session_unset();
if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
setcookie(session_name(), '', time() - 3600, '/');

// Risposta JSON se richiesto in AJAX
$isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == '1') ||
          (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && stripos($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') !== false);

if ($isAjax) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => true]);
  exit;
}

// altrimenti redirect classico
header('Location: login.php');
exit;
