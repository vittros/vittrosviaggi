<?php
require_once 'lib/bootstrap.php';
session_start();
debug_log("✅ log - Logout per utente: $user", "info");
session_unset();
session_destroy();
header('Location: login.php');
exit;
