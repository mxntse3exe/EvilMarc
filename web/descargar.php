<?php

session_start();

// Verificar que l'usuari està autenticat
if (!isset($_SESSION['usuari'])) {
    die("Usuari no autenticat.");
}

// Connexió a la base de dades
$servidor = "localhost";
$usuario = "web";
$password = "T5Dk!xq";
$db = "evilmarc";

$conexion = mysqli_connect($servidor, $usuario, $password, $db);
if (!$conexion) {
    die("Error al connectar amb la base de dades: " . mysqli_connect_error());
}

$usuari = $_SESSION['id_usu'];

//////////////////////////////////////////////////////////////////////////////////////////////////////

// Ruta segura a la clau
define('KEY_PATH', '/etc/secrets/encryption.key');
$key = base64_decode(trim(file_get_contents(KEY_PATH)));

function decryptFile($sourcePath, $destPath, $key) {
    $cipher = "aes-256-cbc";

    if ($fpIn = fopen($sourcePath, 'rb')) {
        $iv = fread($fpIn, 16); // Llegeix l'IV del principi del fitxer
        if ($fpOut = fopen($destPath, 'w')) {
            while (!feof($fpIn)) {
                $ciphertext = fread($fpIn, 16 * 10000 + 16);
                $plaintext = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
                $iv = substr($ciphertext, -16);
                fwrite($fpOut, $plaintext);
            }
            fclose($fpOut);
        }
        fclose($fpIn);
        return true;
    }
    return false;
}

// Paràmetres d'entrada
$file = $_GET['file'];
$dir = $_GET['dir'];


if (file_exists($file) && strpos($file, realpath($dir)) === 0) {
    // (Repeteix aquí el teu bloc per desencriptar i enviar el fitxer)
    $fileDir  = dirname($file);
    $tempFile = $fileDir . '/dec_' . uniqid() . '_' . basename($file);
    if (decryptFile($file, $tempFile, $key)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($tempFile));
        readfile($tempFile);
        unlink($tempFile);
    } else {
        echo "No s'ha pogut desxifrar el fitxer.";
    }
    exit;
}
elseif (true) { // ← aquí entrarem, faràs la comprovació que veus a continuació
    // 1) Obtenir id_arxiu, id_usu (propietari) i dept de l'usuari actual
    $stmt = $conexion->prepare("
        SELECT p.id_arxiu, p.id_usu AS propietari, u.id_dep AS dept_usr
        FROM ARXIUS_PUJATS p
        JOIN USUARIS u ON u.id_usu = ?
        WHERE p.ruta = ?
    ");
    $stmt->bind_param("is", $usuari, $file);
    $stmt->execute();
    $stmt->bind_result($id_arxiu, $propietari, $dept_usr);
    if (! $stmt->fetch()) {
        // no existeix cap arxiu amb aquesta ruta
        die("Accés denegat: fitxer no registrat.");
    }
    $stmt->close();

    // 2) Comprovar si l'usuari és el propietari
    if ($propietari === $usuari) {
        $puedes_descargar = true;
    }
    else {
        $puedes_descargar = false;

        // 3) Comprovar si s'ha compartit directament amb l'usuari
        $stmt = $conexion->prepare("
            SELECT 1
            FROM ARXIUS_COMPARTITS_USUARIS
            WHERE id_arxiu = ? AND id_destinatari = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $id_arxiu, $usuari);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $puedes_descargar = true;
        }
        $stmt->close();

        // 4) Si encara no, comprovar si s'ha compartit amb el seu departament
        if (! $puedes_descargar && ! is_null($dept_usr)) {
            $stmt = $conexion->prepare("
                SELECT 1
                FROM ARXIUS_COMPARTITS_DEPARTAMENTS
                WHERE id_arxiu = ? AND id_dep = ?
                LIMIT 1
            ");
            $stmt->bind_param("ii", $id_arxiu, $dept_usr);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $puedes_descargar = true;
            }
            $stmt->close();
        }
    }

    if ($puedes_descargar) {
        // (Repeteix aquí el teu bloc per desencriptar i enviar el fitxer)
        $fileDir  = dirname($file);
        $tempFile = $fileDir . '/dec_' . uniqid() . '_' . basename($file);
        if (decryptFile($file, $tempFile, $key)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($tempFile));
            readfile($tempFile);
            unlink($tempFile);
        } else {
            echo "No s'ha pogut desxifrar el fitxer.";
        }
        exit;
    }
    else {
        echo "Accés denegat: no tens permís per a aquest arxiu.";
        exit;
    }
}
else {
    echo "Fitxer no trobat o accés no permès.";
}
