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

// Obtenir dades del formulari
$ruta = $_POST['ruta_carpeta'];
$usuaris_seleccionats = $_POST['usuaris'] ?? [];
$departaments_seleccionats = $_POST['departaments'] ?? [];
$url = $_POST['current_dir'];

// Obtenir l'ID de l'usuari
if (!isset($_SESSION['id_usu'])) {
    die("ID d'usuari no definit.");
}
$usuari = $_SESSION['id_usu'];

// ==========================
// 1. Gestionar usuaris
// ==========================

// Obtenir usuaris que ja tenen accés
$sql_usuaris_compartits = "SELECT id_destinatari FROM CARPETES_COMPARTIDES_USUARIS WHERE ruta = '$ruta'";
$result_usuaris_compartits = mysqli_query($conexion, $sql_usuaris_compartits);
$usuaris_compartits = [];
while ($row = mysqli_fetch_assoc($result_usuaris_compartits)) {
    $usuaris_compartits[] = $row['id_destinatari'];
}

// Afegir noves comparticions
foreach ($usuaris_seleccionats as $id_destinatari) {
    if (!in_array($id_destinatari, $usuaris_compartits)) {
        $sql_insert = "INSERT INTO CARPETES_COMPARTIDES_USUARIS (id_propietari, id_destinatari, ruta) 
                       VALUES ('$usuari', '$id_destinatari', '$ruta')";
        if (!mysqli_query($conexion, $sql_insert)) {
            die("Error en la inserció d'usuaris: " . mysqli_error($conexion));
        }
    }
}

// Eliminar comparticions que ja no estan seleccionades
foreach ($usuaris_compartits as $id_destinatari) {
    if (!in_array($id_destinatari, $usuaris_seleccionats)) {
        $sql_delete = "DELETE FROM CARPETES_COMPARTIDES_USUARIS 
                       WHERE ruta = '$ruta' AND id_destinatari = '$id_destinatari'";
        if (!mysqli_query($conexion, $sql_delete)) {
            die("Error en l'eliminació d'usuaris: " . mysqli_error($conexion));
        }
    }
}

// ==========================
// 2. Gestionar departaments
// ==========================

// Obtenir departaments que ja tenen accés
$sql_departaments_compartits = "SELECT id_dep FROM CARPETES_COMPARTIDES_DEPARTAMENTS WHERE ruta = '$ruta'";
$result_departaments_compartits = mysqli_query($conexion, $sql_departaments_compartits);
$departaments_compartits = [];
while ($row = mysqli_fetch_assoc($result_departaments_compartits)) {
    $departaments_compartits[] = $row['id_dep'];
}

// Afegir noves comparticions
foreach ($departaments_seleccionats as $id_dep) {
    if (!in_array($id_dep, $departaments_compartits)) {
        $sql_insert = "INSERT INTO CARPETES_COMPARTIDES_DEPARTAMENTS (id_propietari, id_dep, ruta) 
                       VALUES ('$usuari', '$id_dep', '$ruta')";
        if (!mysqli_query($conexion, $sql_insert)) {
            die("Error en la inserció de departaments: " . mysqli_error($conexion));
        }
    }
}

// Eliminar comparticions que ja no estan seleccionades
foreach ($departaments_compartits as $id_dep) {
    if (!in_array($id_dep, $departaments_seleccionats)) {
        $sql_delete = "DELETE FROM CARPETES_COMPARTIDES_DEPARTAMENTS 
                       WHERE ruta = '$ruta' AND id_dep = '$id_dep'";
        if (!mysqli_query($conexion, $sql_delete)) {
            die("Error en l'eliminació de departaments: " . mysqli_error($conexion));
        }
    }
}

// Redireccionar amb missatge d'èxit
header("Location: pujar_fitxers.php?dir=".urlencode($url));
exit();
?>
