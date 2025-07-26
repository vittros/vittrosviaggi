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

function suggerisci_cartelle($titolo, $base_path)
{
    preg_match_all('/\b\w+\b/u', strtolower($titolo), $matches);
    $parole = array_filter($matches[0], fn($w) => strlen($w) > 3);

    $cartelle = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base_path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $path) {
        $relPath = str_replace($base_path . '/', '', $path);

        // ESCLUDE thumbs e web_wallpaper
        if (str_starts_with($relPath, 'thumbs') || str_starts_with($relPath, 'web_wallpaper')) continue;

        if (!$path->isDir()) continue;

        $dirname = $path->getPathname();
        $relPath = str_replace($base_path . '/', '', $dirname);
        $basename = strtolower(basename($dirname));

        // Esclude le cartelle che NON contengono immagini
        $immagini = glob("$dirname/*.{jpg,jpeg,png,JPG,JPEG,PNG}", GLOB_BRACE);
        if (empty($immagini)) continue;

        // Controlla se il nome cartella contiene parole chiave
        foreach ($parole as $p) {
            if (strpos($basename, $p) !== false) {
                debug_log("✅ Suggerita: $relPath — contiene immagini e matcha '$p'", 'debug');
                $cartelle[$relPath] = $relPath;  // invece di solo basename()
                break;
            }
        }
    }

    return $cartelle;
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

function debug_log($msg, $livello = 'debug') {
    // Configurazione
    $livelli = ['none' => 0, 'error' => 1, 'warn' => 2, 'info' => 3, 'debug' => 4];

    $livello_attivo = defined('DEBUG_LEVEL') ? DEBUG_LEVEL : 'debug';
    $debug_attivo = defined('DEBUG_VITTROS') ? DEBUG_VITTROS : false;

    // Se disattivato, non fare nulla
    if (!$debug_attivo || $livelli[$livello] > $livelli[$livello_attivo]) {
        return;
    }

    $logfile = defined('DEBUG_LOGFILE') ? DEBUG_LOGFILE : '/tmp/debug.log';

    $bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
    $file = isset($bt[0]['file']) ? basename($bt[0]['file']) : '??';
    $line = $bt[0]['line'] ?? '??';
    $pid = $_POST['post_id'] ?? ($_GET['post_id'] ?? 'n/d');
    $timestamp = date('[Y-m-d H:i:s]');

    $entry = "$timestamp [$livello] ($file:$line) [post_id=$pid] $msg\n";
    @file_put_contents($logfile, $entry, FILE_APPEND);
}


function contiene_immagini($dir, $profondita = 3) {
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
function può_visualizzare_post($utente, $post) {
    $ruolo = $utente['ruolo'];
    $vis = $post['visibilita'];

    if ($ruolo === 'admin') return true;
    if ($ruolo === 'editor') return true;
    if ($ruolo === 'ospite') return $post['autore_id'] == $utente['id'];
    if ($ruolo === 'amik_nat') return in_array($vis, ['pubblico', 'nat']);
    if ($ruolo === 'amico') return $vis === 'pubblico';

    return false;
}
function emoji_ruolo($ruolo) {
    return [
        'admin'    => '👑',
        'editor'   => '✏️',
        'amico'    => '👥',
        'amik_nat' => '🌿',
        'ospite'   => '🙋'
    ][$ruolo] ?? '❓';
}
