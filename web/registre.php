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

    require 'vendor/autoload.php';

    use MongoDB\Client;

    // Connexió a la base de dades
    $mongoClient = new Client("mongodb://localhost:27017");
    $db = $mongoClient->logs;
    $collection_pujats = $db->fitxers_pujats;
    $collection_infectats = $db->fitxers_infectats;
    $collection_eliminats = $db->fitxers_eliminats;

    // Opcions de cerca
    $filter = ['id_usuari' => $num_usu];  // Filtra per id_usuari
    $options = ['sort' => ['data' => -1]];  // Ordena per data (descendent)

    // Obtenir dades de la base de dades
    $pujats = $collection_pujats->find($filter, $options);
    $infectats = $collection_infectats->find($filter, $options);
    $eliminats = $collection_eliminats->find($filter, $options);

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
                    <li class="nav-item">
                        <a href="panell_usuari" class="nav-link"><span data-hover="Panell principal">Panell principal</span></a>
                    </li>
                    <li class="nav-item">
                        <a href="sortir" class="nav-link"><span data-hover="Sortir">Sortir</span></a>
                    </li>
            
                </ul>
            </div>
        </div>
    </nav>



    <!-- FUNCIONAMENT -->
    
    <section class="about full-screen d-lg-flex justify-content-center align-items-center">
        <div class="container">

            <div class="row seccio_panell">
                <div style="width: 100%;">
                    <?php
                    if($_SESSION['valido'] == 1) {
                    ?>
                    <h2>Registre d'arxius</h2>
                    
                    <?php
                    if($admin == 1) {
                        echo '<div class="admin_logs"><a href="registre_admin">Veure registres d\'administrador<i class="uil uil-cog"></i></a></div>';
                    }
                    ?>



                    <div class="columnes_logs">
                        <div class="logs">
                            <h6><i class="uil-upload"></i> Arxius pujats</h6>
                            
                            <?php foreach ($pujats as $pujat): ?>
                                
                                <span class="text_logs"><?php echo $pujat->nom_arxiu; ?></span>

                                <span class="text_logs"><?php echo $pujat->data->toDateTime()->setTimezone(new DateTimeZone('Europe/Madrid'))->format('Y-m-d H:i:s'); ?></span>

                                <div class="linia"></div>
                             
                            <?php endforeach; ?>
                        </div>
                        <div class="logs">
                            <h6><i class="uil-bug"></i> Arxius infectats</h6>
                            
                            <?php foreach ($infectats as $infectat): ?>
                                
                                <span class="text_logs"><?php echo $infectat->nom_arxiu; ?></span>
                            
                                <span class="text_logs"><?php echo $infectat->data->toDateTime()->setTimezone(new DateTimeZone('Europe/Madrid'))->format('Y-m-d H:i:s'); ?></span>

                                <div class="linia"></div>
                             
                            <?php endforeach; ?>
                        </div>
                        <div class="logs">
                            <h6><i class="uil-trash-alt"></i> Arxius eliminats</h6>
                            
                            <?php foreach ($eliminats as $eliminat): ?>
                                
                                <span class="text_logs"><?php echo $eliminat->nom_arxiu; ?></span>
                            
                                <span class="text_logs"><?php echo $eliminat->data->toDateTime()->setTimezone(new DateTimeZone('Europe/Madrid'))->format('Y-m-d H:i:s'); ?></span>

                                <div class="linia"></div>
                             
                            <?php endforeach; ?>
                        </div>
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
        // Capturar clic en el botó "Modificar foto"
        document.getElementById('btnModificarFoto').addEventListener('click', function () {
            document.getElementById('files_up').click(); // Simular clic en el camp de fitxer
        });

        // Enviar formulari automàticament quan es selecciona un fitxer
        function submitForm() {
            var fileInput = document.getElementById('files_up');
            if (fileInput.files.length > 0) { // Comprovar si s'ha seleccionat un fitxer
                document.getElementById('formModificarFoto').submit();
            } else {
                alert('Si us plau, selecciona una imatge.');
            }
        }

    </script>

</body>

</html>