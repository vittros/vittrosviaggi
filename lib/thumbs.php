<?php
function generaThumbnailSeNecessario(string $originalPath, string $thumbsBasePath, int $maxW = 300, int $maxH = 300): string {
    // debug_log("📂 generaThumb richiesto per: $originalPath", 'debug');
   
    if (!file_exists($originalPath)) return '';

    $relativePath = str_replace('/srv/http/leNostre/', '', $originalPath);
    $thumbPath = rtrim($thumbsBasePath, '/') . '/' . $relativePath;

    if (file_exists($thumbPath)) return $thumbPath;

    $thumbDir = dirname($thumbPath);
    if (!is_dir($thumbDir)) {
        mkdir($thumbDir, 0775, true);
        debug_log("📂 Cartella creata: $thumbDir", 'debug');
    }

    $imgData = @file_get_contents($originalPath);
    if (!$imgData) {
        debug_log("❌ Impossibile leggere il file origine: $originalPath", 'warn');
        return '';
    }

    $img = @imagecreatefromstring($imgData);
    if (!$img) {
        debug_log("❌ Impossibile creare immagine da stringa: $originalPath", 'warn');
        return '';
    }

    $origW = imagesx($img);
    $origH = imagesy($img);

    $ratio = min($maxW / $origW, $maxH / $origH);
    $newW = (int)($origW * $ratio);
    $newH = (int)($origH * $ratio);

    $thumb = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

    $success = imagejpeg($thumb, $thumbPath, 85);
    if ($success) {
        debug_log("🆕 Thumbnail creato: " . str_replace('/srv/http', '', $thumbPath), 'debug');
    } else {
        debug_log("❌ Errore nel salvataggio JPEG: $thumbPath", 'warn');
    }

    imagedestroy($img);
    imagedestroy($thumb);

    return $thumbPath;
}

?>
