<?php 
session_start();

require 'vendor/autoload.php';
use MongoDB\Client;

// Configuració de MongoDB
$mongoClient = new Client("mongodb://localhost:27017");
$client = $mongoClient;

// Configuració de MySQL
$servidor = "localhost";
$usuario = "web";
$password = "T5Dk!xq";
$db = "evilmarc";

$conexion = mysqli_connect($servidor, $usuario, $password, $db);
if (!$conexion) die("Error al connectar amb la base de dades.");

// Configuració de zona horària
date_default_timezone_set('Europe/Madrid');

// Verificar si l'usuari és vàlid
if (!isset($_SESSION['valido']) || $_SESSION['valido'] != 1) {
    header("Location: inici");
    exit();
}

$usuari = $_SESSION['usuari'];
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
    <link rel="stylesheet" href="css/tooplate-style.css">
    <link rel="icon" type="image/png" href="images/favicon.ico"/>

    <style>
    .badge {
        font-size: 0.75em;
        vertical-align: middle;
    }
    .usuari {
        position: relative;
        padding: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    #llista-usuaris {
        max-height: 400px;
        overflow-y: auto;
    }
    .panell_missatges {
        display: flex;
        gap: 20px;
    }
    /* #finestra-xat {
        width: 70%;
    } */
    #missatges {
        height: 300px;
        overflow-y: auto;
        margin-bottom: 15px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 15px;
    }
    </style>
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

    <!-- CONTINGUT PRINCIPAL -->
    <section class="about full-screen d-lg-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row seccio_panell">
                <div style="width: 100%;">
                    <h2>Xat intern</h2>
                    
                    <div class="panell_missatges">
                        <div id="llista-usuaris">

                            <div class="buscador-container" style="width: 90%;">
                                <input type="text" id="buscador-fitxers" placeholder="Cerca usuaris..." class="form-control">
                                <i class="uil uil-search"></i>
                            </div>



                            <?php
                            $sql = "SELECT usuari, nom, imatge FROM USUARIS WHERE usuari != '".$usuari."'";
                            $result = mysqli_query($conexion, $sql);

                            while ($row = mysqli_fetch_assoc($result)) {
                                // Consulta per comptar missatges no llegits a MongoDB
                                $mongoFilter = [
                                    'emissor' => $row['usuari'],
                                    'receptor' => $_SESSION['usuari'],
                                    'llegit' => false
                                ];
                                $no_llegits = $client->chat->missatges->countDocuments($mongoFilter);
                                
                                echo "<div class='usuari' onclick=\"obrirXat('".$row['usuari']."')\">";
                                echo "<img src='".$row['imatge']."' width='30' height='30' style='border-radius:50%; margin-right:10px;'>";
                                echo $row['nom']." (".$row['usuari'].")";
                                if ($no_llegits > 0) {
                                    echo "<span class='badge badge-pill badge-danger ml-2' id='badge-".$row['usuari']."'>".$no_llegits."</span>";
                                }
                                echo "</div>";
                            }
                            ?>
                        </div>
                        
                        <div id="finestra-xat">
                            <h4 class="titol_xat">Xat amb: <span id="nom-receptor"></span></h4>
                            <div id="missatges"></div>
                            <div class="enviar_text">
                                <input type="text" id="missatge" class="form-control my-2 text_place" placeholder="Escriu el teu missatge...">
                                <button onclick="enviarMissatge()" class="boto_enviar">Enviar</button>
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
        let receptorActual = null;
        let intervalRefrescar = null;

        function obrirXat(usuari) {
            receptorActual = usuari;
            document.getElementById('nom-receptor').textContent = usuari;
            document.getElementById('finestra-xat').style.display = 'block';

            // Resaltar usuari actiu
            document.querySelectorAll('.usuari').forEach(u => u.classList.remove('actiu'));
            const usuaris = document.querySelectorAll('.usuari');
            usuaris.forEach(u => {
                if (u.textContent.includes(usuari)) {
                    u.classList.add('actiu');
                }
            });

            carregarMissatges();
            
            // Aturar qualsevol interval previ
            if (intervalRefrescar) clearInterval(intervalRefrescar);
            
            // Refrescar cada 3 segons
            intervalRefrescar = setInterval(() => {
                carregarMissatges();
                actualitzarBadges();
            }, 3000);
        }

        function carregarMissatges() {
            if (!receptorActual) return;

            fetch(`get_messages.php?receptor=${encodeURIComponent(receptorActual)}`)
                .then(res => res.json())
                .then(missatges => {
                    const cont = document.getElementById('missatges');
                    const estavaAbaix = cont.scrollTop + cont.clientHeight >= cont.scrollHeight - 10;

                    cont.innerHTML = '';

                    missatges.forEach(m => {
                        const div = document.createElement('div');
                        div.classList.add('missatge');
                        if (m.emissor === "<?php echo $_SESSION['usuari']; ?>") {
                            div.classList.add('enviat');
                        } else {
                            div.classList.add('rebut');
                        }
                        div.textContent = m.text;
                        cont.appendChild(div);

                        const data = document.createElement('div');
                        data.classList.add('data-missatge');
                        data.textContent = m.data;

                        if (m.emissor === "<?php echo $_SESSION['usuari']; ?>") {
                            data.classList.add('dreta');
                        } else {
                            data.classList.add('esquerra');
                        }

                        cont.appendChild(data);
                    });

                    if (estavaAbaix) cont.scrollTop = cont.scrollHeight;

                    // Marcar com a llegits si hi ha missatges del receptor
                    if (missatges.some(m => m.emissor === receptorActual)) {
                        fetch('mark_as_read.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ emissor: receptorActual })
                        }).then(() => {
                            const badge = document.getElementById(`badge-${receptorActual}`);
                            if (badge) badge.remove();
                        });
                    }
                });
        }

        function actualitzarBadges() {
            fetch('get_unread_counts.php')
                .then(res => res.json())
                .then(counts => {
                    Object.entries(counts).forEach(([usuari, count]) => {
                        let badge = document.getElementById(`badge-${usuari}`);
                        if (count > 0) {
                            if (!badge) {
                                // Buscar element de l'usuari
                                const userElements = Array.from(document.querySelectorAll('.usuari'));
                                const userElement = userElements.find(el => 
                                    el.textContent.includes(`(${usuari})`));
                                
                                if (userElement) {
                                    badge = document.createElement('span');
                                    badge.id = `badge-${usuari}`;
                                    badge.className = 'badge badge-pill badge-danger ml-2';
                                    badge.textContent = count;
                                    userElement.appendChild(badge);
                                }
                            } else {
                                badge.textContent = count;
                            }
                        } else if (badge) {
                            badge.remove();
                        }
                    });
                });
        }

        function enviarMissatge() {
            const input = document.getElementById('missatge');
            const text = input.value.trim();
            if (!text || !receptorActual) return;

            fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ text: text, receptor: receptorActual })
            }).then(() => {
                input.value = '';
                carregarMissatges();
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            actualitzarBadges(); // Nova línia
            setInterval(actualitzarBadges, 3000); // Nova línia
            
            // Mantenim la funcionalitat original
            const input = document.getElementById('missatge');
            input.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    enviarMissatge();
                }
            });
        });





        // Funció per filtrar fitxers
        function filtrarFitxers() {
            const cercador = document.getElementById('buscador-fitxers');
            const terme = cercador.value.toLowerCase();
            const elements = document.querySelectorAll('.usuari');
            
            elements.forEach(element => {
                const text = element.textContent.toLowerCase();
                if (text.includes(terme)) {
                    element.classList.remove('filtrat');
                } else {
                    element.classList.add('filtrat');
                }
            });
        }

        // Escolta els canvis en el camp de cerca
        document.getElementById('buscador-fitxers').addEventListener('input', filtrarFitxers);

        // Funció per a la tecla Escape
        document.getElementById('buscador-fitxers').addEventListener('keyup', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                filtrarFitxers();
            }
        });



    </script>
</body>
</html>