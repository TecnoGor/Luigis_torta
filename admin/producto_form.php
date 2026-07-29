<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$id = $_GET['id'] ?? null;
$deleteId = $_GET['delete'] ?? null;
$deleteSabor = $_GET['deletesabor'] ?? null;
$producto = null;
$errors = [];

// Eliminar producto
if ($deleteId) {
    $producto = getProducto($deleteId);
    if ($producto) {
        deleteImage($producto['imagen']);
        execute('DELETE FROM productos WHERE id = ?', [$deleteId]);
        flash('success', 'Producto eliminado correctamente');
    }
    header('Location: ' . ADMIN_URL . '/productos.php');
    exit;
}

// Eliminar sabor
if ($deleteSabor && $id) {
    $sabor = getSabor($deleteSabor);
    if ($sabor && $sabor['producto_id'] == $id) {
        if ($sabor['imagen']) {
            $imgPath = UPLOAD_DIR . $sabor['imagen'];
            if (file_exists($imgPath)) unlink($imgPath);
        }
        execute('DELETE FROM producto_sabores WHERE id = ?', [$deleteSabor]);
        flash('success', 'Sabor eliminado correctamente');
    }
    header('Location: ' . ADMIN_URL . '/producto_form.php?id=' . $id);
    exit;
}

// Cargar producto existente
if ($id) {
    $producto = getProducto($id);
    if (!$producto) {
        header('Location: ' . ADMIN_URL . '/productos.php');
        exit;
    }
}

// Agregar sabor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sabor']) && $id) {
    $saborNombre = trim($_POST['sabor_nombre'] ?? '');
    $saborPrecio = (float)($_POST['sabor_precio'] ?? 0);
    $saborStock = (int)($_POST['sabor_stock'] ?? 0);
    $saborOrden = (int)($_POST['sabor_orden'] ?? 0);

    if (empty($saborNombre)) {
        $errors[] = 'El nombre del sabor es requerido';
    } else {
        $saborData = [
            'producto_id' => $id,
            'nombre' => $saborNombre,
            'precio' => $saborPrecio,
            'stock' => $saborStock,
            'orden' => $saborOrden,
        ];

        if (!empty($_FILES['sabor_imagen']['name'])) {
            $filename = uploadImage($_FILES['sabor_imagen']);
            if ($filename) {
                $saborData['imagen'] = $filename;
            } else {
                $errors[] = 'Error al subir la imagen del sabor. Usa JPG, PNG, GIF o WebP.';
            }
        }

        if (empty($errors)) {
            $cols = implode(', ', array_keys($saborData));
            $vals = implode(', ', array_fill(0, count($saborData), '?'));
            insertId("INSERT INTO producto_sabores ($cols) VALUES ($vals)", array_values($saborData));
            flash('success', 'Sabor agregado correctamente');
            header('Location: ' . ADMIN_URL . '/producto_form.php?id=' . $id);
            exit;
        }
    }
}

// Guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'categoria_id' => (int)($_POST['categoria_id'] ?? 0),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'precio' => (float)($_POST['precio'] ?? 0),
        'stock' => (int)($_POST['stock'] ?? 0),
        'unidad_medida' => trim($_POST['unidad_medida'] ?? 'pieza'),
        'disponible' => isset($_POST['disponible']) ? 1 : 0,
        'destacado' => isset($_POST['destacado']) ? 1 : 0,
    ];

    if (empty($data['nombre'])) $errors[] = 'El nombre es requerido';
    if ($data['categoria_id'] <= 0) $errors[] = 'Selecciona una categoría';
    if ($data['precio'] < 0) $errors[] = 'El precio no puede ser negativo';
    if ($data['stock'] < 0) $errors[] = 'El stock no puede ser negativo';

    // Subir imagen
    if (!empty($_FILES['imagen']['name'])) {
        $filename = uploadImage($_FILES['imagen']);
        if ($filename) {
            if ($producto) deleteImage($producto['imagen']);
            $data['imagen'] = $filename;
        } else {
            $errors[] = 'Error al subir la imagen. Usa JPG, PNG, GIF o WebP.';
        }
    }

    if (empty($errors)) {
        if ($id) {
            // Registrar cambio de stock
            if ($producto && $producto['stock'] != $data['stock']) {
                registrarMovimiento($id, $producto['stock'], $data['stock'], 'Edición de producto', $_SESSION['user_id']);
            }

            $sets = [];
            $params = [];
            foreach ($data as $k => $v) {
                $sets[] = "$k = ?";
                $params[] = $v;
            }
            $params[] = $id;
            execute('UPDATE productos SET ' . implode(', ', $sets) . ' WHERE id = ?', $params);
            flash('success', 'Producto actualizado correctamente');
        } else {
            if (isset($data['imagen'])) {
                $sets = ['imagen'];
            }
            $colNames = implode(', ', array_keys($data));
            $placeholders = implode(', ', array_fill(0, count($data), '?'));
            $newId = insertId("INSERT INTO productos ($colNames) VALUES ($placeholders)", array_values($data));

            if (!empty($_FILES['imagen']['name']) && isset($filename)) {
                execute('UPDATE productos SET imagen = ? WHERE id = ?', [$filename, $newId]);
            }

            // Registrar stock inicial
            if ($data['stock'] > 0) {
                registrarMovimiento($newId, 0, $data['stock'], 'Stock inicial', $_SESSION['user_id']);
            }

            flash('success', 'Producto creado correctamente');
        }
        header('Location: ' . ADMIN_URL . '/productos.php');
        exit;
    }
}

$categorias = getCategorias(false);
$isEdit = $id && $producto;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Editar' : 'Nuevo' ?> Producto - <?= SITE_NAME ?></title>
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
            <a href="../" target="_blank">&#128196; Ver Catálogo</a>
            <a href="logout.php">&#128682; Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <h2><?= $isEdit ? 'Editar Producto' : 'Nuevo Producto' ?></h2>
            <a href="productos.php" class="btn btn-secondary btn-sm">&#8592; Volver</a>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $e): ?>
                    <div><?= sanitize($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nombre del Producto *</label>
                        <input type="text" name="nombre" required value="<?= sanitize($producto['nombre'] ?? $_POST['nombre'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Categoría *</label>
                        <select name="categoria_id" required>
                            <option value="">Seleccionar...</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= (($producto['categoria_id'] ?? $_POST['categoria_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                                    <?= sanitize($c['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="3"><?= sanitize($producto['descripcion'] ?? $_POST['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Precio ($) *</label>
                        <input type="number" name="precio" step="0.01" min="0" required value="<?= $producto['precio'] ?? $_POST['precio'] ?? '0.00' ?>">
                    </div>
                    <div class="form-group">
                        <label>Stock *</label>
                        <input type="number" name="stock" min="0" required value="<?= $producto['stock'] ?? $_POST['stock'] ?? '0' ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Unidad de Medida</label>
                        <select name="unidad_medida">
                            <?php foreach (['pieza', 'porción', 'kg', 'docena'] as $u): ?>
                                <option value="<?= $u ?>" <?= (($producto['unidad_medida'] ?? $_POST['unidad_medida'] ?? '') == $u) ? 'selected' : '' ?>><?= ucfirst($u) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Imagen del Producto</label>
                        <input type="file" name="imagen" accept="image/*" onchange="previewImage(this)">
                        <?php if ($producto && !empty($producto['imagen'])): ?>
                            <div style="margin-top:8px;">
                                <img id="image-preview" src="<?= UPLOAD_URL . $producto['imagen'] ?>" class="image-preview" alt="Preview">
                            </div>
                        <?php else: ?>
                            <img id="image-preview" class="image-preview" style="display:none;" alt="Preview">
                        <?php endif; ?>
                    </div>
                </div>

                <div style="display:flex;gap:24px;margin-top:8px;">
                    <div class="checkbox-group">
                        <input type="checkbox" name="disponible" id="disponible" value="1" <?= ($producto['disponible'] ?? $_POST['disponible'] ?? 1) ? 'checked' : '' ?>>
                        <label for="disponible">Disponible en catálogo</label>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" name="destacado" id="destacado" value="1" <?= ($producto['destacado'] ?? $_POST['destacado'] ?? 0) ? 'checked' : '' ?>>
                        <label for="destacado">Producto destacado</label>
                    </div>
                </div>

                <div style="margin-top:24px;display:flex;gap:12px;">
                    <button type="submit" class="btn btn-success" style="width:auto;">
                        <?= $isEdit ? '&#10004; Actualizar' : '&#10004; Crear Producto' ?>
                    </button>
                    <a href="productos.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>

        <?php if ($isEdit): $sabores = getSaboresByProducto($id); ?>
        <div class="form-card" style="margin-top:24px;">
            <h3>&#127855; Sabores / Variedades</h3>

            <?php if (count($sabores) > 0): ?>
            <div class="data-table-wrapper" style="margin-bottom:20px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Orden</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sabores as $s): ?>
                        <tr>
                            <td>
                                <?php if ($s['imagen']): ?>
                                    <img src="<?= UPLOAD_URL . $s['imagen'] ?>" alt="" class="image-preview">
                                <?php else: ?>
                                    <div class="image-preview-placeholder">&#127856;</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= sanitize($s['nombre']) ?></strong></td>
                            <td><?= formatCurrency($s['precio']) ?></td>
                            <td><span class="stock-badge <?= $s['stock'] == 0 ? 'out' : ($s['stock'] <= 3 ? 'low' : 'ok') ?>"><?= $s['stock'] ?></span></td>
                            <td><?= $s['orden'] ?></td>
                            <td>
                                <a href="producto_form.php?id=<?= $id ?>&deletesabor=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Eliminar sabor «<?= sanitize($s['nombre']) ?>»?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p style="color:var(--text-muted);margin-bottom:16px;">Este producto no tiene sabores/variedades registradas.</p>
            <?php endif; ?>

            <hr style="border:none;border-top:1px solid var(--border);margin:16px 0;">

            <h4 style="margin-bottom:12px;">Agregar nuevo sabor</h4>
            <form method="POST" enctype="multipart/form-data" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <input type="hidden" name="add_sabor" value="1">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Nombre *</label>
                    <input type="text" name="sabor_nombre" required placeholder="Ej: Chocolate, Vainilla...">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Precio ($)</label>
                    <input type="number" name="sabor_precio" step="0.01" min="0" value="0.00">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Stock</label>
                    <input type="number" name="sabor_stock" min="0" value="0">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Orden</label>
                    <input type="number" name="sabor_orden" min="0" value="0">
                </div>
                <div class="form-group" style="margin-bottom:0;grid-column:span 2;">
                    <label>Imagen del sabor</label>
                    <input type="file" name="sabor_imagen" accept="image/*">
                </div>
                <div style="grid-column:span 2;margin-top:4px;">
                    <button type="submit" class="btn btn-success" style="width:auto;">+ Agregar Sabor</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
