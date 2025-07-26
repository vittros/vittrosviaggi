<?php
// Libreria per il ridimensionamento delle immagini per vittrosviaggi
// Funzione principale: generaImmagineRidotta($origine, $id_post)

function generaImmagineRidotta($relpath, $id_post)
{
    $base_input = "/srv/http/leNostre";
    $base_output = "/srv/http/vittrosviaggi/foto/post_$id_post";
    $web_output = "/vittrosviaggi/foto/post_$id_post";
    $basename = basename($relpath);

    $origine = realpath("$base_input/$relpath");
    if (!$origine || !file_exists($origine)) {
        debug_log("❌ Impossibile leggere il file origine: $origine", "info");
        return false;
    }

    $dest = "$base_output/$basename";

    debug_log("📁 Origine assoluta: $origine", "info");
    debug_log("📂 Destinazione: $dest", "info");
    umask(0002); // Per permessi 775 / 664
    if (!file_exists($base_output)) {
        if (!mkdir($base_output, 0775, true)) {
            debug_log("❌ Errore nella creazione di $base_output", "info");
            return null;
        } else {
            debug_log("✅ Cartella creata: $base_output", "info");
        }
    }

    if (file_exists($dest)) {
        debug_log("↩ Già esistente, ritorno diretto.", "info");
        return "$web_output/$basename";
    }

    $img_data = @file_get_contents($origine);
    if (!$img_data) {
        debug_log("❌ Impossibile leggere il file origine.", "info");
        return null;
    }

    $img = @imagecreatefromstring($img_data);
    if (!$img) {
        debug_log("❌ Impossibile creare immagine da stringa.", "info");
        return null;
    }

    $orig_w = imagesx($img);
    $orig_h = imagesy($img);
    $max_w = 1200;
    $max_h = 900;
    $ratio = min($max_w / $orig_w, $max_h / $orig_h);
    $new_w = (int)($orig_w * $ratio);
    $new_h = (int)($orig_h * $ratio);

    $thumb = imagecreatetruecolor($new_w, $new_h);
    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $new_w, $new_h, $orig_w, $orig_h);

    if (!imagejpeg($thumb, $dest, 90)) {
        debug_log("❌ Errore nel salvataggio JPEG.", "info");
        return null;
    } else {
        debug_log("✅ Immagine JPEG salvata correttamente: $dest", "info");
    }


    imagedestroy($img);
    imagedestroy($thumb);

    return "$web_output/$basename";
}
