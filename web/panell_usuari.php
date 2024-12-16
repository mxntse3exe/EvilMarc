<?php 
	session_start();

    $servidor = "localhost";
    $usuario = "web";
    $password = "T5Dk!xq";
    $db = "evilmarc";

    $conexion = mysqli_connect($servidor,$usuario,$password,$db);

    if (!$conexion) die ("Error al connectar amb la base de dades.");
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
                    
                    <?php
                    if($_SESSION['valido'] == 1) {
                        $usuari = $_SESSION['usuari'];
                        echo '<li class="nav-item"><a href="sortir" class="nav-link"><span data-hover="Sortir">Sortir</span></a></li>';
                    }
                    else {
                        echo '<li class="nav-item"><a href="inici" class="nav-link"><span data-hover="Iniciar sessió">Iniciar sessió</span></a></li>';
                        echo '<li class="nav-item"><a href="registrar" class="nav-link"><span data-hover="Registrar-se">Registrar-se</span></a></li>';
                    }
                    ?>

                </ul>
            </div>
        </div>
    </nav>



    <!-- FUNCIONAMENT -->
    
    <section class="about full-screen d-lg-flex justify-content-center align-items-center">
        <div class="container">


        <?php
        if($_SESSION['valido'] == 1) {
            $usuari = $_SESSION['usuari']
        ?>

            <div class="row seccio_panell">
                <div>
                    <h2>Panell d'usuari</h2>
                    <?php
                    echo "<p>Benvingut/da, ".$usuari."!</p>";
                    ?>

                    <div class="contingut_panell">
                        <a href="" class="link_panell"><div class="botons_panell"><span>Pujar i escanejar arxius</span></div></a>
                        <a href="" class="link_panell"><div class="botons_panell"><span>Els meus arxius</span></div></a>
                        <a href="" class="link_panell"><div class="botons_panell"><span>Arxius compartits amb mi</span></div></a>
                        <a href="" class="link_panell"><div class="botons_panell"><span>Registre d'arxius pujats</span></div></a>
                        <a href="" class="link_panell"><div class="botons_panell"><span>El meu compte</span></div></a>

                        <!-- Revisar!!! Amb PHP haurem de fer que només apareixi aquest apartat als usuaris administradors!!! -->

                        <?php
                            $sql = "select admin from USUARIS where usuari = '".$usuari."'";

                            $files = mysqli_query($conexion,$sql);

                            while($fila = $files->fetch_assoc()) {
                                $admin = $fila["admin"];
                            }

                            if ($admin == 1) {
                                echo '<a href="" class="link_panell"><div class="botons_panell"><span>Panell de control d\'usuaris</span></div></a>';
                            }
                        ?>

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