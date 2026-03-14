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

