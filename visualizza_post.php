<?php
session_start();
require_once 'lib/config.php';
require_once 'lib/colors.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$ruolo = $_SESSION['ruolo'] ?? 'ospite';

// Prendi l'id del post da GET e validalo
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die("ID post non valido.");
}

// Prendi il post dal DB PRIMA di usare sfondo!
$stmt = $pdo->prepare("SELECT * FROM post WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    die("Post non trovato.");
}

// Controllo permessi su post privato
if ($post['privato'] && !in_array($ruolo, ['amico', 'editor', 'admin'])) {
    die("Non hai i permessi per vedere questo post.");
}

// Ora prendi il colore base in base alla classe sfondo del post
$colori = coloriBase(); // mappa classe => colore

$classe_sfondo = $post['sfondo'] ?? '';
$colori = coloriBase(); // mappa classe => colore
$coloreBase = $colori[$classe_sfondo] ?? '#ffffff';

$coloreSfondo = coloriBase()[$classe_sfondo] ?? '#ffffff';
$coloreTesto = coloreTestoPerSfondo($coloreSfondo);
$coloreCornice = coloreCorniceDaSfondo($classe_sfondo);
$coloreBody = coloreBodyDaSfondo($classe_sfondo);

$coloreTesto = '#236fa1';
$coloreTitolo = $coloreTesto; // o scurisci($coloreTesto, 0.1);
$coloreData = desaturaColore($coloreTesto, 0.5);


// Qui puoi usare la funzione che calcola colori sfondo e cornice, per esempio:
function calcolaColori($coloreBase) {
    // funzione d'esempio che schiarisce o scurisce
    // Puoi inserire la tua logica più sofisticata qui
    $sfondo = $coloreBase;

    // Per la cornice, facciamo un colore più scuro: semplice scuro del 30%
    $cornice = scurisciColore($coloreBase, 30);
    return [$sfondo, $cornice];
}

function scurisciColore($hexColor, $percent) {
    $hexColor = ltrim($hexColor, '#');
    $rgb = sscanf($hexColor, "%02x%02x%02x");
    $newRgb = array_map(function($c) use ($percent) {
        return max(0, min(255, intval($c * (100 - $percent) / 100)));
    }, $rgb);
    return sprintf("#%02x%02x%02x", ...$newRgb);
}

list($coloreSfondo, $coloreCornice) = calcolaColori($coloreBase);

// Prendi le foto collegate
$stmt2 = $pdo->prepare("SELECT f.* FROM foto f JOIN post_foto pf ON f.id = pf.foto_id WHERE pf.post_id = ? ORDER BY pf.ordine ASC");
$stmt2->execute([$id]);
$foto = $stmt2->fetchAll();

?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8" />
<title><?= htmlspecialchars($post['titolo']) ?> - VittRos Viaggi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<link rel="stylesheet" href="<?= BASE_URL ?>css/content.css">
<style>
.img-gallery {
    max-width: 200px;
    margin: 10px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.2);
}
</style>
</head>
<!-- <body class="bg-light"> -->
<body style="background-color: <?= $coloreBody ?>;">

<div class="container my-5">
    <h1 style="color: <?= $coloreTesto ?>;"><?= htmlspecialchars($post['titolo']) ?></h1>
    <p style="color: <?= $coloreTesto ?>;">Creato il <?= htmlspecialchars($post['data_creazione']) ?></p>

    <div class="mb-4"
       style="background-color: <?= htmlspecialchars($coloreSfondo) ?>;
              border: 6px solid <?= htmlspecialchars($coloreCornice) ?>;
              padding: 15px;
              color: <?= htmlspecialchars($coloreTesto) ?>;">
        <?= $post['contenuto'] ?>
    </div>

    <?php if (count($foto) > 0): ?>
        <h4>📸 Foto collegate:</h4>
        <div class="d-flex flex-wrap">
            <?php foreach ($foto as $f): ?>
                <div>
                    <img src="<?= htmlspecialchars($f['percorso']) ?>" alt="<?= htmlspecialchars($f['descrizione']) ?>" class="img-gallery" />
                    <p><?= htmlspecialchars($f['didascalia']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="mt-4">
        <a href="diario_lista.php" class="btn btn-secondary">← Torna alla lista</a>
        <?php if (in_array($ruolo, ['editor', 'admin'])): ?>
            <a href="modifica_post.php?id=<?= $post['id'] ?>" class="btn btn-warning">✏️ Modifica</a>
        <?php endif; ?>
        <?php if ($ruolo === 'admin' || $_SESSION['user_id'] == $post['autore_id']): ?>
            <form method="post" action="cancella_post.php" onsubmit="return confirm('Sei sicuro di voler cancellare questo post?');" style="display:inline-block; margin-left: 10px;">
            <input type="hidden" name="id" value="<?= $post['id'] ?>">
            <button type="submit" class="btn btn-danger">🗑️ Elimina</button>
            </form>
        <?php endif; ?>

    </div>

</div>
</body>
</html>
