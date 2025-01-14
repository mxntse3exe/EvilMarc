<?php 
	session_start();

	if (isset($_POST['iniciar'])) {
		$servidor = "localhost";
		$usuario = "web";
		$password = "T5Dk!xq";
		$db = "evilmarc";

		$conexion = mysqli_connect($servidor,$usuario,$password,$db);

		if (!$conexion) die ("Error al connectar amb la base de dades.");

		$mail = $_REQUEST['mail'];
		$mail = str_replace("=","",$mail);
		$mail = str_replace(" ","",$mail);
		$mail = str_replace("'","",$mail);

		$pass = $_REQUEST['pass'];
		$pass = hash('sha256', $pass, false);

		$sql = "select * from USUARIS where correu='".$mail."' and contrasenya='".$pass."' and validat = 1";
		
		$filas = mysqli_query($conexion,$sql);
		$nfilas = mysqli_num_rows($filas);
        
        $sql_novalidat = "select * from USUARIS where correu='".$mail."' and contrasenya='".$pass."' and validat = 0";

        $files_usuarinovalidat = mysqli_query($conexion,$sql_novalidat);
        $usuari_validat = mysqli_num_rows($files_usuarinovalidat);

        if ($nfilas == 0) {
            $_SESSION['valido'] = 0;
        }

		else {
			$_SESSION['valido'] = 1;

            while($fila = $filas->fetch_assoc()) {
                $_SESSION['usuari'] = $fila["usuari"];
            }

			header("Location: panell_usuari");
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
                    <h2>Iniciar sessió</h2>
                        <div class="contact_contents">
                            
                            <div class="contact-form">	
                                <div class="formulari_reg_log">
                                    <form method="post" action="inici">

                                        <?php
                                        if (isset($_POST['iniciar'])) {

                                            if ($usuari_validat == 1) {
                                                echo "<p class='adverts'>El seu usuari està pendent de validació.</p>";
                                            }
                                            else if ($nfilas == 0) {
                                                echo "<p class='adverts'>Credencials incorrectes.</p>";
                                            }
                                        }
                                        ?>

                                        <input class="form-control" type="email" name="mail" placeholder="correu electrònic">

                                        <div class="password-container">

                                            <input class="form-control" type="password" name="pass" placeholder="contrasenya" id="passwordField">

                                            <span class="toggle-password" onclick="togglePasswordVisibility()">
                                                <i class="unicon uil-eye" id="toggleIcon"></i>
                                            </span>
                                        </div>
                                        
                                        <input class="form-control submit-btn" type="submit" value="Iniciar" name="iniciar">
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