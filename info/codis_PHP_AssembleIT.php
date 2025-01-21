ATENCIÓ!!!! TOTES LES PÀGINES HAN DE COMENÇAR AMB LA SEGÜENT LÍNIA DE CODI!!!

<?php 
	session_start(); 
?>

##########################################################################################################################################################################################################################

<!-- index.php -->

No necessitem cap codi. Crear des de zero.

##########################################################################################################################################################################################################################

<!-- iniciar_sessio.php -->

Codi per validar si ha iniciat sessió correctament. En cas de contrasenya correcta es redirigeix al panell de l'usuari, 
en cas de contrasenya incorrecta es mostra un missatge d'error i s'ha de tornar a iniciar sessió.
(Sota el codi PHP hi haurà d'anar el codi de la pàgina web i el formulari d'inici de sessió.)

<?php 
	session_start();

	if (isset($_POST['iniciar'])) {
		$servidor = "rdbms.strato.de";
		$usuario = "dbu5193381";
		$password = "koVp@DzNZeS7#2!";
		$db = "dbs12617912";

		$conexion = mysqli_connect($servidor,$usuario,$password,$db);

		if (!$conexion) die ("Error al conectar con la base de datos.");

		$usuari = $_REQUEST['usuari'];
		$usuari = str_replace("=","",$usuari);
		$usuari = str_replace(" ","",$usuari);
		$usuari = str_replace("'","",$usuari);

		$pass = $_REQUEST['contrasenya'];
		$pass = hash('sha256', $pass, false);

		$sql = "select * from ADMINISTRADORS where usuario='".$usuari."' and contraseña='".$pass."'";
		//echo $sql

		$filas = mysqli_query($conexion,$sql);
		$nfilas = mysqli_num_rows($filas);

		if ($nfilas == 0) {
			$_SESSION['valido'] = 0;
			header("Location: inici");
			echo "Contrasenya incorrecta. Torna a "."<a href='inici'>iniciar sessió</a>".".";
		}
		else {
			$_SESSION['valido'] = 1;
			$_SESSION['usuari'] = $usuari;
			header("Location: admin");
		}
	}
?>

Codi del formulari d'inici de sessió.

<section>
    <div class="mida">				
        <h2>Iniciar sessió</h2>

        <form method="post" action="inici">
            Usuari: <br>
            <input type="text" name="usuari"><br>
            Contrasenya: <br>
            <input type="password" name="contrasenya"><br>
            <input type="submit" value="Iniciar sessió" name="iniciar"><br>
        </form>
    </div>
</section>

##########################################################################################################################################################################################################################

<!-- registrar.php -->

El codi per fer el registre hem de crear-lo des de zero. És molt semblant al codi que vam fer servir l'any passat per crear productes.

##########################################################################################################################################################################################################################

<!-- panell_usuari.php -->

Codi per comprovar si una sessió s'ha iniciat bé o no. En cas d'intentar entrar a la pàgina sense haver introduït la contrasenya, sortirà un error.
Si entrem a la pàgina amb la contrasenya correcta, ens sortirà el panell de control.
S'ha d'afegir 

<section> 
    <?php
    if($_SESSION['valido'] == 1) {
        $usuari = $_SESSION['usuari'];
    ?>
    <header class="major">
    <?php
        echo "<h2>Benvingut/da, ".$usuari."!</h2>";
    ?>
    </header>
    <div class="consultes">
        <span class="titol">Productes</span><br>
        <a href="crear_prod">1. Crear un producte</a><br>
        <a href="modificar_prod">2. Modificar productes</a><br>
        <a href="visualitzar_prod">3. Visualitzar i eliminar productes</a><br>
        <br>
        <span class="titol">Usuaris</span><br>
        <a href="crear_usuari">4. Crear un usuari</a><br>
        <a href="eliminar_usuari">5. Eliminar un usuari</a><br>
        <a href="modificar_usuari">6. Modificar usuaris</a><br>
        <a href="visualitzar_usuari">7. Visualitzar els usuaris</a><br><br>

        <a href="close" class="button">Log Out</a>
    </div>

    <?php
    }
    else {
        echo "Contrasenya incorrecta. Torna a "."<a href='inici'>iniciar sessió</a>".".";
    }
    ?>
</section>

##########################################################################################################################################################################################################################

<!-- arxius_pujats.php -->

Codi molt semblant al de les pàgines dels productes. Es connecta a la base de dades i d'allà agafem la informació que necessitem.
En el nostre cas haurem de canviar els productes per els arxius i agafar només els que hagi penjat cadascú.
Haurem de crear una opció per permetre que els usuaris puguin compartir els fitxers amb altres usuaris.

<div>
<?php
    $servidor = "rdbms.strato.de";
    $usuario = "dbu5193381";
    $password = "koVp@DzNZeS7#2!";
    $db = "dbs12617912";

    $conexion = mysqli_connect($servidor,$usuario,$password,$db);

    if (!$conexion) die ("Error al connectar amb la base de dades.");

    $sql = "select cod_prod, nom, preu, stock, caract, especif, imatge, categ from COMPONENTES where categ = 'hdd'";

    //echo $sql;
    $filas = mysqli_query($conexion,$sql);
    echo "<div class='container'>";
    while($fila = $filas->fetch_assoc()) {
        echo "<div class='box'>";
        echo "<div class='cuadre'>";
        echo "<img class='imgprod' src='images/imatges_productes/".$fila['imatge']."'>";
        
        echo "<br><b>".$fila['nom']."</b><br>".$fila['preu']."<span>€</span>";
        echo "</div>";
        ?>
        <br>
        <div class="d">
            <button class="btn">Veure més</button>
            <span>ref: <?php echo $fila['cod_prod']; ?></span>
            
            <div class="modal">
                <div class="modal-content">
                    
                    <span class='close'>&times;</span>
                    
                    <?php
                echo "<img class='imatge_producte' src='images/imatges_productes/".$fila['imatge']."'>";
                echo "<div class='text'>";
                echo "<span class='nom'><br>".$fila['nom']."</span><br><span class='preu'>".$fila['preu']."<span>€</span></span><br>Stock disponible: ".$fila['stock']." unitats<br><br>".$fila['caract']."<br><br>".$fila['especif'];
                echo "</div>";
                ?>
            </div>
        </div>
        </div>

        <?php
        // "<td>".$fila['stock']."<td>".$fila['caract']."<td>".$fila['especif']."<td>".$fila['imatge']."<td>".$fila['categ'];
        echo "</div>";
    }
    echo "</div>";

    mysqli_close($conexion);
?>
</div>

##########################################################################################################################################################################################################################

<!-- arxius_compartits.php -->

Codi molt semblant al de les pàgines dels productes. Es connecta a la base de dades i d'allà agafem la informació que necessitem.
En el nostre cas haurem de canviar els productes per els arxius i agafar només els que li hagin compartit a l'usuari.
(Mateix codi que en l'apartat anterior).

##########################################################################################################################################################################################################################

<!-- logs.php -->

Com que els logs estaran guardat en una BD no relacional, haurem de buscar un codi que ens permeti llistar-los.

##########################################################################################################################################################################################################################

<!-- pujar_fitxers.php -->

Aquest codi el vam fer amb el Navi l'any passat per poder penjar arxius dins el nostre hosting.

<!DOCTYPE html>
    <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>File Upload</title>
        </head>
        <body>
            <form enctype="multipart/form-data" method="post">
                <input type="hidden" name="max_file_size" value='5000000'>
                Fichero: <input type="file" name="archivo">
                <br><br>
                <input type="submit">
                <br><br>
            </form>
            <?php
                
                if (strlen($_FILES['archivo']['name']) < 20) {
                    if ($_FILES['archivo']['type'] == "image/jpeg" || $_FILES['archivo']['type'] == "application/pdf") {
                        if ($_FILES['archivo']['size'] <= 5000000) {
                            
                            if (is_uploaded_file ($_FILES['archivo']['tmp_name'])) {
                                $nombreDirectorio = "archivos/";
                                $nombreFichero = $_FILES['archivo']['name'];
                                move_uploaded_file ($_FILES['archivo']['tmp_name'], $nombreDirectorio.$nombreFichero);
                            }

                        }
                        else echo "Error: El tamaño del archivo supera los 1KB";
                    }
                    else echo "Error: Tipo de archivo no permitido";
                }
                else echo "Error: El nombre del archivo supera los 20 caracteres";

            ?>
        </body>
</html>

##########################################################################################################################################################################################################################

<!-- compte.php -->

Codi molt semblant al de modificar productes. Hem de vigilar ja que els camps no seran els mateixos. Sobretot mirar bé com funciona el codi!!!

<section>
    <header class="major">
        <h2>Modificar un producte</h2>
    </header>
    <div class="mida">
        <?php

        if($_SESSION['valido'] == 1) {

            if(isset($_REQUEST['cons'])) {

                $servidor = "rdbms.strato.de";
                $usuario = "dbu5193381";
                $password = "koVp@DzNZeS7#2!";
                $db = "dbs12617912";

                $conexion = mysqli_connect($servidor,$usuario,$password,$db);

                if (!$conexion) die ("Error al connectar amb la base de dades.");

                $id_producto = $_REQUEST['id'];
        
                // Consulta SQL para obtener los datos del producto
                $sql = "SELECT * FROM COMPONENTES WHERE cod_prod = $id_producto";
                $_SESSION['id_producto'] = $id_producto;
                $result = mysqli_query($conexion, $sql);
        
                if($result && mysqli_num_rows($result) > 0) {
                    $producto = mysqli_fetch_assoc($result);
                    $nom_mod = $producto['nom'];
                    $preu_mod = $producto['preu'];
                    $stock_mod = $producto['stock'];
                    $caract_mod = $producto['caract'];
                    $especif_mod = $producto['especif'];
                    $imatge_mod = $producto['imatge'];
                    $categ_mod = $producto['categ'];
                }
                else echo "El producte que intenta modificar no existeix"."<br><br>";
            }

            if(isset($_REQUEST['alta'])) {
                $servidor = "rdbms.strato.de";
                $usuario = "dbu5193381";
                $password = "koVp@DzNZeS7#2!";
                $db = "dbs12617912";

                $conexion = mysqli_connect($servidor,$usuario,$password,$db);

                if (!$conexion) die ("Error al connectar amb la base de dades.");

                $nom = $_REQUEST['nom'];
                $nom = str_replace("=","",$nom);
                $nom = str_replace("'","\'",$nom);
                $nom = str_replace('"','\"',$nom);

                $preu = $_REQUEST['preu'];
                $preu = str_replace("=","",$preu);
                $preu = str_replace(" ","",$preu);
                $preu = str_replace("'","",$preu);

                $stock = $_REQUEST['stock'];
                $stock = str_replace("=","",$stock);
                $stock = str_replace(" ","",$stock);
                $stock = str_replace("'","",$stock);

                $caract = $_REQUEST['caract'];
                $caract = str_replace("'","\'",$caract);
                $caract = str_replace('"','\"',$caract);

                $especif = $_REQUEST['especif'];
                $especif = str_replace("'","\'",$especif);
                $especif = str_replace('"','\"',$especif);

                $categ = $_REQUEST['categ'];
                $categ = str_replace("=","",$categ);
                $categ = str_replace(" ","",$categ);
                $categ = str_replace("'","",$categ);
                $categ = str_replace("-","",$categ);

                    if (is_uploaded_file ($_FILES['imatge']['tmp_name'])) {
                        $nombreFichero = $_FILES['imatge']['name'];
                        move_uploaded_file ($_FILES['imatge']['tmp_name'], "images/imatges_productes/".$nombreFichero);
                        
                        $sqlimg = "update COMPONENTES set imatge = '".$nombreFichero."' WHERE cod_prod = '".$_SESSION['id_producto']."'";
                        mysqli_query($conexion,$sqlimg);
                    }												

                $sql = "update COMPONENTES set nom = '".$nom."', preu = '".$preu."', stock = '".$stock."', caract = '".$caract."', especif = '".$especif."', categ = '".$categ."' WHERE cod_prod = '".$_SESSION['id_producto']."'";

                if (mysqli_query($conexion,$sql)) {
                    echo "Producte modificat correctament"."<br><br>";
                }
                else {
                    echo "Producte no modificat"."<br><br>";
                }
            }
        ?>
            <form action="modificar_prod" method="post">
                Id del producte a modificar: <input type="text" name="id"><br>
                <input type="submit" value="Consultar" name="cons">
            </form>

            <form action="modificar_prod" method="post" enctype="multipart/form-data">
                Nom producte: <input type="text" name="nom" value="<?php echo $nom_mod; ?>"><br>
                Preu (utilitzar '.' com a separador decimal): <input type="text" name="preu" value="<?php echo $preu_mod; ?>"><br>
                Stock: <input type="text" name="stock" value="<?php echo $stock_mod; ?>"><br>
                Característiques: <textarea name="caract" cols="30" rows="7"><?php echo $caract_mod; ?></textarea><br>
                Especificacions: <textarea name="especif" cols="30" rows="7"><?php echo $especif_mod; ?></textarea><br>

                Imatge: <br>
                <img class="imagestyle" src="images/imatges_productes/<?php echo $imatge_mod; ?>" onerror="<?php echo 'Aquest producte no té imatge' ?>"><br>
                
                <span class="files_up">
                    <label for="files_up">Selecciona una imatge</label>
                    <input type="file" id="files_up" name="imatge">
                </span><br><br>

                Categoria: 
                <select name="categ">
                    <option value="placa" <?php if ($categ_mod == 'placa') echo 'selected'; ?>>Placa base</option>
                    <option value="cpu" <?php if ($categ_mod == 'cpu') echo 'selected'; ?>>Processador</option>
                    <option value="ram" <?php if ($categ_mod == 'ram') echo 'selected'; ?>>Memòria RAM</option>
                    <option value="hdd" <?php if ($categ_mod == 'hdd') echo 'selected'; ?>>Disc HDD</option>
                    <option value="ssd" <?php if ($categ_mod == 'ssd') echo 'selected'; ?>>Disc SSD</option>
                    <option value="m2" <?php if ($categ_mod == 'm2') echo 'selected'; ?>>Disc M.2</option>
                    <option value="gpu" <?php if ($categ_mod == 'gpu') echo 'selected'; ?>>Targeta gràfica</option>
                    <option value="font" <?php if ($categ_mod == 'font') echo 'selected'; ?>>Font d'alimentació</option>
                    <option value="pasta" <?php if ($categ_mod == 'pasta') echo 'selected'; ?>>Pasta tèrmica</option>
                    <option value="ventilador" <?php if ($categ_mod == 'ventilador') echo 'selected'; ?>>Ventilador</option>
                    <option value="refliq" <?php if ($categ_mod == 'refliq') echo 'selected'; ?>>Refrigeració líquida</option>
                    <option value="torre" <?php if ($categ_mod == 'torre') echo 'selected'; ?>>Torre</option>
                    <option value="audio" <?php if ($categ_mod == 'audio') echo 'selected'; ?>>Dispositiu d'àudio</option>
                    <option value="teclat" <?php if ($categ_mod == 'teclat') echo 'selected'; ?>>Teclat</option>
                    <option value="ratoli" <?php if ($categ_mod == 'ratoli') echo 'selected'; ?>>Ratolí</option>
                    <option value="pantalla" <?php if ($categ_mod == 'pantalla') echo 'selected'; ?>>Pantalla</option>
                </select><br><br>
                <input type="submit" value="Modificar" name="alta">
            </form>
    
            <?php
        }
        else {
            echo "Loguejat incorrectament. Fes clic aquí per "."<a href='inici'>iniciar sessió.</a>";
        }
        ?>
    </div>
</section>

##########################################################################################################################################################################################################################

<!-- control_usuaris.php -->

Aquesta pàgina només haurà de ser accessible per a l'usuari administrador!!!!! Haurem d'introduïr una variable que comprovi que l'usuari que vol accedir a la pàgina és administrador o no.
Aquesta variable l'haurem d'iniciar en el moment que l'usuari inicia sessió, però no serà fins aquesta part que es comprovarà la seva variable de sessió d'administrador.
Haurem de comprovar que l'usuari s'hagi loguejat correctament i a més que sigui administrador.
El codi és molt semblant al de "arxius_pujats.php", però en comptes de llistar els arxius, llistarem els usuaris.
Els usuaris nous tindràn la opció de ser aprovats o eliminats, els usuaris aprovats tindran la opció de ser eliminats.