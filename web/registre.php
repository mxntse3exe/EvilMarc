<?php 
    session_start();

    // Protecció d'accés
    if (!isset($_SESSION['usuari']) || !isset($_SESSION['valido']) || $_SESSION['valido'] != 1) {
        header('Location: inici');
        exit;
    }

    $servidor = "localhost";
    $usuario = "web";
    $password = "T5Dk!xq";
    $db = "evilmarc";

    $conexion = mysqli_connect($servidor, $usuario, $password, $db);
    if (!$conexion) {
        error_log("[" . date('Y-m-d H:i:s') . "] Error MySQL: " . mysqli_connect_error() . "\n", 3, __DIR__ . '/error.log');
        die("Error intern de connexió. Torna-ho a intentar més tard.");
    }

    $usuari = $_SESSION['usuari'];

    // Utilitza consulta preparada per seguretat i depuració
    $stmt = $conexion->prepare("SELECT id_usu, admin, imatge, nom, cognoms, direccio FROM USUARIS WHERE usuari = ?");
    if (!$stmt) {
        error_log("[" . date('Y-m-d H:i:s') . "] Error prepare: " . $conexion->error . "\n", 3, __DIR__ . '/error.log');
        die("Error intern de connexió. Torna-ho a intentar més tard.");
    }
    $stmt->bind_param("s", $usuari);
    $stmt->execute();
    $stmt->bind_result($id_usu, $admin, $imatge, $nom, $cognoms, $direccio);

    $num_usu = null;
    while ($stmt->fetch()) {
        $_SESSION['admin'] = $admin;
        $_SESSION['id_usu'] = $id_usu;
        $_SESSION['imatge'] = $imatge;
        $num_usu = (string)$id_usu; // Força a string per compatibilitat MongoDB
    }
    $stmt->close();
    if (!$num_usu) {
        die("No s'ha trobat l'usuari.");
    }

    require 'vendor/autoload.php';

    use MongoDB\Client;

    // Connexió a la base de dades
    $mongoClient = new Client("mongodb://localhost:27017");
    $db = $mongoClient->logs;
    $collection_pujats = $db->fitxers_pujats;
    $collection_infectats = $db->fitxers_infectats;
    $collection_eliminats = $db->fitxers_eliminats;

    // Obtenir els valors dels filtres de dates des de la petició GET
    $filtre_data_inici = isset($_GET['data_inici']) ? $_GET['data_inici'] : null;
    $filtre_data_fi = isset($_GET['data_fi']) ? $_GET['data_fi'] : null;

    // Validació essencial de dates al servidor (format YYYY-MM-DD)
    if ($filtre_data_inici && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtre_data_inici)) {
        $filtre_data_inici = null;
    }
    if ($filtre_data_fi && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filtre_data_fi)) {
        $filtre_data_fi = null;
    }

    // Crear el filtre base per a MongoDB: només mostra els fitxers de l'usuari actual
    $filter = ['id_usuari' => $num_usu];

    // Afegir condicions de filtre per dates si l'usuari les ha especificat
    if ($filtre_data_inici) {
        // Si hi ha data d'inici, afegeix condició de data >= data_inici
        $filter['data'] = ['$gte' => new MongoDB\BSON\UTCDateTime(strtotime($filtre_data_inici) * 1000)];
    }
    if ($filtre_data_fi) {
        // Si ja hi ha condició de data, afegeix també la condició <= data_fi
        if (isset($filter['data'])) {
            $filter['data']['$lte'] = new MongoDB\BSON\UTCDateTime(strtotime($filtre_data_fi . ' 23:59:59') * 1000);
        } else {
            // Si només hi ha data de fi, crea condició de data <= data_fi
            $filter['data'] = ['$lte' => new MongoDB\BSON\UTCDateTime(strtotime($filtre_data_fi . ' 23:59:59') * 1000)];
        }
    }

    // Opcions per ordenar els resultats per data descendent
    $options = ['sort' => ['data' => -1]];

    // Consultar les col·leccions de MongoDB amb el filtre i opcions
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

                    <!-- Formulari de filtre per dates -->
                    <div class="filtre-dates">
                        <form method="get" action="">
                            <!-- Camp per seleccionar la data d'inici del filtre -->
                            <label for="data_inici">Des de:</label>
                            <input type="date" id="data_inici" name="data_inici" value="<?php echo htmlspecialchars($filtre_data_inici ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            
                            <!-- Camp per seleccionar la data de fi del filtre -->
                            <label for="data_fi">Fins a:</label>
                            <input type="date" id="data_fi" name="data_fi" value="<?php echo htmlspecialchars($filtre_data_fi ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            
                            <!-- Botó per aplicar el filtre -->
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                            <!-- Botó per netejar els filtres i tornar a la vista original -->
                            <a href="registre" class="btn btn-secondary reset-btn">Netejar</a>
                        </form>
                    </div>

                    <div class="columnes_logs">
                        <div class="logs">
                            <h6><i class="uil-upload"></i> Arxius pujats</h6>
                            
                            <?php foreach ($pujats as $pujat): ?>
                                <!-- Mostra el nom de l'arxiu pujat escapant-lo per seguretat -->
                                <span class="text_logs"><?php echo htmlspecialchars($pujat->nom_arxiu, ENT_QUOTES, 'UTF-8'); ?></span>
                                <!-- Mostra la data de pujada -->
                                <span class="text_logs"><?php echo $pujat->data->toDateTime()->setTimezone(new DateTimeZone('Europe/Madrid'))->format('Y-m-d H:i:s'); ?></span>
                                <div class="linia"></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="logs">
                            <h6><i class="uil-bug"></i> Arxius infectats</h6>
                            
                            <?php foreach ($infectats as $infectat): ?>
                                <!-- Mostra el nom de l'arxiu infectat escapant-lo per seguretat -->
                                <span class="text_logs"><?php echo htmlspecialchars($infectat->nom_arxiu, ENT_QUOTES, 'UTF-8'); ?></span>
                                <!-- Mostra la data d'infecció -->
                                <span class="text_logs"><?php echo $infectat->data->toDateTime()->setTimezone(new DateTimeZone('Europe/Madrid'))->format('Y-m-d H:i:s'); ?></span>
                                <div class="linia"></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="logs">
                            <h6><i class="uil-trash-alt"></i> Arxius eliminats</h6>
                            
                            <?php foreach ($eliminats as $eliminat): ?>
                                <!-- Mostra el nom de l'arxiu eliminat escapant-lo per seguretat -->
                                <span class="text_logs"><?php echo htmlspecialchars($eliminat->nom_arxiu, ENT_QUOTES, 'UTF-8'); ?></span>
                                <!-- Mostra la data d'eliminació -->
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