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

function debug_log($msg, $level = 'info', $file = DEBUG_LOGFILE)
{
    if (!defined('DEBUG_VITTROS') || !DEBUG_VITTROS) return;

    $livelli = ['none' => 0, 'error' => 1, 'warn' => 2, 'info' => 3, 'debug' => 4];

    if (!isset($livelli[DEBUG_LEVEL]) || $livelli[$level] > $livelli[DEBUG_LEVEL]) return;

    // Scrive nel log con data, livello e messaggio
    $riga = sprintf("[%s] [%s] %s\n", date("Y-m-d H:i:s"), strtoupper($level), $msg);
    file_put_contents($file, $riga, FILE_APPEND);
}
