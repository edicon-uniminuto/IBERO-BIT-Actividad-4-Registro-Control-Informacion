<?php
/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: backend/public/movimientos.php
   TECNOLOGÍAS: HTML5, PHP 8, PDO
   DESCRIPCIÓN: Consulta de los movimientos de stock registrados en la base de datos.
   ============================================================
*/
declare(strict_types=1);
require_once dirname(__DIR__) . '/inicio.php';
require_once dirname(__DIR__) . '/vistas.php';
$consulta = $pdo->query('SELECT id, producto_nombre, variacion, motivo, creado_en FROM movimientos_stock ORDER BY id DESC LIMIT 100');
$movimientos = $consulta->fetchAll();
cabecera('Movimientos', 'movimientos');
?>
<section class="page-heading"><div><div class="eyebrow">TRAZABILIDAD <span class="eyebrow-line"></span> KARDEX BÁSICO</div><h1>Historial de movimientos</h1><p>Consulta los últimos 100 cambios de stock registrados por la aplicación.</p></div><a class="btn btn-soft" href="index.php">← Volver a productos</a></section>
<div class="surface table-surface full-width"><div class="surface-heading"><div><div class="section-kicker">AUDITORÍA DE INVENTARIO</div><h2>Movimientos recientes</h2></div><span class="records-count"><?= count($movimientos) ?> registros</span></div><div class="table-scroll"><table><thead><tr><th>FECHA (BOGOTÁ)</th><th>PRODUCTO</th><th>VARIACIÓN</th><th>MOTIVO</th></tr></thead><tbody>
<?php foreach ($movimientos as $m): ?><tr><td><?= escapar(fechaBogota($m['creado_en'])) ?></td><td><strong><?= escapar($m['producto_nombre']) ?></strong></td><td><span class="stock-badge <?= (int) $m['variacion'] > 0 ? 'good' : 'out' ?>"><?= (int) $m['variacion'] > 0 ? '+' : '' ?><?= (int) $m['variacion'] ?> uds.</span></td><td><?= escapar($m['motivo']) ?></td></tr><?php endforeach; ?>
<?php if (!$movimientos): ?><tr><td colspan="4" class="empty-state">Aún no hay movimientos. Usa +1, −1 o registra un producto con stock.</td></tr><?php endif; ?></tbody></table></div><div class="table-foot">La eliminación de un producto no borra el historial: se conserva su nombre.</div></div>
<?php pie(); ?>
