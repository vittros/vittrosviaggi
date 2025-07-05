<?php
function mostra_form_post($post = null, $cartelle = [], $rel_path = '', $suggerite = []) {
    $titolo = $post['titolo'] ?? '';
    $contenuto = $post['contenuto'] ?? '';
    $cartella_selezionata = $post['cartella'] ?? $rel_path;
    $musica = $post['musica'] ?? '';
    $id = $post['id'] ?? '';
    $sfondo = $post['sfondo'] ?? '';

    $livello_superiore = dirname($cartella_selezionata);
    if ($livello_superiore === '.' || $cartella_selezionata === '') $livello_superiore = '';

    $ha_sottocartelle = count($cartelle) > 0;
?>
<form action="modifica_post.php" method="post">
  <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
  <input type="hidden" name="sfondo" id="sfondo" value="<?= htmlspecialchars($sfondo) ?>">
  <input type="hidden" name="cartella_foto" id="cartella_foto" value="<?= htmlspecialchars($cartella_selezionata) ?>">

  <div class="layout">
    <div class="sidebar-col">

      <div class="scroll-box">
  <?php if ($cartella_selezionata): ?>
    <div class="navigazione">
      <p><strong>📂 Selezionata:</strong> <?= htmlspecialchars($cartella_selezionata) ?></p>
<?php
$super = dirname($cartella_selezionata);
$puoi_salire = $cartella_selezionata !== '' && $super !== $cartella_selezionata;
if ($puoi_salire):
?>
  <p style="margin-bottom: 4px;">
    <button type="button" class="btn btn-light" onclick="selezionaCartella('<?= htmlspecialchars($super === '.' ? '' : $super) ?>')">
      🔝 ..
    </button>
  </p>
<?php endif; ?>

    </div>
  <?php endif; ?> 
        <ul class="cartelle-ul">
          <?php foreach ($cartelle as $c): 
            $path = trim($cartella_selezionata . '/' . $c, '/'); ?>
            <li><button type="button" class="cartella-btn" onclick="selezionaCartella('<?= htmlspecialchars($path) ?>')">📁 <?= htmlspecialchars($c) ?></button></li>
          <?php endforeach; ?>
<?php if (empty($cartelle) && $cartella_selezionata): ?>
    <!-- <p><em>Nessuna sottocartella trovata.</em></p> -->
    <ul class="foto-lista">
      <?php
        $base_path = '/srv/http/leNostre';
        $dir_foto = realpath($base_path . '/' . $cartella_selezionata);
        if ($dir_foto && is_dir($dir_foto)) {
            $files = scandir($dir_foto);
            foreach ($files as $f) {
                if (preg_match('/\.(jpe?g|png)$/i', $f)) {
                    echo "<li>🖼️ $f</li>";
                }
            }
        }
      ?>
    </ul>
<?php endif; ?>

        </ul>
      </div>

      <button type="button" class="btn btn-secondary" onclick="apriGalleria()" <?= $ha_sottocartelle ? 'disabled' : '' ?>>🖼️ Apri galleria</button>

      <?php if (!empty($suggerite)): ?>
        <details style="margin-top: 10px;">
          <summary>📎 Cartelle suggerite</summary>
          <ul class="cartelle-ul" style="margin-top: 5px;">
            <?php foreach ($suggerite as $path => $nome): ?>
              <li>
                <button type="button" class="cartella-btn" onclick="selezionaCartella('<?= htmlspecialchars($path) ?>')">📁 <?= htmlspecialchars($path) ?></button>
              </li>
            <?php endforeach; ?>
          </ul>
        </details>
      <?php endif; ?>
    </div>

    <div class="editor-col">
      <label for="titolo">Titolo:</label>
      <input type="text" name="titolo" id="titolo" value="<?= htmlspecialchars($titolo) ?>" required>

      <label for="contenuto">Contenuto:</label>
      <textarea name="contenuto" id="contenuto"><?= htmlspecialchars($contenuto) ?></textarea>

      <label for="musica">🎵 File musicale:</label>
      <input type="text" name="musica" value="<?= htmlspecialchars($musica) ?>">
    </div>
  </div>

  <div class="azioni">
    <button type="submit" name="azione" value="salva" class="btn btn-primary">💾 Salva</button>
    <button type="submit" name="azione" value="annulla" class="btn btn-secondary">↩️ Annulla</button>
  </div>
</form>

<script>
function selezionaCartella(path) {
  const id = <?= json_encode($id) ?>;
  window.location.href = `modifica_post.php?id=${encodeURIComponent(id)}&path=${encodeURIComponent(path)}`;
}
function apriGalleria() {
  const cartella = document.getElementById('cartella_foto').value;
  if (!cartella) {
    alert("Seleziona una cartella.");
    return;
  }
  const url = "galleria.php?cartella=" + encodeURIComponent(cartella);
  window.open(url, "Galleria", "width=800,height=600");
}
</script>

<style>
.layout { display: flex; gap: 20px; align-items: flex-start; }
.sidebar-col { flex: 1; max-width: 300px; }
.editor-col { flex: 2; min-width: 0; }
/* .scroll-box {
  max-height: 600px; overflow-y: auto;
  background: #f8f9fa; padding: 10px; border: 1px solid #ccc;
} */
.scroll-box {
  max-height: 600px;
  overflow-y: auto;
  background: #f8f9fa;
  padding: 6px 10px;
  border: 1px solid #ccc;
  font-size: 15px;
  text-align: left; /* <-- forza tutto a sinistra */
}

.scroll-box p {
  margin: 4px 0;
}

.cartella-btn {
  background: none; border: none; color: #007bff; cursor: pointer;
  padding: 5px 0; text-align: left;
}
.cartella-btn:hover { text-decoration: underline; }
input[type="text"], textarea {
  width: 100%; padding: 8px; margin-bottom: 10px;
}
.btn {
  margin-top: 10px; padding: 6px 12px;
  font-size: 14px; border-radius: 4px; border: none; cursor: pointer;
}
.btn-primary { background: #007bff; color: white; }
.btn-secondary { background: #6c757d; color: white; margin-left: 10px; }
<style>
...
.foto-lista {
  list-style: none;
  padding-left: 0px;
  text-align: left;
  margin-top: 10px;
}

.foto-lista li {
  padding: 2px 0;
  font-size: 14px;
}
</style>

</style>
<?php } ?>
