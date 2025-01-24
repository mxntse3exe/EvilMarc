# PER POSAR EL PROJECTE EVILMARC EN MARXA

## Instal·lació del servei d'Apache

``` bash
sudo apt update
sudo apt-get install apache2
sudo service apache2 start
```

## Instal·lació de MariaDB

``` bash
sudo apt-get install mariadb-server
```

Haurem d'assegurar la instal·lació de MariaDB:

``` bash
sudo mysql_secure_installation
```

*Establim el password de root*
- Establim password de root --> Stucom1234

*Unix socket*
- Switch to unix_socket authentication --> No

*No canviem el password de root*
- Change the root password --> No

*Eliminem usuaris anònims*
- Remove anonymous users --> Yes

*Deshabilitem la connexió root de forma remota*
- Disallow root login remotely --> Yes

*Eliminem les databases de prova*
- Remove test database and acces to it --> Yes

*Refresquem les taules de privilegis*
- Reload privilege tables --> Yes 

Ara accedirem dins la base de dades:
``` bash
sudo mysql
```

``` sql
use mysql;
ALTER USER 'root'@'localhost'IDENTIFIED BY 'Stucom1234';
FLUSH PRIVILEGES;
exit
```

``` bash
sudo mysql -u root -p
```

Crearem l'usuari que farem servir per la nostra web:

``` sql
CREATE USER 'web'@'localhost' IDENTIFIED BY 'T5Dk!xq';

CREATE DATABASE IF NOT EXISTS evilmarc;

GRANT ALL PRIVILEGES ON evilmarc.* TO 'web'@'localhost';

FLUSH PRIVILEGES;

exit
```

Entrem dins MariaDB amb l'usuari que acabem de Crearem

``` bash
sudo mysql -u web -p 
```

Farem servir la base de dades **evilmarc**.

``` sql
use evilmarc
```

Crearem les bases de dades necessàries pel bon funcionament de la web:

``` sql
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
```

## Instal·lació PHP

``` bash
sudo apt-get install php
sudo apt-get install php-mysql
sudo service apache2 restart
```

Per eliminar el nom de les extensions .php, .HTML. Haurem de dir-li a l'apache que no ignori l'arxiu **.htaccess**:

``` bash
sudo a2enmod rewrite
sudo service apache2 restart

sudo nano /etc/apache2/sites-enabled/000-default.conf
```

Dins d'aquest arxiu afegirem les següents línies:

``` nano
<Directory /var/www/>
                Options Indexes FollowSymLinks
                AllowOverride All
                Require all granted
</Directory>
```

Reiniciem el servei d'Apache

``` bash
sudo service apache2 restart
```

## Instal·lació Python i connector Python amb mysql

``` bash
sudo apt install python3-pip
sudo apt update && sudo apt install python3-pip
sudo pip3 install mysql-connector-python
```

## Clonació repositori GitHub

Haurem de clonar el repositori de GitHub **EvilMarc**.
Un cop el tinguem al nostre equip, haurem de copiar tots els arxius de dins el directori web a la ruta **/var/www/html**.

## Gestió de permisos

Per tal que la web funcioni correctament, haurem de donar permisos a les següents carpetes:

- hem de donar permisos a la carpeta on guardarem les fotos de perfil images/perfil:

``` bash
sudo chown www-data:www-data /var/www/html/images/perfil
sudo chmod 755 /var/www/html/images/perfil
```

- hem de donar permisos d'execució al programa de Python:

``` bash
sudo chown www-data:www-data /var/www/html/evilmarc_web.py
sudo chmod 755 /var/www/html/evilmarc_web.py
```

- hem de crear i donar permisos a la carpeta on guardarem els arxius pujats temporalment:

``` bash
sudo mkdir /var/www/html/fitxers/fitxers_temp
sudo chown www-data:www-data /var/www/html/fitxers/fitxers_temp
sudo chmod 755 /var/www/html/fitxers/fitxers_temp
```

- hem de donar permisos a la carpeta on guardarem els arxius dels usuaris:

``` bash
sudo chown www-data:www-data /var/www/html/fitxers/fitxers_usuaris
sudo chmod 755 /var/www/html/fitxers/fitxers_usuaris
```

## Editem l'arxiu php.ini

``` bash
sudo nano /etc/php/8.1/apache2/php.ini
```

Canviarem els dos paràmetres següents:

- upload_max_filesize = 650M 
- post_max_size = 650M

