<?php
require_once __DIR__ . '/ridimensiona_lib.php';

function ridimensiona_per_post(string $path, int $id_post): ?string
{
    $base_dir = '/srv/http/leNostre';
    $full_path = realpath("$base_dir/$path");

    if (!$full_path || !is_file($full_path) || strpos($full_path, realpath($base_dir)) !== 0) {
        debug_log("❌ Path non valido o immagine non trovata: $path", "info");
        return null;
    }

    return generaImmagineRidotta($full_path, $id_post);
}
