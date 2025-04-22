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
        $num_usu = $_SESSION['id_usu'];
    }

    date_default_timezone_set('Europe/Madrid');

    require 'vendor/autoload.php';

    use MongoDB\Client;

    // Connexió a la base de dades
    $mongoClient = new Client("mongodb://localhost:27017");
    $db = $mongoClient->logs;
    $collection_pujats = $db->fitxers_pujats;
    $collection_infectats = $db->fitxers_infectats;
    $collection_eliminats = $db->fitxers_eliminats;

    // Opcions de cerca
    $filter = ['id_usuari' => $num_usu];  // Filtra per id_usuari
    $options = ['sort' => ['data' => -1]];  // Ordena per data (descendent)

    // Obtenir dades de la base de dades
    $pujats = $collection_pujats->find($filter, $options);
    $infectats = $collection_infectats->find($filter, $options);
    $eliminats = $collection_eliminats->find($filter, $options);

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

    <style>
    #finestra-xat {
        display: none;
        visibility: visible !important;
        opacity: 1 !important;
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



    <!-- FUNCIONAMENT -->
    
    <section class="about full-screen d-lg-flex justify-content-center align-items-center">
        <div class="container">

            <div class="row seccio_panell">
                <div style="width: 100%;">
                    <?php
                    if($_SESSION['valido'] == 1) {
                    ?>
                    <h2>Xat intern</h2>
                    
                    <div class="panell_missatges">
                        <div id="llista-usuaris">
                            
                            <?php
                            $sql = "SELECT usuari, nom, imatge FROM USUARIS WHERE usuari != '".$usuari."'";
                            $result = mysqli_query($conexion, $sql);

                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<div class='usuari' onclick=\"obrirXat('".$row['usuari']."')\">";
                                echo "<img src='".$row['imatge']."' width='30' height='30' style='border-radius:50%; margin-right:10px;'>";
                                echo $row['nom']." (".$row['usuari'].")</div>";
                            }
                            ?>
                        </div>
                        <div id="finestra-xat" style="display:block;">
                            <h4 class="titol_xat">Xat amb: <span id="nom-receptor"></span></h4>
                            <div id="missatges"></div>
                            <div class="enviar_text">
                                <input type="text" id="missatge" class="form-control my-2 text_place" placeholder="Escriu el teu missatge...">
                                <button onclick="enviarMissatge()" class="boto_enviar">Enviar</button>

                            </div>
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

        function obrirXat(usuari) {
            console.log(document.getElementById('finestra-xat'));

            receptorActual = usuari;
            document.getElementById('nom-receptor').textContent = usuari;
            document.getElementById('finestra-xat').style.display = 'block';

             // Eliminar classe activa de tots
            document.querySelectorAll('.usuari').forEach(u => u.classList.remove('actiu'));

            // Afegir classe activa a l'usuari clicat
            const usuaris = document.querySelectorAll('.usuari');
            usuaris.forEach(u => {
                if (u.textContent.includes(usuari)) {
                    u.classList.add('actiu');
                }
            });

            carregarMissatges();
            // Refresca automàticament cada 3 segons
            if (window.refrescar) clearInterval(window.refrescar);
            window.refrescar = setInterval(carregarMissatges, 3000);
        }

        function carregarMissatges() {
            if (!receptorActual) return;

            fetch(`get_messages.php?receptor=${encodeURIComponent(receptorActual)}`)
                .then(res => res.json())
                .then(missatges => {
                    const cont = document.getElementById('missatges');

                    // Comprova si l'usuari ja estava a baix
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

                    // Si estava a baix abans de carregar, hi tornem
                    if (estavaAbaix) {
                        cont.scrollTop = cont.scrollHeight;
                    }
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

        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('missatge');
            input.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    enviarMissatge();
                }
            });
        });


    </script>

</body>

</html>