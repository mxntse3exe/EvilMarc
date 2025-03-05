# Per posar en marxa el projecte *EvilMarc*

## Instal·lació del servei d'Apache

Instal·larem l'Apache per muntar el nostre servidor web:

``` bash
sudo apt update
sudo apt-get install apache2
sudo service apache2 start
```

## Instal·lació de MariaDB

Instal·larem el SGBD MariaDB:

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

Entrem dins la BD amb l'usuari root i la contrasenya que acabem de crear:

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

Entrem dins MariaDB amb l'usuari que acabem de crear i la seva contrasenya:

``` bash
sudo mysql -u web -p 
```

Farem servir la base de dades **evilmarc**:

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

CREATE TABLE ARXIUS_PUJATS (
    id_arxiu INT AUTO_INCREMENT PRIMARY KEY,
    nom_arxiu VARCHAR(255) NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    hash VARCHAR(64) NOT NULL,
    id_usu INT NOT NULL,
    FOREIGN KEY (id_usu) REFERENCES USUARIS(id_usu) ON DELETE CASCADE
);

CREATE TABLE ARXIUS_COMPARTITS_USUARIS (
    id_propietari INT NOT NULL,
    id_destinatari INT NOT NULL,
    id_arxiu INT NOT NULL,
    PRIMARY KEY (id_propietari, id_destinatari, id_arxiu),
    FOREIGN KEY (id_propietari) REFERENCES USUARIS(id_usu) ON DELETE CASCADE,
    FOREIGN KEY (id_destinatari) REFERENCES USUARIS(id_usu) ON DELETE CASCADE,
    FOREIGN KEY (id_arxiu) REFERENCES ARXIUS_PUJATS(id_arxiu) ON DELETE CASCADE
);

CREATE TABLE ARXIUS_COMPARTITS_DEPARTAMENTS (
    id_propietari INT NOT NULL,
    id_dep INT NOT NULL,
    id_arxiu INT NOT NULL,
    PRIMARY KEY (id_propietari, id_dep, id_arxiu),
    FOREIGN KEY (id_propietari) REFERENCES USUARIS(id_usu) ON DELETE CASCADE,
    FOREIGN KEY (id_dep) REFERENCES DEPARTAMENTS(id_dep) ON DELETE CASCADE,
    FOREIGN KEY (id_arxiu) REFERENCES ARXIUS_PUJATS(id_arxiu) ON DELETE CASCADE
);

CREATE TABLE CARPETES_COMPARTIDES_USUARIS (
    id_propietari INT NOT NULL,
    id_destinatari INT NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    PRIMARY KEY (id_propietari, id_destinatari, ruta),
    FOREIGN KEY (id_propietari) REFERENCES USUARIS(id_usu) ON DELETE CASCADE,
    FOREIGN KEY (id_destinatari) REFERENCES USUARIS(id_usu) ON DELETE CASCADE
);

CREATE TABLE CARPETES_COMPARTIDES_DEPARTAMENTS (
    id_propietari INT NOT NULL,
    id_dep INT NOT NULL,
    ruta VARCHAR(500) NOT NULL,
    PRIMARY KEY (id_propietari, id_dep, ruta),
    FOREIGN KEY (id_propietari) REFERENCES USUARIS(id_usu) ON DELETE CASCADE,
    FOREIGN KEY (id_dep) REFERENCES DEPARTAMENTS(id_dep) ON DELETE CASCADE
);
```

## Instal·lació PHP

Instal·larem el PHP i el connector PHP amb MySQL:

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

Reiniciem el servei d'Apache:

``` bash
sudo service apache2 restart
```

## Instal·lació Python i connector Python amb MySQL

Instal·larem el Python i el connector de Python amb MySQL:

``` bash
sudo apt install python3-pip
sudo apt update && sudo apt install python3-pip
sudo pip3 install mysql-connector-python
```

## Instal·lació de Docker per a fer servir MongoDB

### Instal·lació Docker

(Opcional) Desinstal·lar paquets conflictius:

```bash
for pkg in docker.io docker-doc docker-compose docker-compose-v2 podman-
docker containerd runc; do sudo apt-get remove $pkg; done
```

Afegir la clau GPG oficial de Docker:

```bash
sudo apt-get update
sudo apt-get install ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
```

Afegir el repositori a les fonts APT (hem de posar les comandes una per una):

```bash
echo \ "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
$(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
sudo apt-get update
```

Instal·lar l'última versió de Docker:

```bash
sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

Verificar que la instal·lació de Docker Engine és correcte:

```bash
sudo docker run hello-world
```

Configurar Docker per iniciar-se en l'arrancada del sistema:

```bash
sudo systemctl enable docker.service
sudo systemctl enable containerd.service
```

### Docker Container de MongoDB

Descargar imatge de MongoDB V.4:

```bash
sudo docker pull mongo:4
```

Crear ruta en el nostre sistema per poder guardar les dades del MongoDB:

```bash
sudo mkdir /mongodb
```

Executar el contenidor de MongoDB:

```bash
sudo docker run --name my-mongo -d -p 27017:27017 -v /mongodb:/data/db mongo:4
```

Verifiquem que el contenidor s'estigui executant:

```bash
sudo docker ps
```

Iniciem la shell de MongoDB:

```bash
sudo docker exec -it my-mongo mongo
```

### Integració de MongoDB amb Python per poder escriure coses a la BD no relacional

Farem servir les següents comandes:

```bash
sudo pip3 install pymongo
sudo docker exec -it my-mongo mongo
use logs
exit
```

### Iniciar servei de Docker

```bash
sudo docker start my-mongo
sudo docker exec -it my-mongo mongo
```

## Clonació repositori GitHub

Haurem de clonar el repositori de GitHub **EvilMarc**.
Un cop el tinguem al nostre equip, haurem de copiar tots els arxius de dins el directori web a la ruta del nostre servidor **/var/www/html**.

## Gestió de permisos

Per tal que la web funcioni correctament, haurem de donar permisos a les següents carpetes:

- Hem de donar permisos a la carpeta on guardarem les fotos de perfil dels usuaris */var/www/html/images/perfil*:

``` bash
sudo chown www-data:www-data /var/www/html/images/perfil
sudo chmod 755 /var/www/html/images/perfil
```

- Hem de donar permisos d'execució al programa de Python *evilmarc_web.py*:

``` bash
sudo chown www-data:www-data /var/www/html/evilmarc_web.py
sudo chmod 755 /var/www/html/evilmarc_web.py
```

- Hem de crear i donar permisos a la carpeta on guardarem els arxius pujats temporalment */var/www/html/fitxers/fitxers_temp*:

``` bash
sudo mkdir /var/www/html/fitxers/fitxers_temp
sudo chown www-data:www-data /var/www/html/fitxers/fitxers_temp
sudo chmod 755 /var/www/html/fitxers/fitxers_temp
```

- Hem de donar permisos a la carpeta on guardarem els arxius dels usuaris */var/www/html/fitxers/fitxers_usuaris*:

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

## END
