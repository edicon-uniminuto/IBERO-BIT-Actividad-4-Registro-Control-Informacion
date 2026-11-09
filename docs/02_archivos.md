# Documentación técnica por archivo

Este índice sigue la práctica de explicar individualmente cada archivo del código.

| Archivo | Función | Entradas | Salidas / controles |
|---|---|---|---|
| `conexion.php` | Crea la conexión MySQL mediante PDO | Variables `APP_DB_*`, valores por defecto de Laragon | `PDO` con sentencias nativas y UTF-8 |
| `backend/inicio.php` | Inicializa sesión, incluye módulos y conecta | HTTP entrante | Disponibilidad de `$pdo` o error 503 sin revelar credenciales |
| `backend/funciones.php` | Validación y utilidades transversales | POST, token CSRF, fechas, importe | Mensajes, protección XSS, formato COP, conversión horaria |
| `backend/inventario.php` | SQL de productos + existencias | GET de filtros y POST validado | `SELECT`, `INSERT`, `UPDATE`, `DELETE`, SQL atómico de stock y movimientos |
| `backend/categorias.php` | SQL del mantenimiento de categorías | ID, nombre | Consultas preparadas, FK y cambios en cascada |
| `backend/vistas.php` | Encabezado, menú, pie reutilizable | Título, menú activo | HTML consistente entre pantallas |
| `index.php` | Controlador / vista del CRUD principal | `POST accion`, `GET editar`, `q`, `categoria` | Formulario, tabla, alertas y métricas |
| `categorias.php` | Controlador / vista del CRUD secundario | POST guardar/eliminar, GET editar | Listado de categorías con total asociado |
| `movimientos.php` | Histórico de auditoría básica | HTTP GET | Últimos 100 registros, fechas Bogotá |
| `exportar.php` | Descarga del inventario | HTTP GET | CSV UTF-8 para Excel (separador `;`) |
| `frontend/css/estilos.css` | Presentación sin dependencias externas | Reglas responsive | Escritorio, tablet, móvil |
| `frontend/js/app.js` | Interacción cliente | Envío de formularios de borrado | Confirmación con `window.confirm` |
| `frontend/favicon.svg` | Identidad visual | Navegador | Favicon vectorial |
| `database/01_schema.sql` | DDL relacional | HeidiSQL | BD, PK, FK e índices |
| `database/02_seed.sql` | Datos iniciales del docente | HeidiSQL | Seis categorías, cinco productos |
| `tests/test_validaciones.php` | Pruebas unitarias | PHP CLI | Resultado OK/ERROR y código de salida |
