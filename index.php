<?php
/*
   ============================================================
   PROYECTO: IberoTech - Actividad 4 - Registro y control de información
   INSTITUCIÓN: BIT - Corporación Universitaria Iberoamericana
   ESTUDIANTE: Edilberto Elias Contreras Sierra
   DOCENTE: Jairo Niño
   ARCHIVO: index.php
   TECNOLOGÍAS: HTML5, PHP 8, PDO
   DESCRIPCIÓN: Pantalla principal y CRUD de productos con indicadores, filtros y ajustes.
   ============================================================
*/
declare(strict_types=1);
require_once __DIR__ . '/backend/inicio.php';
require_once __DIR__ . '/backend/vistas.php';

$errores = [];
$categorias = obtenerCategorias($pdo);
$productoEditar = ['id' => 0, 'nombre' => '', 'marca' => '', 'categoria' => 'Accesorios', 'precio' => '', 'stock' => '0'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarCsrf($_POST['csrf'] ?? null);
    $accion = (string) ($_POST['accion'] ?? '');
    $id = esIdValido($_POST['id'] ?? null) ? (int) $_POST['id'] : 0;

    if ($accion === 'guardar') {
        $productoEditar = [
            'id' => $id, 'nombre' => trim((string) ($_POST['nombre'] ?? '')),
            'marca' => trim((string) ($_POST['marca'] ?? '')),
            'categoria' => trim((string) ($_POST['categoria'] ?? '')),
            'precio' => trim((string) ($_POST['precio'] ?? '')),
            'stock' => trim((string) ($_POST['stock'] ?? '')),
        ];
        $errores = validarProducto($productoEditar, $categorias);
        if (!$errores) {
            try {
                if (guardarProducto($pdo, $productoEditar, $id)) {
                    redirigir('index.php', $id > 0 ? 'actualizado' : 'creado');
                }
                $errores[] = 'No se encontró el producto que intentas actualizar.';
            } catch (PDOException $e) {
                error_log('[IberoTech] Guardar: ' . $e->getMessage());
                $errores[] = 'No se pudo guardar el producto. Revisa los datos y vuelve a intentar.';
            }
        }
    } elseif ($accion === 'eliminar') {
        if ($id > 0 && eliminarProducto($pdo, $id)) {
            redirigir('index.php', 'eliminado');
        }
        $errores[] = 'No se pudo eliminar: el producto ya no existe.';
    } elseif (in_array($accion, ['sumar_stock', 'restar_stock'], true)) {
        $delta = $accion === 'sumar_stock' ? 1 : -1;
        if ($id > 0 && ajustarStock($pdo, $id, $delta)) {
            redirigir('index.php', 'stock');
        }
        $errores[] = 'No se puede ajustar el stock (límite alcanzado o producto inexistente).';
    } else {
        $errores[] = 'La acción solicitada no es válida.';
    }
} elseif (isset($_GET['editar']) && esIdValido($_GET['editar'])) {
    $existente = obtenerProducto($pdo, (int) $_GET['editar']);
    if ($existente) {
        $productoEditar = $existente;
    } else {
        $errores[] = 'El producto solicitado para edición no existe.';
    }
}

$busqueda = trim((string) ($_GET['q'] ?? ''));
$filtroCategoria = trim((string) ($_GET['categoria'] ?? ''));
if (longitud($busqueda) > 120) $busqueda = substr($busqueda, 0, 120);
if (!in_array($filtroCategoria, $categorias, true)) $filtroCategoria = '';
$productos = listarProductos($pdo, $busqueda, $filtroCategoria);
$indicadores = obtenerIndicadores($pdo);
$ok = mensajeExito((string) ($_GET['ok'] ?? ''));

cabecera('Productos', 'productos');
?>
<section class="page-heading">
  <div><div class="eyebrow">CONTROL DE INVENTARIO <span class="eyebrow-line"></span> IBEROTECH</div>
    <h1>Gestión de productos</h1>
    <p>Administra tus productos, consulta existencias y registra movimientos en tiempo real.</p></div>
  <div class="heading-actions"><a class="btn btn-soft" href="categorias.php">Administrar categorías</a><a class="btn btn-blue" href="exportar.php">⇩ Exportar CSV</a></div>
</section>
<?php if ($ok !== ''): ?><div class="alert alert-success" role="status">✓ <?= escapar($ok) ?></div><?php endif; ?>
<?php if ($errores): ?><div class="alert alert-error" role="alert"><strong>Revisa los siguientes datos:</strong><ul><?php foreach ($errores as $e): ?><li><?= escapar($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

<div class="kpi-grid">
  <article class="kpi-card"><div class="kpi-icon blue">▦</div><div><span>Total de productos</span><strong><?= (int) $indicadores['total_productos'] ?></strong><small>Registros en base de datos</small></div></article>
  <article class="kpi-card"><div class="kpi-icon teal">▤</div><div><span>Unidades disponibles</span><strong><?= (int) $indicadores['total_unidades'] ?></strong><small>Existencias acumuladas</small></div></article>
  <article class="kpi-card"><div class="kpi-icon violet">$</div><div><span>Valor del inventario</span><strong><?= escapar(pesos($indicadores['valor_inventario'])) ?></strong><small>Precio × stock</small></div></article>
  <article class="kpi-card"><div class="kpi-icon amber">!</div><div><span>Stock bajo</span><strong><?= (int) $indicadores['stock_bajo'] ?></strong><small>Entre 1 y 3 unidades</small></div></article>
</div>

<div class="content-grid">
  <section class="surface form-surface" id="formulario-producto"><div class="surface-heading"><div><div class="section-kicker">FORMULARIO</div><h2><?= (int) $productoEditar['id'] > 0 ? 'Editar producto #' . (int) $productoEditar['id'] : 'Nuevo producto' ?></h2></div><span class="surface-icon">✎</span></div>
    <p class="form-intro">Completa la información del producto. Todos los campos son obligatorios.</p>
    <form method="post" action="index.php#formulario-producto" class="product-form">
      <input type="hidden" name="accion" value="guardar"><input type="hidden" name="id" value="<?= (int) $productoEditar['id'] ?>"><input type="hidden" name="csrf" value="<?= escapar(tokenCsrf()) ?>">
      <label for="nombre">Nombre del producto</label><input id="nombre" name="nombre" type="text" maxlength="120" minlength="3" required placeholder="Ej. Monitor IPS 27 pulgadas" value="<?= escapar($productoEditar['nombre']) ?>">
      <label for="marca">Marca</label><input id="marca" name="marca" type="text" maxlength="60" minlength="2" required placeholder="Ej. Samsung" value="<?= escapar($productoEditar['marca']) ?>">
      <label for="categoria">Categoría</label><select id="categoria" name="categoria" required><option value="">Selecciona una categoría</option><?php foreach ($categorias as $categoria): ?><option value="<?= escapar($categoria) ?>" <?= $productoEditar['categoria'] === $categoria ? 'selected' : '' ?>><?= escapar($categoria) ?></option><?php endforeach; ?></select>
      <div class="form-two"><div><label for="precio">Precio unitario (COP)</label><input id="precio" type="number" name="precio" min="1000" max="9999999999.99" step="0.01" required placeholder="1000.00" value="<?= escapar($productoEditar['precio']) ?>"></div><div><label for="stock">Stock inicial</label><input id="stock" type="number" name="stock" min="0" max="1000000" step="1" required value="<?= escapar($productoEditar['stock']) ?>"></div></div>
      <button class="btn btn-blue btn-full" type="submit"><?= (int) $productoEditar['id'] > 0 ? 'Guardar cambios' : '+ Registrar producto' ?></button>
      <?php if ((int) $productoEditar['id'] > 0): ?><a class="btn btn-soft btn-full" href="index.php">Cancelar edición</a><?php endif; ?>
      <div class="form-help">ⓘ El precio mínimo permitido es de $1.000 y el stock nunca puede ser negativo.</div>
    </form>
  </section>
  <section class="surface table-surface"><div class="surface-heading"><div><div class="section-kicker">CATÁLOGO</div><h2>Productos registrados</h2></div><span class="records-count"><?= count($productos) ?> resultados</span></div>
    <form class="filters" action="index.php" method="get"><div class="search-field"><span>⌕</span><input aria-label="Buscar producto" type="search" name="q" maxlength="120" placeholder="Buscar nombre, marca o categoría…" value="<?= escapar($busqueda) ?>"></div><select aria-label="Filtrar categoría" name="categoria"><option value="">Todas las categorías</option><?php foreach ($categorias as $c): ?><option value="<?= escapar($c) ?>" <?= $filtroCategoria === $c ? 'selected' : '' ?>><?= escapar($c) ?></option><?php endforeach; ?></select><button class="btn btn-blue" type="submit">Buscar</button><a class="btn btn-soft" href="index.php">Limpiar</a></form>
    <div class="table-scroll"><table><thead><tr><th>PRODUCTO</th><th>CATEGORÍA</th><th>PRECIO</th><th>EXISTENCIAS</th><th>ACCIONES</th></tr></thead><tbody>
    <?php foreach ($productos as $p): ?>
      <tr><td class="product-cell"><div class="product-avatar"><?= escapar(strtoupper(substr((string) $p['nombre'], 0, 1))) ?></div><div><strong><?= escapar($p['nombre']) ?></strong><small><?= escapar($p['marca']) ?> · #<?= (int) $p['id'] ?></small></div></td>
      <td><span class="category-pill"><?= escapar($p['categoria']) ?></span></td><td class="price-cell"><?= escapar(pesos($p['precio'])) ?></td>
      <td><?php if ((int) $p['stock'] === 0): ?><span class="stock-badge out">● Agotado</span><?php elseif ((int) $p['stock'] <= 3): ?><span class="stock-badge low">● Últimas <?= (int) $p['stock'] ?> uds.</span><?php else: ?><span class="stock-badge good">● <?= (int) $p['stock'] ?> disp.</span><?php endif; ?>
        <div class="stock-actions"><form method="post" action="index.php" class="inline"><input type="hidden" name="csrf" value="<?= escapar(tokenCsrf()) ?>"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><input type="hidden" name="accion" value="restar_stock"><button type="submit" aria-label="Restar una unidad a <?= escapar($p['nombre']) ?>" title="Restar una unidad" <?= (int) $p['stock'] <= 0 ? 'disabled' : '' ?>>−1</button></form><form method="post" action="index.php" class="inline"><input type="hidden" name="csrf" value="<?= escapar(tokenCsrf()) ?>"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><input type="hidden" name="accion" value="sumar_stock"><button type="submit" aria-label="Sumar una unidad a <?= escapar($p['nombre']) ?>" title="Sumar una unidad" <?= (int) $p['stock'] >= 1000000 ? 'disabled' : '' ?>>+1</button></form></div>
      </td><td><div class="row-actions"><a class="btn-icon edit" title="Editar" aria-label="Editar <?= escapar($p['nombre']) ?>" href="index.php?editar=<?= (int) $p['id'] ?>#formulario-producto">✎</a><form method="post" action="index.php" class="inline" data-confirm="¿Eliminar este producto? Los movimientos históricos se conservarán."><input type="hidden" name="csrf" value="<?= escapar(tokenCsrf()) ?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button class="btn-icon delete" type="submit" title="Eliminar" aria-label="Eliminar <?= escapar($p['nombre']) ?>">✕</button></form></div></td></tr>
    <?php endforeach; ?>
    <?php if (!$productos): ?><tr><td colspan="5" class="empty-state">No se encontraron productos. Modifica los filtros o registra uno nuevo.</td></tr><?php endif; ?>
    </tbody></table></div>
    <div class="table-foot"><span>Se muestran <?= count($productos) ?> registros según los filtros.</span><a href="movimientos.php">Ver historial de movimientos ↗</a></div>
  </section>
</div>
<?php pie(); ?>
