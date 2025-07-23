<?php
$pagina_corrente = 'modifica';
require_once 'lib/bootstrap.php';
require_once 'lib/caricaTinyMCE.php';

$id_post = $_GET['id'] ?? null;
// ⚠️ In un progetto reale bisognerebbe validare l'ID

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM post WHERE id = ?");
$stmt->execute([$id_post]);
$post = $stmt->fetch();
$autore = $post['autore_id'] ?? '';
$titolo = $post['titolo'] ?? '';
$msg = "✅ L'utente: $autore sta modificando il POST: id=$id_post titolo=$titolo";
debug_log($msg, "info");


if (!$post) {
    echo "❌ Post non trovato.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titolo = $_POST['titolo'] ?? '';
    $contenuto = $_POST['contenuto'] ?? '';

    $stmt = $pdo->prepare("UPDATE post SET titolo = ?, contenuto = ?, data_modifica = NOW() WHERE id = ?");
    $stmt->execute([$titolo, $contenuto, $id_post]);

    // ✅ Logging salvataggio
    $stmt = $pdo->prepare("INSERT INTO user_history (session_id, version, user_id, ip_add, action) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        session_id(),
        $app_version ?? '1.0.0',
        $_SESSION['user_id'] ?? 0,
        $_SERVER['REMOTE_ADDR'] ?? 'IP?',
        'salva_post'
    ]);

    header("Location: index.php?saved=1");
    exit;
}
?>

<?php include 'lib/header.php'; ?>

<div class="editor-box">
  <form method="post" action="modifica_post.php?id=<?= htmlspecialchars($id_post) ?>">

    <h2 class="mb-3">Modifica post:</h2>
    <input type="text" name="titolo" class="form-control mb-2" value="<?= htmlspecialchars($post['titolo']) ?>" required>

    <p class="text-muted small">
      Creato il: <?= htmlspecialchars($post['data_creazione']) ?>
    </p>

    <textarea id="contenuto" name="contenuto"><?= htmlspecialchars($post['contenuto']) ?></textarea>

    <div class="btn-toolbar mt-4">
      <button type="submit" class="btn btn-success">💾 Salva</button>
      <a href="index.php" class="btn btn-secondary">❌ Annulla</a>
      <button type="button" class="btn btn-info" onclick="apriPopup(<?= $id_post ?>)">📷🎵 Multimedia</button>
      <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
    </div>
  </form>
</div>

<?php 
$usa_editor = true;
include 'lib/footer.php';
?>
<script>
  const titoloPost = <?= json_encode($titolo) ?>;

  function apriPopup(postId) {
    const url = `media_popup.php?post_id=${postId}&titolo=${encodeURIComponent(titoloPost)}`;
    window.open(
      url,
      'popupMultimedia',
      'width=850,height=600,resizable=yes,scrollbars=yes,toolbar=no,menubar=no,status=no'
    );
  }
</script>




