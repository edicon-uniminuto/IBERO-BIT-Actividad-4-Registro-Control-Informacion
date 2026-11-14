<?php
/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: backend/vistas.php
   TECNOLOGÍAS: PHP 8, HTML5
   DESCRIPCIÓN: Compone el encabezado, navegación y pie comunes de las vistas.
   ============================================================
*/
declare(strict_types=1);

function cabecera(string $titulo, string $activo): void
{
    $tituloEsc = escapar($titulo);
    $menu = [
        ['../../frontend/app.html', 'rest', 'Inventario API', '▦'],
        ['index.php', 'productos', 'Productos (PHP)', '▦'],
        ['categorias.php', 'categorias', 'Categorías', '◈'],
        ['movimientos.php', 'movimientos', 'Movimientos', '↗'],
    ];
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<meta name="description" content="Gestor de inventario IberoTech con PHP, MySQL y Laragon">';
    echo '<title>' . $tituloEsc . ' | IberoTech</title><link rel="icon" type="image/svg+xml" href="../../frontend/favicon.svg">';
    echo '<link rel="stylesheet" href="../../frontend/css/estilos.css"></head><body>';
    echo '<div class="app-layout"><aside class="sidebar"><a class="logo" href="index.php"><span class="logo-icon">IT</span><span><strong>IberoTech</strong><small>Gestor de inventario</small></span></a>';
    echo '<div class="sidebar-label">ADMINISTRACIÓN</div><nav class="nav" aria-label="Navegación principal">';
    foreach ($menu as [$url, $item, $etiqueta, $icono]) {
        $clase = $activo === $item ? ' activo' : '';
        echo '<a class="nav-link' . $clase . '" href="' . escapar($url) . '"><span class="nav-icon">' . escapar($icono) . '</span>' . escapar($etiqueta) . '</a>';
    }
    echo '</nav><div class="sidebar-note"><span class="note-dot"></span> PHP 8 + MySQL<br><small>Actividad 4 · Diplomado Full Stack</small></div></aside>';
    echo '<main class="main"><header class="topbar"><div class="topbar-crumb">Diplomado Desarrollador Full Stack <span>/</span> ' . $tituloEsc . '</div><div class="topbar-status"><span class="status-dot"></span> Entorno local · Laragon</div></header><div class="page">';
}

function pie(): void
{
    echo '</div><footer class="footer">IberoTech · CRUD académico con PHP, HTML, CSS, JavaScript y MySQL <span>IBERO · Actividad 4</span></footer></main></div>';
    echo '<script src="../../frontend/js/app.js" defer></script></body></html>';
}
