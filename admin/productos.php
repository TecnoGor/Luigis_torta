<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$busqueda = trim($_GET['q'] ?? '');
$categoriaFiltro = $_GET['categoria'] ?? '';

$sql = 'SELECT p.*, c.nombre as categoria_nombre 
        FROM productos p 
        JOIN categorias c ON p.categoria_id = c.id 
        WHERE 1=1';
$params = [];

if ($busqueda) {
    $sql .= ' AND (p.nombre LIKE ? OR p.descripcion LIKE ?)';
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}
if ($categoriaFiltro) {
    $sql .= ' AND p.categoria_id = ?';
    $params[] = $categoriaFiltro;
}
$sql .= ' ORDER BY p.nombre ASC';
$productos = fetchAll($sql, $params);
$categorias = getCategorias(false);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <h2>&#127856; <?= SITE_NAME ?></h2>
            <p>Panel de Control</p>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php">&#128202; Dashboard</a>
            <a href="productos.php" class="active">&#127856; Productos</a>
            <a href="categorias.php">&#128193; Categorías</a>
            <a href="inventario.php">&#128230; Inventario</a>
            <a href="chat.php">&#128172; Mensajes<?php $chatNoLeidos = getNoLeidosAdminTotal(); if ($chatNoLeidos > 0): ?><span class="badge-chat"><?= $chatNoLeidos ?></span><?php endif; ?></a>
            <a href="../" target="_blank">&#128196; Ver Catálogo</a>
            <a href="logout.php">&#128682; Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <h2>Productos</h2>
            <a href="producto_form.php" class="btn btn-primary" style="width:auto;">+ Nuevo Producto</a>
        </div>

        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>

        <div class="form-card" style="margin-bottom:24px;">
            <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
                <div class="form-group" style="flex:1;min-width:200px;margin-bottom:0;">
                    <label>Buscar</label>
                    <input type="text" name="q" placeholder="Nombre del producto..." value="<?= sanitize($busqueda) ?>">
                </div>
                <div class="form-group" style="min-width:200px;margin-bottom:0;">
                    <label>Categoría</label>
                    <select name="categoria">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $categoriaFiltro == $c['id'] ? 'selected' : '' ?>><?= sanitize($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-secondary btn-sm">Filtrar</button>
            </form>
        </div>

        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr><td colspan="7" style="text-align:center;padding:30px;color:#a1887f;">No se encontraron productos</td></tr>
                    <?php else: ?>
                        <?php foreach ($productos as $p): ?>
                        <tr>
                            <td>
                                <?php if ($p['imagen']): ?>
                                    <img src="<?= UPLOAD_URL . $p['imagen'] ?>" alt="" class="image-preview">
                                <?php else: ?>
                                    <div class="image-preview-placeholder">&#127856;</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= sanitize($p['nombre']) ?></strong>
                                <?php if ($p['destacado']): ?>
                                    <span style="color:#f57c00;font-size:0.75rem;"> &#11088;</span>
                                <?php endif; ?>
                            </td>
                            <td><?= sanitize($p['categoria_nombre']) ?></td>
                            <td><strong><?= formatCurrency($p['precio']) ?></strong></td>
                            <td>
                                <span class="stock-badge <?= $p['stock'] == 0 ? 'out' : ($p['stock'] <= 5 ? 'low' : 'ok') ?>">
                                    <?= $p['stock'] ?> <?= $p['unidad_medida'] ?><?= $p['stock'] != 1 ? 's' : '' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($p['disponible']): ?>
                                    <span style="color:#16a34a;font-weight:600;font-size:0.85rem;">Activo</span>
                                <?php else: ?>
                                    <span style="color:#dc2626;font-weight:600;font-size:0.85rem;">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="producto_form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-secondary">Editar</a>
                                    <a href="producto_form.php?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirmarEliminar('<?= sanitize($p['nombre']) ?>')">Eliminar</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
