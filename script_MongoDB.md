
# 🐳 DOCKER + MongoDB – Guia d’instal·lació i automatització

## 🔧 1. Eliminar paquets conflictivos (opcional)
```bash
for pkg in docker.io docker-doc docker-compose docker-compose-v2 podman-docker containerd runc; do
  sudo apt-get remove $pkg
done
```

## 🔑 2. Afegir la clau GPG oficial de Docker
```bash
sudo apt-get update
sudo apt-get install ca-certificates curl

sudo install -m 0755 -d /etc/apt/keyrings

sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
```

## 📦 3. Afegir el repositori oficial de Docker
```bash
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt-get update
```

## 🧱 4. Instal·lar Docker
```bash
sudo apt-get install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```

## ✅ 5. Verificar la instal·lació
```bash
sudo docker run hello-world
```

## ⚙️ 6. Activar Docker en l’arrencada del sistema
```bash
sudo systemctl enable docker.service
sudo systemctl enable containerd.service
```

## 🐳 7. Crear contenidor MongoDB amb Docker
```bash
sudo mkdir /mongodb
sudo docker pull mongo:4

sudo docker run --name my-mongo -d -p 27017:27017 -v /mongodb:/data/db mongo:4
```

## 🔍 8. Verificar i accedir a MongoDB
```bash
sudo docker ps
sudo docker exec -it my-mongo mongo
```

## 🛠️ 9. Crear base de dades i col·lecció d’exemple
```js
use productos

db.createCollection("ordenadores")

db.ordenadores.insertOne({
  modelo: "Acer Aspire",
  tipo: "Portátil",
  ram: "16GB"
})

db.ordenadores.find()
```

## 🛑 10. Detenir el contenidor
```bash
sudo docker stop my-mongo
```

## 🐍 11. Integració amb Python
```bash
sudo pip3 install pymongo
sudo docker exec -it my-mongo mongo

use logs
db.createCollection("fitxers_pujats")
```

📄 **Exemple d'inserció:**
```js
db.fitxers_pujats.insertOne({
  nom_arxiu: "ejemplo.txt",
  ruta: "/uploads/ejemplo.txt",
  hash: "abc123hash",
  infectat: false,
  usuari: "victor",
  data_pujada: new Date()
})
```

## 🧪 12. Reiniciar servei manualment
```bash
sudo docker start my-mongo
sudo docker exec -it my-mongo mongo
```

## 🐘 13. Integració amb PHP i Composer
```bash
cd ~
sudo apt update

sudo apt install php-pear php-dev libmongoc-dev libbson-dev
sudo pecl install mongodb

echo "extension=mongodb.so" | sudo tee /etc/php/8.1/mods-available/mongodb.ini
sudo phpenmod mongodb
sudo systemctl restart apache2
php -m | grep mongodb

sudo apt install php-cli unzip
sudo curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

cd /var/www/html
sudo composer require mongodb/mongodb
```

## ⚙️ 14. Automatitzar execució de MongoDB en iniciar el sistema

### ① Crear script:
```bash
sudo nano /usr/local/bin/activar-mongo.sh
```

**Contingut:**
```bash
#!/bin/bash
docker start my-mongo
docker exec my-mongo mongo --eval "db.adminCommand('ping')"
```

**Permisos:**
```bash
sudo chmod +x /usr/local/bin/activar-mongo.sh
```

### ② Crear servei systemd:
```bash
sudo nano /etc/systemd/system/mongodb-activador.service
```

**Contingut:**
```ini
[Unit]
Description=Activador de MongoDB en Docker
After=docker.service
Requires=docker.service

[Service]
Type=oneshot
ExecStart=/usr/local/bin/activar-mongo.sh
RemainAfterExit=true

[Install]
WantedBy=multi-user.target
```

### ③ Activar el servei:
```bash
sudo systemctl daemon-reexec
```

```bash
sudo systemctl daemon-reload
```

```bash
sudo systemctl enable mongodb-activador.service
```

```bash
sudo systemctl start mongodb-activador.service
```

🔎 **Verificar l’estat:**
```bash
sudo systemctl status mongodb-activador.service
```
