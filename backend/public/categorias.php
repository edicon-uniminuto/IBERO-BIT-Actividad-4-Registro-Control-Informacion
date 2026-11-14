<?php
/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: backend/public/categorias.php
   TECNOLOGÍAS: HTML5, PHP 8, PDO
   DESCRIPCIÓN: Pantalla y control de altas, consultas, edición y eliminación de categorías.
   ============================================================
*/
declare(strict_types=1);
require_once dirname(__DIR__) . '/inicio.php';
require_once dirname(__DIR__) . '/vistas.php';

$errores = [];
$editando = ['id' => 0, 'nombre' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf($_POST['csrf'] ?? null);
    $accion = (string) ($_POST['accion'] ?? '');
    $id = esIdValido($_POST['id'] ?? null) ? (int) $_POST['id'] : 0;
    if ($accion === 'guardar_categoria') {
        $editando = ['id' => $id, 'nombre' => trim((string) ($_POST['nombre'] ?? ''))];
        $errores = validarCategoria($editando['nombre']);
        if (!$errores) {
            try {
                if (guardarCategoria($pdo, $editando['nombre'], $id)) {
                    redirigir('categorias.php', $id > 0 ? 'categoria_actualizada' : 'categoria_creada');
                }
                $errores[] = 'No se encontró la categoría para actualizar.';
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $errores[] = 'Ya existe una categoría con ese nombre.';
                } else {
                    error_log('[IberoTech] Categoría guardar: ' . $e->getMessage());
                    $errores[] = 'No se pudo guardar la categoría.';
                }
            }
        }
    } elseif ($accion === 'eliminar_categoria') {
        try {
            if ($id > 0 && eliminarCategoria($pdo, $id)) redirigir('categorias.php', 'categoria_eliminada');
            $errores[] = 'La categoría no existe.';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errores[] = 'No se puede eliminar una categoría con productos asociados.';
            } else {
                error_log('[IberoTech] Categoría eliminar: ' . $e->getMessage());
                $errores[] = 'No se pudo eliminar la categoría.';
            }
        }
    } else $errores[] = 'Acción no válida.';
} elseif (isset($_GET['editar']) && esIdValido($_GET['editar'])) {
    $encontrada = obtenerCategoria($pdo, (int) $_GET['editar']);
    if ($encontrada) $editando = $encontrada;
    else $errores[] = 'Categoría no encontrada.';
}
$categorias = listarCategorias($pdo);
$ok = mensajeExito((string) ($_GET['ok'] ?? ''));
cabecera('Categorías', 'categorias');
?>
<section class="page-heading"><div><div class="eyebrow">ORGANIZACIÓN <span class="eyebrow-line"></span> CATÁLOGO</div><h1>Categorías de productos</h1><p>Clasifica tu inventario y administra las opciones disponibles en el formulario principal.</p></div><a class="btn btn-soft" href="index.php">← Volver a productos</a></section>
<?php if ($ok): ?><div class="alert alert-success" role="status">✓ <?= escapar($ok) ?></div><?php endif; ?>
<?php if ($errores): ?><div class="alert alert-error" role="alert"><ul><?php foreach ($errores as $error): ?><li><?= escapar($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="content-grid category-grid"><section class="surface form-surface"><div class="surface-heading"><div><div class="section-kicker">MANTENIMIENTO</div><h2><?= (int) $editando['id'] > 0 ? 'Editar categoría' : 'Nueva categoría' ?></h2></div><span class="surface-icon">◈</span></div>
<form class="product-form" method="post" action="categorias.php"><input type="hidden" name="csrf" value="<?= escapar(tokenCsrf()) ?>"><input type="hidden" name="accion" value="guardar_categoria"><input type="hidden" name="id" value="<?= (int) $editando['id'] ?>"><label for="nombreCategoria">Nombre de la categoría</label><input id="nombreCategoria" name="nombre" type="text" maxlength="60" minlength="3" required placeholder="Ej. Periféricos" value="<?= escapar($editando['nombre']) ?>"><button class="btn btn-blue btn-full" type="submit"><?= (int) $editando['id'] > 0 ? 'Guardar cambios' : '+ Crear categoría' ?></button><?php if ((int) $editando['id'] > 0): ?><a href="categorias.php" class="btn btn-soft btn-full">Cancelar edición</a><?php endif; ?></form>
<div class="info-panel">Al renombrar una categoría, sus productos se actualizan automáticamente. Una categoría con productos asociados no se puede eliminar.</div></section>
<section class="surface table-surface"><div class="surface-heading"><div><div class="section-kicker">GESTIÓN</div><h2>Categorías registradas</h2></div><span class="records-count"><?= count($categorias) ?> categorías</span></div>
<div class="table-scroll"><table><thead><tr><th>CATEGORÍA</th><th>PRODUCTOS</th><th>ACCIONES</th></tr></thead><tbody><?php foreach ($categorias as $c): ?><tr><td><strong><?= escapar($c['nombre']) ?></strong></td><td><span class="category-pill"><?= (int) $c['productos'] ?> asociados</span></td><td><div class="row-actions"><a class="btn-icon edit" href="categorias.php?editar=<?= (int) $c['id'] ?>" aria-label="Editar <?= escapar($c['nombre']) ?>">✎</a><form class="inline" method="post" action="categorias.php" data-confirm="¿Eliminar la categoría? Solo es posible si no contiene productos."><input type="hidden" name="csrf" value="<?= escapar(tokenCsrf()) ?>"><input type="hidden" name="accion" value="eliminar_categoria"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>"><button class="btn-icon delete" type="submit" aria-label="Eliminar <?= escapar($c['nombre']) ?>" <?= (int) $c['productos'] > 0 ? 'disabled title="Tiene productos asociados"' : '' ?>>✕</button></form></div></td></tr><?php endforeach; ?></tbody></table></div></section></div>
<?php pie(); ?>
