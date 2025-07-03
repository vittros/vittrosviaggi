<?php
// Recupera il nome della cartella dal parametro GET
$cartella = $_GET['cartella'] ?? '';
if (!$cartella) {
    die("Cartella non specificata.");
}

// Base path e path completo
$base_dir = '/volume1/web/leNostre';
$target_dir = realpath($base_dir . '/' . $cartella);

// Sicurezza: assicurati che non esca dal base_dir
if (!$target_dir || strpos($target_dir, realpath($base_dir)) !== 0) {
    die("Accesso non consentito.");
}

// Funzione ricorsiva per trovare tutte le immagini
function trovaImmagini($dir) {
    $files = [];
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $files = array_merge($files, trovaImmagini($path));
        } elseif (preg_match('/\.(jpg|jpeg|png|gif)$/i', $item)) {
            $files[] = $path;
        }
    }
    return $files;
}

// Trova tutte le immagini
$immagini = [];
$items = scandir($target_dir);
foreach ($items as $item) {
    if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $item)) {
        $immagini[] = $target_dir . '/' . $item;
    }
}

// Output HTML
echo "<!DOCTYPE html><html lang='it'><head><meta charset='UTF-8'>";
echo "<title>Galleria: $cartella</title>";
echo <<<CSS
<style>
  body { font-family: sans-serif; }
  img { width: 150px; margin: 8px; border: 3px solid transparent; cursor: pointer; }
  img:hover { border-color: blue; }
  img.selezionata { border-color: red; }
</style>
CSS;
echo "</head><body>";
echo "<h2>Foto in: <code>" . htmlspecialchars($cartella) . "</code></h2>";

foreach ($immagini as $imgPath) {
    $webPath = str_replace('/volume1/web', '', $imgPath); // da path assoluto a URL web
    $safeUrl = htmlspecialchars($webPath);
    echo "<img src='$safeUrl' onclick='selectPhoto(this, \"$safeUrl\")'>";
}

echo <<<JS
<script>
function selectPhoto(el, url) {
    // Effetto visivo
    document.querySelectorAll('img').forEach(img => img.classList.remove('selezionata'));
    el.classList.add('selezionata');

    // Inserisce immagine nel CKEditor e chiude
    if (window.opener && window.opener.CKEDITOR) {
        window.opener.CKEDITOR.instances['contenuto'].insertHtml('<img src="' + url + '" style="max-width:100%;">');
        window.close();
    } else {
        alert("CKEditor non trovato nella finestra principale.");
    }
}
</script>
</body></html>
JS;
?>

