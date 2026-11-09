<?php
/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: backend/inventario.php
   TECNOLOGÍAS: PHP 8, PDO, SQL
   DESCRIPCIÓN: Operaciones CRUD de productos, métricas y registro de movimientos.
   ============================================================
*/
declare(strict_types=1);

function obtenerCategorias(PDO $pdo): array
{
    return $pdo->query('SELECT nombre FROM categorias ORDER BY nombre ASC')->fetchAll(PDO::FETCH_COLUMN);
}

function obtenerProducto(PDO $pdo, int $id): ?array
{
    $consulta = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
    $consulta->execute([$id]);
    return $consulta->fetch() ?: null;
}

function listarProductos(PDO $pdo, string $busqueda = '', string $filtroCategoria = ''): array
{
    $sql = 'SELECT p.* FROM productos p WHERE 1 = 1';
    $parametros = [];
    if ($busqueda !== '') {
        $sql .= ' AND (p.nombre LIKE ? OR p.marca LIKE ? OR p.categoria LIKE ?)';
        $t = '%' . $busqueda . '%';
        array_push($parametros, $t, $t, $t);
    }
    if ($filtroCategoria !== '') {
        $sql .= ' AND p.categoria = ?';
        $parametros[] = $filtroCategoria;
    }
    $sql .= ' ORDER BY p.id DESC';
    $consulta = $pdo->prepare($sql);
    $consulta->execute($parametros);
    return $consulta->fetchAll();
}

function obtenerIndicadores(PDO $pdo): array
{
    return $pdo->query('SELECT COUNT(*) AS total_productos,
      COALESCE(SUM(stock), 0) AS total_unidades,
      COALESCE(SUM(precio * stock), 0) AS valor_inventario,
      COALESCE(SUM(CASE WHEN stock > 0 AND stock <= 3 THEN 1 ELSE 0 END), 0) AS stock_bajo
      FROM productos')->fetch();
}

function guardarProducto(PDO $pdo, array $datos, int $id): bool
{
    $nombre = trim((string) $datos['nombre']);
    $marca = trim((string) $datos['marca']);
    $categoria = trim((string) $datos['categoria']);
    $precio = trim((string) $datos['precio']);
    $stock = (int) $datos['stock'];

    if ($id > 0) {
        $pdo->beginTransaction();
        try {
            $actual = $pdo->prepare('SELECT id, nombre, stock FROM productos WHERE id = ? FOR UPDATE');
            $actual->execute([$id]);
            $anterior = $actual->fetch();
            if (!$anterior) {
                $pdo->rollBack();
                return false;
            }
            $stmt = $pdo->prepare('UPDATE productos SET nombre=?, marca=?, categoria=?, precio=?, stock=? WHERE id=?');
            $stmt->execute([$nombre, $marca, $categoria, $precio, $stock, $id]);
            $delta = $stock - (int) $anterior['stock'];
            if ($delta !== 0) {
                registrarMovimiento($pdo, $id, $nombre, $delta, 'Edición de producto');
            }
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO productos (nombre, marca, categoria, precio, stock) VALUES (?,?,?,?,?)');
        $stmt->execute([$nombre, $marca, $categoria, $precio, $stock]);
        $idNuevo = (int) $pdo->lastInsertId();
        if ($stock > 0) registrarMovimiento($pdo, $idNuevo, $nombre, $stock, 'Stock inicial');
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

function eliminarProducto(PDO $pdo, int $id): bool
{
    $consulta = $pdo->prepare('DELETE FROM productos WHERE id = ?');
    $consulta->execute([$id]);
    return $consulta->rowCount() > 0;
}

function registrarMovimiento(PDO $pdo, int $id, string $nombre, int $delta, string $motivo): void
{
    $consulta = $pdo->prepare('INSERT INTO movimientos_stock (producto_id, producto_nombre, variacion, motivo) VALUES (?,?,?,?)');
    $consulta->execute([$id, $nombre, $delta, $motivo]);
}

/** Ajuste atómico: resta solo con stock positivo, nunca queda negativo. */
function ajustarStock(PDO $pdo, int $id, int $delta): bool
{
    if (!in_array($delta, [-1, 1], true)) return false;
    $pdo->beginTransaction();
    try {
        $consulta = $pdo->prepare('SELECT nombre, stock FROM productos WHERE id = ? FOR UPDATE');
        $consulta->execute([$id]);
        $producto = $consulta->fetch();
        if (!$producto || ((int) $producto['stock'] === 0 && $delta < 0) ||
            ((int) $producto['stock'] >= 1000000 && $delta > 0)) {
            $pdo->rollBack();
            return false;
        }
        $sql = $delta > 0
            ? 'UPDATE productos SET stock = stock + 1 WHERE id = ? AND stock < 1000000'
            : 'UPDATE productos SET stock = stock - 1 WHERE id = ? AND stock > 0';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        if (!$stmt->rowCount()) {
            $pdo->rollBack();
            return false;
        }
        registrarMovimiento($pdo, $id, $producto['nombre'], $delta, 'Ajuste rápido ' . ($delta > 0 ? '+1' : '-1'));
        $pdo->commit();
        return true;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
