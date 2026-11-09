<?php
/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: backend/categorias.php
   TECNOLOGÍAS: PHP 8, PDO, SQL
   DESCRIPCIÓN: Consultas y operaciones del CRUD de categorías.
   ============================================================
*/
declare(strict_types=1);

function listarCategorias(PDO $pdo): array
{
    return $pdo->query('SELECT c.id, c.nombre, COUNT(p.id) AS productos
       FROM categorias c LEFT JOIN productos p ON p.categoria = c.nombre
       GROUP BY c.id, c.nombre ORDER BY c.nombre')->fetchAll();
}

function obtenerCategoria(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, nombre FROM categorias WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function guardarCategoria(PDO $pdo, string $nombre, int $id): bool
{
    if ($id > 0) {
        // ON UPDATE CASCADE replica el cambio de nombre a productos.
        $stmt = $pdo->prepare('UPDATE categorias SET nombre = ? WHERE id = ?');
        $stmt->execute([$nombre, $id]);
        return $stmt->rowCount() > 0 || obtenerCategoria($pdo, $id) !== null;
    }
    $stmt = $pdo->prepare('INSERT INTO categorias (nombre) VALUES (?)');
    return $stmt->execute([$nombre]);
}

function eliminarCategoria(PDO $pdo, int $id): bool
{
    // ON DELETE RESTRICT impide eliminar categorías asignadas a productos.
    $stmt = $pdo->prepare('DELETE FROM categorias WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->rowCount() > 0;
}
