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
        if($_SESSION['valido'] == 1) {
            $id_usu = $_SESSION['id_usu'];

            $sql = "select * from USUARIS where id_usu = $id_usu";
            
            $result = mysqli_query($conexion, $sql);
    
            if($result && mysqli_num_rows($result) > 0) {
                $info_usuari_bd = mysqli_fetch_assoc($result);

                $usuari_mod = $info_usuari_bd['usuari'];
                $correu_mod = $info_usuari_bd['correu'];
                $nom_mod = $info_usuari_bd['nom'];
                $cognoms_mod = $info_usuari_bd['cognoms'];
                $direccio_mod = $info_usuari_bd['direccio']; 

                $foto_perfil_mod = $info_usuari_bd['imatge'];
            }

            if(isset($_REQUEST['modificar'])) {
                
                $usuari_nou = $_REQUEST['usuari_input'];
                $usuari_nou = str_replace("=","",$usuari_nou);
                $usuari_nou = str_replace("'","\'",$usuari_nou);
                $usuari_nou = str_replace('"','\"',$usuari_nou);
                
                $correu_nou = $_REQUEST['correu_input'];
                $correu_nou = str_replace("=","",$correu_nou);
                $correu_nou = str_replace("'","\'",$correu_nou);
                $correu_nou = str_replace('"','\"',$correu_nou);
                
                $nom_nou = $_REQUEST['nom_input'];
                $nom_nou = str_replace("=","",$nom_nou);
                $nom_nou = str_replace("'","\'",$nom_nou);
                $nom_nou = str_replace('"','\"',$nom_nou);

                $cognoms_nou = $_REQUEST['cognoms_input'];
                $cognoms_nou = str_replace("=","",$cognoms_nou);
                $cognoms_nou = str_replace("'","\'",$cognoms_nou);
                $cognoms_nou = str_replace('"','\"',$cognoms_nou);

                $direccio_nou = $_REQUEST['direccio_input'];
                $direccio_nou = str_replace("=","",$direccio_nou);
                $direccio_nou = str_replace("'","\'",$direccio_nou);
                $direccio_nou = str_replace('"','\"',$direccio_nou);
										

                $sql = "update USUARIS set usuari = '$usuari_nou', correu = '$correu_nou', nom = '$nom_nou', cognoms = '$cognoms_nou', direccio = '$direccio_nou' where id_usu = '$id_usu'";


                $sql_check_usuari = "select * from USUARIS where usuari = '$usuari_nou' and id_usu != '$id_usu'";
                $sql_check_correu = "select * from USUARIS where correu = '$correu_nou' and id_usu != '$id_usu'";

                $usuari_check = mysqli_query($conexion, $sql_check_usuari);
                $correu_check = mysqli_query($conexion, $sql_check_correu);

                if ($usuari_check && mysqli_num_rows($usuari_check) > 0) {
                    echo "Aquest nom d'usuari ja existeix!";
                }
                else {
                    if ($correu_check && mysqli_num_rows($correu_check) > 0) {
                        echo "Aquest correu electrònic ja existeix!";
                    }
                    else {
                        if (mysqli_query($conexion,$sql)) {
                            $_SESSION['usuari'] = $usuari_nou;
                            header("Location: compte");
                        }
                        else {
                            echo "Usuari no modificat"."<br><br>";
                        }
                    }
                }

            }

            if(isset($_POST['foto'])) {
                if (is_uploaded_file ($_FILES['imatge']['tmp_name'])) {

                    $nombreFichero = "foto_$id_usu.png";

                    $rutaDestino = "images/perfil/" . $nombreFichero;

                    if (file_exists($rutaDestino)) {
                        unlink($rutaDestino);
                    }

                    move_uploaded_file ($_FILES['imatge']['tmp_name'], $rutaDestino);
                    
                    $sqlimg = "update USUARIS set imatge = '$rutaDestino' where id_usu = '$id_usu'";
                    mysqli_query($conexion,$sqlimg);
                    header("Location: compte?rand=".uniqid());

                }	
            }


            if(isset($_REQUEST['modificarpass'])) {
                
                $pass_actual = $_REQUEST['contrasenya_actual'];
                $pass_actual = str_replace("=","",$pass_actual);
                $pass_actual = str_replace("'","\'",$pass_actual);
                $pass_actual = str_replace('"','\"',$pass_actual);

                $pass_nova = $_REQUEST['nova_contrasenya'];
                $pass_nova = str_replace("=","",$pass_nova);
                $pass_nova = str_replace("'","\'",$pass_nova);
                $pass_nova = str_replace('"','\"',$pass_nova);
                
                $pass_actual_hash = hash('sha256', $pass_actual, false);
				$pass_nova_hash = hash('sha256', $pass_nova, false);					

                $sql_obtenir_pass = "select contrasenya from USUARIS where id_usu = '$id_usu'";

                $contrasenya_bd = mysqli_query($conexion, $sql_obtenir_pass);

                if ($fila = mysqli_fetch_assoc($contrasenya_bd)) {
                    $contrasenya_bd_hash = $fila['contrasenya'];
                
                    if ($contrasenya_bd_hash === $pass_actual_hash) {
                        // La contrasenya introduïda és correcta
                        // Aquí s'actualitza la contrasenya nova
                        $sql_actualitzar = "update USUARIS set contrasenya = '$pass_nova_hash' WHERE id_usu = '$id_usu'";
                        if (mysqli_query($conexion, $sql_actualitzar)) {
                            echo "Contrasenya actualitzada correctament.";
                        } else {
                            echo "Error en actualitzar la contrasenya.";
                        }
                    } else {
                        echo "La contrasenya actual no és correcta.";
                    }
                } else {
                    echo "Usuari no trobat.";
                }

            }


        ?>

                    <div class="contingut_panell_dades">

                        <div class="contact-form marge">
                            <div class="form_modificar columna">


                                <form action="compte" method="post" enctype="multipart/form-data" id="formModificarFoto">
                                    
                                    <div><img class="foto_perfil" src="<?php echo $foto_perfil_mod. '?rand=' . uniqid(); ?>" alt="Foto de perfil"></div>


                                    <input type="file" id="files_up" name="imatge" style="display: none;" onchange="submitForm()">
                                    
                                    <input type="hidden" name="foto" value="1">


                                    <button type="button" class="form-control submit-btn" id="btnModificarFoto" name="foto">Modificar foto</button>

                                </form>


                                <form action="compte" method="post" enctype="multipart/form-data" id="formModificarPass">
                                    <p>Canviar la contrasenya</p>
                                    <p class="label_mod">Contrasenya actual</p>
                                    <input class="form-control mod_dades_inp contra" type="password" id="contrasenya_actual" name="contrasenya_actual" required>
                                    
                                    <p class="label_mod">Nova contrasenya</p>
                                    <input class="form-control mod_dades_inp contra" type="password" id="nova_contrasenya" name="nova_contrasenya" required>

                                    <input class="form-control submit-btn" type="submit" value="Canviar contrasenya" name="modificarpass">

                                </form>


                            </div>

                        </div>
                        
                        
                        <div class="contact-form">	
                            <div class="form_modificar">

                                <form action="compte" method="post" enctype="multipart/form-data" class="form_dades_mod">
                                    <p class="label_mod">Nom d'usuari</p>
                                    <input class="form-control mod_dades_inp" type="text" name="usuari_input" value="<?php echo $usuari_mod; ?>" required>

                                    <p class="label_mod">Correu electrònic</p>
                                    <input class="form-control mod_dades_inp" type="text" name="correu_input" value="<?php echo $correu_mod; ?>" required>

                                    <p class="label_mod">Nom</p>
                                    <input class="form-control mod_dades_inp" type="text" name="nom_input" value="<?php echo $nom_mod; ?>">

                                    <p class="label_mod">Cognoms</p>
                                    <input class="form-control mod_dades_inp" type="text" name="cognoms_input" value="<?php echo $cognoms_mod; ?>">

                                    <p class="label_mod">Direcció</p>
                                    <input class="form-control mod_dades_inp" type="text" name="direccio_input" value="<?php echo $direccio_mod; ?>">
                                    
                                    
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

    </script>

</body>

</html>