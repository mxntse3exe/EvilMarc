<?php

    session_start();

    // Genera el token CSRF si no existeix
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

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
                <div>
                    <h2>El meu compte</h2>

        <?php
        if (isset($_GET['contrasenya']) && $_GET['contrasenya'] === 'ok') {
            echo '<div class="alert alert-success">Contrasenya actualitzada correctament.</div>';
        }
        // Comprova si l'usuari està autenticat
        if($_SESSION['valido'] == 1) {
            $id_usu = $_SESSION['id_usu'];

            // Obté totes les dades de l'usuari de la base de dades amb consulta preparada
            $stmt = $conexion->prepare("SELECT * FROM USUARIS WHERE id_usu = ?");
            $stmt->bind_param("i", $id_usu);
            $stmt->execute();
            $result = $stmt->get_result();

            // Si s'ha trobat l'usuari, assigna les dades a variables
            if($result && mysqli_num_rows($result) > 0) {
            $info_usuari_bd = mysqli_fetch_assoc($result);

            $usuari_mod = $info_usuari_bd['usuari'];
            $correu_mod = $info_usuari_bd['correu'];
            $nom_mod = $info_usuari_bd['nom'];
            $cognoms_mod = $info_usuari_bd['cognoms'];
            $direccio_mod = $info_usuari_bd['direccio'];

            $foto_perfil_mod = $info_usuari_bd['imatge'];
            }

            // Comprova si s'ha enviat el formulari per modificar les dades de l'usuari
            if (isset($_REQUEST['modificar'])) {

            // Obté i valida el valor del nou nom d'usuari del formulari
            $usuari_nou = $_REQUEST['usuari_input'];
            if (!preg_match('/^[a-zA-ZÀ-ÿ0-9 _-]{1,50}$/u', $usuari_nou)) {
                die("Nom d'usuari no vàlid.");
            }

            // Obté i valida el valor del nou correu electrònic
            $correu_nou = $_REQUEST['correu_input'];
            if (!filter_var($correu_nou, FILTER_VALIDATE_EMAIL) || strlen($correu_nou) > 100) {
                die("Correu electrònic no vàlid.");
            }

            // Obté i valida el valor del nou nom
            $nom_nou = $_REQUEST['nom_input'];
            if (!preg_match('/^[a-zA-ZÀ-ÿ \'-]{0,50}$/u', $nom_nou)) {
                die("Nom no vàlid.");
            }

            // Obté i valida el valor dels nous cognoms
            $cognoms_nou = $_REQUEST['cognoms_input'];
            if (!preg_match('/^[a-zA-ZÀ-ÿ \'-]{0,100}$/u', $cognoms_nou)) {
                die("Cognoms no vàlids.");
            }

            // Obté i valida la nova direcció
            $direccio_nou = $_REQUEST['direccio_input'];
            if (!preg_match('/^[a-zA-ZÀ-ÿ0-9ºª \'\-.,#]{0,150}$/u', $direccio_nou)) {
                die("Direcció no vàlida.");
            }
            if (strlen($direccio_nou) > 150) {
                die("Direcció massa llarga.");
            }

            // Comprova el token CSRF per protegir contra atacs CSRF
            if (!isset($_REQUEST['csrf_token']) || $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) {
                die("Token CSRF invàlid.");
            }

            // Comprova si el nom d'usuari ja existeix a la base de dades (excloent l'usuari actual)
            $stmt_check_usuari = $conexion->prepare("SELECT id_usu FROM USUARIS WHERE usuari = ? AND id_usu != ?");
            $stmt_check_usuari->bind_param("si", $usuari_nou, $id_usu);
            $stmt_check_usuari->execute();
            $stmt_check_usuari->store_result();
            if ($stmt_check_usuari->num_rows > 0) {
                // Si el nom d'usuari ja existeix, mostra un missatge d'error
                echo "Aquest nom d'usuari ja existeix!";
                $stmt_check_usuari->close();
            } else {
                $stmt_check_usuari->close();
                // Comprova si el correu electrònic ja existeix a la base de dades (excloent l'usuari actual)
                $stmt_check_correu = $conexion->prepare("SELECT id_usu FROM USUARIS WHERE correu = ? AND id_usu != ?");
                $stmt_check_correu->bind_param("si", $correu_nou, $id_usu);
                $stmt_check_correu->execute();
                $stmt_check_correu->store_result();
                if ($stmt_check_correu->num_rows > 0) {
                // Si el correu electrònic ja existeix, mostra un missatge d'error
                echo "Aquest correu electrònic ja existeix!";
                $stmt_check_correu->close();
                } else {
                $stmt_check_correu->close();
                // Actualitza les dades de l'usuari de manera segura amb una consulta preparada
                $stmt = $conexion->prepare("UPDATE USUARIS SET usuari = ?, correu = ?, nom = ?, cognoms = ?, direccio = ? WHERE id_usu = ?");
                $stmt->bind_param("sssssi", $usuari_nou, $correu_nou, $nom_nou, $cognoms_nou, $direccio_nou, $id_usu);

                if ($stmt->execute()) {
                    // Actualitza la sessió amb el nou nom d'usuari i redirigeix a la mateixa pàgina
                    $_SESSION['usuari'] = $usuari_nou;
                    header("Location: compte");
                    exit();
                } else {
                    // Mostra un error si la consulta falla
                    echo "Error en actualitzar les dades.";
                }
                $stmt->close();
                }
            }
            }

            // Comprova si s'ha enviat el formulari per modificar la foto de perfil
            if(isset($_POST['foto'])) {
                // Comprova el token CSRF per protegir contra atacs CSRF
                if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                    $missatge_error = "Token CSRF invàlid.";
                } else {
                    // Comprova si s'ha pujat un fitxer d'imatge
                    if (is_uploaded_file ($_FILES['imatge']['tmp_name'])) {

                        // Comprova el tipus MIME de la imatge
                        $mime = mime_content_type($_FILES['imatge']['tmp_name']);
                        $allowed = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];

                        if (!in_array($mime, $allowed)) {
                            $missatge_error = "Només es permeten imatges PNG, JPG, GIF o WEBP.";
                        } elseif ($_FILES['imatge']['size'] > 2*1024*1024) { // 2MB
                            $missatge_error = "La imatge és massa gran (màxim 2MB).";
                        } else {
                            // Defineix el nom i la ruta de la nova foto de perfil
                            $nombreFichero = "foto_$id_usu.png";
                            $rutaDestino = "images/perfil/" . $nombreFichero;

                            // Si ja existeix una foto anterior, l'elimina
                            if (file_exists($rutaDestino)) {
                                unlink($rutaDestino);
                            }

                            // Mou la nova imatge a la carpeta de perfil
                            move_uploaded_file ($_FILES['imatge']['tmp_name'], $rutaDestino);

                            // Actualitza la ruta de la imatge de perfil de manera segura amb consulta preparada
                            $stmt_update_img = $conexion->prepare("UPDATE USUARIS SET imatge = ? WHERE id_usu = ?");
                            $stmt_update_img->bind_param("si", $rutaDestino, $id_usu);
                            $stmt_update_img->execute();
                            $stmt_update_img->close();

                            // Redirigeix per evitar re-enviament del formulari i refrescar la imatge
                            header("Location: compte?rand=" . uniqid());
                            exit();
                        }
                    }
                }
            }


            // Comprova si s'ha enviat el formulari per modificar la contrasenya
            if(isset($_REQUEST['modificarpass'])) {
                // Comprova el token CSRF per protegir contra atacs CSRF
                if (!isset($_REQUEST['csrf_token']) || $_REQUEST['csrf_token'] !== $_SESSION['csrf_token']) {
                die("Token CSRF invàlid.");
                }

                // Obté la contrasenya actual i la nova contrasenya del formulari
                $pass_actual = $_REQUEST['contrasenya_actual'];
                $pass_nova = $_REQUEST['nova_contrasenya'];
                $pass_comfirmar = $_REQUEST['confirmar_contrasenya'];

                // Validació de la nova contrasenya
                // if (strlen($pass_nova) < 8 || strlen($pass_nova) > 50) {
                //     die("La nova contrasenya ha de tenir entre 8 i 50 caràcters.");
                // }
                // if (!preg_match('/^[a-zA-ZÀ-ÿ0-9!@#$%^&*()_+={}\[\]:;"\'<>,.?\/\\|-]{8,50}$/u', $pass_nova)) {
                //     die("La nova contrasenya no és vàlida.");
                // }
                if ((strlen($pass_nova) < 8 || strlen($pass_nova) > 50) || (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,50}$/u', $pass_nova))) {
                    echo "<p class='adverts'>La contrasenya ha de contenir almenys una majúscula, una minúscula, un número i un caràcter especial. Ha de tenir entre 8 i 50 caràcters.</p>";
                }
                else {
                    if ($pass_nova !== $pass_comfirmar) {
                        echo "Les contrasenyes noves no coincideixen.";
                        
                    }
                    else {
                        
                        // Consulta la contrasenya actual de la base de dades (amb consulta preparada)
                        $stmt = $conexion->prepare("SELECT contrasenya FROM USUARIS WHERE id_usu = ?");
                        $stmt->bind_param("i", $id_usu);
                        $stmt->execute();
                        $stmt->bind_result($contrasenya_bd_hash);
                        if ($stmt->fetch()) {
                            $stmt->close();
                            $contrasenya_correcta = false;
        
                            if ($contrasenya_bd_hash === hash('sha256', $pass_actual, false)) {
                                $contrasenya_correcta = true;
                            }
        
                            if ($contrasenya_correcta) {
                                // Genera el hash de la nova contrasenya
                                $pass_nova_hash = hash('sha256', $pass_nova, false);
        
        
                                // Actualitza la contrasenya nova (consulta preparada)
                                $stmt_update = $conexion->prepare("UPDATE USUARIS SET contrasenya = ? WHERE id_usu = ?");
                                $stmt_update->bind_param("si", $pass_nova_hash, $id_usu);
                                if ($stmt_update->execute()) {
                                    echo "Contrasenya actualitzada correctament.";
                                } else {
                                    echo "Error en actualitzar la contrasenya.";
                                }
                                $stmt_update->close();
                            } else {
                                echo "La contrasenya actual no és correcta.";
                            }
                        } else {
                            echo "Usuari no trobat.";
                        }
                    }
                }

            }


    








            // // Obté i neteja la contrasenya actual del formulari
            // $pass_actual = $_REQUEST['contrasenya_actual'];
            // $pass_actual = str_replace("=","",$pass_actual);
            // $pass_actual = str_replace("'","\'",$pass_actual);
            // $pass_actual = str_replace('"','\"',$pass_actual);

            // // Obté i neteja la nova contrasenya del formulari
            // $pass_nova = $_REQUEST['nova_contrasenya'];
            // $pass_nova = str_replace("=","",$pass_nova);
            // $pass_nova = str_replace("'","\'",$pass_nova);
            // $pass_nova = str_replace('"','\"',$pass_nova);

            // // Calcula el hash SHA-256 de les contrasenyes
            // $pass_actual_hash = hash('sha256', $pass_actual, false);
            // // Genera el hash de la nova contrasenya
            // if (strlen($pass_nova) < 8 || strlen($pass_nova) > 50) {
            //     die("La nova contrasenya ha de tenir entre 8 i 50 caràcters.");
            // }
            // if (!preg_match('/^[a-zA-ZÀ-ÿ0-9!@#$%^&*()_+={}\[\]:;"\'<>,.?\/\\|-]{8,50}$/u', $pass_nova)) {
            //     die("La nova contrasenya no és vàlida.");
            // }
            // $pass_nova_hash = password_hash($pass_nova, PASSWORD_DEFAULT);

            // // Consulta la contrasenya actual de la base de dades
            // $sql_obtenir_pass = "select contrasenya from USUARIS where id_usu = '$id_usu'";

            // $contrasenya_bd = mysqli_query($conexion, $sql_obtenir_pass);

            // // Comprova si s'ha trobat l'usuari i valida la contrasenya actual
            // if ($fila = mysqli_fetch_assoc($contrasenya_bd)) {
            //     $contrasenya_bd_hash = $fila['contrasenya'];

            //     if ($contrasenya_bd_hash === $pass_actual_hash) {
            //     // Si la contrasenya actual és correcta, actualitza la contrasenya nova
            //     $sql_actualitzar = "update USUARIS set contrasenya = '$pass_nova_hash' WHERE id_usu = '$id_usu'";
            //     if (mysqli_query($conexion, $sql_actualitzar)) {
            //         echo "Contrasenya actualitzada correctament.";
            //     } else {
            //         echo "Error en actualitzar la contrasenya.";
            //     }
            //     } else {
            //     // Si la contrasenya actual no coincideix, mostra un error
            //     echo "La contrasenya actual no és correcta.";
            //     }
            // } else {
            //     // Si no es troba l'usuari, mostra un error
            //     echo "Usuari no trobat.";
            // }

            // }
            
        ?>

                    <div class="contingut_panell_dades">

                        <div class="contact-form marge">
                            <div class="form_modificar columna">


                                <form action="compte" method="post" enctype="multipart/form-data" id="formModificarFoto">

                                    <div><img class="foto_perfil" src="<?php echo $foto_perfil_mod. '?rand=' . uniqid(); ?>" alt="Foto de perfil"></div>

                                    <input type="file" id="files_up" name="imatge" style="display: none;" onchange="submitForm()">

                                    <input type="hidden" name="foto" value="1">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                                    <button type="button" class="form-control submit-btn" id="btnModificarFoto" name="foto">Modificar foto</button>

                                    <?php if (!empty($missatge_error)): ?>
                                        <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                                            <?php echo htmlspecialchars($missatge_error, ENT_QUOTES, 'UTF-8'); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tancar"></button>
                                        </div>
                                    <?php endif; ?>

                                </form>


                                <form action="compte" method="post" enctype="multipart/form-data" id="formModificarPass">

                                    <p>Canviar contrasenya</p>

                                    <p class="label_mod">Contrasenya actual</p>
                                    <div class="password-container">
                                        <input class="form-control mod_dades_inp contra" type="password" name="contrasenya_actual" id="passwordFieldActual" required>
                                        <span class="toggle-password" onclick="togglePasswordVisibility('passwordFieldActual', 'toggleIconActual')">
                                            <i class="unicon uil-eye" id="toggleIconActual"></i>
                                        </span>
                                    </div>

                                    <p class="label_mod">Nova contrasenya</p>
                                    <div class="password-container">
                                        <input class="form-control mod_dades_inp contra" type="password" name="nova_contrasenya" id="passwordFieldNova" required>
                                        <span class="toggle-password" onclick="togglePasswordVisibility('passwordFieldNova', 'toggleIconNova')">
                                            <i class="unicon uil-eye" id="toggleIconNova"></i>
                                        </span>
                                    </div>

                                    <p class="label_mod">Confirma la nova contrasenya</p>
                                    <div class="password-container">
                                        <input class="form-control mod_dades_inp contra" type="password" name="confirmar_contrasenya" id="passwordFieldConfirma" required>
                                        <span class="toggle-password" onclick="togglePasswordVisibility('passwordFieldConfirma', 'toggleIconConfirma')">
                                            <i class="unicon uil-eye" id="toggleIconConfirma"></i>
                                        </span>
                                    </div>

                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                                    <input class="form-control submit-btn" type="submit" value="Canviar contrasenya" name="modificarpass">

                                </form>


                            </div>

                        </div>


                        <div class="contact-form">
                            <div class="form_modificar">
                       


                                <form action="compte" method="post" enctype="multipart/form-data" class="form_dades_mod">
                                    <p class="label_mod">Nom d'usuari</p>
                                    <!-- Escapem el valor per evitar XSS -->
                                    <input class="form-control mod_dades_inp" type="text" name="usuari_input" value="<?php echo htmlspecialchars($usuari_mod, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <br>
                                    <p class="label_mod">Correu electrònic</p>
                                    <!-- Escapem el valor per evitar XSS -->
                                    <input class="form-control mod_dades_inp" type="text" name="correu_input" value="<?php echo htmlspecialchars($correu_mod, ENT_QUOTES, 'UTF-8'); ?>" required>
                                    <br> 
                                    <p class="label_mod">Nom</p>
                                    <!-- Escapem el valor per evitar XSS -->
                                    <input class="form-control mod_dades_inp" type="text" name="nom_input" value="<?php echo htmlspecialchars($nom_mod, ENT_QUOTES, 'UTF-8'); ?>">
                                    <br>
                                    <p class="label_mod">Cognoms</p>
                                    <!-- Escapem el valor per evitar XSS -->
                                    <input class="form-control mod_dades_inp" type="text" name="cognoms_input" value="<?php echo htmlspecialchars($cognoms_mod, ENT_QUOTES, 'UTF-8'); ?>">
                                    <br>
                                    <p class="label_mod">Direcció</p>
                                    <!-- Escapem el valor per evitar XSS -->
                                    <input class="form-control mod_dades_inp" type="text" name="direccio_input" value="<?php echo htmlspecialchars($direccio_mod, ENT_QUOTES, 'UTF-8'); ?>">

                                    <!-- Afegim un token CSRF per protegir contra atacs CSRF -->
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <br>
                                    <input class="form-control submit-btn" type="submit" value="Modificar dades" name="modificar">
                                </form>

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

        function togglePasswordVisibility(passwordFieldId, toggleIconId) {
            const passwordField = document.getElementById(passwordFieldId);
            const toggleIcon = document.getElementById(toggleIconId);

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