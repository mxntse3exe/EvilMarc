import os
import shutil
import requests
import mysql.connector
import hashlib
import mimetypes


# Connexió i creació BD
def creacio_bd(host, user, password):
    try:
        mariadb_conn = mysql.connector.connect(
            host=host, 
            user=user, 
            password=password)

        mariadb_cursor = mariadb_conn.cursor()

        mariadb_cursor.execute("CREATE DATABASE IF NOT EXISTS virustotal")
        mariadb_cursor.execute("USE virustotal")
        mariadb_cursor.execute("""
            CREATE TABLE IF NOT EXISTS fitxers (
                file_hash VARCHAR(64) PRIMARY KEY,
                scan_id VARCHAR(60),
                infected BOOLEAN
            )
        """)
        mariadb_cursor.close()
        mariadb_conn.close()

    except:
        print("La connexió amb la base de dades no s'ha pogut establir correctament. Si us plau, torna-ho a intentar.")
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
    mariadb_cursor.execute("USE virustotal")

    mariadb_cursor.execute("SELECT COUNT(*) FROM fitxers WHERE file_hash = %s", (hash_arxiu,))
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
    print(id_scan)

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
    mariadb_cursor.execute("USE virustotal")


    mariadb_cursor.execute("INSERT INTO fitxers (file_hash, scan_id, infected) VALUES (%s, %s, %s)", (hash_arxiu, id_scan, virus))
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
    mariadb_cursor.execute("USE virustotal")

    mariadb_cursor.execute("SELECT infected FROM fitxers WHERE file_hash = %s", (hash_arxiu,))

    for (infected) in mariadb_cursor:
        if infected == 0:
            mariadb_cursor.close()
            mariadb_conn.close()
            return False
        elif infected == 1:
            mariadb_cursor.close()
            mariadb_conn.close()
            return True


# Inici programa

host = input("Introdueixi el host de la base de dades: ")
user = input("Introdueixi l'usuari de la base de dades: ")
password = input("Introdueixi la contrasenya de la base de dades: ")

creacio_bd(host, user, password)
ruta_carpeta = input ("Introdueix la ruta del directori que desitja analitzar: ")

# Verificar la existència del directori
if os.path.exists(ruta_carpeta) and os.path.isdir(ruta_carpeta):
    print("\nCarpeta trobada, procedim a l'escaneig. \n")
    # Descobrim els fitxers dins del directori.
    for ruta_actual, directoris, arxius in os.walk(ruta_carpeta):
        for arxiu in arxius:
            # Obtenir ruta i hash de l'arxiu 
            ruta_arxiu = os.path.join(ruta_actual, arxiu)    
            hash_arxiu = obtenir_hash(ruta_arxiu)
    
            print(f"Analitzant l'arxiu {arxiu}...")
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

                    try:
                        id_scan = pujar_arxiu(arxiu, ruta_arxiu, url)
                        arxiu_infectat = obtenir_escaneig_arxiu(id_scan, host, user, password, hash_arxiu)
                    except:
                        print(f"No s'ha pogut analitzar l'arxiu {arxiu} amb l'API de VirusTotal. Si us plau, torna-ho a intentar.")

                else:
                    print(f"L'arxiu {arxiu} pesa massa! No podem escanejar arxius tan grans.")

            

            if arxiu_infectat:
                print(f"L'arxiu {arxiu} està infectat!")
                ruta_arxiu_final = os.path.join(ruta_carpeta, "arxius_infectats")
                if not os.path.exists(ruta_arxiu_final):
                    os.mkdir(ruta_arxiu_final)
                
                try:
                    shutil.move(ruta_arxiu, ruta_arxiu_final)
                except:
                    print(f"L'arxiu {arxiu} ja existeix dins la carpeta {ruta_arxiu_final}.")


            else:
                print(f"L'arxiu {arxiu} no està infectat!")
                ruta_arxiu_final = os.path.join(ruta_carpeta, "arxius_no_infectats")
                if not os.path.exists(ruta_arxiu_final):
                    os.mkdir(ruta_arxiu_final)
                
                try:
                    shutil.move(ruta_arxiu, ruta_arxiu_final)
                except:
                    print(f"L'arxiu {arxiu} ja existeix dins la carpeta {ruta_arxiu_final}.")

            print("")
                
            
    print ("El programa ha finalitzat, no queden més arxius per analitzar dins el directori.")

else:
    print("Carpeta no trobada.")