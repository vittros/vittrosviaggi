<?php
$cartella = trim(urldecode($_GET['cartella'] ?? ''), '/');
if (!$cartella) die("Cartella non specificata.");

$base_dir = realpath('/srv/http/leNostre');
$target_dir = $base_dir . '/' . $cartella;

// DEBUG:
echo "<pre>";
echo "base_dir = $base_dir\n";
echo "cartella = $cartella\n";
echo "target_dir = $target_dir\n";
echo "realpath(target_dir) = " . realpath($target_dir) . "\n";
echo "check = " . strpos(realpath($target_dir), $base_dir) . "\n";
echo "</pre>";

if (!is_dir($target_dir) || strpos(realpath($target_dir), $base_dir) !== 0) {
    die("Accesso non consentito.");
}


// Cerca immagini
$immagini = [];
foreach (scandir($target_dir) as $item) {
    if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $item)) {
        $immagini[] = $target_dir . '/' . $item;
    }
}

echo "<!DOCTYPE html><html lang='it'><head><meta charset='UTF-8'><title>Galleria</title>";
echo "<style>
body { font-family: sans-serif; padding: 20px; }
img { width: 150px; margin: 8px; border: 3px solid transparent; cursor: pointer; }
img:hover { border-color: blue; }
img.selezionata { border-color: red; }
</style></head><body>";
echo "<h2>📸 Foto in: <code>" . htmlspecialchars($cartella) . "</code></h2>";

foreach ($immagini as $imgPath) {
    $webPath = str_replace(realpath('/srv/http'), '', realpath($imgPath));
    $safeUrl = htmlspecialchars($webPath);
    echo "<img src='$safeUrl' onclick='selectPhoto(this, \"$safeUrl\")'>";
}

echo <<<JS
<script>
function selectPhoto(el, url) {
  document.querySelectorAll('img').forEach(img => img.classList.remove('selezionata'));
  el.classList.add('selezionata');
  if (window.opener && window.opener.tinymce) {
    const editor = window.opener.tinymce.get("contenuto");
    editor.insertContent('<img src="' + url + '" style="max-width:100%;">');
    window.close();
  } else {
    alert("TinyMCE non trovato.");
  }
}
</script>
</body></html>
JS;
