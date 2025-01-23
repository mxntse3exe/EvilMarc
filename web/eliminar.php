<?php
session_start();

if (!isset($_SESSION['usuari'])) {
    die("Accés no permés.");
}

$base_dir = '/var/www/html/fitxers/fitxers_usuaris/fitxers_' . $_SESSION['id_usu'];
$file = isset($_GET['file']) ? $_GET['file'] : null;
$folder = isset($_GET['folder']) ? $_GET['folder'] : null;

if ($file) {
    $file_path = realpath($file);
    if (strpos($file_path, realpath($base_dir)) === 0 && file_exists($file_path)) {
        unlink($file_path);
        echo "Arxiu eliminat correctament.";
    } else {
        die("Accés no permés.");
    }
} elseif ($folder) {
    $folder_path = realpath($folder);
    if (strpos($folder_path, realpath($base_dir)) === 0 && is_dir($folder_path)) {
        // Función recursiva para eliminar una carpeta y su contenido
        function deleteDirectory($dir) {
            if (!file_exists($dir)) {
                return true;
            }
            if (!is_dir($dir)) {
                return unlink($dir);
            }
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') {
                    continue;
                }
                if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                    return false;
                }
            }
            return rmdir($dir);
        }
        deleteDirectory($folder_path);
        echo "Carpeta eliminada correctament.";
    } else {
        die("Accés no permés.");
    }
} else {
    die("No s'ha especificat cap arxiu o carpeta.");
}

// Redirigir de vuelta al explorador de archivos
header("Location: pujar_fitxers.php?dir=" . urlencode($_GET['dir']));
exit();
?>