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

    // Processar filtres de dates
    $filtre_data_inici = isset($_GET['data_inici']) ? $_GET['data_inici'] : null;
    $filtre_data_fi = isset($_GET['data_fi']) ? $_GET['data_fi'] : null;

    // Crear filtre base per usuari
    $filter = ['id_usuari' => $num_usu];
    
    // Afegir filtres de dates si s'han especificat
    if ($filtre_data_inici) {
        $filter['data'] = ['$gte' => new MongoDB\BSON\UTCDateTime(strtotime($filtre_data_inici) * 1000)];
    }
    if ($filtre_data_fi) {
        if (isset($filter['data'])) {
            $filter['data']['$lte'] = new MongoDB\BSON\UTCDateTime(strtotime($filtre_data_fi . ' 23:59:59') * 1000);
        } else {
            $filter['data'] = ['$lte' => new MongoDB\BSON\UTCDateTime(strtotime($filtre_data_fi . ' 23:59:59') * 1000)];
        }
    }

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
                    if($_SESSION['valido'] == 1 && $admin == 1) {
                    ?>
                    <h2>Registre d'arxius</h2>
                    
                   
                    <div class="admin_logs"><a href="registre">Veure registres de l'usuari<i class="uil-user-circle"></i></a></div>
                    
                    <!-- Formulari de filtre per dates -->
                    <div class="filtre-dates">
                        <form method="get" action="">
                            <label for="data_inici">Des de:</label>
                            <input type="date" id="data_inici" name="data_inici" value="<?php echo $filtre_data_inici; ?>">
                            
                            <label for="data_fi">Fins a:</label>
                            <input type="date" id="data_fi" name="data_fi" value="<?php echo $filtre_data_fi; ?>">
                            
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <a href="registre_admin" class="btn btn-secondary reset-btn">Netejar</a>
                        </form>
                    </div>

                    <div class="columnes_logs">
                        <div class="logs">
                            <h6><i class="uil-upload"></i> Arxius pujats</h6>
                            
                            <?php foreach ($pujats as $pujat): ?>

                                <?php
                                $id = $pujat->id_usuari;
                                $sql_id_nom = "select * from USUARIS where id_usu = ".$id;

                                $files_id = mysqli_query($conexion,$sql_id_nom);

                                while($fila_id = $files_id->fetch_assoc()) {
                                    $nom = $fila_id["usuari"];
                                }

                                if ($nom == '') $nom = "Usuari eliminat";
                                ?>


                                <span class="text_logs nom"><b><?php echo $nom; ?></b></span>
                                
                                <span class="text_logs"><?php echo $pujat->nom_arxiu; ?></span>

                                <span class="text_logs"><?php echo $pujat->data->toDateTime()->setTimezone(new DateTimeZone('Europe/Madrid'))->format('Y-m-d H:i:s'); ?></span>

                                <div class="linia"></div>
                             
                            <?php endforeach; ?>
                        </div>
                        <div class="logs">
                            <h6><i class="uil-bug"></i> Arxius infectats</h6>
                            
                            <?php foreach ($infectats as $infectat): ?>
                                
                                <?php
                                $id = $infectat->id_usuari;
                                $sql_id_nom = "select * from USUARIS where id_usu = ".$id;

                                $files_id = mysqli_query($conexion,$sql_id_nom);

                                while($fila_id = $files_id->fetch_assoc()) {
                                    $nom = $fila_id["usuari"];
                                }

                                if ($nom == '') $nom = "Usuari eliminat";
                                ?>


                                <span class="text_logs nom"><b><?php echo $nom; ?></b></span>

                                <span class="text_logs"><?php echo $infectat->nom_arxiu; ?></span>
                            
                                <span class="text_logs"><?php echo $infectat->data->toDateTime()->setTimezone(new DateTimeZone('Europe/Madrid'))->format('Y-m-d H:i:s'); ?></span>

                                <div class="linia"></div>
                             
                            <?php endforeach; ?>
                        </div>
                        <div class="logs">
                            <h6><i class="uil-trash-alt"></i> Arxius eliminats</h6>
                            
                            <?php foreach ($eliminats as $eliminat): ?>

                                <?php
                                $id = $eliminat->id_usuari;
                                $sql_id_nom = "select * from USUARIS where id_usu = ".$id;

                                $files_id = mysqli_query($conexion,$sql_id_nom);

                                while($fila_id = $files_id->fetch_assoc()) {
                                    $nom = $fila_id["usuari"];
                                }

                                if ($nom == '') $nom = "Usuari eliminat";
                                ?>


                                <span class="text_logs nom"><b><?php echo $nom; ?></b></span>
                                
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
        // Validar que la data d'inici no sigui posterior a la data de fi
        document.querySelector('form').addEventListener('submit', function(e) {
            const dataInici = document.getElementById('data_inici').value;
            const dataFi = document.getElementById('data_fi').value;
            
            if (dataInici && dataFi && new Date(dataInici) > new Date(dataFi)) {
                alert('La data d\'inici no pot ser posterior a la data de fi.');
                e.preventDefault();
            }
        });
    </script>

</body>

</html>