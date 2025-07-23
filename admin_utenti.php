<?php
require_once 'lib/bootstrap.php';
if (!isset($_SESSION['ruolo']) || $_SESSION['ruolo'] !== 'admin') {
    echo "<p>Accesso negato. Solo per amministratori.</p>";
    exit;
}

require_once 'lib/functions.php'; // connessione DB
$db = getPDO();

$username = $_SESSION['username'];
debug_log("👀 L'utente $username entra in gestione utenti.", "info");

// Gestione aggiornamenti utente
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    if (isset($_POST['cambia_ruolo'])) {
        $nuovo_ruolo = $_POST['nuovo_ruolo'];
        $stmt = $db->prepare("UPDATE utenti SET ruolo = ? WHERE id = ?");
        $stmt->execute([$nuovo_ruolo, $id]);
    } elseif (isset($_POST['toggle_attivo'])) {
        $attivo = intval($_POST['attivo']) ? 0 : 1;
        $stmt = $db->prepare("UPDATE utenti SET attivo = ? WHERE id = ?");
        $stmt->execute([$attivo, $id]);
    } elseif (isset($_POST['elimina'])) {
        $stmt = $db->prepare("DELETE FROM utenti WHERE id = ?");
        $stmt->execute([$id]);
    }
}

// Carica tutti gli utenti
$stmt = $db->query("SELECT * FROM utenti ORDER BY id ASC");
$utenti = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Gestione Utenti - vittrosviaggi</title>
    <link rel="stylesheet" href="css/content.css">
    <link rel="stylesheet" href="css/theme-<?= htmlspecialchars($tema_attivo) ?>.css">
    <style>
        .admin-box {
            max-width: 1000px;
            margin: auto;
            padding: 2em;
            background: #fff;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2em;
        }

        th,
        td {
            border-bottom: 1px solid #ccc;
            padding: 0.5em;
            text-align: center;
        }

        form.inline {
            display: inline;
        }
    </style>
</head>

<body class="page-admin theme-<?= htmlspecialchars($tema_attivo) ?>">
    <div class="admin-box">
        <h1>Gestione Utenti</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Ruolo</th>
                    <th>Attivo</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($utenti as $u): ?>
                    <tr>
                        <td><?= $u['id'] ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                        <td>
                            <form method="post" class="inline">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <select name="nuovo_ruolo" <?= $u['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                    <?php foreach (['admin', 'editor', 'amico', 'ospite'] as $ruolo): ?>
                                        <option value="<?= $ruolo ?>" <?= $u['ruolo'] === $ruolo ? 'selected' : '' ?>><?= $ruolo ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <button name="cambia_ruolo">Salva</button>
                                <?php endif; ?>
                            </form>
                        </td>
                        <td>
                            <form method="post" class="inline">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="attivo" value="<?= $u['attivo'] ?>">
                                <button name="toggle_attivo"><?= $u['attivo'] ? '✅' : '❌' ?></button>
                            </form>
                        </td>
                        <td>
                            <form method="post" class="inline" onsubmit="return confirm('Sei sicuro di voler eliminare questo utente?')">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button name="elimina">🗑️</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p style="text-align: right;">
            <a href="admin_nuovo_utente.php" class="btn">➕ Crea nuovo utente</a>
        </p>
    </div>
    <div>
        <p style="text-align:center;">
            <a href="admin.php">⬅️ Torna al pannello admin</a>
            <a href="logout.php" class="btn">🚪 Logout</a>
        </p>
    </div>
</body>

</html>