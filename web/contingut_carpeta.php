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
        $dep = $fila["id_dep"];
    }

    // Trobar els arxius compartits amb l'usuari

    

    $sql_arxius_usu = "select ruta from ARXIUS_PUJATS where id_arxiu IN (select id_arxiu from ARXIUS_COMPARTITS_USUARIS where id_destinatari = ".$num_usu.")";

    $sql_arxius_dep = "select ruta from ARXIUS_PUJATS where id_arxiu IN (select id_arxiu from ARXIUS_COMPARTITS_DEPARTAMENTS where id_dep = ".$dep.")";

    $sql_carpetes_usu = "select ruta from CARPETES_COMPARTIDES_USUARIS where id_destinatari = ".$num_usu;

    $sql_carpetes_dep = "select ruta from CARPETES_COMPARTIDES_DEPARTAMENTS where id_dep = ".$dep;

    // Arrays para almacenar los archivos y carpetas compartidos
    $arxius_compartits = [];
    $carpetes_compartides = [];

    // Ejecutar y almacenar archivos compartidos con usuarios
    $result = mysqli_query($conexion, $sql_arxius_usu);
    while ($row = mysqli_fetch_assoc($result)) {
        $arxius_compartits[] = $row['ruta'];
    }

    // Ejecutar y almacenar archivos compartidos por departamento
    $result = mysqli_query($conexion, $sql_arxius_dep);
    while ($row = mysqli_fetch_assoc($result)) {
        $arxius_compartits[] = $row['ruta'];
    }

    // Ejecutar y almacenar carpetas compartidas con usuarios
    $result = mysqli_query($conexion, $sql_carpetes_usu);
    while ($row = mysqli_fetch_assoc($result)) {
        $carpetes_compartides[] = $row['ruta'];
    }

    // Ejecutar y almacenar carpetas compartidas por departamento
    $result = mysqli_query($conexion, $sql_carpetes_dep);
    while ($row = mysqli_fetch_assoc($result)) {
        $carpetes_compartides[] = $row['ruta'];
    }


    // Quan la ruta base es passa per primera vegada, l'assignem a la sessió
    if (!isset($_SESSION['ruta_carpeta_base'])) {
        // Si no està configurada, la configurem amb la ruta inicial
        $_SESSION['ruta_carpeta_base'] = $_GET['dir']; // Ruta base de l'usuari
    }
    $ruta_carpeta_base = $_SESSION['ruta_carpeta_base']; // Usar la ruta guardada a la sessió
    


    // Ruta base de tu directorio
    $base_dir = '/var/www/html/fitxers/fitxers_usuaris/fitxers_'.$num_usu;
    
    // Obtener la ruta actual desde la URL o usar la base por defecto
    $current_dir = isset($_GET['dir']) ? $_GET['dir'] : $base_dir;
    
    // Prevenir accesos fora del directori base i permetre només les carpetes compartides
    $real_current_dir = realpath($current_dir);
    
    // Comprovar si la carpeta actual o alguna de les seves superiors està compartida
    $es_carpeta_compartida = false;
    $ruta_temporal = $real_current_dir;
    
    while ($ruta_temporal !== realpath($base_dir) && $ruta_temporal !== '/') {
        foreach ($carpetes_compartides as $carpeta) {
            if (realpath($carpeta) === $ruta_temporal) {
                $es_carpeta_compartida = true;
                break 2; // Sortim dels dos bucles
            }
        }
        $ruta_temporal = dirname($ruta_temporal); // Pugem un nivell
    }
    
    if (strpos($real_current_dir, realpath($base_dir)) !== 0 && !$es_carpeta_compartida) {
        die("Accés no permès.");
    }



    // Obtener contenido del directorio
    $items = scandir($current_dir);

    // Generar la ruta relativa per mostrar-la a l'usuari
    // $relative_dir = str_replace(realpath($base_dir), '', $real_current_dir);
    // $relative_dir = $relative_dir === '' ? '/' : $relative_dir;

    $relative_dir = str_replace(realpath($ruta_carpeta_base), '', $real_current_dir);
    $relative_dir = trim($relative_dir, '/'); // Eliminar barres innecessàries
    
    if ($relative_dir === '') {
        $relative_dir = ''; // Si estem a la carpeta base, mostrar només '/'
    } else {
        $relative_dir = '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relative_dir); // Afegir la barra inicial
    }
    

    // Extreure només l'última part de la ruta
    $final_part = basename($ruta_carpeta_base);


    // Si la variable de sessió no està definida, la guardem
    if (!isset($_SESSION['ruta_final'])) {
        $_SESSION['ruta_final'] = $final_part;
    }
    $ruta_final = $_SESSION['ruta_final'];


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
                    <h2>Arxius compartits amb mi</h2>

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
                    <p>Directori actual: <?php echo "/"."$final_part".htmlspecialchars($relative_dir); ?></p>
                </div>            

                <div>
                    <?php
                    
                    // Comprovar si estàs a la ruta base o no
                    if ($current_dir !== $ruta_carpeta_base) {
                        // Si no estàs a la ruta base, puja un nivell
                        $parent_dir = dirname($current_dir);
                        echo '<a href="?dir=' . urlencode($parent_dir) . '" class="carpeta"><i class="uil uil-arrow-left"></i> Enrere</a><br><br>';
                    } else {
                        // Si estàs a la ruta base, enllaçar a compartits.php
                        echo '<a href="compartits" class="carpeta"><i class="uil uil-arrow-left"></i> Enrere a compartits</a><br><br>';
                    }

                    

                    
                    // Mostrar les carpetes
                    foreach ($items as $item) {
                        // Excloure les carpetes . i ..
                        if ($item !== '.' && $item !== '..') {
                            $item_path = $current_dir . '/' . $item;
                
                            // Si és una carpeta, mostrar-la amb un enllaç
                            if (is_dir($item_path)) {
                                echo '<a href="?dir=' . urlencode($item_path) . '" class="carpeta">';
                                    echo '<div class="llista_fitxers">';
                                        echo '<div><i class="uil uil-folder"></i> ' . htmlspecialchars($item) . '</div>';
                                    echo '</div>';
                                echo '</a>';
                            }
                            // Si és un arxiu, mostrar-lo amb un enllaç de descàrrega
                            else {
                                echo '<div class="llista_fitxers">';
                                    echo '<span>' . htmlspecialchars($item) . '</span>';
                                    echo '<div class="botons_arxius">';
                                        echo '<a href="descargar.php?file=' . urlencode($item_path) . '&dir=' . urlencode($current_dir) . '">descarregar <i class="uil uil-arrow-down"></i></a>';
                                    echo '</div>';
                                echo '</div>';
                            }
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


</body>

</html>