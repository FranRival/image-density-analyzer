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