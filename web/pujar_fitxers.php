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
                        <form enctype="multipart/form-data" method="post" action="pujar_fitxers" class="pujar_arxius_carpetes">
                            <input type="hidden" name="max_file_size" value="5000000">

                            <label for="archivo" class="custom-file-upload"><i class="uil uil-file-alt"></i>Selecciona l'arxiu o carpeta que vols pujar...</label>

                            <input type="file" name="archivo" class="input_arxiu" id="archivo" onchange="mostrarNombreArchivo()">
                            
                            <input type="submit" value="Pujar arxiu" class="pujar_arxiu_submit" name="arxiu">
                        </form>
                        <span id="file-name"></span>
                    </div>

                    
                    
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
                            echo '<a href="?dir='.urlencode($item_path).'" class="carpeta"><div class="llista_fitxers"><div><i class="uil uil-folder"></i> '.htmlspecialchars($item).'</div></div></a>';
                        } else {
                            // Mostrar archivos
                            echo '<div class="llista_fitxers"><span>'.htmlspecialchars($item).'</span>   <a href="descargar.php?file=' . urlencode($item_path) . '&dir=' . urlencode($base_dir) . '">descarregar <i class="uil uil-arrow-down"></i></a>  </div>';
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