<?php
session_start();

// Verificar si l'usuari està autenticat
if (!isset($_SESSION['usuari'])) {
    die("Accés no permés.");
}

// Obtenir el nom de la carpeta i el directori actual
$nomCarpeta = isset($_POST['nom_carpeta']) ? $_POST['nom_carpeta'] : null;
$currentDir = isset($_POST['current_dir']) ? $_POST['current_dir'] : null;

if (!$nomCarpeta || !$currentDir) {
    die("Falten dades per crear la carpeta.");
}

// Validar el nom de la carpeta (evitar caràcters no permesos)
if (preg_match('/[\/\\\?\*:|"<>]/', $nomCarpeta)) {
    die("El nom de la carpeta conté caràcters no permesos.");
}

// Ruta completa de la nova carpeta
$rutaCarpeta = $currentDir . DIRECTORY_SEPARATOR . $nomCarpeta;

// Verificar si la carpeta ja existeix
if (file_exists($rutaCarpeta)) {
    die("La carpeta ja existeix.");
}

// Crear la carpeta
if (mkdir($rutaCarpeta, 0755, true)) {
    echo "Carpeta creada correctament.";
} else {
    echo "Error en crear la carpeta.";
}
?>