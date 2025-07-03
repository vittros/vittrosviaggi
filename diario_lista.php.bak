<?php
session_start();
require_once 'lib/functions.php';
$pdo = getPDO();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if (isset($_GET['msg'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_GET['msg']) . '</div>';
}

$ruolo = $_SESSION['ruolo'] ?? 'ospite';

$sql = "SELECT * FROM post ORDER BY data_creazione DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8" />
<title>Elenco Post - VittRos Viaggi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container my-5">
    <h1>📖 Elenco Post</h1>
    <?php if (count($posts) === 0): ?>
        <p>Nessun post presente.</p>
    <?php else: ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Titolo</th>
                <th>Data</th>
                <th>Privato</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($posts as $post):
            $visibile = !$post['privato'] || in_array($ruolo, ['amico','editor','admin']);
            if (!$visibile) continue;
        ?>
            <tr>
                <td><?= htmlspecialchars($post['titolo']) ?></td>
                <td><?= htmlspecialchars($post['data_creazione']) ?></td>
                <td><?= $post['privato'] ? '✔' : '—' ?></td>
                <td>
                    <a href="visualizza_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-primary">Leggi</a>
                    <?php if (in_array($ruolo, ['editor','admin'])): ?>
                        <a href="modifica_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-warning">Modifica</a>
                    <?php endif; ?>
                    <?php if ($ruolo === 'admin'): ?>
                        <a href="cancella_post.php?id=<?= $post['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Sei sicuro di voler cancellare questo post?');">Elimina</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary mt-4">← Torna alla Home</a>
</div>
</body>
</html>
