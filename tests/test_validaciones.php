<?php
/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: tests/test_validaciones.php
   TECNOLOGÍAS: PHP 8, pruebas de CLI
   DESCRIPCIÓN: Verifica las validaciones de negocio y utilidades sin necesidad de MySQL.
   ============================================================
*/
declare(strict_types=1);
require_once dirname(__DIR__) . '/backend/funciones.php';

$categorias = ['Accesorios','Computadores','Audio','Redes'];
$valido = ['nombre'=>'Mouse Vertical', 'marca'=>'Logitech', 'categoria'=>'Accesorios', 'precio'=>'68000.00', 'stock'=>'15'];
$pruebas = [
    'Producto válido' => validarProducto($valido, $categorias) === [],
    'Nombre menor a 3 caracteres' => count(validarProducto([...$valido, 'nombre'=>'AB'], $categorias)) > 0,
    'Precio inferior a 1000' => count(validarProducto([...$valido, 'precio'=>'999'], $categorias)) > 0,
    'Stock negativo' => count(validarProducto([...$valido, 'stock'=>'-1'], $categorias)) > 0,
    'Stock decimal' => count(validarProducto([...$valido, 'stock'=>'2.4'], $categorias)) > 0,
    'Categoría desconocida' => count(validarProducto([...$valido, 'categoria'=>'Falsa'], $categorias)) > 0,
    'Marca vacía' => count(validarProducto([...$valido, 'marca'=>''], $categorias)) > 0,
    'Precio con demasiados decimales' => count(validarProducto([...$valido, 'precio'=>'1000.999'], $categorias)) > 0,
    'Validación nombre de categoría' => validarCategoria('A') !== [],
    'ID numérico positivo' => esIdValido('42') && !esIdValido('-7') && !esIdValido('2 OR 1=1'),
    'Escapado HTML' => escapar('<script>') === '&lt;script&gt;',
    'Importe formateado' => pesos(2450000) === '$2.450.000',
    'Zona horaria Bogotá' => fechaBogota('2026-11-08 20:00:00') === '08/11/2026 15:00',
];
$fallos = 0;
foreach ($pruebas as $nombre => $pasa) {
    echo ($pasa ? '[OK] ' : '[ERROR] ') . $nombre . PHP_EOL;
    if (!$pasa) ++$fallos;
}
echo PHP_EOL . 'Resultado: ' . (count($pruebas) - $fallos) . '/' . count($pruebas) . ' pruebas superadas.' . PHP_EOL;
exit($fallos > 0 ? 1 : 0);
