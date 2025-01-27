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
            foreach ($_FILES['archivo']['name'] as $key => $name) {
                $tmp_name = $_FILES['archivo']['tmp_name'][$key];
                $error = $_FILES['archivo']['error'][$key];
        
                if ($error === UPLOAD_ERR_OK) {
                    // Genera un nom únic per al fitxer
                    $nomUnic = generarNomUnic($current_dir, $name);
                    $target_path = $current_dir . DIRECTORY_SEPARATOR . $nomUnic;
        
                    // Mou el fitxer pujat
                    move_uploaded_file($tmp_name, $target_path);
                }
            }
            echo "Arxius individuals pujats correctament.";
        }

        // Procesar carpetas
        if (isset($_FILES['carpeta']) && $_POST['upload_type'] === 'folder') {
            foreach ($_FILES['carpeta']['name'] as $key => $name) {
                $tmp_name = $_FILES['carpeta']['tmp_name'][$key];
                $error = $_FILES['carpeta']['error'][$key];

                if ($error === UPLOAD_ERR_OK) {
                    $relative_path = $_FILES['carpeta']['full_path'][$key]; // Ruta relativa completa
                    $target_path = $current_dir . DIRECTORY_SEPARATOR . $relative_path;

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
                    }
                }
                
                
            }
            
            echo "Carpeta pujada correctament.";
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
                <p>Directori actual: <?php echo htmlspecialchars($relative_dir); ?></p>

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
                                    echo '<a href="eliminar.php?folder=' . urlencode($item_path) . '&dir=' . urlencode($base_dir) . '" onclick="return confirm(\'Estàs segur que vols eliminar aquesta carpeta?\')">eliminar <i class="uil uil-trash-alt"></i></a>';
                                echo '</div>';
                            echo '</a>';
                        } else {
                            // Mostrar archivos
                            // echo '<div class="llista_fitxers"><span>'.htmlspecialchars($item).'</span>   <a href="descargar.php?file=' . urlencode($item_path) . '&dir=' . urlencode($base_dir) . '">descarregar <i class="uil uil-arrow-down"></i></a>  </div>';
                            echo '<div class="llista_fitxers">';
                                echo '<span>'.htmlspecialchars($item).'</span>';
                                echo '<div>';
                                    echo '<a href="descargar.php?file=' . urlencode($item_path) . '&dir=' . urlencode($current_dir) . '">descarregar <i class="uil uil-arrow-down"></i></a>';
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
    </script>

</body>

</html>