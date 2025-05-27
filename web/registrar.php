<?php
session_start();

// Genera el token CSRF si no existeix
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if(isset($_REQUEST['registre'])) {
    // Comprova el token CSRF
    if (!isset($_REQUEST['csrf_token']) || $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Token CSRF invàlid.");
    }

    $servidor = "localhost";
    $usuario = "web";
    $password = "T5Dk!xq";
    $db = "evilmarc";

    $conexion = mysqli_connect($servidor,$usuario,$password,$db);

    if (!$conexion) {
        error_log("Error de connexió a la BD: " . mysqli_connect_error());
        die("Error al connectar amb la base de dades.");
    }

    // Validació i neteja d'entrada
    $correu = filter_var($_REQUEST['correu'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($correu, FILTER_VALIDATE_EMAIL)) {
        die("L'adreça de correu electrònic no és vàlida.");
    }

    $usuari = preg_replace('/[^a-zA-Z0-9_]/', '', $_REQUEST['usuari']);
    if (strlen($usuari) < 4 || strlen($usuari) > 20) {
        die("El nom d'usuari ha de tenir entre 4 i 20 caràcters alfanumèrics.");
    }

    $pass = $_REQUEST['contrasenya'];

    // Validació de contrasenya
    if ((strlen($pass) < 8 || strlen($pass) > 50) || (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,50}$/u', $pass))) {
        $error_contrasenya = "<p class='adverts'>La contrasenya ha de contenir almenys una majúscula, una minúscula, un número i un caràcter especial. Ha de tenir entre 8 i 50 caràcters.</p>";
    } else {
        // Comprovem si l'usuari o correu ja existeixen amb prepared statements
        $stmt = $conexion->prepare("SELECT id_usu FROM USUARIS WHERE correu = ? OR usuari = ?");
        $stmt->bind_param("ss", $correu, $usuari);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error_existent = "Ja existeix un compte amb aquest correu o usuari.";
        } else {
            // Hash segur de la contrasenya
            $pass_hash = hash('sha256', $pass, false);
            
            // Inserció amb prepared statement
            $stmt_insert = $conexion->prepare("INSERT INTO USUARIS (usuari, correu, contrasenya) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $usuari, $correu, $pass_hash);
            
            if ($stmt_insert->execute()) {
                $missatge_exit = "Usuari creat correctament, esperi que l'administrador verifiqui el seu compte.";
            } else {
                error_log("Error en registrar usuari: " . $stmt_insert->error);
                $error_registre = "No hem pogut crear el seu usuari en aquests moments.";
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
    $conexion->close();
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
                                        // Mostrar missatges d'error o èxit escapant la sortida
                                        if (isset($error_contrasenya)) {
                                            echo "<p class='adverts'>".htmlspecialchars($error_contrasenya, ENT_QUOTES, 'UTF-8')."</p>";
                                        }
                                        if (isset($error_existent)) {
                                            echo "<p class='adverts'>".htmlspecialchars($error_existent, ENT_QUOTES, 'UTF-8')."</p>";
                                        }
                                        if (isset($error_registre)) {
                                            echo "<p class='adverts'>".htmlspecialchars($error_registre, ENT_QUOTES, 'UTF-8')."</p>";
                                        }
                                        if (isset($missatge_exit)) {
                                            echo "<p class='adverts'>".htmlspecialchars($missatge_exit, ENT_QUOTES, 'UTF-8')."</p>";
                                        }
                                        ?>

                                        <input class="form-control" type="email" name="correu" placeholder="correu electrònic" 
                                            value="<?php echo isset($correu) ? htmlspecialchars($correu, ENT_QUOTES, 'UTF-8') : ''; ?>" required>

                                        <input class="form-control" type="text" name="usuari" placeholder="nom d'usuari" 
                                            value="<?php echo isset($usuari) ? htmlspecialchars($usuari, ENT_QUOTES, 'UTF-8') : ''; ?>" required>

                                        <div class="password-container">
                                            <input class="form-control" type="password" name="contrasenya" placeholder="contrasenya" id="passwordField" required>
                                            <span class="toggle-password" onclick="togglePasswordVisibility()">
                                                <i class="unicon uil-eye" id="toggleIcon"></i>
                                            </span>
                                        </div>
                                        
                                        <!-- Token CSRF -->
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                        
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