<?php
// lib/colors.php
// Cambia luminosità del colore esadecimale: $percent negativo scurisce, positivo schiarisce
function cambiaLuminositaColore(string $hexColor, float $percent): string {
    $hexColor = ltrim($hexColor, '#');

    if (strlen($hexColor) == 3) {
        $hexColor = $hexColor[0].$hexColor[0].$hexColor[1].$hexColor[1].$hexColor[2].$hexColor[2];
    }

    $r = hexdec(substr($hexColor, 0, 2));
    $g = hexdec(substr($hexColor, 2, 2));
    $b = hexdec(substr($hexColor, 4, 2));

    $r = max(0, min(255, intval($r + ($percent * 255))));
    $g = max(0, min(255, intval($g + ($percent * 255))));
    $b = max(0, min(255, intval($b + ($percent * 255))));

    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

// Colori base associati alle classi sfondo
function coloriBase(): array {
    return [
        'bg-azzurro' => '#a8d0ff',
        'bg-giallo' => '#fff9cc',
        'bg-verde' => '#d4f1be',
        // aggiungi qui altre classi e colori base
    ];
}

// Calcola il colore cornice dato il nome della classe sfondo e la percentuale di scurimento (default -15%)
function coloreCorniceDaSfondo(string $classe_sfondo, float $percent = -0.15): string {
    $colori = coloriBase();
    $colore_base = $colori[$classe_sfondo] ?? '#ffffff';
    return cambiaLuminositaColore($colore_base, $percent);
}
// Riceve un colore esadecimale tipo #RRGGBB
// e un valore percentuale per schiarire o scurire (- per scurire, + per schiarire)
function modificaColore($hexColor, $percent) {
    // Rimuove #
    $hexColor = ltrim($hexColor, '#');

    // Converte in RGB
    $r = hexdec(substr($hexColor, 0, 2));
    $g = hexdec(substr($hexColor, 2, 2));
    $b = hexdec(substr($hexColor, 4, 2));

    // Modifica RGB
    $r = max(0, min(255, $r + round(255 * $percent / 100)));
    $g = max(0, min(255, $g + round(255 * $percent / 100)));
    $b = max(0, min(255, $b + round(255 * $percent / 100)));

    // Ritorna in esadecimale con #
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}
function desaturaColore(string $hex, float $percentuale = 0.5): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) return '#999999'; // fallback

    list($r, $g, $b) = [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    ];

    // Calcolo la luminanza media
    $media = (int)(($r + $g + $b) / 3);

    // Desatura verso il grigio
    $r = (int)($r + ($media - $r) * $percentuale);
    $g = (int)($g + ($media - $g) * $percentuale);
    $b = (int)($b + ($media - $b) * $percentuale);

    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

// Funzione principale che ritorna array [sfondo_chiaro, bordo_scuro]
function coloriDaBase($baseColor) {
    $sfondo = modificaColore($baseColor, +30);   // +30% più chiaro
    $bordo = modificaColore($baseColor, -40);    // -40% più scuro
    return [$sfondo, $bordo];
}

function coloreBodyDaSfondo(string $classe): string {
    $mappa = coloriBase();
    $colore = $mappa[$classe] ?? '#ffffff';

    // schiarisce il colore (fattore tra 0 e 1)
    $fattore = 0.2;
    list($r, $g, $b) = sscanf($colore, "#%02x%02x%02x");
    $r = min(255, (int) ($r + (255 - $r) * $fattore));
    $g = min(255, (int) ($g + (255 - $g) * $fattore));
    $b = min(255, (int) ($b + (255 - $b) * $fattore));
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}
function coloreTestoPerSfondo(string $bgHex): string {
    $bgHex = ltrim($bgHex, '#');

    if (strlen($bgHex) === 3) {
        // Supporto formato #abc
        $bgHex = $bgHex[0].$bgHex[0].$bgHex[1].$bgHex[1].$bgHex[2].$bgHex[2];
    }

    $r = hexdec(substr($bgHex, 0, 2));
    $g = hexdec(substr($bgHex, 2, 2));
    $b = hexdec(substr($bgHex, 4, 2));

    // Calcolo luminanza percepita
    $luminanza = (0.299 * $r + 0.587 * $g + 0.114 * $b);

    return ($luminanza > 150) ? '#222222' : '#f9f9f9';  // chiaro → testo scuro, scuro → testo chiaro
}

