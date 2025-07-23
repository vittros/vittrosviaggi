<?php
// lib/footer.php
require_once 'lib/caricaTinyMCE.php';
if (isset($usa_editor) && $usa_editor === true) {
    caricaTinyMCE();
}



$data_format = date_create($data_modifica);
$data_breve = $data_format ? $data_format->format('d/m/Y') : '';
?>

<div class="footer-hover-zone"></div>

<footer>
  © <?= date('Y') ?> <strong><?= htmlspecialchars($developper)?> & Linda'</strong> – v<?= $versione_app ?>, ultimo aggiornamento <?= $data_breve ?>
</footer>

<script>
  const footer = document.querySelector('footer');
  const zone = document.querySelector('.footer-hover-zone');
  let hideTimeout;

  zone.addEventListener('mouseenter', () => {
    clearTimeout(hideTimeout);
    footer.style.opacity = '1';
    footer.style.pointerEvents = 'auto';
    hideTimeout = setTimeout(() => {
      footer.style.opacity = '0';
      footer.style.pointerEvents = 'none';
    }, 2000); // 5 secondi
  });

  footer.addEventListener('mouseenter', () => {
    clearTimeout(hideTimeout); // resta visibile se ci stai sopra
  });

  footer.addEventListener('mouseleave', () => {
    hideTimeout = setTimeout(() => {
      footer.style.opacity = '0';
      footer.style.pointerEvents = 'none';
    }, 2000); // 2 secondi dopo che esci
  });
</script>
