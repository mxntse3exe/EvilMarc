<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuari'])) {
    die("Accés no permés.");
}

$servidor = "localhost";
$usuario = "web";
$password = "T5Dk!xq";
$db = "evilmarc";

$conexion = mysqli_connect($servidor,$usuario,$password,$db);


// Directorio base del usuario
$base_dir = '/var/www/html/fitxers/fitxers_usuaris/fitxers_' . $_SESSION['id_usu'];

// Obtener el archivo o carpeta a eliminar
$file = isset($_GET['file']) ? $_GET['file'] : null;
$folder = isset($_GET['folder']) ? $_GET['folder'] : null;

// Verificar si se proporcionó un archivo o carpeta
if ($file || $folder) {
    // Obtener la ruta real del archivo o carpeta
    $target_path = realpath($file ? $file : $folder);

    // Verificar que la ruta esté dentro del directorio base permitido
    if (strpos($target_path, realpath($base_dir)) !== 0) {
        die("Accés no permés.");
    }

    // Eliminar archivo
    if ($file && file_exists($target_path)) {
        unlink($target_path);
        echo "Arxiu eliminat correctament.";

        $sql = "delete from ARXIUS_PUJATS where ruta = '$target_path'";
        mysqli_query($conexion,$sql);

    }
    // Eliminar carpeta
    elseif ($folder && is_dir($target_path)) {
        // Función recursiva para eliminar una carpeta y su contenido
        function deleteDirectory($dir,$conexion) {
            if (!file_exists($dir)) {
                return true;
            }
            if (!is_dir($dir)) {

                $sql = "delete from ARXIUS_PUJATS where ruta = '$dir'";
                mysqli_query($conexion, $sql);


                return unlink($dir);

            }
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') {
                    continue;
                }
                if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item,$conexion)) {
                    return false;
                }
            }
            return rmdir($dir);
        }
        deleteDirectory($target_path,$conexion);
        echo "Carpeta eliminada correctament.";
    } else {
        die("El fitxer o carpeta no existeix.");
    }
} else {
    die("No s'ha especificat cap arxiu o carpeta.");
}

// Redirigir de vuelta al explorador de archivos
if (isset($_GET['dir'])) {
    $redirect_url = 'pujar_fitxers.php?dir=' . urlencode($_GET['dir']);
    header("Location: $redirect_url");
    exit();
} else {
    // Si no se proporciona el parámetro 'dir', redirigir a la página principal
    header("Location: pujar_fitxers.php");
    exit();
}
?>