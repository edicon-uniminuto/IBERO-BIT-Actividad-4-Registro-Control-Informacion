<?php
/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: backend/public/api.php
   TECNOLOGÍAS: PHP 8, PDO, HTTP, JSON
   DESCRIPCIÓN: API REST de productos; reutiliza la lógica de inventario y valida solicitudes.
   ============================================================
*/
declare(strict_types=1);
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
require_once dirname(__DIR__) . '/conexion.php';
require_once dirname(__DIR__) . '/funciones.php';
require_once dirname(__DIR__) . '/inventario.php';

function responder(int $codigo, array $datos): never {
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
    exit;
}
function errorApi(int $codigo, string $mensaje): never {
    responder($codigo, ['ok' => false, 'error' => $mensaje]);
}

$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
header('Allow: GET, POST, PUT, DELETE, OPTIONS');
if ($metodo === 'OPTIONS') { responder(200, ['ok' => true]); }
if (!in_array($metodo, ['GET','POST','PUT','DELETE'], true)) errorApi(405, 'Método no soportado.');
$id = 0;
if (isset($_GET['id'])) {
    if (!esIdValido($_GET['id']) || (float) $_GET['id'] > 4294967295) errorApi(400, 'ID de producto inválido.');
    $id = (int) $_GET['id'];
}
if (in_array($metodo, ['PUT','DELETE'], true) && !$id) errorApi(400, 'Se requiere el ID del producto.');
if ($metodo === 'POST' && $id) errorApi(400, 'POST no admite un ID existente.');
$datos = [];
if (in_array($metodo, ['POST','PUT'], true)) {
    if (strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0])) !== 'application/json') errorApi(415, 'Usa Content-Type: application/json.');
    try { $objeto = json_decode(file_get_contents('php://input'), false, 32, JSON_THROW_ON_ERROR); }
    catch (JsonException $e) { errorApi(400, 'JSON inválido.'); }
    if (!is_object($objeto)) errorApi(400, 'El cuerpo debe ser un objeto JSON.');
    $datos = (array) $objeto;
    foreach ($datos as $valor) if (!is_string($valor) && !is_int($valor) && !is_float($valor)) errorApi(400, 'Los campos deben ser texto o números.');
}
try {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start(['cookie_httponly'=>true,'use_strict_mode'=>true,'cookie_samesite'=>'Lax']);
    $csrf = tokenCsrf();
    if ($metodo !== 'GET' && !hash_equals($csrf, $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) errorApi(403, 'Token CSRF inválido. Consulta primero la API.');
    session_write_close();
    if ($metodo === 'GET' && ((!is_string($_GET['q'] ?? '')) || (!is_string($_GET['categoria'] ?? '')))) errorApi(400, 'Filtro inválido.');
    try { $pdo = conectarBaseDatos(); }
    catch (PDOException $e) { error_log('[IberoTech API] Conexión: '.$e->getMessage()); errorApi(503, 'Base de datos no disponible. Revisa MySQL en Laragon.'); }
    if ($metodo === 'GET') {
        if ($id) {
            $producto = obtenerProducto($pdo, $id);
            if (!$producto) errorApi(404, 'Producto no encontrado.');
            responder(200, ['ok'=>true,'producto'=>$producto,'csrf'=>$csrf]);
        }
        $q = trim($_GET['q'] ?? '');
        $categoria = trim($_GET['categoria'] ?? '');
        if (longitud($q) > 120 || longitud($categoria) > 60) errorApi(400, 'Filtro demasiado largo.');
        $categorias = obtenerCategorias($pdo);
        $productos = listarProductos($pdo, $q, $categoria);
        // La guía recalcula los indicadores sobre el conjunto filtrado.
        $kpis = ['total_productos'=>count($productos),'total_unidades'=>0,'valor_inventario'=>0,'stock_bajo'=>0];
        foreach ($productos as $p) {
            $stock = (int) $p['stock'];
            $kpis['total_unidades'] += $stock;
            $kpis['valor_inventario'] += (float) $p['precio'] * $stock;
            if ($stock >= 1 && $stock <= 3) ++$kpis['stock_bajo'];
        }
        $kpis['valor_inventario'] = round($kpis['valor_inventario'], 2);
        responder(200, ['ok'=>true,'kpis'=>$kpis,'productos'=>$productos,'categorias'=>$categorias,'csrf'=>$csrf]);
    }
    if ($metodo === 'DELETE') {
        if (!eliminarProducto($pdo, $id)) errorApi(404, 'Producto no encontrado.');
        responder(200, ['ok'=>true,'mensaje'=>'Producto eliminado correctamente.']);
    }
    // Reto +1/-1: PUT parcial explícito; se usa la transacción atómica existente.
    if ($metodo === 'PUT' && array_key_exists('delta', $datos)) {
        if (count($datos) !== 1 || !in_array($datos['delta'], [-1,1], true)) errorApi(400, 'El ajuste debe ser +1 o -1.');
        if (!obtenerProducto($pdo, $id)) errorApi(404, 'Producto no encontrado.');
        if (!ajustarStock($pdo, $id, $datos['delta'])) errorApi(409, 'No se puede ajustar el stock: límite alcanzado o producto inexistente.');
        responder(200, ['ok'=>true,'mensaje'=>'Stock actualizado y movimiento registrado.']);
    }
    $errores = validarProducto($datos, obtenerCategorias($pdo));
    if ($errores) responder(400, ['ok'=>false,'error'=>implode(' ', $errores),'errores'=>$errores]);
    $idNuevo = null;
    if (!guardarProducto($pdo, $datos, $id, $idNuevo)) errorApi(404, 'Producto no encontrado.');
    responder($metodo === 'POST' ? 201 : 200, ['ok'=>true,'mensaje'=>$metodo === 'POST' ? 'Producto creado correctamente.' : 'Producto actualizado correctamente.','id'=>$idNuevo ?? $id]);
} catch (PDOException $e) {
    error_log('[IberoTech API] SQL: '.$e->getMessage());
    if ($e->getCode() === '23000') errorApi(409, 'Conflicto de integridad de datos. Revisa la categoría y los registros.');
    errorApi(500, 'Error al procesar la operación en MySQL.');
} catch (Throwable $e) {
    error_log('[IberoTech API] '.$e->getMessage());
    errorApi(500, 'Error interno de la aplicación.');
}
