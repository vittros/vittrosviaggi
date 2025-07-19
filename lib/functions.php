<?php
// lib/functions.php

define('BASE_URL', '/vittrosviaggi/');

// Connessione PDO
function getPDO() {
    static $pdo = null;
    if ($pdo === null) {
        $host = 'localhost';
        $db = 'vittrosviaggi';
        $user = 'xxxx';
        $pass = 'xxxx';
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

// TinyMCE: script e inizializzazione
function caricaTinyMCE() {
    echo <<<EOT
<script src="/libs/tinymce/tinymce.min.js"></script>
<script>
tinymce.init({
    selector: '#contenuto',
    plugins: 'lists link image media code table',
    toolbar: 'undo redo | styleselect | fontfamily fontsize | bold italic underline | forecolor backcolor | sfondoSelect | alignleft aligncenter alignright | bullist numlist | link image media | code',
    content_css: '/css/content.css',
    height: 400,
    font_family_formats: 'Arial=arial,helvetica,sans-serif; Courier New=courier new,courier; Georgia=georgia,palatino; Tahoma=tahoma,arial,helvetica; Verdana=verdana,geneva',
    font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
    setup: function(editor) {
        const CLASSI_SFONDO = {
            '': 'Default (nessuno)',
            'bg-azzurro': 'Sfondo azzurro',
            'bg-giallo': 'Sfondo giallo'
        };

        editor.ui.registry.addMenuButton('sfondoSelect', {
            text: 'Sfondo',
            fetch: function(callback) {
                const items = Object.entries(CLASSI_SFONDO).map(([classe, label]) => {
                    return {
                        type: 'menuitem',
                        text: label,
                        onAction: function() {
                            // Imposta la classe sul body dell'editor
                            editor.getBody().className = classe;
                            // Aggiorna il campo hidden
                            const inputSfondo = document.getElementById('sfondo');
                            if (inputSfondo) inputSfondo.value = classe;
                        }
                    };
                });
                callback(items);
            }
        });

        editor.on('init', () => {
            const sfondoSalvato = document.getElementById('sfondo')?.value;
            if (sfondoSalvato) {
                editor.getBody().className = sfondoSalvato;
            }
        });
    }
});
</script>
EOT;
}


// Recupera o crea una bozza di post
function crea_o_recupera_bozza($autore_id) {
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

// Suggerisce cartelle in base al titolo
function suggerisci_cartelle($titolo, $base_path) {
    preg_match_all('/\b\w+\b/u', strtolower($titolo), $matches);
    $parole = array_filter($matches[0], fn($w) => strlen($w) > 3);

    $cartelle = [];
    $anni = scandir($base_path);
    foreach ($anni as $anno) {
        if (!is_dir("$base_path/$anno") || !preg_match('/^\d{4}$/', $anno)) continue;

        $subdirs = scandir("$base_path/$anno");
        foreach ($subdirs as $sub) {
            $full = "$base_path/$anno/$sub";
            if (!is_dir($full)) continue;
            $nome = strtolower($sub);
            foreach ($parole as $p) {
                if (strpos($nome, $p) !== false) {
                    $cartelle["$anno/$sub"] = $sub;
                    break;
                }
            }
        }
    }
    return $cartelle;
}
