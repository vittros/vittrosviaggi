<?php
// crea_post.php
require 'lib/config.php'; // connessione a MariaDB

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = $_POST['titolo'] ?? '';
    $contenuto = $_POST['contenuto'] ?? '';
    $privato = isset($_POST['privato']) ? 1 : 0;

    $stmt = $pdo->prepare("INSERT INTO post (titolo, contenuto, privato) VALUES (?, ?, ?)");
    $stmt->execute([$titolo, $contenuto, $privato]);

    $post_id = $pdo->lastInsertId();
    
    // foto e slideshow saranno gestiti in seguito
    echo "Post creato con ID: $post_id";
}
?>

<form method="post">
    <label>Titolo:<br>
        <input type="text" name="titolo" required>
    </label><br><br>

    <label>Contenuto:<br>
        <textarea name="contenuto" id="editor" rows="10" cols="60"></textarea>
    </label><br><br>

    <label>
        <input type="checkbox" name="privato"> Post privato
    </label><br><br>

    <button type="submit">Salva post</button>
</form>

<!-- CKEditor (esempio base, versione senza JS avanzato) 
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    CKEDITOR.replace('editor');
</script> -->
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
    window.onload = function() {
        CKEDITOR.replace('contenuto');
    };
</script>


