<?php
// converti_errorlog.php
$directory = __DIR__;  // Puoi anche cambiare con un path assoluto se preferisci
$estensioni = ['php'];
$conteggio = 0;

function convertiFile($file)
{
    global $conteggio;

    $contenuto = file_get_contents($file);
    $pattern = '/\berror_log\s*\(\s*(.+?)\s*\)\s*;/s';

    // Solo se c'è almeno un match
    if (preg_match_all($pattern, $contenuto, $matches)) {
        $nuovo_contenuto = preg_replace_callback($pattern, function ($m) {
            return 'debug_log(' . trim($m[1]) . ', "info");';
        }, $contenuto);

        file_put_contents($file, $nuovo_contenuto);
        echo "✔️  Modificato: $file\n";
        $conteggio++;
    }
}

function esploraCartelle($cartella)
{
    $oggetti = scandir($cartella);
    foreach ($oggetti as $obj) {
        if ($obj === '.' || $obj === '..') continue;
        $path = "$cartella/$obj";
        if (is_dir($path)) {
            esploraCartelle($path);
        } elseif (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            convertiFile($path);
        }
    }
}

echo "🔍 Scansione cartelle da: $directory\n";
esploraCartelle($directory);
echo "✅ Conversione completata. File modificati: $conteggio\n";

