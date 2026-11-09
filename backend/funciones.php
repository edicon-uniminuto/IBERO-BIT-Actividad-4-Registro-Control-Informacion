<?php
/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: backend/funciones.php
   TECNOLOGÍAS: PHP 8
   DESCRIPCIÓN: Funciones de validación, formato, filtrado y seguridad CSRF.
   ============================================================
*/
declare(strict_types=1);

function escapar(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function longitud(string $texto): int
{
    return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
}

function tokenCsrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verificarCsrf(?string $token): void
{
    if (!is_string($token) || !hash_equals(tokenCsrf(), $token)) {
        http_response_code(403);
        exit('Solicitud rechazada: token de seguridad no válido. Actualiza la página.');
    }
}

function esEnteroNoNegativo(string $texto): bool
{
    return preg_match('/^\d+$/D', $texto) === 1 && (float) $texto <= PHP_INT_MAX;
}

/** Precio mínimo del reto: $1.000 COP; máximo compatible con DECIMAL(12,2). */
function validarProducto(array $datos, array $categorias): array
{
    $errores = [];
    $nombre = trim((string) ($datos['nombre'] ?? ''));
    $marca = trim((string) ($datos['marca'] ?? ''));
    $categoria = trim((string) ($datos['categoria'] ?? ''));
    $precio = trim((string) ($datos['precio'] ?? ''));
    $stock = trim((string) ($datos['stock'] ?? ''));

    if (longitud($nombre) < 3 || longitud($nombre) > 120) {
        $errores[] = 'El nombre debe tener entre 3 y 120 caracteres.';
    }
    if (longitud($marca) < 2 || longitud($marca) > 60) {
        $errores[] = 'La marca debe tener entre 2 y 60 caracteres.';
    }
    if (!in_array($categoria, $categorias, true)) {
        $errores[] = 'Selecciona una categoría registrada.';
    }
    if (!preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', $precio) || (float) $precio < 1000 || (float) $precio > 9999999999.99) {
        $errores[] = 'El precio debe ser numérico y de al menos $1.000 COP (hasta 2 decimales).';
    }
    if (!esEnteroNoNegativo($stock) || (float) $stock > 1000000) {
        $errores[] = 'El stock debe ser un entero entre 0 y 1.000.000.';
    }
    return $errores;
}

function validarCategoria(string $nombre): array
{
    $nombre = trim($nombre);
    return longitud($nombre) < 3 || longitud($nombre) > 60
        ? ['La categoría debe tener entre 3 y 60 caracteres.']
        : [];
}

function esIdValido(mixed $id): bool
{
    return is_scalar($id) && preg_match('/^[1-9]\d*$/D', (string) $id) === 1
        && (float) $id <= PHP_INT_MAX;
}

function redirigir(string $pagina, string $codigo): never
{
    header('Location: ' . $pagina . '?ok=' . rawurlencode($codigo), true, 303);
    exit;
}

function mensajeExito(string $codigo): string
{
    return [
        'creado' => 'Producto creado correctamente.',
        'actualizado' => 'Producto actualizado correctamente.',
        'eliminado' => 'Producto eliminado correctamente.',
        'stock' => 'Stock actualizado y movimiento registrado.',
        'categoria_creada' => 'Categoría creada correctamente.',
        'categoria_actualizada' => 'Categoría actualizada correctamente.',
        'categoria_eliminada' => 'Categoría eliminada correctamente.',
    ][$codigo] ?? '';
}

function pesos(mixed $importe): string
{
    return '$' . number_format((float) $importe, 0, ',', '.');
}

function fechaBogota(string $utc): string
{
    $fecha = new DateTimeImmutable($utc, new DateTimeZone('UTC'));
    return $fecha->setTimezone(new DateTimeZone('America/Bogota'))->format('d/m/Y H:i');
}
