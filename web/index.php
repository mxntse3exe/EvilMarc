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

    <link rel="icon" type="image/png" href="images/favicon.ico"/>


</head>

<body>

    <!-- MENU -->
    <nav class="navbar navbar-expand-sm navbar-light">
        <div class="container">
            <a class="navbar-brand" href="index">EvilMarc</a>

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
            <div class="row seccio_panell_index">
                <div class="index_pujar_arxius">

                    <h1 class="animated animated-text">
                        <span class="mr-2">Amb EvilMarc</span>
                        <div class="animated-info">
                            <span class="animated-item">analitza arxius</span>
                            <span class="animated-item">puja carpetes</span>
                            <span class="animated-item">gestiona fitxers</span>
                        </div>
                    </h1>
                    <p>Amb EvilMarc podràs analitzar els teus fitxers per detectar virus en qüestió de segons i guardar-los de manera segura al nostre núvol privat. Protegeix els teus documents amb EvilMarc!</p>
                </div>

                <div class="pujar_arxius_index">
                    <h3 class="text_analitzar">Analitza el teu arxiu</h3>
                    
                    <form enctype="multipart/form-data" method="post" action="index" class="form_pujar_arxius">
                        <input type="hidden" name="max_file_size" value="681574400">



                        <label for="archivo" class="custom-file-upload"><i class="uil uil-file-alt"></i>Selecciona l'arxiu que vols analitzar...</label>
                        


                        <input type="file" name="archivo" class="input_arxiu" id="archivo">
                        
                        <input type="submit" value="Pujar arxiu" class="pujar_arxiu_submit" name="arxiu">
                    </form>


                    <?php 
                        if(isset($_POST['arxiu'])){
                            
                            if (is_uploaded_file ($_FILES['archivo']['tmp_name'])) {
                                $nombreDirectorio = "/var/www/html/fitxers/fitxers_temp/";

                                $uniqid = uniqid();

                                $nombreFichero = $uniqid."_".$_FILES['archivo']['name'];

                                move_uploaded_file ($_FILES['archivo']['tmp_name'], $nombreDirectorio.$nombreFichero);
                                
                                $command = escapeshellcmd("python3 /var/www/html/evilmarc_web.py ".escapeshellarg($nombreFichero));

                                $output = shell_exec($command);
                                echo "<p class='sortida_analisi'>".$output."</p>";
                            }
                        }
                    ?>

                </div>

            </div>




            <div class="row seccio_panell_index" id="Funcionament">
                <div class="contents">
                    <h2>Com analitzar un arxiu?</h2>
                    <div class="videotext_funcionament">
                        <p>
                            EvilMarc és molt més que una eina per analitzar arxius! Mira el vídeo que tens a continuació per descobrir totes les funcionalitats que t’oferim: <br><br>
                            - Pujada segura de fitxers <br>
                            - Compartició de fitxers i carpetes <br>
                            - Xat en temps real amb els altres companys <br>
                            - Configuració del teu compte
                        </p>

                        <video width="427" controls autoplay muted loop class="video_funcionament">
                            <source src="images/project/video_projecte.mp4" type="video/mp4">
                        </video>
                    </div>
                </div>
            </div>

            <div class="row seccio_panell_index" id="Contacte">
                <div class="contents">
                <h2>Contacta'ns</h2>
                    <div class="contact_contents">
                        
                        <p>
                            Si necessites posar-te en contacte amb nosaltres, pots escriure'ns a l’adreça següent: <br>
                            <i class='uil uil-envelope'></i> <a href="mailto:evilmarc@evilmarc.com">evilmarc@evilmarc.com</a> <br><br>
                            També ens pots trucar al telèfon: <br>
                            <i class='uil uil-phone'></i> <a href="tel:+34600123456">+34 600 123 456</a> <br><br>
                            Horari d’atenció: de dilluns a divendres, de 15:00h a 20:00h. <br>
                        </p>
                        


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

</body>

</html>
