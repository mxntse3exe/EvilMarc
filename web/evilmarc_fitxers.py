import os
import shutil
import requests
import mysql.connector
import hashlib
import mimetypes
import time
import sys
import json
from pymongo import MongoClient
import datetime



# Connexió i creació BD
def creacio_bd(host, user, password):
    try:
        mariadb_conn = mysql.connector.connect(
            host=host, 
            user=user, 
            password=password)

        mariadb_cursor = mariadb_conn.cursor()

        mariadb_cursor.execute("CREATE DATABASE IF NOT EXISTS evilmarc")
        mariadb_cursor.execute("USE evilmarc")
        mariadb_cursor.execute("""
            CREATE TABLE IF NOT EXISTS FITXERS_ANALITZATS (
                file_hash VARCHAR(64) PRIMARY KEY,
                scan_id VARCHAR(60),
                infected BOOLEAN
            )
        """)
        mariadb_cursor.close()
        mariadb_conn.close()

    except:
        exit()

# Funció per obtenir el hash de l'arxiu
def obtenir_hash(arxiu):
    with open (arxiu, 'rb') as f:
        return hashlib.sha256(f.read()).hexdigest()

# Funció per verificar si el hash de l'arxiu es troba en la BD
def hash_in_bd(host, user, password, hash_arxiu):
    mariadb_conn = mysql.connector.connect(
        host=host, 
        user=user, 
        password=password)
    mariadb_cursor = mariadb_conn.cursor()
    mariadb_cursor.execute("USE evilmarc")

    mariadb_cursor.execute("SELECT COUNT(*) FROM FITXERS_ANALITZATS WHERE file_hash = %s", (hash_arxiu,))
    result = mariadb_cursor.fetchone()

    mariadb_cursor.close()
    mariadb_conn.close()

    if result[0] > 0:
        return True
    else:
        return False

# Funció per pujar arxius
def pujar_arxiu(arxiu, ruta_arxiu, url):
    tipo_mime, encoding = mimetypes.guess_type(ruta_arxiu)

    files = { "file": (arxiu, open(ruta_arxiu , "rb"), tipo_mime) }
    headers = {
        "accept": "application/json",
        "x-apikey": "fde521c337a28adcd8f663e416411c1623d14926a92fedf997e3ed1667b68765"
    }

    response = requests.post(url, files=files, headers=headers).json()

    id_scan = response["data"]["id"]

    return id_scan

# Funció per obtenir URL per pujar arxius superiors a 32MB
def obtenir_url_arxiu_gran():
    url = "https://www.virustotal.com/api/v3/files/upload_url"

    headers = {
        "accept": "application/json",
        "x-apikey": "fde521c337a28adcd8f663e416411c1623d14926a92fedf997e3ed1667b68765"
    }

    response = requests.get(url, headers=headers).json()
    url_arxiu_gran = response["data"]

    return url_arxiu_gran

# Funció per emmagatzemar els resultats de l'escaneig dins la BD
def guardar_escaneig_bd(host, user, password, hash_arxiu, id_scan, virus):
    mariadb_conn = mysql.connector.connect(
        host=host, 
        user=user, 
        password=password)
    mariadb_cursor = mariadb_conn.cursor()
    mariadb_cursor.execute("USE evilmarc")


    mariadb_cursor.execute("INSERT INTO FITXERS_ANALITZATS (file_hash, scan_id, infected) VALUES (%s, %s, %s)", (hash_arxiu, id_scan, virus))
    mariadb_conn.commit()

    mariadb_cursor.close()
    mariadb_conn.close()

# Funció per obtenir l'escaneig de l'arxiu mitjançant l'API de Virus Total
def obtenir_escaneig_arxiu(id_scan, host, user, password, hash_arxiu):
    url = f"https://www.virustotal.com/api/v3/files/{hash_arxiu}"

    headers = {
        "accept": "application/json",
        "x-apikey": "fde521c337a28adcd8f663e416411c1623d14926a92fedf997e3ed1667b68765"
    }

    response = requests.get(url, headers=headers).json()

    total_virus = response["data"]["attributes"]["last_analysis_stats"]["malicious"]

    if total_virus > 0:
        guardar_escaneig_bd(host, user, password, hash_arxiu, id_scan, 1)
        return True
    else:
        guardar_escaneig_bd(host, user, password, hash_arxiu, id_scan, 0)
        return False

# Funció per saber si l'arxiu esta infectat dins la BD
def info_arxiu_bd(host, user, password, hash_arxiu):
    mariadb_conn = mysql.connector.connect(
        host=host, 
        user=user, 
        password=password)
    mariadb_cursor = mariadb_conn.cursor()
    mariadb_cursor.execute("USE evilmarc")

    mariadb_cursor.execute("SELECT infected FROM FITXERS_ANALITZATS WHERE file_hash = %s", (hash_arxiu,))

    result = mariadb_cursor.fetchone()
    infected = result[0]

    if infected == 0:
        mariadb_cursor.close()
        mariadb_conn.close()
        return False
    elif infected == 1:
        mariadb_cursor.close()
        mariadb_conn.close()
        return True

# Funció per guardar la informació de l'arxiu pujat dins la BD
def guardar_arxiu_pujat_bd (host, user, password, nom, ruta, hash):

    parts_ruta = ruta.split('/')
    id_usuari = parts_ruta[6].split('_')[1]


    mariadb_conn = mysql.connector.connect(
        host=host, 
        user=user, 
        password=password)
    mariadb_cursor = mariadb_conn.cursor()
    mariadb_cursor.execute("USE evilmarc")

    mariadb_cursor.execute("INSERT INTO ARXIUS_PUJATS (nom_arxiu, ruta, hash, id_usu) VALUES (%s, %s, %s, %s)", (nom, ruta, hash, id_usuari))
    mariadb_conn.commit()


    mariadb_cursor.close()
    mariadb_conn.close()

# Funció MongoDB registrar arxius infectats:
def registrar_fitxers_infectats(nom_arxiu, ruta_arxiu):
    parts_ruta = ruta_arxiu.split('/')
    id_usuari = parts_ruta[6].split('_')[1]

    # Conectar:
    client = MongoClient("mongodb://localhost:27017/")
    db = client["logs"]
    collection = db["fitxers_infectats"]

    # Data
    data_actual = datetime.datetime.now()

    # Creació log
    log = {
        "id_usuari" : id_usuari,
        "nom_arxiu" : nom_arxiu,
        "infectat" : True,
        "ruta_arxiu" : ruta_arxiu,
        "data" : data_actual
    }

    collection.insert_one(log)

# Funció MongoDB registrar arxius pujats:
def registrar_fitxers(nom_arxiu, ruta_arxiu):
    parts_ruta = ruta_arxiu.split('/')
    id_usuari = parts_ruta[6].split('_')[1]

    # Conectar:
    client = MongoClient("mongodb://localhost:27017/")
    db = client["logs"]
    collection = db["fitxers_pujats"]

    # Data
    data_actual = datetime.datetime.now()

    # Creació log
    log = {
        "id_usuari" : id_usuari,
        "nom_arxiu" : nom_arxiu,
        "infectat" : False,
        "ruta_arxiu" : ruta_arxiu,
        "data" : data_actual
    }

    collection.insert_one(log)



host = "localhost"
user = "web"
password = "T5Dk!xq"



if len(sys.argv) != 3:
    exit()

nom_arxiu = sys.argv[1]

creacio_bd(host, user, password)

ruta_carpeta = sys.argv[2]

diccionari_json = sys.stdin.read()
diccionari = json.loads(diccionari_json)


# Verificar la existència del directori
if os.path.exists(ruta_carpeta) and os.path.isdir(ruta_carpeta):
    # Descobrim els fitxers dins del directori.
    for ruta_actual, directoris, arxius in os.walk(ruta_carpeta):
        for arxiu in arxius:

            # Obtenir ruta i hash de l'arxiu 
            if arxiu == nom_arxiu:

                ruta_arxiu = os.path.join(ruta_actual, arxiu)    
                hash_arxiu = obtenir_hash(ruta_arxiu)
        
                if hash_in_bd(host, user, password, hash_arxiu):

                    arxiu_infectat = info_arxiu_bd(host, user, password, hash_arxiu)
                    
                else:
                    # Obtenir tamany de fitxer en MB
                    tamany_arxiu = os.path.getsize(ruta_arxiu) / (1024 * 1024)
                
                    if tamany_arxiu < 650:
                        if tamany_arxiu < 32:
                            url = "https://www.virustotal.com/api/v3/files"

                        else:
                            url = obtenir_url_arxiu_gran()


                        ####################
                        escanejat = False
                        while not escanejat:
                            try:
                                id_scan = pujar_arxiu(arxiu, ruta_arxiu, url)
                                arxiu_infectat = obtenir_escaneig_arxiu(id_scan, host, user, password, hash_arxiu)
                                escanejat = True
                            except:
                                time.sleep(1)
                        ####################

                    else:
                        print(f"L'arxiu {arxiu} pesa massa! No podem escanejar arxius tan grans.")




                if arxiu_infectat:
          
                    diccionari['infectats'].append(f'{nom_arxiu}')
                    print(json.dumps(diccionari))

                    if os.path.exists(ruta_arxiu):
                        os.remove(ruta_arxiu)

                    registrar_fitxers_infectats(nom_arxiu, ruta_arxiu)    
                    

                        
                
                else:

                    diccionari['nets'].append(f'{nom_arxiu}')

                    guardar_arxiu_pujat_bd(host, user, password, arxiu, ruta_arxiu.replace(".tmp", ""), hash_arxiu)

                    registrar_fitxers(nom_arxiu, ruta_arxiu.replace(".tmp", ""))

                    print(json.dumps(diccionari))
