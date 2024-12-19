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
                        <form enctype="multipart/form-data" method="post" class="form-inline">
                            <input type="hidden" name="max_file_size" value="680244480"> <!-- Máximo 649MB -->
                            <div class="form-group">
                                <label for="archivo" class="mr-2">Selecciona el fitxer:</label>
                                <input type="file" name="archivo" id="archivo" class="form-control">
                            </div>
                            <br><br>
                            <button type="submit" class="btn custom-btn custom-btn-bg custom-btn-link mt-3">
                                <i class='uil uil-file-alt'></i> Pujar i Analitzar
                            </button>
                        </form>
                        <br>
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
                            if (strlen($_FILES['archivo']['name']) < 50) {
                                if ($_FILES['archivo']['type'] == "image/jpeg" || $_FILES['archivo']['type'] == "application/pdf") {
                                    if ($_FILES['archivo']['size'] <= 680244480) { // 649MB en bytes
                                        if (is_uploaded_file($_FILES['archivo']['tmp_name'])) {
                                            $nombreDirectorio = "archivos/";
                                            $nombreFichero = $_FILES['archivo']['name'];
                                            
                                            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $nombreDirectorio . $nombreFichero)) {
                                                echo "<div class='alert alert-success mt-3'>Fitxer pujat correctament.</div>";
                                            } else {
                                                echo "<div class='alert alert-danger mt-3'>Error: No s'ha pogut pujar el fitxer.</div>";
                                            }
                                        }
                                    } else {
                                        echo "<div class='alert alert-danger mt-3'>Error: El tamany del fitxer supera els 649MB.</div>";
                                    }
                                } else {
                                    echo "<div class='alert alert-danger mt-3'>Error: Tipus de fitxer no permès. Només imatges JPEG i PDFs.</div>";
                                }
                            } else {
                                echo "<div class='alert alert-danger mt-3'>Error: El nom del fitxer supera els 20 caràcters.</div>";
                            }
                        }
                        ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

                        <a href="www.google.es" class="btn custom-btn custom-btn-bg custom-btn-link"><i class='uil uil-file-alt'></i> Puja un arxiu...</a>
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
                            
                            <div class="contact_text">
                                <p>Per qualsevol dubte, consulta o pregunta, no dubtis de posar-te en contacte amb nosaltres mitjançant el següent formulari. <br><br> Us atendrem tan aviat com ens sigui possible. <br><br> Moltes gràcies per la vostra confiança!</p>
                            </div>
                            

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