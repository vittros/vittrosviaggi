<?php
require_once 'lib/bootstrap.php';
$pagina_corrente = 'lista'; // oppure 'lista' o altro se vuoi un nome più descrittivo

$pdo = getPDO();

if (isset($_GET['msg'])) {
    echo '<div class="alert alert-success">' . htmlspecialchars($_GET['msg']) . '</div>';
}

$ruolo = $_SESSION['ruolo'] ?? 'ospite';
$id = $_SESSION['id'] ?? null;
$username = $_SESSION['username'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

debug_log("👀 SESSION keys: id=$id, user_id=$user_id, username=$username, ruolo=$ruolo", "info");

// Query
$sql = "SELECT id, titolo, autore_id, data_creazione, visibilita, bozza FROM post ORDER BY data_creazione DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="it">
<head>
  <!-- ... -->
</head>

<body class="bg-light">
  <div class="container my-5 ">
    <div class="post-box">
      <h1>📖 Elenco Post</h1>
      <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
      <?php endif; ?>

      <?php if (!$posts): ?>
        <p>Nessun post presente.</p>
      <?php else: ?>
        <table class="table table-striped align-middle">
          <thead>
            <tr>
              <th>Titolo</th>
              <th>Data</th>
              <th>Visibilità</th>
              <th class="text-end">Azioni</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($posts as $post):
              if (!puo_visualizzare_post($post, $_SESSION ?? [])) continue;

              $isBozza = !empty($post['bozza']);
              $isOwner = isset($_SESSION['id']) && (int)$_SESSION['id'] === (int)$post['autore_id'];
              $ruolo   = $_SESSION['ruolo'] ?? null;
              $canEdit = $isOwner || in_array($ruolo, ['editor', 'admin'], true);
            ?>
            <tr>
              <td>
                <?= htmlspecialchars($post['titolo']) ?>
                <?php if ($isBozza): ?><span class="badge bg-secondary ms-1">Bozza</span><?php endif; ?>
              </td>
              <td><?= htmlspecialchars($post['data_creazione']) ?></td>
              <td><?= ['pubblico'=>'🌍','nat'=>'🌿','privato'=>'🔒'][$post['visibilita']] ?? '❓' ?></td>
              <td class="text-end">
                <a href="visualizza_post.php?id=<?= (int)$post['id'] ?>" class="btn btn-sm btn-primary">Leggi</a>
                <?php if ($canEdit): ?>
                  <a href="modifica_post.php?id=<?= (int)$post['id'] ?>" class="btn btn-sm btn-warning">Modifica</a>
                <?php endif; ?>
                <?php if ($canEdit && $isBozza): ?>
                  <button class="btn btn-sm btn-success" onclick="pubblicaRapido(<?= (int)$post['id'] ?>)">Pubblica</button>
                <?php endif; ?>
                <?php if ($ruolo === 'admin'): ?>
                  <a href="cancella_post.php?id=<?= (int)$post['id'] ?>" class="btn btn-sm btn-danger"
                     onclick="return confirm('Sei sicuro di voler cancellare questo post?');">Elimina</a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

      <a href="index.php" class="btn btn-secondary mt-4">← Torna alla Home</a>
    </div>
  </div>

  <?php include 'lib/footer.php'; // <-- ORA è DENTRO al <body> ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/disputil_toast.js"></script>
  <script>
  function pubblicaRapido(id){
    const body = new URLSearchParams({ id });
    fetch('ajax/publish_min.php', {
      method: 'POST',
      headers: {'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},
      body
    })
    .then(r => r.ok ? r.json() : Promise.reject('HTTP '+r.status))
    .then(j => {
      if (j?.ok || j?.success) {
        DispUtil.toast('✅ Post pubblicato', 'success');
        setTimeout(()=>location.reload(), 700);
      } else {
        DispUtil.toast('❌ Errore: '+(j?.err||'sconosciuto'), 'danger');
      }
    })
    .catch(e => DispUtil.toast('❌ Errore rete: '+e, 'danger'));
  }
  </script>
</body>
</html>