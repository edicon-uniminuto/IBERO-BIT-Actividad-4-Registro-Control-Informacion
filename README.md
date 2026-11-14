# IberoTech · Actividad 4 — CRUD PHP + MySQL

**Diplomado Desarrollador Full Stack — Corporación Universitaria Iberoamericana**

**Consigna de la Actividad 4 – Registro y control de información:** «Se entregará el CRUD hecho en clase, con HTML y PHP, subido al repositorio Git». Esta entrega conserva ese CRUD y añade las mejoras propuestas en clase.**

Aplicación académica basada en el **Gestor de Inventario IberoTech** del apartado 8 de la guía de clase *Backend desde Cero: Servidor, HTTP, PHP, Laragon y Aplicación CRUD*. Mantiene las cuatro operaciones sobre `productos` y aplica todos los ejercicios de la sección 9 (excepto el reto final alternativo, porque se eligió conservar el CRUD de clase), más un módulo de categorías, movimientos y exportación CSV.

## Tecnologías

- **Frontend:** HTML5 semántico, CSS3 responsive y JavaScript (sin CDN).
- **Backend:** PHP 8.x y PDO con sentencias preparadas.
- **Persistencia:** MySQL 8 o MariaDB compatible con InnoDB, instalada a través de Laragon para Windows.
- **Herramientas:** Laragon, HeidiSQL, navegador y Git/GitHub.

## Instalación en Laragon (Windows)

1. Descomprime el proyecto y copia la carpeta **`crud-ibero`** completa a `C:\laragon\www\crud-ibero`.
2. Abre Laragon, pulsa **Iniciar Todo** y comprueba que Apache y MySQL estén activos.
3. Abre **HeidiSQL** desde Laragon. Conéctate con las credenciales MySQL de tu equipo.
4. Para **una base nueva**, ejecuta `database/01_schema.sql` y luego `database/02_seed.sql`. Si ya tienes la base de esta entrega, no vuelvas a importar las semillas. **No importes datos de ejemplo sobre una base productiva ni sin revisar las coincidencias por nombre**.
5. Revisa `backend/conexion.php`. Su configuración predeterminada es host `127.0.0.1`, puerto `3306`, base `ibero_tienda`, usuario `root`, contraseña vacía. No almacenes contraseñas reales en GitHub; usa variables de entorno `APP_DB_*` cuando corresponda.
6. Abre **`http://localhost/crud-ibero/`** (no abras `backend/public/index.php` con doble clic). En algunos Laragon también funcionará `http://crud-ibero.test`.
7. Verifica los **cinco productos iniciales**, **34 unidades** y el **valor total $21.670.000**.

> Si hay un fallo de conexión: confirma que MySQL esté ejecutándose, que `ibero_tienda` exista, que PDO MySQL esté habilitado en el PHP de Laragon y que las credenciales sean correctas.

## Alcance de los módulos

| URL | Operación | Descripción |
|---|---|---|
| `/backend/public/index.php` | **CRUD productos** | Crear, listar, buscar, filtrar, editar y borrar; formulario con marca; KPIs del inventario |
| `/backend/public/index.php` (POST) | Ajustes | Botones `+1` y `−1` con SQL atómico, control de stock mínimo y registro histórico |
| `/backend/public/categorias.php` | **CRUD categorías** | Crear, consultar, renombrar y eliminar categorías no utilizadas; renombrado en cascada |
| `/backend/public/movimientos.php` | Consulta | Últimos 100 movimientos de existencias (lectura) |
| `/backend/public/exportar.php` | Exportación | Descarga de inventario en CSV separado por punto y coma con protección básica frente a fórmulas |

## Mejoras de la guía implementadas

1. **Audio y Redes** como nuevas categorías persistentes.
2. **Reglas de negocio:** nombre de 3–120 caracteres, precio mínimo de $1.000 COP, stock entero no negativo y marca obligatoria.
3. **Indicador de agotado, bajo stock (1–3) y disponible (>3)**.
4. **Ajuste rápido +1/−1**, sin permitir inventario negativo.
5. **Campo `marca`** en todo el CRUD y consultas.
6. **Aportes adicionales:** CRUD de categorías, historial de movimientos, descarga CSV, filtros por categoría, tablero de cuatro KPIs, estilos adaptativos, protección CSRF y consultas preparadas.

## Estructura comentada

```text
crud-ibero/
├── .htaccess                    # Entrada y compatibilidad de rutas
├── backend/
│   ├── .htaccess                # Deniega acceso directo a módulos internos
│   ├── conexion.php             # PDO y configuración MySQL
│   ├── inicio.php               # Inicialización y sesión
│   ├── funciones.php            # Validaciones y CSRF
│   ├── inventario.php           # Operaciones SQL y stock
│   ├── categorias.php            # Operaciones SQL de categorías
│   ├── vistas.php                # Plantillas PHP compartidas
│   └── public/                  # Entradas HTTP autorizadas
│       ├── .htaccess
│       ├── api.php               # API REST JSON
│       ├── index.php             # CRUD PHP de productos
│       ├── categorias.php        # CRUD de categorías
│       ├── movimientos.php       # Historial
│       └── exportar.php          # CSV
├── frontend/
│   ├── app.html                  # Interfaz HTML REST
│   ├── css/estilos.css
│   ├── js/app.js
│   ├── js/rest.js
│   └── favicon.svg
├── database/                    # Scripts SQL originales
├── tests/                       # Pruebas y resultados originales
└── docs/                        # Guías y evidencias originales
```

## Verificaciones

Sin MySQL se puede ejecutar:

```powershell
php -l backend/public/index.php
php -l backend/public/categorias.php
php -l backend/funciones.php
php tests/test_validaciones.php
```

**Importante:** el resultado de las pruebas unitarias no sustituye la verificación real de `INSERT`, `SELECT`, `UPDATE` y `DELETE` en HeidiSQL y Laragon.

## Publicación en GitHub

Abre una terminal en `C:\laragon\www\IBERO-BIT-Actividad-4-Registro-Control-Informacion` y ejecuta:

```powershell
git init
git add .
git commit -m "Actividad 4: CRUD IberoTech PHP MySQL"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/TU_REPOSITORIO.git
git push -u origin main
```

Añade al documento de entrega la **URL real del repositorio** y una **captura** en GitHub mostrando `backend/public/index.php`, `backend/`, `frontend/`, `database/` y el README. **GitHub almacena el código; no ejecuta automáticamente PHP/MySQL como un servidor web**.

## Seguridad y limitaciones

- Validación en PHP, consultas PDO preparadas, salida escapada, token CSRF en cambios, comprobaciones del stock en SQL y confirmación antes de borrar.
- Ejemplo **académico local**, sin inicio de sesión ni roles; no desplegarlo públicamente sin autenticación, autorización, HTTPS y configuración segura.
- Apache impide el acceso HTTP a los módulos internos de `backend/`, `database/`, `tests/` y `docs/`; solo permite los cinco archivos de `backend/public/`. En otros servidores hay que configurar el bloqueo de forma equivalente.
- El seed instala datos de ejemplo, no datos comerciales reales; las marcas son ilustrativas.
- El historial conserva movimientos anteriores pero no es un sistema contable completo.

## Actualización: frontend HTML + API REST

La segunda guía de clase se implementa de forma incremental. Abre `http://localhost/crud-ibero/frontend/app.html` (ajusta la carpeta a tu instalación). El `backend/public/index.php` original sigue disponible, igual que categorías, movimientos y CSV. No hay migraciones SQL. El frontend estático consume `backend/public/api.php` mediante `frontend/js/rest.js`. Esta versión conserva CSS propio, sin Bootstrap.

Consulta [la guía de la API y pruebas](docs/04_api_rest.md) para rutas, token CSRF y pruebas de MySQL en Laragon. Los indicadores de la nueva vista se recalculan con los filtros, como indica la guía.

## Organización de rutas

La entrada principal es `frontend/app.html`; la API está en `backend/public/api.php`. Los PHP auxiliares están protegidos en `backend/`. Las rutas antiguas de raíz se redirigen por `.htaccess` (Apache con `mod_rewrite` y `AllowOverride All`), sin duplicar archivos PHP ni HTML. Si no está habilitada la redirección, accede directamente a `frontend/app.html`. En una actualización sobre el repositorio anterior, elimina las copias antiguas de raíz después de trasladarlas, para evitar duplicados.
