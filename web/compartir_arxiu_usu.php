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
$ruta = $_POST['ruta_arxiu'];
$usuaris_seleccionats = $_POST['usuaris'] ?? [];

// Obtenir l'ID de l'usuari
if (!isset($_SESSION['id_usu'])) {
    die("ID d'usuari no definit.");
}
$usuari = $_SESSION['id_usu'];

// Obtenir l'ID de l'arxiu
$sql_id_arxiu = "select id_arxiu from ARXIUS_PUJATS where ruta = '$ruta'";
$result_id_arxiu = mysqli_query($conexion, $sql_id_arxiu);

if (!$result_id_arxiu) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

if (mysqli_num_rows($result_id_arxiu) == 0) {
    die("No s'ha trobat cap arxiu amb la ruta proporcionada.");
}

$row = mysqli_fetch_assoc($result_id_arxiu);
$id_arxiu = $row['id_arxiu'];

// Obtenir departaments que ja tenen accés
$sql_usuaris_compartits = "select id_destinatari from ARXIUS_COMPARTITS_USUARIS where id_arxiu = '$id_arxiu'";
$result_usuaris_compartits = mysqli_query($conexion, $sql_usuaris_compartits);

if (!$result_usuaris_compartits) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

$usuaris_compartits = [];
while ($row = mysqli_fetch_assoc($result_usuaris_compartits)) {
    $usuaris_compartits[] = $row['id_destinatari'];
}

// Afegir noves comparticions
foreach ($usuaris_seleccionats as $id_destinatari) {
    if (!in_array($id_destinatari, $usuaris_compartits)) {
        $sql_insert = "INSERT INTO ARXIUS_COMPARTITS_USUARIS (id_propietari, id_destinatari, id_arxiu) 
                       VALUES ('$usuari', '$id_destinatari', '$id_arxiu')";
        if (!mysqli_query($conexion, $sql_insert)) {
            die("Error en la inserció: " . mysqli_error($conexion));
        }
    }
}

// Eliminar comparticions que ja no estan seleccionades
foreach ($usuaris_compartits as $id_destinatari) {
    if (!in_array($id_destinatari, $usuaris_seleccionats)) {
        $sql_delete = "DELETE FROM ARXIUS_COMPARTITS_USUARIS 
                       WHERE id_arxiu = '$id_arxiu' AND id_destinatari = '$id_destinatari'";
        if (!mysqli_query($conexion, $sql_delete)) {
            die("Error en l'eliminació: " . mysqli_error($conexion));
        }
    }
}

// Redireccionar amb missatge d'èxit
header("Location: pujar_fitxers.php");
exit();
?>