<?php
// lib/functions.php
require_once __DIR__ . '/config.php';

// Connessione PDO
function getPDO()
{
    static $pdo = null;
    if ($pdo === null) {
        $host = DB_HOST;
        $db = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;
        $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Errore connessione DB: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Recupera o crea una bozza di post
function crea_o_recupera_bozza($autore_id)
{
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM post WHERE autore_id = ? AND bozza = 1 ORDER BY data_creazione DESC LIMIT 1");
    $stmt->execute([$autore_id]);
    $bozza = $stmt->fetch();
    if ($bozza) return $bozza;

    $stmt = $pdo->prepare("INSERT INTO post (titolo, contenuto, autore_id, bozza, data_creazione) VALUES ('', '', ?, 1, NOW())");
    $stmt->execute([$autore_id]);
    $id = $pdo->lastInsertId();
    return [
        'id' => $id,
        'titolo' => '',
        'contenuto' => '',
        'cartella' => '',
        'musica' => '',
        'bozza' => 1,
        'autore_id' => $autore_id
    ];
}

function has_images_in_dir(string $dir, array $exts = ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG']): bool
{
    if (!is_dir($dir)) return false;
    try {
        foreach (new FilesystemIterator($dir, FilesystemIterator::SKIP_DOTS) as $f) {
            if ($f->isFile()) {
                $ext = pathinfo($f->getFilename(), PATHINFO_EXTENSION);
                if ($ext && in_array($ext, $exts, true)) return true;
            }
        }
    } catch (Throwable $e) { /* opzionale: debug_log(...) */
    }
    return false;
}

/**
 * Elenco completo delle cartelle *che contengono immagini*, relativo a $base_path.
 * Esclude radici non desiderate e la root stessa.
 */
function elenco_cartelle_con_immagini(string $base_path, array $exclude_roots = ['thumbs', 'web_wallpaper']): array
{
    $out = [];
    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base_path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($rii as $spl) {
        if (!$spl->isDir()) continue;
        $abs = $spl->getPathname();
        $rel = ltrim(str_replace($base_path, '', $abs), '/');
        if ($rel === '') continue; // salta root

        // esclusioni
        foreach ($exclude_roots as $ex) {
            if ($rel === $ex || str_starts_with($rel, $ex . '/')) continue 2;
        }

        if (has_images_in_dir($abs)) {
            $out[$rel] = $rel;
        }
    }
    ksort($out, SORT_NATURAL | SORT_FLAG_CASE);
    return $out;
}

/**
 * Versione migliorata dei suggerimenti (veloce + con stopwords/fuzzy leggero).
 * Puoi anche tenere la tua, ma questa è più parsimoniosa di I/O.
 */
function suggerisci_cartelle(string $titolo, string $base_path, array $opts = []): array
{
    $image_exts   = $opts['image_exts']   ?? ['jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'];
    $min_len      = $opts['min_len']      ?? 3;
    $max_results  = $opts['max_results']  ?? 25;
    $fuzzy_dist   = $opts['fuzzy_dist']   ?? 1;
    $exclude_roots = $opts['exclude_roots'] ?? ['thumbs', 'web_wallpaper'];
    $stopwords    = $opts['stopwords']    ?? [
        'di',
        'del',
        'della',
        'delle',
        'dei',
        'da',
        'dal',
        'dalla',
        'nelle',
        'nei',
        'con',
        'per',
        'tra',
        'fra',
        'nel',
        'una',
        'uno',
        'un',
        'il',
        'lo',
        'la',
        'le',
        'i',
        'gli',
        'che',
        'alla',
        'alle',
        'ai',
        'agli',
        'the',
        'of',
        'in',
        'on',
        'at',
        'to',
        'and',
        'for',
        'with',
        'from',
        'by',
        'a',
        'an'
    ];

    $norm = function (string $s): string {
        $s = mb_strtolower($s, 'UTF-8');
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        return $s ?: '';
    };
    $titolo_norm = $norm($titolo);
    preg_match_all('/\b[\p{L}\p{N}]+\b/u', $titolo_norm, $m);
    $tokens = array_values(array_filter($m[0] ?? [], function ($w) use ($stopwords, $min_len) {
        return mb_strlen($w, 'UTF-8') > $min_len && !in_array($w, $stopwords, true);
    }));
    if (empty($tokens)) return [];

    $scores = [];
    $rii = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base_path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($rii as $spl) {
        if (!$spl->isDir()) continue;
        $absDir  = $spl->getPathname();
        $relPath = ltrim(str_replace($base_path, '', $absDir), '/');
        if ($relPath === '') continue;

        foreach ($exclude_roots as $ex) {
            if ($relPath === $ex || str_starts_with($relPath, $ex . '/')) continue 2;
        }
        if (!has_images_in_dir($absDir, $image_exts)) continue;

        $base_norm = $norm(basename($absDir));
        if ($base_norm === '') continue;

        preg_match_all('/\b[\p{L}\p{N}]+\b/u', $base_norm, $mm);
        $dir_tokens = array_unique($mm[0] ?? []);

        $score = 0;
        foreach ($tokens as $tk) {
            if (in_array($tk, $dir_tokens, true)) {
                $score += 2;
                continue;
            }
            if ($fuzzy_dist > 0 && levenshtein($tk, $base_norm) <= $fuzzy_dist) {
                $score += 1;
            }
        }
        if ($score > 0) $scores[$relPath] = max($scores[$relPath] ?? 0, $score);
    }

    if (empty($scores)) return [];
    uasort($scores, fn($a, $b) => $b <=> $a);
    $top = array_slice($scores, 0, $max_results, true);

    $out = [];
    foreach ($top as $rel => $_) $out[$rel] = $rel;
    return $out;
}


function getValoreConfigurazione($campo)
{
    // Prepara la query per recuperare il valore del campo dalla vista 'last_conf'
    $sql = "SELECT $campo FROM last_conf LIMIT 1";

    // Ottieni la connessione PDO
    $pdo = getPDO();

    // Esegui la query
    $stmt = $pdo->query($sql);

    // Verifica se la query ha restituito risultati
    if ($stmt) {
        $row = $stmt->fetch();
        return $row ? $row[$campo] : null;
    }

    return null;  // Se non c'è risultato, restituisci null
}
function aggiornaConfigurazione($campo, $valore)
{
    // Assicurati che il codice per aggiornare la configurazione sia corretto
    $pdo = getPDO();  // La funzione di connessione PDO
    $sql = "UPDATE configurazione SET $campo = :valore WHERE id = (SELECT MAX(id) FROM configurazione)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':valore', $valore, PDO::PARAM_STR);
    $stmt->execute();
}

function debug_log($msg, $livello = 'debug')
{
    // livelli
    $livelli = ['none' => 0, 'error' => 1, 'warn' => 2, 'info' => 3, 'debug' => 4];
    $livello_attivo = defined('DEBUG_LEVEL') ? DEBUG_LEVEL : 'debug';
    $debug_attivo   = defined('DEBUG_VITTROS') ? DEBUG_VITTROS : false;
    if (!$debug_attivo || $livelli[$livello] > $livelli[$livello_attivo]) return;

    // prova a capire il post_id da varie fonti
    $pid = null;
    foreach (['post_id', 'id'] as $k) {
        if (isset($_POST[$k]) && ctype_digit((string)$_POST[$k])) {
            $pid = (int)$_POST[$k];
            break;
        }
        if (isset($_GET[$k])  && ctype_digit((string)$_GET[$k])) {
            $pid = (int)$_GET[$k];
            break;
        }
    }
    if ($pid === null && !empty($_SESSION['current_post_id'])) $pid = (int)$_SESSION['current_post_id'];
    if ($pid === null && !empty($_SERVER['HTTP_REFERER'])) {
        $q = [];
        parse_str(parse_url($_SERVER['HTTP_REFERER'], PHP_URL_QUERY) ?: '', $q);
        if (!empty($q['id']) && ctype_digit((string)$q['id'])) $pid = (int)$q['id'];
    }

    $logfile = defined('DEBUG_LOGFILE') ? DEBUG_LOGFILE : '/tmp/debug.log';
    $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
    $file = isset($bt[0]['file']) ? basename($bt[0]['file']) : '??';
    $line = $bt[0]['line'] ?? '??';
    $timestamp = date('[Y-m-d H:i:s]');
    if ($pid === null) {
        $entry = "$timestamp [$livello] ($file:$line) $msg\n";
    } else {
        $entry = "$timestamp [$livello] ($file:$line) [post_id=" . ($pid ?? 'n/d') . "] $msg\n";
    }
    @file_put_contents($logfile, $entry, FILE_APPEND);
}



function contiene_immagini($dir, $profondita = 3)
{
    if ($profondita <= 0) return false;
    foreach (scandir($dir) as $entry) {
        if ($entry === '.' || $entry === '..') continue;
        $full = "$dir/$entry";
        if (is_file($full) && preg_match('/\.(jpe?g|png)$/i', $entry)) {
            return true;
        } elseif (is_dir($full)) {
            if (contiene_immagini($full, $profondita - 1)) return true;
        }
    }
    return false;
}
function puo_visualizzare_post(array $post, ?array $utente = null, string $scope = 'pubblico'): bool
{
    // $scope: 'pubblico' = sito pubblico; 'backend' = area riservata / editor
    // Campi attesi in $post: id, autore_id, visibilita, bozza (0/1)

    $vis   = $post['visibilita'] ?? 'pubblico';
    $bozza = (int)($post['bozza'] ?? 0);
    $autoreId = (int)($post['autore_id'] ?? 0);

    // Utente (può essere null = visitatore anonimo)
    $uid   = $utente['id']   ?? 0;
    $ruolo = $utente['ruolo'] ?? null;

    // Admin / editor vedono tutto, sempre
    if ($ruolo === 'admin' || $ruolo === 'editor') {
        return true;
    }

    // Se è una BOZZA, solo l'autore può vederla (oltre ad admin/editor sopra)
    if ($bozza === 1) {
        return $uid > 0 && $uid === $autoreId;
    }

    // Da qui in giù: post pubblicati (bozza = 0)

    // (Opzionale) Regola speciale per 'ospite' in area backend:
    // nel backend l'ospite vede solo i propri post
    if ($scope === 'backend' && $ruolo === 'ospite') {
        return $uid > 0 && $uid === $autoreId;
    }

    // Visibilità
    switch ($vis) {
        case 'pubblico':
            // Visitatore anonimo incluso
            return true;

        case 'nat':
            // Autore sempre; altrimenti serve ruolo amik_nat
            if ($uid > 0 && $uid === $autoreId) return true;
            return ($ruolo === 'amik_nat');

        case 'privato':
            // Solo autore (admin/editor già gestiti sopra)
            return $uid > 0 && $uid === $autoreId;

        default:
            return false;
    }
}
// alias retro‑compatibile (con accento)
if (!function_exists('può_visualizzare_post')) {
    function può_visualizzare_post($utente, $post)
    {
        return puo_visualizzare_post($post, $utente, 'pubblico');
    }
}
function current_user_id()
{
    return $_SESSION['id'] ?? 0;
}
function user_can_edit_post($autoreId)
{
    $uid = current_user_id();
    $ruolo = $_SESSION['ruolo'] ?? null;
    if (in_array($ruolo, ['admin', 'editor'], true)) return true;
    return (int)$autoreId === (int)$uid;
}

function emoji_ruolo($ruolo)
{
    return [
        'admin'    => '👑',
        'editor'   => '✏️',
        'amico'    => '👥',
        'amik_nat' => '🌿',
        'ospite'   => '🙋'
    ][$ruolo] ?? '❓';
}
