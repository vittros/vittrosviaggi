<?php
define('BASE_URL', '<?= BASE_URL ?>');
define('DB_HOST', 'localhost');
define('DB_USER', 'XXXX');   // <-- cambia con il tuo
define('DB_PASS', 'XXXXXX');     // <-- cambia con la tua
define('DB_NAME', 'vittrosviaggi');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}
?>

