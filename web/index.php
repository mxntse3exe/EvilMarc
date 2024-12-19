<?php 
    session_start(); 
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

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="#Funcionament" class="nav-link"><span data-hover="Funcionament">Funcionament</span></a>
                </li>
                <li class="nav-item">
                    <a href="#Contacte" class="nav-link"><span data-hover="Contacte">Contacte</span></a>
                </li>
            </ul>

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

    <!-- PUJAR ARXIUS -->
    <section class="about full-screen d-lg-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row">

                <div class="col-lg-7 col-md-12 col-12 d-flex align-items-center">
                    <div class="about-text">
                        <h1 class="animated animated-text">
                            <span class="mr-2">Amb EvilMarc podràs</span>
                                <div class="animated-info">
                                    <span class="animated-item">analitzar arxius</span>
                                    <span class="animated-item">gestionar carpetes</span>
                                    <span class="animated-item">compartir fitxers</span>
                                </div>
                        </h1>

                        <p>Building a successful product is a challenge. I am highly energetic in user experience design, interfaces and web development.</p>


                    </div>
                </div>

                <div class="col-lg-5 col-md-12 col-12">
                    <div class="pujar_arxius_index">
                        <h3 class="text_analitzar">Analitza el teu arxiu</h3>
                        <br>
                        <form method="post" enctype="multipart/form-data">
                            Selecciona el archivo para subir:
                            <input type="file" name="fileToUpload" id="fileToUpload">
                            <input type="submit" value="Subir archivo" name="submit">
                        </form>
                        <br>
                        <?php
                        // Definir la carpeta donde se subirán los archivos
                        $target_dir = "fitxers/fitxers_usuaris/";
                        $mensaje = '';

                        // Verificar si el formulario fue enviado
                        if (isset($_POST["submit"])) {
                            // Obtener la información del archivo subido
                            $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
                            $uploadOk = 1;
                            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                            // Verificar si el archivo es una imagen (aunque no estés limitando los tipos de archivo, esto es parte del código original)
                            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
                            if ($check !== false) {
                                $mensaje = "El archivo es una imagen - " . $check["mime"] . ".";
                                $uploadOk = 1;
                            } else {
                                $mensaje = "El archivo no es una imagen.";
                                $uploadOk = 0;
                            }

                            // Verificar si el archivo ya existe
                            if (file_exists($target_file)) {
                                $mensaje = "El archivo ya existe.";
                                $uploadOk = 0;
                            }

                            // Verificar el tamaño del archivo
                            if ($_FILES["fileToUpload"]["size"] > 681574400) { // 650 MB
                                $mensaje = "El archivo es demasiado grande.";
                                $uploadOk = 0;
                            }

                            // Si todo está bien, intentar mover el archivo
                            if ($uploadOk == 0) {
                                $mensaje .= " El archivo no se subió.";
                            } else {
                                if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
                                    $mensaje = "El archivo " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " se ha subido correctamente.";
                                } else {
                                    $mensaje = "Error al subir el archivo.";
                                }
                            }
                        }

                        // Mostrar mensaje
                        if ($mensaje) {
                            echo "<p>$mensaje</p>";
                        }
                        ?>
                    </div>
                </div>



            </div>
        </div>
    </section>

    <!-- FUNCIONAMENT -->
    
    <section class="about full-screen d-lg-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row">
                <div class="seccio_index" id="Funcionament">
                    <h2>Com analitzar un arxiu?</h2>
                    <div class="funcionament_contents">
                        <div class="video_funcionament">
                            <!-- <iframe width="400" height="250" src="https://www.youtube.com/embed/xvFZjo5PgG0?si=OxInnWXff0cEFuFZ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe> -->
                        </div>
                        <div class="text_funcionament">
                            <p>Per tal de poder analitzar el teu arxiu, hauras de pujar-lo a la nostra web seguint els passos del vídeo que trobaràs a continuació.</p>
                        </div>
                    </div>
                </div>


    <!-- CONTACTE -->
                <div class="seccio_index" id="Contacte">
                    <h2>Formulari de contacte</h2>
                        <div class="contact_contents">
                            

                            <div class="contact-form">    
                                <div class="contact_formulari">
                                    <form method="post" action="contacte">
                                        <input class="form-control" type="text" name="assumpte" placeholder="Assumpte">
                                        <input class="form-control" type="text" name="correu" placeholder="El teu correu">
                                        <textarea class="form-control" name="mensaje" cols="30" rows="7" placeholder="Missatge"></textarea>
                                        <input class="form-control submit-btn" type="submit" value="Enviar" name="enviar">
                                    </form>
                                </div>        
                            </div>


                        </div>

                </div>
                
                


            </div>
        </div>
    </section>


    </section>
    <section class="about full-screen d-lg-flex justify-content-center align-items-center" id="Contacte">

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
