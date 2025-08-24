<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// porta sempre user_id -> id
if (isset($_SESSION['user_id']) && empty($_SESSION['id'])) {
  $_SESSION['id'] = (int)$_SESSION['user_id'];
}
// porta eventuale utente['id'] -> id
if (isset($_SESSION['utente']['id']) && empty($_SESSION['id'])) {
  $_SESSION['id'] = (int)$_SESSION['utente']['id'];
}

// porta eventuale utente['ruolo'] -> ruolo
if (isset($_SESSION['utente']['ruolo']) && empty($_SESSION['ruolo'])) {
  $_SESSION['ruolo'] = $_SESSION['utente']['ruolo'];
}

// helper robusti
if (!function_exists('current_user_id')) {
  function current_user_id(): int {
    return (int)($_SESSION['id'] ?? 0);
  }
}
if (!function_exists('current_user_role')) {
  function current_user_role(): ?string {
    return $_SESSION['ruolo'] ?? null;
  }
}
if (!function_exists('is_user_logged_in')) {
  function is_user_logged_in(): bool {
    $id = current_user_id();
    $ruolo = current_user_role();
    return $id > 0 || !empty($ruolo);
  }
}
if (!function_exists('user_can_edit_post')) {
  function user_can_edit_post($autoreId): bool {
    $ruolo = current_user_role();
    if (in_array($ruolo, ['admin','editor'], true)) return true;
    return current_user_id() === (int)$autoreId;
  }
}


// Timeout
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit;
}
$_SESSION['last_activity'] = time();

// Include header
if (!defined('AJAX_MODE')) {
    require_once __DIR__ . '/header.php';
}
