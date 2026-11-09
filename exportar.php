<?php
/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: exportar.php
   TECNOLOGÍAS: PHP 8, PDO, CSV
   DESCRIPCIÓN: Exporta el inventario persistido a un archivo CSV para Excel.
   ============================================================
*/
declare(strict_types=1);
require_once __DIR__ . '/backend/inicio.php';
$productos = listarProductos($pdo);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="iberotech_inventario_' . date('Ymd') . '.csv"');
header('X-Content-Type-Options: nosniff');
$salida = fopen('php://output', 'wb');
fwrite($salida, "\xEF\xBB\xBF"); // BOM para Excel en Windows.
fputcsv($salida, ['ID','Producto','Marca','Categoría','Precio COP','Stock','Valor COP'], ';', '"', '');
foreach ($productos as $p) {
    // Evita que Excel interprete fórmulas introducidas en textos por un usuario.
    $seguro = static function (string $s): string {
        return preg_match('/^[=+\-@\t\r]/u', $s) ? "'" . $s : $s;
    };
    fputcsv($salida, [
        (int) $p['id'], $seguro((string) $p['nombre']), $seguro((string) $p['marca']),
        $seguro((string) $p['categoria']), $p['precio'], (int) $p['stock'],
        number_format(((float) $p['precio'] * (int) $p['stock']), 2, '.', ''),
    ], ';', '"', '');
}
fclose($salida);
