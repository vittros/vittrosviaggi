<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'lib/config.php';

$ruolo = $_SESSION['ruolo'] ?? 'ospite';
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>VittRos Viaggi - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">

    <h1 class="text-center mb-4">🧭 VittRos Viaggi - Benvenuto <?= htmlspecialchars($_SESSION['username']) ?></h1>

    <nav class="mb-4">
        <a href="diario_lista.php" class="btn btn-primary me-2">📖 Lista Post</a>

        <?php if (in_array($ruolo, ['admin', 'editor'])): ?>
            <a href="crea_nuovo_post.php" class="btn btn-success me-2">✍️ Nuovo Post</a>
            <a href="gestione_post.php" class="btn btn-warning me-2">⚙️ Gestione Post</a>
        <?php endif; ?>

        <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
    </nav>

    <div>
        <p>Seleziona una voce dal menu per iniziare.</p>
    </div>

</div>

</body>
</html>
