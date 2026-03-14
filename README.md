#### Función del plugin

Analiza todos los posts y genera una tabla con:

| Post ID | Post Title | Date | Total Images | ImgBox Images | Other Images | Density Level |

Esto permite detectar:

- posts sobrecargados
- posts con muchas imágenes externas
- posts que podrían causar cuellos de botella


Lo mas importante, cuales son los post que pesan muchisimo mas. 

- Los post que estan quebrados. Que lanzan unposible 404. 
- posts con 100+ imágenes
- posts con carga externa excesiva
- posts que podrían afectar Core Web Vitals
- densidad promedio de tu sitio

(((estoo subirlo a linkedin)))


#### V2.

- Image Weight calculator

*Post A - 120 images - avg 180kb TOTAL WEIGHT: 21 MB  
Esto destruye por completo el TTFB o el DOM load.

Completada:

- Post: 1
- Images: 120
- Estimated Weight: 21 MB
- Density: CRITICAL
- Risk: HIGH

#### Clasificacion de densidad:
NORMAL: 0-20  
MEDIUM: 21-40  
HIGH: 41-80  
CRITICAL: 80+  

#### Peso
LOW: < 5MB  
MEDIUM 5-15MB  
HIGH: 15MB  



****Este plugin solo mide le riesgo estructural
- Muchas imagenes + mucho peso estimado = riesgo de lentitud.

No obtiene el peso exacto de las imagenes....  
LA ESTIMACION PROMEDIO DE LAS IMAGENES ES DE 180kb

Pero hay imagenes que podrian tener estos pesos:  

- 60kb
- 250kb
- 1.5MB


#### V2.1

Ya no "Estimar" el peso de las imagenes. Saber la cantidad exacta de peso. Se usara HEAD request. 

1. Enviar una peticion HEAD al servidor de la imagen
2. Leer el Header: 

HTTP:

- HTTP/1.1 200 OK
- Content-Type: image/jpeg
- Content-Length: 245678 <------ este es el valor

**el cambio se realizara en el weight-estimator.php

#### V2.3

***necesitamos saber cuantos request se le haran al servidor

- Problema: si el post tiene 50 a 200 imagenes, se haran todas juntas. 
- si son 500 post (en realidad son como 40k), entonces se haran 250k peticiones

** se tiene que arreglar usando caching  
** limitar scans  
** escanear por lotes  

Esta web, al ser una biblioteca de imagenes, contiene fechas. Desde 2021. 

#### PREVISIONES

- si una imagen ya fue medida, no hay que volverla a medir. Guardarla en un Wordpress transient o una tabla custom. SE USARA UNA TABLA PROPIA EN SQL

Estructura: 

|    campo   |   tipo   |
|:----------:|:--------:|
| id         | bigint   |
| image_url  | text     | 
| size_bytes | bigint   |
| checked_at | datetime |

- El scaneo bloquea al admin. El post tiene 80 imagenes demoraria unos 30 segundos. Pero si analizamos 1000 post, entonces sera un problema.

#### V2.4

- Se tiene que usar AJAX batch scanning, que va a scanear bloques de 20, 30 o 50 post. 

- si son 40k post, entonces:

AJAX request 1 → posts 1–50  
AJAX request 2 → posts 51–100  
AJAX request 3 → posts 101–150  

**Evitamos el timeout PHP ** congelar wp-admin ** memory overload

Flujo completo del sistema

1️⃣ Usuario hace clic en Scan Posts  
2️⃣ JS inicia proceso AJAX  
3️⃣ Se escanean 50 posts por request  
4️⃣ Se actualiza la barra de progreso  
5️⃣ Continúa hasta terminar  

#### IMplementacion de Id y no offset

request 1 → ID > 0  
procesa → 1..50  
last_id = 50  

request 2 → ID > 50  
procesa → 51..100  
last_id = 100  

request 3 → ID > 100  
procesa → 101..150  

** Evitando que se tenga que ir incrementando de 1 a 50, de 1 a 100, de 1 a 150, de 1 a 200, de 1 a 250,,....de 1 a 39950...

