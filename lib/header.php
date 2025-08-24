<?php
// Da includere subito dopo session_start() e require dei file
if (!isset($pagina_corrente)) {
    $pagina_corrente = 'home';
}

require_once __DIR__ . '/functions.php';

function render_user_badge(): string {
  if (!isset($_SESSION['username'], $_SESSION['ruolo'])) return '';
  $emoji = emoji_ruolo($_SESSION['ruolo']);
  $username = htmlspecialchars($_SESSION['username']);
  $ruolo = htmlspecialchars($_SESSION['ruolo']);
  $base = defined('BASE_URL') ? BASE_URL : '/vittrosviaggi_1.1/';
  return <<<HTML
<div id="global-user-badge" class="user-badge">
  <span class="who">$emoji <strong>$username</strong> <small>($ruolo)</small></span>
  <button type="button" class="logout" data-logout>Logout</button>
</div>
<script>
(()=> {
//   const BASE = ${json_encode(defined('BASE_URL') ? BASE_URL : '/vittrosviaggi_1.1/')};
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-logout]');
    if (!btn) return;
    e.preventDefault();
    try { await fetch(BASE + 'logout.php?ajax=1', {method:'POST', credentials:'include'}); } catch(_) {}

    // se siamo in un popup, chiudi e porta la finestra principale al login
    if (window.opener && !window.opener.closed) {
      try { window.opener.location.href = BASE + 'login.php?bye=1'; } catch(_) {}
      window.close();
      setTimeout(()=>{ location.href = BASE + 'login.php?bye=1'; }, 200);
    } else {
      location.href = BASE + 'login.php?bye=1';
    }
  });
})();
</script>
HTML;
}
echo render_user_badge();

?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <title>VittRos Viaggi - <?= ucfirst($pagina_corrente) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/content.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/theme-<?= TEMA_ATTIVO ?>.css?v=1">
</head>

<body class="page-<?= $pagina_corrente ?> theme-<?= TEMA_ATTIVO ?>">
    <?= $info_utente ?>