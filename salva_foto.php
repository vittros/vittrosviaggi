<?php
require_once 'lib/bootstrap.php'; // include già session_start, $tema_attivo, header.php, functions.php


$pdo = getPDO();

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['foto']['tmp_name'];
    $nome_file = basename($_FILES['foto']['name']);
    $percorso_relativo = "foto/" . $nome_file;
    $percorso_assoluto = __DIR__ . "/foto/" . $nome_file;

    // Debug info
    echo "File temp: $file_tmp<br>";
    echo "Destinazione: $percorso_assoluto<br>";
    echo "Cartella esiste? " . (is_dir(dirname($percorso_assoluto)) ? "Sì" : "No") . "<br>";
    echo "È scrivibile? " . (is_writable(dirname($percorso_assoluto)) ? "Sì" : "No") . "<br>";

    if (!move_uploaded_file($file_tmp, $percorso_assoluto)) {
        die("❌ Impossibile spostare il file nella cartella.");
    }

    $stmt = $pdo->prepare("INSERT INTO foto (nome_file, percorso) VALUES (?, ?)");
    $stmt->bind_param("ss", $nome_file, $percorso_relativo);

    if ($stmt->execute()) {
        echo "✅ Foto caricata con successo!";
    } else {
        echo "❌ Errore nel salvataggio: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "❌ Errore nel caricamento: ";
    var_dump($_FILES['foto']['error']);
}

$conn->close();
