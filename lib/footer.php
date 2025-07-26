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
  © <?= date('Y') ?> <strong><?= htmlspecialchars($developper) ?> & Linda'</strong> – v<?= $versione_app ?>, ultimo aggiornamento <?= $data_breve ?>
</footer>

<script>
  const footer = document.querySelector('footer');
  const zone = document.querySelector('.footer-hover-zone');
  let showTimeout = null;
  let hideTimeout = null;

  // Mostra il footer dopo 1 secondo se il mouse resta nella zona
  zone.addEventListener('mouseenter', () => {
    clearTimeout(showTimeout);
    clearTimeout(hideTimeout);
    showTimeout = setTimeout(() => {
      footer.style.opacity = '1';
      footer.style.pointerEvents = 'auto';
    }, 1000);
  });

  // Se il mouse esce dalla zona prima di 1 secondo, annulla il timer
  zone.addEventListener('mouseleave', () => {
    clearTimeout(showTimeout);
    // se era già visibile, programma la chiusura
    if (footer.style.opacity === '1') {
      hideTimeout = setTimeout(() => {
        footer.style.opacity = '0';
        footer.style.pointerEvents = 'none';
      }, 2000);
    }
  });

  // Se entri nel footer, resta visibile
  footer.addEventListener('mouseenter', () => {
    clearTimeout(hideTimeout);
  });

  // Se esci dal footer, si nasconde dopo 2 secondi
  footer.addEventListener('mouseleave', () => {
    hideTimeout = setTimeout(() => {
      footer.style.opacity = '0';
      footer.style.pointerEvents = 'none';
    }, 2000);
  });
</script>