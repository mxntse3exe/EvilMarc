<?php
if(isset($_REQUEST['registre'])) {
    $servidor = "localhost";
    $usuario = "web";
    $password = "T5Dk!xq";
    $db = "evilmarc";

    $conexion = mysqli_connect($servidor,$usuario,$password,$db);

    if (!$conexion) die ("Error al connectar amb la base de dades.");

    $correu = $_REQUEST['correu'];
    $correu = str_replace("=","",$correu);
    $correu = str_replace(" ","",$correu);
    $correu = str_replace("'","",$correu);

    $usuari = $_REQUEST['usuari'];
    $usuari = str_replace("=","",$usuari);
    $usuari = str_replace(" ","",$usuari);
    $usuari = str_replace("'","",$usuari);

    $pass = $_REQUEST['contrasenya'];
    $pass = hash('sha256', $pass, false);


    $sql_comprovar_correu = "select * from USUARIS where correu = '".$correu."'";
    $sql_comprovar_usuari = "select * from USUARIS where usuari = '".$usuari."'";


    $sql = "insert into USUARIS(usuari,correu,contrasenya) values('".$usuari."','".$correu."','".$pass."')";

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

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                <span class="navbar-toggler-icon"></span>
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="inici" class="nav-link"><span data-hover="Iniciar sessió">Iniciar sessió</span></a>
                    </li>
                    <li class="nav-item">
                        <a href="registrar" class="nav-link"><span data-hover="Registrar-se">Registrar-se</span></a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>



    <!-- FUNCIONAMENT -->
    
    <section class="about full-screen d-lg-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row seccio_reg_log">
                <div>
                    <h2>Registrar-se</h2>
                        <div class="contact_contents">
                            
                            <div class="contact-form">	
                                <div class="formulari_reg_log">
                                    <form method="post" action="registrar">

                                        <?php

                                        if(isset($_REQUEST['registre'])) {
                                            $files_correu = mysqli_query($conexion,$sql_comprovar_correu);
                                            $num_files_correu = mysqli_num_rows($files_correu);

                                            $files_usuari = mysqli_query($conexion,$sql_comprovar_usuari);
                                            $num_files_usuari = mysqli_num_rows($files_usuari);

                                            if ($num_files_correu == 0) {
                                                if ($num_files_usuari == 0) {
                                                    if (mysqli_query($conexion,$sql)) {
                                                        echo "<p class='adverts'>Usuari creat correctament, esperi que l'administrador verifiqui el seu compte.</p>";
                                                    }
                                                    else {
                                                        echo "<p class='adverts'>No hem pogut crear el seu usuari en aquests moments.</p>";
                                                    }
                                                }
                                                else {
                                                    echo "<p class='adverts'>Ja existeix un compte amb aquest correu o usuari.</p>";
                                                }
                                            }
                                            else {
                                                echo "<p class='adverts'>Ja existeix un compte amb aquest correu o usuari.</p>";
                                            }
                                        }
                                        ?>

                                        <input class="form-control" type="email" name="correu" placeholder="correu electrònic" required>


                                        <input class="form-control" type="text" name="usuari" placeholder="nom d'usuari" required>


                                        <div class="password-container">

                                            <input class="form-control" type="password" name="contrasenya" placeholder="contrasenya" id="passwordField" required>

                                            <span class="toggle-password" onclick="togglePasswordVisibility()">
                                                <i class="unicon uil-eye" id="toggleIcon"></i>
                                            </span>
                                        </div>
                                        
                                        <input class="form-control submit-btn" type="submit" value="Registrar-se" name="registre">
                                    </form>
                                </div>		
                            </div>


                        </div>

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
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('passwordField');
            const toggleIcon = document.getElementById('toggleIcon');
            if (passwordField.type === "password") {
                passwordField.type = "text";
                toggleIcon.classList.remove('uil-eye');
                toggleIcon.classList.add('uil-eye-slash');
            } else {
                passwordField.type = "password";
                toggleIcon.classList.remove('uil-eye-slash');
                toggleIcon.classList.add('uil-eye');
            }
        }
    </script>

</body>

</html>