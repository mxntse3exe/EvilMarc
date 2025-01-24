# PER POSAR EL PROJECTE EVILMARC EN MARXA

## Instal·lem i iniciem el servei d'Apache

``bash
sudo apt update
sudo apt-get install apache2
sudo service apache2 start



---- Instal·lem MariaDB --------------------------------------------------------------------------------------------------------------------

sudo apt-get install mariadb-server



---- Instal·lació segura MariaDB -----------------------------------------------------------------------------------------------------------

sudo mysql_secure_installation


	(Establim el password de root)
	- Establim password de root --> Stucom1234

	(unix socket)
	- Switch to unix_socket authentication --> No

	(No canviem el password de root)
	- Change the root password --> No

	(Eliminem usuaris anònims)
	Remove anonymous users --> Yes

	(Deshabilitem la connexió root de forma remota)
	Disallow root login remotely --> Yes

	(Eliminem les databases de prova)
	Remove test database and acces to it --> Yes

	(Refresquem les taules de privilegis)
	Reload privilege tables--> Yes 


sudo mysql
use mysql;

ALTER USER 'root'@'localhost'IDENTIFIED BY 'Stucom1234';
FLUSH PRIVILEGES;

exit


sudo mysql -u root -p

CREATE USER 'web'@'localhost' IDENTIFIED BY 'T5Dk!xq';


CREATE DATABASE IF NOT EXISTS evilmarc;

GRANT ALL PRIVILEGES ON evilmarc.* TO 'web'@'localhost';

FLUSH PRIVILEGES;

exit

sudo mysql -u web -p 

use evilmarc

CREATE TABLE DEPARTAMENTS (
    id_dep INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
);


CREATE TABLE USUARIS (
    id_usu INT AUTO_INCREMENT PRIMARY KEY,
    usuari VARCHAR(50) NOT NULL,
    correu VARCHAR(100) NOT NULL,
    contrasenya VARCHAR(64) NOT NULL,
    nom VARCHAR(50),
    cognoms VARCHAR(100),
    direccio VARCHAR(255),
    imatge VARCHAR(255) DEFAULT 'images/perfil/perfil_default.png',
    admin BOOLEAN DEFAULT FALSE,
    validat BOOLEAN DEFAULT FALSE,
    id_dep INT,
    FOREIGN KEY (id_dep) REFERENCES DEPARTAMENTS(id_dep)
);


CREATE TABLE ARXIUS (
    id_arxiu INT AUTO_INCREMENT PRIMARY KEY,
    nom_arxiu VARCHAR(255) NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    hash VARCHAR(64) NOT NULL,
    infectat BOOLEAN DEFAULT FALSE
);


CREATE TABLE ARXIUS_PUJATS (
    id_usu INT NOT NULL,
    id_arxiu INT NOT NULL,
    PRIMARY KEY (id_usu, id_arxiu),
    FOREIGN KEY (id_usu) REFERENCES USUARIS(id_usu) ON DELETE CASCADE,
    FOREIGN KEY (id_arxiu) REFERENCES ARXIUS(id_arxiu) ON DELETE CASCADE
);


CREATE TABLE ARXIUS_COMPARTITS (
    id_propietari INT NOT NULL,
    id_destinatari INT NOT NULL,
    id_arxiu INT NOT NULL,
    PRIMARY KEY (id_propietari, id_destinatari, id_arxiu),
    FOREIGN KEY (id_propietari) REFERENCES USUARIS(id_usu) ON DELETE CASCADE,
    FOREIGN KEY (id_destinatari) REFERENCES USUARIS(id_usu) ON DELETE CASCADE,
    FOREIGN KEY (id_arxiu) REFERENCES ARXIUS(id_arxiu) ON DELETE CASCADE
);












---- Instal·lem el PHP ---------------------------------------------------------------------------------------------------------------------

sudo apt-get install php
sudo apt-get install php-mysql
sudo service apache2 restart


---- Per eliminar el nom de les extensions .php, .HTML. Haurem de dir-li a l'apache que no ignori l'arxiu .htaccess ------------------------

sudo a2enmod rewrite
sudo service apache2 restart

sudo nano /etc/apache2/sites-enabled/000-default.conf


	Dins d'aquest arxiu afegirem les següents línies:


	<Directory /var/www/>
 	               Options Indexes FollowSymLinks
 	               AllowOverride All
	               Require all granted
	</Directory>


sudo service apache2 restart


---- Per fer servir el Python amb MySQL ----------------------------------------------------------------------------------------------------

sudo apt install python3-pip
sudo apt update && sudo apt install python3-pip
sudo pip3 install mysql-connector-python



Fem un pull del directori de github, i copiem els arxius de la carpeta dels arxius web a la carpeta de la web /var/www/html:

- hem de donar permisos a la carpeta on guardarem les fotos de perfil images/perfil:

	sudo chown www-data:www-data /var/www/html/images/perfil
	sudo chmod 755 /var/www/html/images/perfil


- hem de donar permisos d'execució al programa de Python:

	sudo chown www-data:www-data /var/www/html/evilmarc_web.py
	sudo chmod 755 /var/www/html/evilmarc_web.py


- hem de crear i donar permisos a la carpeta on guardarem els arxius pujats temporalment:
	
	sudo mkdir /var/www/html/fitxers/fitxers_temp
	sudo chown www-data:www-data /var/www/html/fitxers/fitxers_temp
	sudo chmod 755 /var/www/html/fitxers/fitxers_temp

- hem de donar permisos a la carpeta on guardarem els arxius dels usuaris:

	sudo chown www-data:www-data fitxers_usuaris/

- hem d'editar l'arxiu /etc/php/8.1/apache2/php.ini:

	sudo nano /etc/php/8.1/apache2/php.ini
	upload_max_filesize = 650M 
	post_max_size = 650M

