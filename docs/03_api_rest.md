# Actividad 4 - Registro y control de información: actualización API REST

Se conserva el código anterior, organizado por capas. Se incorpora `frontend/app.html` como frontend HTML independiente del CRUD de productos y `backend/public/api.php` como backend JSON. `frontend/js/rest.js` utiliza fetch y mantiene la interfaz sin recargar. Los módulos complementarios categorías, movimientos y CSV siguen accesibles mediante sus páginas PHP existentes. No se requiere Laravel ni se alteran los scripts SQL.

## Contrato HTTP

| Método | Ruta | Resultado |
|---|---|---|
| GET | backend/public/api.php | Productos, categorías, KPIs filtrados y csrf |
| GET | backend/public/api.php?q=teclado&categoria=Accesorios | Filtros |
| GET | backend/public/api.php?id=4 | Producto individual o 404 |
| POST | backend/public/api.php | Creación, 201 |
| PUT | backend/public/api.php?id=4 | Edición, 200 |
| PUT | backend/public/api.php?id=4 | Ajuste con `{"delta":1}` o `{"delta":-1}`, 200 |
| DELETE | backend/public/api.php?id=4 | Eliminación, 200 |

Cuerpo JSON para crear/editar: `{"nombre":"Producto prueba","marca":"Marca demo","categoria":"Accesorios","precio":"1500.00","stock":"2"}`. Se exige marca y categoría registrada para conservar el esquema existente. Las operaciones de escritura requieren el encabezado `X-CSRF-Token` (valor `csrf` del GET inicial) y la misma cookie de sesión PHP; el navegador lo gestiona automáticamente. Para Thunder Client/Postman conserva la cookie PHPSESSID entre solicitudes y copia el token al encabezado. No se habilita CORS abierto: sirve HTML y API desde el mismo Apache de Laragon.

## Verificación en Laragon

1. Inicia Apache y MySQL. Usa la misma base existente `ibero_tienda`. Solo en una instalación nueva importa `database/01_schema.sql` y `02_seed.sql`.
2. Abre `/crud-ibero/frontend/app.html` (ajusta la carpeta). En F12 > Network verifica GET `backend/public/api.php` (JSON/200).
3. Crea un producto de prueba, verifica POST 201, edítalo (GET individual y PUT 200), ajusta +1/-1 (PUT 200), comprueba el semáforo y KPIs, busca por nombre y elimina el producto (DELETE 200). Comprueba que la navegación no recarga el documento.
4. Envia por cliente HTTP un precio 500 o stock negativo; espera 400. Prueba ID inexistente, espera 404. Prueba petición sin CSRF, espera 403.
5. Consulta `backend/public/movimientos.php` y la base MySQL para observar los registros y la conservación del historial tras la eliminación. No borres datos preexistentes.
6. Comprueba el CSS original. Captura pantallas y solicitudes HTTP reales para actualizar el informe.

## Alcance

La API cubre productos, filtros, KPIs y ajuste de stock, como pide la guía. Las operaciones SQL se reutilizan del backend existente. Los módulos complementarios conservan sus rutas PHP. Las pruebas sin MySQL y aquellas con respuestas simuladas no demuestran persistencia real.

## Revalidación del 14/11/2026
Se probaron operaciones REST y rutas PHP existentes usando una base SQLite temporal mediante un adaptador de pruebas, y se renderizó el HTML con Chromium en 320, 390, 768, 1024 y 1440 px con un puente hacia la API PHP. Los resultados detallados están en `tests/resultado_api_rest.txt`. Las capturas `docs/evidencias/REST_QA_*.png` corresponden a ese entorno aislado, no a Laragon ni MySQL. La prueba final MySQL/PDO del entorno Windows sigue pendiente.

## Reorganización de rutas

Los nuevos resultados de comprobación de rutas y de preservación de evidencias están en `tests/resultado_reorganizacion.txt`. No se reemplazaron las capturas originales.
