# Instalación paso a paso — Windows + Laragon

1. Ubica la carpeta completa en `C:\laragon\www\crud-ibero` (la ruta debe contener las carpetas `backend/` y `frontend/`).
2. Inicia Laragon > **Iniciar Todo**. Si los puertos 80 o 3306 están ocupados, resuelve el conflicto antes de seguir.
3. Abre Laragon > **Base de datos / HeidiSQL**.
4. Ejecuta `database/01_schema.sql` en una pestaña SQL. Deben aparecer las tablas `categorias`, `productos` y `movimientos_stock` dentro de `ibero_tienda`.
5. Ejecuta `database/02_seed.sql` (categorías: Computadores, Monitores, Accesorios, Almacenamiento, Audio y Redes; productos: cinco de la clase).
6. Verifica con:

```sql
USE ibero_tienda;
SHOW TABLES;
SELECT id, nombre, marca, categoria, precio, stock FROM productos ORDER BY id;
SELECT COUNT(*) AS productos, SUM(stock) AS unidades,
       SUM(stock * precio) AS valor_cop FROM productos;
SELECT * FROM categorias ORDER BY nombre;
```

7. Visita `http://localhost/crud-ibero/`. Debes ver el encabezado IberoTech, el formulario y los productos.
8. Realiza las pruebas de `docs/04_api_rest.md`. Guarda las capturas en `docs/evidencias/`.

Si la conexión falla, revisa `backend/conexion.php` o configura variables de entorno `APP_DB_HOST`, `APP_DB_PORT`, `APP_DB_NAME`, `APP_DB_USER`, `APP_DB_PASSWORD`.

## Otras comprobaciones útiles

```sql
-- Comprueba cambios registrados tras +1/-1 y ediciones:
SELECT * FROM movimientos_stock ORDER BY id DESC;
-- Comprueba el bloqueo por FK antes de borrar categorías utilizadas:
SELECT categoria, COUNT(*) FROM productos GROUP BY categoria;
```

## Actualización REST de la entrega existente

Si ya tienes la base `ibero_tienda`, no vuelvas a importar los scripts SQL. Abre `http://localhost/crud-ibero/frontend/app.html` (ajusta el directorio a tu instalación). Consulta `docs/04_api_rest.md` para comprobar la API y sus operaciones. La versión `backend/public/index.php` permanece disponible.

## Organización de rutas

La entrada principal es `frontend/app.html`; la API está en `backend/public/api.php`. Los PHP auxiliares están protegidos en `backend/`. Las rutas antiguas de raíz se redirigen por `.htaccess` (Apache con `mod_rewrite` y `AllowOverride All`), sin duplicar archivos PHP ni HTML. Si no está habilitada la redirección, accede directamente a `frontend/app.html`. En una actualización sobre el repositorio anterior, elimina las copias antiguas de raíz después de trasladarlas, para evitar duplicados.
