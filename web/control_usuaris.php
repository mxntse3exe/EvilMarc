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

        shell_exec('mkdir /var/www/html/fitxers/fitxers_usuaris/fitxers_'.$_REQUEST["id"]);
    }

    if(isset($_REQUEST['eliminar'])) {
        $sql_eliminar = 'delete from USUARIS where id_usu ='.$_REQUEST["id"].';';

        mysqli_query($conexion,$sql_eliminar);
    }

    if(isset($_REQUEST['fer_admin'])) {
        $sql_admin = 'update USUARIS set admin = 1 where id_usu ='.$_REQUEST["id"].';';

        mysqli_query($conexion,$sql_admin);
    }

    if(isset($_REQUEST['treure_admin'])) {
        $sql_admin = 'update USUARIS set admin = 0 where id_usu ='.$_REQUEST["id"].';';

        mysqli_query($conexion,$sql_admin);
    }

    if(isset($_REQUEST['crear_dep'])) {
        $nom_dep = $_REQUEST['nom_dep'];
        $nom_dep = str_replace("=","",$nom_dep);
        $nom_dep = str_replace("'","",$nom_dep);

        $crear_dep = "insert into DEPARTAMENTS (nom) values ('$nom_dep')";


        $comprovar_existencia_dep = "select * from DEPARTAMENTS where nom = '$nom_dep'";

        $files_departaments = mysqli_query($conexion,$comprovar_existencia_dep);
        if (mysqli_num_rows($files_departaments) == 0) {
            mysqli_query($conexion,$crear_dep);
        }
    }

    if(isset($_REQUEST['eliminar_dep'])) {
        $dep = $_REQUEST['dep_eliminar'];
        $dep = str_replace("=","",$dep);
        $dep = str_replace("'","",$dep);

        $eliminar_dep = "delete from DEPARTAMENTS where id_dep = '$dep'";

        $reset_dep = "update USUARIS set id_dep = NULL where id_dep = '$dep'";
        mysqli_query($conexion, $reset_dep);


        mysqli_query($conexion,$eliminar_dep);
    }

    if (isset($_POST['departament']) && isset($_POST['id'])) {
        $id_usuari = $_POST['id'];
        $id_departament = $_POST['departament'];

        $id_usuari = str_replace("=","",$id_usuari);
        $id_usuari = str_replace("'","",$id_usuari);

        $id_departament = str_replace("=","",$id_departament);
        $id_departament = str_replace("'","",$id_departament);

        $update_dep_usu = 'update USUARIS set id_dep = '.$id_departament.' where id_usu = '.$id_usuari.';';
        mysqli_query($conexion,$update_dep_usu);
    }

    if(isset($_REQUEST['crear_usu'])) {

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
    
    
        $sql = "insert into USUARIS(usuari,correu,contrasenya,validat) values('".$usuari."','".$correu."','".$pass."','1')";
    

        $buscar_id_usu_creat = "select id_usu from USUARIS where usuari = '$usuari'";
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
            $usuari = $_SESSION['usuari'];

        ?>

            <div class="row seccio_panell">
                <div style="width: 90%;">
                    <h2>Panell de control d'usuaris</h2>

                    <div class="botons_administracio">

                        <div class="crear_usu_boto">
                            <span class="btn" onclick="abrirPopup_crearusu()">Crear usuari</span>
                            
                            <div class="overlay" id="overlay_crearusu" onclick="cerrarPopup_crearusu()"></div>
                            <div class="popup" id="popup_crearusu">
                                <h4>Crear usuari</h4>
                                <div class="contact-form">
                                    <form action="control_usuaris" method="post">


                                    
        
                                        <input class="form-control" type="email" name="correu" placeholder="correu electrònic" required>
                                        <input class="form-control" type="text" name="usuari" placeholder="nom d'usuari" required>

                                        <div class="password-container">

                                            <input class="form-control" type="password" name="contrasenya" placeholder="contrasenya" id="passwordField" required>

                                            <span class="toggle-password" onclick="togglePasswordVisibility()">
                                                <i class="unicon uil-eye" id="toggleIcon"></i>
                                            </span>

                                        </div>

                                        <input class="form-control submit-btn" type="submit" value="Crear usuari" name="crear_usu">
    
                                    </form>
                                </div>
                                <span class="close-icon" onclick="cerrarPopup_crearusu()">×</span>
                            </div>
                        </div>



                        <div class="gestionar_deps">
                            <span class="btn" onclick="abrirPopup_veuredep()">Veure departaments</span>
                            <div class="overlay" id="overlay_veuredep" onclick="cerrarPopup_veuredep()"></div>
                            <div class="popup_dep" id="popup_veuredep">
                                <h4>Veure departaments</h4>

                                <?php
                                $sql_departaments = "select * from DEPARTAMENTS";
                                $departaments = mysqli_query($conexion,$sql_departaments);

                                while($departament = $departaments->fetch_assoc()) {
                                    echo "<h6>".$departament['nom']."</h6>";

                                    $sql_departaments_usuaris = "select * from USUARIS where id_dep = ".$departament['id_dep'];
                                    $departaments_usuaris = mysqli_query($conexion,$sql_departaments_usuaris);
                                    
                                    if (mysqli_num_rows($departaments_usuaris) == 0) {
                                        echo "<p>No hi ha usuaris en aquest departament.</p>";
                                    }
                                    else {

                                        while($departament_usuari = $departaments_usuaris->fetch_assoc()) {
                                            echo "<div class='info_usuari_dep'>";
    
                                            echo "<img src='".$departament_usuari['imatge']."' class='foto_usuari_dep'>";
                                            echo "<p>".$departament_usuari['usuari']." - ".$departament_usuari['correu']." - ".$departament_usuari['nom']." ".$departament_usuari['cognoms']."</p>";
                                            echo "</div>";
                                        }
                                    }
                                }
                                
                                ?>
                                <span class="close-icon" onclick="cerrarPopup_veuredep()">×</span>
                            </div>





                            <span class="btn" onclick="abrirPopup_creardep()">Crear departament</span>
                            <div class="overlay" id="overlay_creardep" onclick="cerrarPopup_creardep()"></div>
                            <div class="popup" id="popup_creardep">
                                <h4>Crear departament</h4>
                                <div class="contact-form">
                                    <form action="control_usuaris" method="post">
        
                                        <input class="form-control" type="text" name="nom_dep" placeholder="Nom departament" required>
                                        <input class="form-control submit-btn" type="submit" value="Crear departament" name="crear_dep">
    
                                    </form>
                                </div>
                                <span class="close-icon" onclick="cerrarPopup_creardep()">×</span>
                            </div>

                            <span class="btn" onclick="abrirPopup_eliminardep()">Eliminar departament</span>
                            <div class="overlay" id="overlay_eliminardep" onclick="cerrarPopup_eliminardep()"></div>
                            <div class="popup" id="popup_eliminardep">
                                <h4>Eliminar departament</h4>
                                <div class="contact-form">
                                    <form action="control_usuaris" method="post">
        
                                        <select class="form-control" type="select" name="dep_eliminar" required>
                                            <?php
                                            $sql_departaments = "select * from DEPARTAMENTS";
                                            $departaments = mysqli_query($conexion,$sql_departaments);
                                            while($departament = $departaments->fetch_assoc()) {
                                                echo "<option value='".$departament['id_dep']."'>".$departament['nom']."</option>";
                                            }
                                            ?>
                                        </select>
                                        <input class="form-control submit-btn" type="submit" value="Eliminar departament" name="eliminar_dep">
    
                                    </form>
                                </div>
                                <span class="close-icon" onclick="cerrarPopup_eliminardep()">×</span>
                            </div>
                        </div>

                    </div>

                    <?php
                    if(isset($_REQUEST['crear_usu'])) {
                        $files_correu = mysqli_query($conexion,$sql_comprovar_correu);
                        $num_files_correu = mysqli_num_rows($files_correu);

                        $files_usuari = mysqli_query($conexion,$sql_comprovar_usuari);
                        $num_files_usuari = mysqli_num_rows($files_usuari);

                        if ($num_files_correu == 0) {
                            if ($num_files_usuari == 0) {
                                if (mysqli_query($conexion,$sql)) {
                                    echo "<p class='msg_comprovacio'>Usuari creat correctament.</p>";

                                    $ids_usuari = mysqli_query($conexion,$buscar_id_usu_creat);
                                    while($id_usuari_creat = $ids_usuari->fetch_assoc()) {
                                    
                                        shell_exec('mkdir /var/www/html/fitxers/fitxers_usuaris/fitxers_'.$id_usuari_creat["id_usu"]);

                                    }
                                }
                                else {
                                    echo "<p class='msg_comprovacio'>No hem pogut crear l'usuari en aquests moments.</p>";
                                }
                            }
                            else {
                                echo "<p class='msg_comprovacio'>Ja existeix un compte amb aquest correu o usuari.</p>";
                            }
                        }
                        else {
                            echo "<p class='msg_comprovacio'>Ja existeix un compte amb aquest correu o usuari.</p>";
                        }
                    }
                    ?>


                    <div class="contingut_panell" style="width: 90%;">
                    
                    <?php
                    $consulta_usuaris = "select * from USUARIS;";

                    $usuaris_bd = mysqli_query($conexion,$consulta_usuaris);

                    while($usuari_bd = $usuaris_bd->fetch_assoc()) {
                    ?>



                        <div class="boto_control_usu">
                            <div class="info_usu">

                                <img src="<?php echo $usuari_bd['imatge']; ?>" class="boto_control_usu_img">
                                <div class="dades_usu">
                                    <span><?php echo $usuari_bd['usuari']; if ($usuari_bd["admin"] == 1) echo "  (administrador)";?></span>
                                    <p class="text_dades"><?php echo $usuari_bd['nom']; ?> <?php echo $usuari_bd['cognoms']; ?></p>
                                    <p class="text_dades"><?php echo $usuari_bd['correu']; ?></p>
                                </div>
                            </div>
                            <div class="botons_usu">


                                <?php
                                if ($usuari_bd["validat"] == 0) {
                                ?>

                                <!-- botó per validar els usuaris -->
                                <form action="control_usuaris" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $usuari_bd['id_usu']; ?>">

                                    <input class="boto_validar" type="submit" value="Validar" name="validar">
                                </form>

                                <?php
                                }
                                else {
                                ?>

                                    <?php
                                    if ($usuari_bd["admin"] == 0 && $usuari_bd["usuari"] != $usuari) {
                                    ?>

                                    <form action="control_usuaris" method="POST">
                                        <input type="hidden" name="id" value="<?php echo $usuari_bd['id_usu']; ?>">
                                        <input class="boto_fer_admin" type="submit" value="&#10003; Admin" name="fer_admin">
                                    </form>

                                    <?php
                                    }
                                    else if ($usuari_bd["admin"] == 1 && $usuari_bd["usuari"] != $usuari) {
                                    ?>

                                    <form action="control_usuaris" method="POST">
                                        <input type="hidden" name="id" value="<?php echo $usuari_bd['id_usu']; ?>">
                                        <input class="boto_treure_admin" type="submit" value="&#10007; Admin" name="treure_admin">
                                    </form>

                                    <?php
                                    }
                                    ?>


                                    <!-- botó per canviar el departament dels usuaris -->
                                    <form action="control_usuaris" method="POST">
                                        <input type="hidden" name="id" value="<?php echo $usuari_bd['id_usu']; ?>">

                                        <select type="select" name="departament" onchange="this.form.submit()" class="departament_usuari_selector">
                                            <?php
                                            $sql_departaments = "select * from DEPARTAMENTS";
                                            $departaments = mysqli_query($conexion,$sql_departaments);

                                            if ($usuari_bd['id_dep'] == NULL) {
                                                echo "<option value='".NULL."' $selected>Sense departament</option>";
                                            }
                                            while($departament = $departaments->fetch_assoc()) {
                                                $selected = ($usuari_bd['id_dep'] == $departament['id_dep']) ? 'selected' : '';
                                                echo "<option value='".$departament['id_dep']."' $selected>".$departament['nom']."</option>";
                                            }
                                            ?>
                                        </select>
                                        
                                    </form>

                                <?php
                                }
                                ?>

                                <!-- botó per eliminar els usuaris -->
                                <form action="control_usuaris" method="POST">
                                    <input type="hidden" name="id" value="<?php echo $usuari_bd['id_usu']; ?>">

                                    <input class="boto_eliminar" type="submit" value="Eliminar" name="eliminar">
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

    <script>
        // popup per crear departaments
        function abrirPopup_creardep() {
            document.getElementById("overlay_creardep").style.display = "block";
            document.getElementById("popup_creardep").style.display = "block";
        }
        function cerrarPopup_creardep() {
            document.getElementById("overlay_creardep").style.display = "none";
            document.getElementById("popup_creardep").style.display = "none";
        }


        // popup per eliminar departaments
        function abrirPopup_eliminardep() {
            document.getElementById("overlay_eliminardep").style.display = "block";
            document.getElementById("popup_eliminardep").style.display = "block";
        }
        function cerrarPopup_eliminardep() {
            document.getElementById("overlay_eliminardep").style.display = "none";
            document.getElementById("popup_eliminardep").style.display = "none";
        }

        // popup per veure els departaments i els usuaris
        function abrirPopup_veuredep() {
            document.getElementById("overlay_veuredep").style.display = "block";
            document.getElementById("popup_veuredep").style.display = "block";
        }
        function cerrarPopup_veuredep() {
            document.getElementById("overlay_veuredep").style.display = "none";
            document.getElementById("popup_veuredep").style.display = "none";
        }

        // popup per crear usuaris
        function abrirPopup_crearusu() {
            document.getElementById("overlay_crearusu").style.display = "block";
            document.getElementById("popup_crearusu").style.display = "block";
        }
        function cerrarPopup_crearusu() {
            document.getElementById("overlay_crearusu").style.display = "none";
            document.getElementById("popup_crearusu").style.display = "none";
        }


    </script>

</body>

</html>