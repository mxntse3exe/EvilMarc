<?php
// Obtener los parámetros de la URL
$file = $_GET['file'];
$dir = $_GET['dir'];

// Verificar si el archivo existe y está dentro del directorio del usuario
if (file_exists($file) && strpos($file, realpath($dir)) === 0) {
    // Enviar los headers HTTP para forzar la descarga
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));

    // Leer el archivo y enviarlo al cliente
    readfile($file);
    exit;
} else {
    // Manejar errores si el archivo no existe o está fuera del directorio
    echo "Archivo no encontrado o acceso no permitido.";
}