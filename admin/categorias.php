<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

// Eliminar categoría
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $cat = getCategoria($id);
    if ($cat) {
        $tieneProductos = fetchOne('SELECT COUNT(*) as total FROM productos WHERE categoria_id = ?', [$id])['total'];
        if ($tieneProductos > 0) {
            flash('success', 'No se puede eliminar: la categoría tiene productos asociados. Mueve o elimina los productos primero.');
        } else {
            execute('DELETE FROM categorias WHERE id = ?', [$id]);
            flash('success', 'Categoría eliminada correctamente');
        }
    }
    header('Location: ' . ADMIN_URL . '/categorias.php');
    exit;
}

// Crear/Actualizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $orden = (int)($_POST['orden'] ?? 0);
    $activa = isset($_POST['activa']) ? 1 : 0;
    $editId = $_POST['edit_id'] ?? null;

    if (empty($nombre)) {
        flash('success', 'El nombre de la categoría es requerido');
    } else {
        if ($editId) {
            execute('UPDATE categorias SET nombre=?, descripcion=?, orden=?, activa=? WHERE id=?',
                [$nombre, $descripcion, $orden, $activa, $editId]);
            flash('success', 'Categoría actualizada correctamente');
        } else {
            insertId('INSERT INTO categorias (nombre, descripcion, orden, activa) VALUES (?,?,?,?)',
                [$nombre, $descripcion, $orden, $activa]);
            flash('success', 'Categoría creada correctamente');
        }
    }
    header('Location: ' . ADMIN_URL . '/categorias.php');
    exit;
}

$categorias = getCategorias(false);
$editCat = null;
if (isset($_GET['edit'])) {
    $editCat = getCategoria((int)$_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías - <?= SITE_NAME ?></title>
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
            <a href="productos.php">&#127856; Productos</a>
            <a href="categorias.php" class="active">&#128193; Categorías</a>
            <a href="inventario.php">&#128230; Inventario</a>
            <a href="chat.php">&#128172; Mensajes<?php $chatNoLeidos = getNoLeidosAdminTotal(); if ($chatNoLeidos > 0): ?><span class="badge-chat"><?= $chatNoLeidos ?></span><?php endif; ?></a>
            <a href="../" target="_blank">&#128196; Ver Catálogo</a>
            <a href="logout.php">&#128682; Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <h2>Categorías</h2>
        </div>

        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>

        <div class="form-card" style="margin-bottom:24px;">
            <h3><?= $editCat ? 'Editar Categoría' : 'Nueva Categoría' ?></h3>
            <form method="POST">
                <?php if ($editCat): ?>
                    <input type="hidden" name="edit_id" value="<?= $editCat['id'] ?>">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre *</label>
                        <input type="text" name="nombre" required value="<?= sanitize($editCat['nombre'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Orden</label>
                        <input type="number" name="orden" value="<?= $editCat['orden'] ?? 0 ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="2"><?= sanitize($editCat['descripcion'] ?? '') ?></textarea>
                </div>
                <div class="checkbox-group" style="margin-bottom:16px;">
                    <input type="checkbox" name="activa" id="activa" value="1" <?= ($editCat['activa'] ?? 1) ? 'checked' : '' ?>>
                    <label for="activa">Activa (visible en catálogo)</label>
                </div>
                <div style="display:flex;gap:12px;">
                    <button type="submit" class="btn btn-success" style="width:auto;"><?= $editCat ? '&#10004; Actualizar' : '&#10004; Crear' ?></button>
                    <?php if ($editCat): ?>
                        <a href="categorias.php" class="btn btn-secondary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Orden</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $c): ?>
                    <tr>
                        <td><?= $c['orden'] ?></td>
                        <td><strong><?= sanitize($c['nombre']) ?></strong></td>
                        <td><?= sanitize($c['descripcion'] ?: '-') ?></td>
                        <td><?= $c['total_productos'] ?></td>
                        <td>
                            <?php if ($c['activa']): ?>
                                <span style="color:#16a34a;font-weight:600;font-size:0.85rem;">Activa</span>
                            <?php else: ?>
                                <span style="color:#dc2626;font-weight:600;font-size:0.85rem;">Inactiva</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="?edit=<?= $c['id'] ?>" class="btn btn-sm btn-secondary">Editar</a>
                                <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirmarEliminar('<?= sanitize($c['nombre']) ?>')">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
