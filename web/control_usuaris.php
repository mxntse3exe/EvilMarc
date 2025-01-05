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
    }

    if(isset($_REQUEST['validar'])) {
        $sql_validar = 'update USUARIS set validat = 1 where id_usu ='.$_REQUEST["id"].';';

        mysqli_query($conexion,$sql_validar);
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
        if(($_SESSION['valido'] == 1) && ($admin == 1)) {
            $usuari = $_SESSION['usuari']

        ?>

            <div class="row seccio_panell">
                <div>
                    <h2>Panell de control d'usuaris</h2>
                    <div class="contingut_panell">
                    
                    <?php
                    $consulta_usuaris = "select * from USUARIS;";

                    $usuaris_bd = mysqli_query($conexion,$consulta_usuaris);

                    while($usuari_bd = $usuaris_bd->fetch_assoc()) {
                    ?>



                        <div class="boto_control_usu">

                            <img src="<?php echo $usuari_bd['imatge']; ?>" class="boto_control_usu_img">
                            <div class="dades_usu">
                                <span><?php echo $usuari_bd['usuari']; ?></span>
                                <p class="text_dades"><?php echo $usuari_bd['nom']; ?> <?php echo $usuari_bd['cognoms']; ?></p>
                                <p class="text_dades"><?php echo $usuari_bd['correu']; ?></p>
                            </div>
                            <div>

                                <!-- botó per validar els usuaris -->
                                <form action="control_usuaris" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $usuari_bd['id_usu']; ?>">

                                    <input class="form-control submit-btn" type="submit" value="Validar" name="validar">
                                </form>

                                <!-- botó per eliminar els usuaris -->
                                <form action="control_usuaris" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $usuari_bd['id_usu']; ?>">

                                    <input class="form-control submit-btn" type="submit" value="Eliminar" name="eliminar">
                                </form>

                            </div>

                        </div>
                        
                        
                        <?php
                    }
                    echo "</div>";
                    ?>

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