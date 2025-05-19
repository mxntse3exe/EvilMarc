# 🛡️ Configuració bàsica de pfSense

## Accés web a pfSense

- **Adreça web:** `192.168.1.1`
    
- **Usuari:** `admin`
    
- **Contrasenya:** `Stucom1234`

## 🔌 Connexions de xarxa (totes en _red interna_ excepte pfSense)

| Màquina          | Xarxa                             |
| ---------------- | --------------------------------- |
| Ubuntu Server    | Red interna (LAN)                 |
| Kali Linux (GUI) | Red interna (LAN)                 |
| pfSense          | Adaptador 1: Adaptador Pont (WAN) |
|                  | Adaptador 2: Red interna (LAN)    |

> Accedirem a la interfície web de pfSense des de Kali Linux amb entorn gràfic.


## 🔒 Configuració de **Firewall > Rules > WAN**

![[Pasted image 20250514190438.png]]


**Com configurar-ho:**

### 1. 🔐 Obertura del port SSH per accedir remotament

- **Editar regla de _Firewall_**	- Edit Firewall rule
		![[Pasted image 20250514191114.png]]

	- **Source (origen)**:
		![[Pasted image 20250514191358.png]]

	- **Destination (destinació)**:
		![[Pasted image 20250514191433.png]]

	- **Opcions extra i informació de la regla**
		![[Pasted image 20250514191529.png]]



### 2. 🌐 Redirecció de la WAN cap al servidor intern
-  Edit Firewall Rule
		![[Pasted image 20250514191802.png]]

 - Source
		![[Pasted image 20250514191830.png]]
		

- Destination:
		![[Pasted image 20250514191912.png]]

- Extra options && Rule Information:
		![[Pasted image 20250514192026.png]]
	



### 3. 🌐 Accés a la interfície web de pfSense des de LAN
- Per tal de establir una connexió del pfsense amb interfície gráfica en un directori:
	- Edit Firewall Rule:
		![[Pasted image 20250514192909.png]]
		
	- Source:
	  ![[Pasted image 20250514193054.png]]
	  
	  
	- Destination:
		![[Pasted image 20250514193125.png]]
		
	  
	- Extra options && Rule Information:
		![[Pasted image 20250514193153.png]]


# 🔁 Configurar Port Forward (NAT)

![[Pasted image 20250514193913.png]]


### 1. 🌐 NAT cap a la web del pfSense
- Edit Redirect Entry:
	![[Pasted image 20250514194128.png]]
	![[Pasted image 20250514194142.png]]
	

### 2. 🌐 NAT cap a la web del servidor
![[Pasted image 20250514194340.png]]
![[Pasted image 20250514194356.png]]



### 3. 🔐 NAT per accedir via SSH al servidor de projecte
![[Pasted image 20250514194459.png]]
![[Pasted image 20250514194509.png]]
