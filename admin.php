<?php
require_once 'lib/bootstrap.php';
$salvataggio_ok = isset($_GET['ok']);

$temi_disponibili = ['default', 'estate', 'birra'];
$username = $_SESSION['username'];
debug_log("👀 L'utente $username entra in amministrazione.", "info");
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>Amministrazione - vittrosviaggi</title>
    <link rel="stylesheet" href="css/content.css">
    <link id="tema-css" rel="stylesheet" href="css/theme-<?= htmlspecialchars($tema_attivo) ?>.css">

    <style>
        .admin-box {
            max-width: 800px;
            margin: auto;
            padding: 2em;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }

        h1 {
            text-align: center;
        }

        select {
            padding: 0.5em;
        }
    </style>
</head>

<body class="page-admin theme-<?= htmlspecialchars($tema_tema_attivoattuale) ?>">
    <div class="admin-box">
        <h1>Amministrazione</h1>
        <form method="post" action="salva_tema.php" oninput="aggiornaPreview()">
            <label for="tema">Tema attivo:</label>
            <select name="tema" id="tema" onchange="aggiornaPreview()">
                <?php foreach ($temi_disponibili as $tema): ?>
                    <option value="<?= $tema ?>" <?= $tema === $tema_attivo ? 'selected' : '' ?>><?= ucfirst($tema) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Salva</button>
            <?php if ($salvataggio_ok): ?>
                <div class="alert alert-success text-center">✅ Tema salvato correttamente.</div>
            <?php endif; ?>
        </form>

        <h2>Strumenti di amministrazione</h2>
        <ul style="line-height: 2em;">
            <li><a href="admin_utenti.php">👥 Gestione utenti</a></li>
            <!-- In futuro: -->
            <li><a href="admin_post.php">📝 Gestione post</a></li>
        </ul>
        
        <h2>Debug & Log</h2>
        <table style="margin: auto; text-align: left; font-family: monospace;">
            <tr>
                <th>Debug attivo:</th>
                <td><?= DEBUG_VITTROS ? '✅ Sì' : '❌ No' ?></td>
            </tr>
            <tr>
                <th>Livello log:</th>
                <td><?= htmlspecialchars(DEBUG_LEVEL) ?></td>
            </tr>
            <tr>
                <th>File di log:</th>
                <td><?= htmlspecialchars(DEBUG_LOGFILE) ?></td>
            </tr>
        </table>

        <?php if (file_exists(DEBUG_LOGFILE)): ?>
            <details style="margin-top:1em;">
                <summary>📄 Visualizza ultimi log</summary>
                <pre style="max-height: 300px; overflow-y: auto; background: #eee; padding: 1em; border-radius: 8px;">
<?= htmlspecialchars(shell_exec("tail -n 50 " . DEBUG_LOGFILE)) ?>
        </pre>
            </details>
        <?php else: ?>
            <p style="text-align:center; color: red;">⚠️ File di log non trovato: <?= htmlspecialchars(DEBUG_LOGFILE) ?></p>
        <?php endif; ?>


        <hr>
        <p>In futuro: gestione utenti, post, assegnazioni, esportazione...</p>
    </div>
    <div>
        <p style="text-align:center; margin-top: 2em;">
            <a href="index.php"> ⬅️ Torna alla home</a>
            <a href="logout.php">🚪 Logout</a>
        </p>
    </div>

    <script>
        function aggiornaPreview() {
            const tema = document.getElementById('tema').value;

            // Cambia classe nel body
            document.body.className = 'page-admin theme-' + tema;

            // Cambia href del link CSS
            const linkTema = document.getElementById('tema-css');
            linkTema.setAttribute('href', 'css/theme-' + tema + '.css');
        }
    </script>

</body>

</html>