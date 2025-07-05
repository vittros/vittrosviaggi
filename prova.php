<?php
$super = dirname($cartella_selezionata);

// Se sei nella root, disattiva il bottone
if ($cartella_selezionata !== ''): ?>
  <p>
    <button type="button" class="btn btn-light" onclick="selezionaCartella('<?= htmlspecialchars($super === '.' ? '' : $super) ?>')">
      🔝 ..
    </button>
  </p>
<?php endif; ?>


.scroll-box {
  text-align: left; /* <-- forza tutto a sinistra */
}

.foto-lista {
  list-style: none;
  padding-left: 10px;
  margin-top: 10px;
  text-align: left; /* doppia sicurezza */
}

.foto-lista li {
  padding: 2px 0;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
}
