<?php 
	session_start();

    $servidor = "localhost";
    $usuario = "web";
    $password = "T5Dk!xq";
    $db = "evilmarc";

    $conexion = mysqli_connect($servidor,$usuario,$password,$db);

    if (!$conexion) die ("Error al connectar amb la base de dades.");

    $usuari = $_SESSION['usuari'];

    $sql = "select * from USUARIS where usuari = '".$usuari."'";

    $files = mysqli_query($conexion,$sql);

    while($fila = $files->fetch_assoc()) {
        $admin = $fila["admin"];

        $_SESSION['admin'] = $admin;
        $_SESSION['id_usu'] = $fila["id_usu"];
        $_SESSION['imatge'] = $fila["imatge"];

        $nom = $fila['nom'];
        $cognoms = $fila['cognoms'];
        $direccio = $fila['direccio'];
        $num_usu = $_SESSION['id_usu'];
    }


    // Ruta base de tu directorio
    $base_dir = '/var/www/html/fitxers/fitxers_usuaris/fitxers_'.$num_usu;

    // Obtener la ruta actual desde la URL o usar la base por defecto
    $current_dir = isset($_GET['dir']) ? $_GET['dir'] : $base_dir;

    // Prevenir accesos fuera del directorio base
    $real_current_dir = realpath($current_dir);
    if (strpos($real_current_dir, realpath($base_dir)) !== 0) {
        die("Accés no permés.");
    }

    // Obtener contenido del directorio
    $items = scandir($current_dir);

    $relative_dir = str_replace(realpath($base_dir), '', $real_current_dir);
    $relative_dir = $relative_dir === '' ? '/' : $relative_dir;

?>

<?php

$keyPath = '/etc/secrets/encryption.key';
$key = base64_decode(trim(file_get_contents($keyPath)));

define('ENCRYPTION_KEY', $key);

function encryptFile($sourcePath, $destPath, $key) {
    $iv = openssl_random_pseudo_bytes(16);
    $cipher = "aes-256-cbc";
    
    if ($fpOut = fopen($destPath, 'w')) {
        fwrite($fpOut, $iv); // Escriu l'IV al principi del fitxer
        if ($fpIn = fopen($sourcePath, 'rb')) {
            while (!feof($fpIn)) {
                $plaintext = fread($fpIn, 16 * 10000);
                $ciphertext = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
                $iv = substr($ciphertext, -16); // Utilitza els últims 16 bytes com a IV per al següent bloc
                fwrite($fpOut, $ciphertext);
            }
            fclose($fpIn);
        }
        fclose($fpOut);
        return true;
    }
    return false;
}


function generarNomUnic($directori, $nomFitxer) {
    $nomSenseExtensio = pathinfo($nomFitxer, PATHINFO_FILENAME);
    $extensio = pathinfo($nomFitxer, PATHINFO_EXTENSION);
    $rutaCompleta = $directori . DIRECTORY_SEPARATOR . $nomFitxer;
    $comptador = 1;

    // Mentre el fitxer existeixi, afegeix un número al final
    while (file_exists($rutaCompleta)) {
        $nouNom = $nomSenseExtensio . " ($comptador)." . $extensio;
        $rutaCompleta = $directori . DIRECTORY_SEPARATOR . $nouNom;
        $comptador++;
    }

    return basename($rutaCompleta);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['archivo']) || isset($_FILES['carpeta'])) {
        $base_dir = '/var/www/html/fitxers/fitxers_usuaris/fitxers_' . $_SESSION['id_usu'];
        $current_dir = isset($_POST['current_dir']) ? $_POST['current_dir'] : $base_dir;

        // Prevenir accesos fuera del directorio base
        $real_current_dir = realpath($current_dir);
        if (strpos($real_current_dir, realpath($base_dir)) !== 0) {
            die("Accés no permés.");
        }

        // Procesar archivos sueltos
        if (isset($_FILES['archivo']) && $_POST['upload_type'] === 'file') {


            $_SESSION['missatge_pujada'] = "";
            $diccionari_json = '{"nets":[],"infectats":[]}';

            foreach ($_FILES['archivo']['name'] as $key => $name) {
                $tmp_name = $_FILES['archivo']['tmp_name'][$key];
                $error = $_FILES['archivo']['error'][$key];
        
                if ($error === UPLOAD_ERR_OK) {
                    // Genera un nom únic per al fitxer
                    $nomUnic = generarNomUnic($current_dir, $name);
                    $target_path = $current_dir . DIRECTORY_SEPARATOR . $nomUnic;
        
                    // Mou el fitxer pujat
                    move_uploaded_file($tmp_name, $target_path.".tmp");

                    // move_uploaded_file($tmp_name, $target_path . '.tmp');
                    // encryptFile($target_path . '.tmp', $target_path, ENCRYPTION_KEY);
                    // unlink($target_path . '.tmp'); // Elimina el fitxer temporal no xifrat


                    $command = "echo " . escapeshellarg($diccionari_json) . " | python3 /var/www/html/evilmarc_fitxers.py " . escapeshellarg($nomUnic.".tmp") . " " . escapeshellarg($current_dir);
                    $output = shell_exec($command);
                    
                }
                $diccionari_json = $output;

                
            }

            $diccionari = json_decode($diccionari_json, true);

            foreach ($diccionari["nets"] as $fitxerNet) {
                $sourcePath = $current_dir . DIRECTORY_SEPARATOR . $fitxerNet;
                $destPath = str_replace(".tmp", "", $sourcePath); // Exemple: afegir extensió .enc per al fitxer xifrat
                encryptFile($sourcePath, $destPath, ENCRYPTION_KEY);
                unlink($sourcePath); // Elimina el fitxer original no xifrat
            }

            $nets = count($diccionari["nets"]);
            $infectats = count($diccionari["infectats"]);

            // Construir el missatge segons els fitxers pujats: lògica singulars i plurals
            if ($nets > 0 && $infectats === 0) {
                if ($nets === 1) {
                    $_SESSION['missatge_pujada'] = "S'ha pujat correctament $nets arxiu!";
                }
                else {
                    $_SESSION['missatge_pujada'] = "S'han pujat correctament $nets arxius!";
                }
            } elseif ($nets === 0 && $infectats > 0) {
                if ($infectats === 1) {
                    $_SESSION['missatge_pujada'] = "<i class='uil uil-exclamation-triangle'></i> L'arxiu <b>" . implode(", ", str_replace(".tmp", "", $diccionari["infectats"])) . "</b> no s'ha pogut pujar, està infectat.";
                }
                else {
                    $_SESSION['missatge_pujada'] = "<i class='uil uil-exclamation-triangle'></i> Els arxius <b>" . implode(", ", str_replace(".tmp", "", $diccionari["infectats"])) . "</b> no s'han pogut pujar, estan infectats.";
                }
            } elseif ($nets > 0 && $infectats > 0) {
                if ($nets === 1) {
                    if ($infectats === 1) {
                        $_SESSION['missatge_pujada'] = "S'ha pujat correctament $nets arxiu. <br><i class='uil uil-exclamation-triangle'></i> L'arxiu <b>" . implode(", ", str_replace(".tmp", "", $diccionari["infectats"])) . "</b> no s'ha pogut pujar, està infectat.";
                    }
                    else {
                        $_SESSION['missatge_pujada'] = "S'ha pujat correctament $nets arxiu. <br><i class='uil uil-exclamation-triangle'></i> Els arxius <b>" . implode(", ", str_replace(".tmp", "", $diccionari["infectats"])) . "</b> no s'han pogut pujar, estan infectats.";
                    }
                }
                else {
                    if ($infectats === 1) {
                        $_SESSION['missatge_pujada'] = "S'han pujat correctament $nets arxius. <br><i class='uil uil-exclamation-triangle'></i> L'arxiu <b>" . implode(", ", str_replace(".tmp", "", $diccionari["infectats"])) . "</b> no s'ha pogut pujar, està infectat.";
                    }
                    else {
                        $_SESSION['missatge_pujada'] = "S'han pujat correctament $nets arxius. <br><i class='uil uil-exclamation-triangle'></i> Els arxius <b>" . implode(", ", str_replace(".tmp", "", $diccionari["infectats"])) . "</b> no s'han pogut pujar, estan infectats.";
                    }
                }
            
            
            }

        }

        // Procesar carpetas
        if (isset($_FILES['carpeta']) && $_POST['upload_type'] === 'folder') {


            $_SESSION['missatge_pujada'] = "";
            $diccionari_json = '{"nets":[],"infectats":[]}';


            foreach ($_FILES['carpeta']['name'] as $key => $name) {
                $tmp_name = $_FILES['carpeta']['tmp_name'][$key];
                $error = $_FILES['carpeta']['error'][$key];

                if ($error === UPLOAD_ERR_OK) {
                    $relative_path = $_FILES['carpeta']['full_path'][$key]; // Ruta relativa completa
                    $target_path = $current_dir . DIRECTORY_SEPARATOR . $relative_path;

                    $carpeta_principal = explode('/', $relative_path)[0];


                    $ruta_carpeta_principal = $current_dir . DIRECTORY_SEPARATOR . $carpeta_principal;


                    // Crear directorios si no existen
                    $target_dir = dirname($target_path);
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true); // Crear directorios recursivamente
                    }

                    // Mover el archivo o carpeta
                    if (is_dir($tmp_name)) {
                        if (!file_exists($target_path)) {
                            mkdir($target_path, 0777, true);
                        }
                    } else {
                        move_uploaded_file($tmp_name, $target_path);

                        // move_uploaded_file($tmp_name, $target_path . '.tmp');
                        // encryptFile($target_path . '.tmp', $target_path, ENCRYPTION_KEY);
                        // unlink($target_path . '.tmp'); // Elimina el fitxer temporal no xifrat
                    }
                }
            }

            $command = "echo " . escapeshellarg($diccionari_json) . " | python3 /var/www/html/evilmarc_carpetes.py " . " " . escapeshellarg($ruta_carpeta_principal);
            $output = shell_exec($command);



            $diccionari_json = $output;
            $diccionari = json_decode($diccionari_json, true);

            $nets = count($diccionari["nets"]);
            $infectats = count($diccionari["infectats"]);

            foreach ($diccionari["nets"] as $nomFitxerEnc) {
                $sourcePath = $nomFitxerEnc;
                $tempPath = $sourcePath . '.tmp'; // Fitxer temporal per guardar el xifrat
            
                encryptFile($sourcePath, $tempPath, ENCRYPTION_KEY);
            
                unlink($sourcePath); // Esborra l'original sense xifrar
                rename($tempPath, $sourcePath); // Canvia el nom del fitxer temporal pel definitiu (mateix nom original)
            }

            //Construir el missatge segons els fitxers pujats: lògica singulars i plurals
            if ($nets > 0 && $infectats === 0) {
                if ($nets === 1) {
                    $_SESSION['missatge_pujada'] = "S'ha pujat correctament $nets arxiu!";
                }
                else {
                    $_SESSION['missatge_pujada'] = "S'han pujat correctament $nets arxius!";
                }
            } elseif ($nets === 0 && $infectats > 0) {
                if ($infectats === 1) {
                    $_SESSION['missatge_pujada'] = "<i class='uil uil-exclamation-triangle'></i> L'arxiu <b>" . implode(", ", $diccionari["infectats"]) . "</b> no s'ha pogut pujar, està infectat.";
                }
                else {
                    $_SESSION['missatge_pujada'] = "<i class='uil uil-exclamation-triangle'></i> Els arxius <b>" . implode(", ", $diccionari["infectats"]) . "</b> no s'han pogut pujar, estan infectats.";
                }
            } elseif ($nets > 0 && $infectats > 0) {
                if ($nets === 1) {
                    if ($infectats === 1) {
                        $_SESSION['missatge_pujada'] = "S'ha pujat correctament $nets arxiu. <br><i class='uil uil-exclamation-triangle'></i> L'arxiu <b>" . implode(", ", $diccionari["infectats"]) . "</b> no s'ha pogut pujar, està infectat.";
                    }
                    else {
                        $_SESSION['missatge_pujada'] = "S'ha pujat correctament $nets arxiu. <br><i class='uil uil-exclamation-triangle'></i> Els arxius <b>" . implode(", ", $diccionari["infectats"]) . "</b> no s'han pogut pujar, estan infectats.";
                    }
                }
                else {
                    if ($infectats === 1) {
                        $_SESSION['missatge_pujada'] = "S'han pujat correctament $nets arxius. <br><i class='uil uil-exclamation-triangle'></i> L'arxiu <b>" . implode(", ", $diccionari["infectats"]) . "</b> no s'ha pogut pujar, està infectat.";
                    }
                    else {
                        $_SESSION['missatge_pujada'] = "S'han pujat correctament $nets arxius. <br><i class='uil uil-exclamation-triangle'></i> Els arxius <b>" . implode(", ", $diccionari["infectats"]) . "</b> no s'han pogut pujar, estan infectats.";
                    }
                }
            }
            
        }
    } else {
        echo "No s'ha seleccionat cap fitxer o carpeta.";
    }
}



?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>EvilMarc</title>

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/unicons.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">

    <!-- MAIN STYLE -->
    <link rel="stylesheet" href="css/tooplate-style.css">

    <link rel="icon" type="image/png" href="images/favicon.ico"/>
</head>

<body>

    <!-- MENU -->
    <nav class="navbar navbar-expand-sm navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index"> EvilMarc</a>

            <div id="navbarNav">
                <ul class="navbar-nav">
                    
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a href="panell_usuari" class="nav-link"><span data-hover="Panell principal">Panell principal</span></a>
                        </li>
                        <li class="nav-item">
                            <a href="sortir" class="nav-link"><span data-hover="Sortir">Sortir</span></a>
                        </li>
                
                    </ul>

                </ul>
            </div>
        </div>
    </nav>

    

    <!-- FUNCIONAMENT -->
    
    <section class="about full-screen d-lg-flex justify-content-center align-items-center">
        <div class="container">


        <?php
        if(($_SESSION['valido'] == 1)) {
            $usuari = $_SESSION['usuari'];

        ?>

            <div class="row seccio_panell">
                <div style="width: 90%;">
                    <h2>Pujar fitxers</h2>

                    <div class="pujar_arxius">

                        <div class="botons_pujar">
                            <!-- Formulario para subir archivos -->
                            <form id="file-upload-form" enctype="multipart/form-data" method="post" action="pujar_fitxers" class="pujar_arxius_carpetes">
                                <input type="hidden" name="max_file_size" value="681574400">
                                <input type="hidden" name="current_dir" value="<?php echo htmlspecialchars($current_dir); ?>">
                                <input type="hidden" name="upload_type" value="file">

                                <!-- Campo para archivos sueltos -->
                                <div id="file-upload-section">
                                    <label for="archivo" class="custom-file-upload">
                                        <i class="uil uil-file-alt"></i>Selecciona l'arxiu que vols pujar...
                                    </label>
                                    <input type="file" name="archivo[]" class="input_arxiu" id="archivo" multiple>
                                </div>
                            </form>

                            <!-- Formulario para subir carpetas -->
                            <form id="folder-upload-form" enctype="multipart/form-data" method="post" action="pujar_fitxers" class="pujar_arxius_carpetes">
                                <input type="hidden" name="max_file_size" value="681574400">
                                <input type="hidden" name="current_dir" value="<?php echo htmlspecialchars($current_dir); ?>">
                                <input type="hidden" name="upload_type" value="folder">

                                <!-- Campo para carpetas -->
                                <div id="folder-upload-section">
                                    <label for="carpeta" class="custom-file-upload">
                                        <i class="uil uil-folder"></i>Selecciona la carpeta que vols pujar...
                                    </label>
                                    <input type="file" name="carpeta[]" class="input_arxiu" id="carpeta" webkitdirectory>
                                </div>
                            </form>
                            </div>

                        <!-- Mensaje de estado -->
                        <div id="upload-status"></div>
                    </div>

                    <!-- Script para manejar la subida automática de archivos y carpetas -->
                    <script>
                        // Función para enviar el formulario automáticamente
                        function uploadFiles(formId) {
                            const form = document.getElementById(formId);
                            const status = document.getElementById('upload-status');

                            // Mostrar mensaje de "Subiendo..."
                            status.innerHTML = 'Pujant fitxers...';

                            // Enviar el formulario usando Fetch API
                            const formData = new FormData(form);
                            fetch(form.action, {
                                method: 'POST',
                                body: formData,
                            })
                            .then(response => response.text())
                            .then(data => {
                                // Mostrar el resultado de la subida
                                status.innerHTML = data;

                                // Recargar la página para actualizar el explorador de archivos
                                window.location.reload(); // Recargar inmediatamente

                            

                            })
                            .catch(error => {
                                status.innerHTML = 'Error en la pujada: ' + error.message;
                            });
                        }

                        // Subir archivos automáticamente al seleccionarlos
                        document.getElementById('archivo').addEventListener('change', () => {
                            uploadFiles('file-upload-form');
                        });

                        // Subir carpetas automáticamente al seleccionarlas
                        document.getElementById('carpeta').addEventListener('change', () => {
                            uploadFiles('folder-upload-form');
                        });
                    </script>

                

                </div>
            </div>



            <div class="explorador">

            <?php
            // Mostrar mensaje si existe en la sesión
            if (isset($_SESSION['missatge_pujada'])) {
                echo "<div class='info_pujada'>" . $_SESSION['missatge_pujada'] . "</div>";
                
            }
            ?>


                <div class="info_ruta">
                    <p>Directori actual: <?php echo htmlspecialchars($relative_dir); ?></p>
    
                    <div class="buscador_crear">
                        <button id="crear-carpeta" class="btn">Crear carpeta</button>
    
    
                        <div class="buscador-container">
                            <input type="text" id="buscador-fitxers" placeholder="Cerca fitxers o carpetes..." class="form-control">
                            <i class="uil uil-search"></i>
                        </div>

                    </div>

                </div>

                

                <!-- script per poder crear noves carpetes -->
                <script>
                document.getElementById('crear-carpeta').addEventListener('click', function() {
                    // Demanar el nom de la carpeta mitjançant una alerta
                    const nomCarpeta = prompt('Introdueix el nom de la carpeta:');

                    if (nomCarpeta) {
                        // Enviar el nom de la carpeta al servidor mitjançant AJAX
                        const currentDir = "<?php echo htmlspecialchars($current_dir); ?>";
                        const formData = new FormData();
                        formData.append('nom_carpeta', nomCarpeta);
                        formData.append('current_dir', currentDir);

                        fetch('crear_carpeta.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.text())
                        .then(data => {
                            alert(data); // Mostrar el missatge de resposta del servidor
                            window.location.reload(); // Recarregar la pàgina per veure els canvis
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                    }
                });
                </script>


                <div>
                    <?php
                    // Mostrar enlace para regresar al directorio anterior
                    if ($current_dir !== $base_dir) {
                        $parent_dir = dirname($current_dir);
                        echo '<a href="?dir=' . urlencode($parent_dir) . '" class="carpeta"><i class="uil uil-arrow-left"></i> Enrere</a><br><br>';
                    }

                    // Listar contenido del directorio
                    foreach ($items as $item) {
                        if ($item === '.' || $item === '..') continue;

                        $item_path = $current_dir . DIRECTORY_SEPARATOR . $item;



                        if (is_dir($item_path)) {
                            // Mostrar carpetas con enlace
                            // echo '<a href="?dir='.urlencode($item_path).'" class="carpeta"><div class="llista_fitxers"><div><i class="uil uil-folder"></i> '.htmlspecialchars($item).'</div></div></a>';
                            echo '<a href="?dir='.urlencode($item_path).'" class="carpeta">';
                                echo '<div class="llista_fitxers">';
                                    echo '<div><i class="uil uil-folder"></i> '.htmlspecialchars($item);
                                    echo '</div>';
                                    
                                    echo '<div class="botons_arxius">';
                                    

                                    $popup_id = 'popup_' . md5($item_path); // Genera un ID únic per cada popup

                                    // Botó per compartir arxius
                                    echo '<a href="javascript:void(0)" onclick="abrirPopup_compartir(\''.$popup_id.'\')">compartir <i class="uil uil-users-alt"></i></a>';
                                    

                                    ?>

                                    
                                    <div class="overlay" id="overlay_<?php echo $popup_id; ?>" onclick="cerrarPopup_compartir('<?php echo $popup_id; ?>')"></div>
                                    <div class="popup_comp" id="<?php echo $popup_id; ?>">
                                        <h4>Compartir</h4>


                                        <div class="buscador-container" style="width: 100%;">
                                            <input type="text" id="buscador-usudep" placeholder="Cerca usuaris i departaments..." class="form-control">
                                            <i class="uil uil-search"></i>
                                        </div>






                                        <div class="compartir_arxius">
                                            
                                            <form class="contact-form compartir" action="compartir_carpeta.php" method="POST">
                                                <?php
                                                // Obtenir els usuaris que ja tenen accés a l'arxiu
                                                $sql_usuaris_compartits = "SELECT id_destinatari FROM CARPETES_COMPARTIDES_USUARIS WHERE ruta = '$item_path'";
                                                $result_usuaris_compartits = mysqli_query($conexion, $sql_usuaris_compartits);
                                                $usuaris_compartits = [];
                                                while ($row = mysqli_fetch_assoc($result_usuaris_compartits)) {
                                                    $usuaris_compartits[] = $row['id_destinatari'];
                                                }

                                                // Obtenir tots els usuaris
                                                $sql_usuaris = "SELECT * FROM USUARIS WHERE validat = 1 AND id_usu != '$num_usu' ORDER BY usuari";
                                                $usuaris = mysqli_query($conexion, $sql_usuaris);

                                                echo "<h6>Usuaris</h6>";
                                                while ($usuari = $usuaris->fetch_assoc()) {
                                                    $checked = in_array($usuari['id_usu'], $usuaris_compartits) ? 'checked' : '';
                                                    echo "<label class='usudeps'>";
                                                    echo "<input type='checkbox' name='usuaris[]' value='" . $usuari['id_usu'] . "' $checked> ";
                                                    echo "<img src='" . $usuari['imatge'] . "' class='foto_usuari_dep'>";
                                                    echo $usuari['usuari'];
                                                    echo "</label>";
                                                }

                                                // Obtenir els departaments que ja tenen accés a l'arxiu
                                                $sql_departaments_compartits = "SELECT id_dep FROM CARPETES_COMPARTIDES_DEPARTAMENTS WHERE ruta = '$item_path'";
                                                $result_departaments_compartits = mysqli_query($conexion, $sql_departaments_compartits);
                                                $departaments_compartits = [];
                                                while ($row = mysqli_fetch_assoc($result_departaments_compartits)) {
                                                    $departaments_compartits[] = $row['id_dep'];
                                                }

                                                // Obtenir tots els departaments
                                                $sql_departaments = "SELECT * FROM DEPARTAMENTS ORDER BY nom";
                                                $departaments = mysqli_query($conexion, $sql_departaments);

                                                echo "<h6>Departaments</h6>";
                                                while ($departament = $departaments->fetch_assoc()) {
                                                    $checked = in_array($departament['id_dep'], $departaments_compartits) ? 'checked' : '';
                                                    echo "<label class='usudeps'>";
                                                    echo "<input type='checkbox' name='departaments[]' value='" . $departament['id_dep'] . "' $checked> ";
                                                    echo $departament['nom'];
                                                    echo "</label>";
                                                }
                                                ?>

                                                <!-- Input ocult per enviar la ruta -->
                                                <input type="hidden" name="ruta_carpeta" value="<?php echo $item_path; ?>">
                                                
                                                <!-- Input ocult per enviar la URL -->
                                                <input type="hidden" name="current_dir" value="<?php echo htmlspecialchars($current_dir); ?>">

                                                <!-- Un únic botó per compartir amb usuaris i departaments -->
                                                <button type="submit" class="boto_compartir">Compartir amb usuaris i departaments</button>
                                            </form>
                                        </div>




                                        <span class="close-icon" onclick="cerrarPopup_compartir('<?php echo $popup_id; ?>')">×</span>
                                    </div>




                                    <?php


                                    echo '<a href="eliminar.php?folder=' . urlencode($item_path) . '&dir=' . urlencode($current_dir) . '" onclick="return confirm(\'Estàs segur que vols eliminar aquesta carpeta?\')">eliminar <i class="uil uil-trash-alt"></i></a>';
                                    echo '</div>';
                                echo '</div>';
                            echo '</a>';
                        } else {
                            // Mostrar archivos
                            echo '<div class="llista_fitxers">';
                                echo '<span>'.htmlspecialchars($item).'</span>';
                                echo '<div class="botons_arxius">';

                                    echo '<a href="descargar.php?file=' . urlencode($item_path) . '&dir=' . urlencode($current_dir) . '">descarregar <i class="uil uil-arrow-down"></i></a>';


                                    $popup_id = 'popup_' . md5($item_path); // Genera un ID únic per cada popup

                                    // Botó per compartir arxius
                                    echo '<a href="javascript:void(0)" onclick="abrirPopup_compartir(\''.$popup_id.'\')">compartir <i class="uil uil-users-alt"></i></a>';
                                    

                                    ?>

                                    
                                    <div class="overlay" id="overlay_<?php echo $popup_id; ?>" onclick="cerrarPopup_compartir('<?php echo $popup_id; ?>')"></div>
                                    <div class="popup_comp" id="<?php echo $popup_id; ?>">
                                        <h4>Compartir</h4>


                                        <div class="buscador-container" style="width: 100%;">
                                            <input type="text" id="buscador-usudep" placeholder="Cerca usuaris i departaments..." class="form-control">
                                            <i class="uil uil-search"></i>
                                        </div>


                                        <div class="compartir_arxius">

                                            <form class="contact-form compartir" action="compartir_arxiu.php" method="POST">
                                                <?php
                                                // Obtenir els usuaris que ja tenen accés a l'arxiu
                                                $sql_usuaris_compartits = "select id_destinatari from ARXIUS_COMPARTITS_USUARIS where id_arxiu = (select id_arxiu from ARXIUS_PUJATS where ruta = '$item_path')";
                                                $result_usuaris_compartits = mysqli_query($conexion, $sql_usuaris_compartits);
                                                $usuaris_compartits = [];
                                                while ($row = mysqli_fetch_assoc($result_usuaris_compartits)) {
                                                    $usuaris_compartits[] = $row['id_destinatari'];
                                                }

                                                // Obtenir tots els usuaris
                                                $sql_usuaris = "select * from USUARIS where validat = 1 AND id_usu != '$num_usu' ORDER BY usuari";
                                                $usuaris = mysqli_query($conexion, $sql_usuaris);

                                                echo "<h6>Usuaris</h6>";
                                                while ($usuari = $usuaris->fetch_assoc()) {
                                                    $checked = in_array($usuari['id_usu'], $usuaris_compartits) ? 'checked' : '';
                                                    echo "<label class='usudeps'>";
                                                    echo "<input type='checkbox' name='usuaris[]' value='" . $usuari['id_usu'] . "' $checked> ";
                                                    echo "<img src='" . $usuari['imatge'] . "' class='foto_usuari_dep'>";
                                                    echo $usuari['usuari'];
                                                    echo "</label>";
                                                }

                                                // Obtenir els departaments que ja tenen accés a l'arxiu
                                                $sql_departaments_compartits = "select id_dep from ARXIUS_COMPARTITS_DEPARTAMENTS where id_arxiu = (select id_arxiu from ARXIUS_PUJATS where ruta = '$item_path')";
                                                $result_departaments_compartits = mysqli_query($conexion, $sql_departaments_compartits);
                                                $departaments_compartits = [];
                                                while ($row = mysqli_fetch_assoc($result_departaments_compartits)) {
                                                    $departaments_compartits[] = $row['id_dep'];
                                                }

                                                // Obtenir tots els departaments
                                                $sql_departaments = "select * from DEPARTAMENTS ORDER BY nom";
                                                $departaments = mysqli_query($conexion, $sql_departaments);

                                                echo "<h6>Departaments</h6>";
                                                while ($departament = $departaments->fetch_assoc()) {
                                                    $checked = in_array($departament['id_dep'], $departaments_compartits) ? 'checked' : '';
                                                    echo "<label class='usudeps'>";
                                                    echo "<input type='checkbox' name='departaments[]' value='" . $departament['id_dep'] . "' $checked> ";
                                                    echo $departament['nom'];
                                                    echo "</label>";
                                                }
                                                ?>

                                                <!-- Input ocult per enviar la ruta -->
                                                <input type="hidden" name="ruta_arxiu" value="<?php echo $item_path; ?>">
                                                
                                                <!-- Input ocult per enviar la URL -->
                                                <input type="hidden" name="current_dir" value="<?php echo htmlspecialchars($current_dir); ?>">

                                                <!-- Un únic botó per compartir amb usuaris i departaments -->
                                                <button type="submit" class="boto_compartir">Compartir amb usuaris i departaments</button>
                                            </form>
                                        </div>

                                        <span class="close-icon" onclick="cerrarPopup_compartir('<?php echo $popup_id; ?>')">×</span>
                                    </div>




                                    <?php
                                    
                                    echo '<a href="eliminar.php?file=' . urlencode($item_path) . '&dir=' . urlencode($current_dir) . '" onclick="return confirm(\'Estàs segur que vols eliminar aquest arxiu?\')">eliminar <i class="uil uil-trash-alt"></i></a>';  
                                echo '</div>';
                            echo '</div>';
                        }

                        
                    }
                    ?>
                </div>
            </div>



        <?php
        }
        else {
            echo "Credencials incorrectes. Fes clic "."<a href='inici'>aquí</a>"." per iniciar sessió.";
        }
        ?>

                </div>
                
            </div>
        </div>
    </section>



    <!-- FOOTER -->
    <footer class="footer py-5">
        <div class="container">
            <div class="row">

                <div class="col-lg-12 col-12">
                    <p class="copyright-text text-center">Copyright &copy; 2025 EvilMarc . All rights reserved</p>
                    <p class="copyright-text text-center">Designed by EvilMarc Team</p>
                </div>

            </div>
        </div>
    </footer>


    <script src="js/jquery-3.3.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/Headroom.js"></script>
    <script src="js/jQuery.headroom.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/smoothscroll.js"></script>
    <script src="js/custom.js"></script>

    <script>
        // popup per crear departaments
        function abrirPopup_compartir(popupid) {
            document.getElementById("overlay_"+popupid).style.display = "block";
            document.getElementById(popupid).style.display = "block";
        }
        function cerrarPopup_compartir(popupid) {
            document.getElementById("overlay_"+popupid).style.display = "none";
            document.getElementById(popupid).style.display = "none";
        }
    </script>


    <script>
    function mostrarNombreArchivo() {
        const archivoInput = document.getElementById('archivo');
        const nombreArchivo = document.getElementById('file-name');

        // Mostrar el nombre del archivo seleccionado
        if (archivoInput.files.length > 0) {
            // nombreArchivo.textContent = archivoInput.files[0].name;

            const archivo = archivoInput.files[0];
            const nombre = archivo.name;
            const peso = (archivo.size / 1024 / 1024).toFixed(2);

            const fecha = new Date();

            // Formatear la fecha (día, mes, año)
            const dia = fecha.getDate(); 
            const mes = fecha.getMonth() + 1; 
            const año = fecha.getFullYear(); 

            // Formatear la hora (horas, minutos, segundos)
            const horas = fecha.getHours();
            const minutos = fecha.getMinutes(); 
            const segundos = fecha.getSeconds(); 

            const fecha_hora = dia + "/" + mes + "/" + año + " - (" + horas + ":" + minutos + ":" + segundos + ")";

            nombreArchivo.textContent = "nom arxiu: " + nombre + " - pes arxiu: " + peso + " MB - data: " + fecha_hora;
        } else {
            nombreArchivo.textContent = "";
        }
    }


        // Funció per filtrar fitxers
        //function filtrarFitxers() {
        //     const cercador = document.getElementById('buscador-fitxers');
        //     const terme = cercador.value.toLowerCase();
        //     const elements = document.querySelectorAll('.llista_fitxers');
            
        //     elements.forEach(element => {
            
        //         const text = element.textContent.toLowerCase();
        //         if (text.includes(terme)) {
        //             element.classList.remove('filtrat');
        //         } else {
        //             element.classList.add('filtrat');
        //         }
        //     });
        // }

        function filtrarFitxers() {
            const cercador = document.getElementById('buscador-fitxers');
            const terme = cercador.value.toLowerCase();
            const elements = document.querySelectorAll('.llista_fitxers');
            
            elements.forEach(element => {
                // Buscar específicament en el títol de l'arxiu/carpeta
                const titolElement = element.querySelector('span, div:first-child'); // Selecciona el span o el primer div
                const titol = titolElement ? titolElement.textContent.toLowerCase() : '';
                
                if (titol.includes(terme)) {
                    element.classList.remove('filtrat');
                } else {
                    element.classList.add('filtrat');
                }
            });
        }

        // Escolta els canvis en el camp de cerca
        document.getElementById('buscador-fitxers').addEventListener('input', filtrarFitxers);

        // Funció per a la tecla Escape
        document.getElementById('buscador-fitxers').addEventListener('keyup', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                filtrarFitxers();
            }
        });




        // Funció per filtrar fitxers
        function filtrarUsus() {
            const cercador = document.getElementById('buscador-usudep');
            const terme = cercador.value.toLowerCase();
            const elements = document.querySelectorAll('.usudeps');
            
            elements.forEach(element => {
                const text = element.textContent.toLowerCase();
                if (text.includes(terme)) {
                    element.classList.remove('filtrat');
                } else {
                    element.classList.add('filtrat');
                }
            });
        }

        // Escolta els canvis en el camp de cerca
        document.getElementById('buscador-usudep').addEventListener('input', filtrarUsus);

        // Funció per a la tecla Escape
        document.getElementById('buscador-usudep').addEventListener('keyup', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                filtrarUsus();
            }
        });
    </script>

</body>

</html>
