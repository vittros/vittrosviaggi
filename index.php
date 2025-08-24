<?php
require_once 'lib/bootstrap.php';
require_once 'lib/config.php';
require_once 'lib/functions.php';
$pagina_corrente = 'home';
require_once 'lib/header.php';
// Controllo dell'utente
if (!isset($_SESSION['user_id'])) {
    // Redireziona al modulo di login
    header('Location: login.php');
    exit();
}
$ruolo = $_SESSION['ruolo'] ?? 'ospite';
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>VittRos Viaggi - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/content.css">
    <link rel="stylesheet" href="css/theme-<?= htmlspecialchars($tema_attivo) ?>.css">
</head>

<!-- <body class="bg-light"> -->
<body class="page-home theme-<?= htmlspecialchars($tema_attivo) ?>">


    <div class="container my-5">

        <h1 class="text-center mb-4">🧭 VittRos Viaggi - Benvenuto <?= htmlspecialchars($_SESSION['username']) ?></h1>

        <nav class="mb-4">
            <a href="diario_lista.php" class="btn btn-primary me-2">📖 Lista Post</a>

            <?php if (in_array($ruolo, ['admin', 'editor'])): ?>
                <a href="crea_nuovo_post.php" class="btn btn-success me-2">✍️ Nuovo Post</a>
                <a href="gestione_post.php" class="btn btn-warning me-2">⚙️ Gestione Post</a>
            <?php endif;

            if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') {
                echo '<div class="admin-link" style="margin-top: 1em;"<br>
            <a href="admin.php" style="padding: 0.5em 1em; background: #0066cc; color: white; text-decoration: none; border-radius: 5px;">
                ⚙️ Amministrazione
            </a>
          </div>';
            }
            ?>
        </nav>

        <div>
            <p>Seleziona una voce dal menu per iniziare.</p>
            <br>
            <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
        </div>

    </div>

</body>
<?php include 'lib/footer.php'; ?>
</html>