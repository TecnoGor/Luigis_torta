<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

// Actualizar stock rápido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productoId = (int)($_POST['producto_id'] ?? 0);
    $nuevoStock = (int)($_POST['nuevo_stock'] ?? 0);
    $motivo = trim($_POST['motivo'] ?? '');

    if ($productoId > 0 && $nuevoStock >= 0) {
        $producto = getProducto($productoId);
        if ($producto) {
            $stockAnterior = $producto['stock'];
            execute('UPDATE productos SET stock = ? WHERE id = ?', [$nuevoStock, $productoId]);
            registrarMovimiento($productoId, $stockAnterior, $nuevoStock, $motivo ?: 'Ajuste manual', $_SESSION['user_id']);
            flash('success', "Stock de \"{$producto['nombre']}\" actualizado: $stockAnterior → $nuevoStock");
        }
    }
    header('Location: ' . ADMIN_URL . '/inventario.php');
    exit;
}

$productos = fetchAll(
    'SELECT p.*, c.nombre as categoria_nombre 
     FROM productos p 
     JOIN categorias c ON p.categoria_id = c.id 
     WHERE p.disponible = 1 
     ORDER BY p.stock ASC, p.nombre ASC'
);

$historial = getHistorialInventario(null, 30);
$bajoStock = getProductosBajoStock(5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - <?= SITE_NAME ?></title>
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
            <a href="categorias.php">&#128193; Categorías</a>
            <a href="inventario.php" class="active">&#128230; Inventario</a>
            <a href="chat.php">&#128172; Mensajes<?php $chatNoLeidos = getNoLeidosAdminTotal(); if ($chatNoLeidos > 0): ?><span class="badge-chat"><?= $chatNoLeidos ?></span><?php endif; ?></a>
            <a href="../" target="_blank">&#128196; Ver Catálogo</a>
            <a href="logout.php">&#128682; Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <h2>Control de Inventario</h2>
        </div>

        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>

        <div class="dashboard-cards" style="margin-bottom:32px;">
            <div class="dash-card">
                <div class="dash-icon">&#128230;</div>
                <div class="dash-number"><?= array_sum(array_column($productos, 'stock')) ?></div>
                <div class="dash-label">Total en Stock</div>
            </div>
            <div class="dash-card">
                <div class="dash-icon">&#9888;&#65039;</div>
                <div class="dash-number" style="color:#e65100"><?= count($bajoStock) ?></div>
                <div class="dash-label">Stock Bajo (≤5)</div>
            </div>
            <div class="dash-card">
                <div class="dash-icon">&#128176;</div>
                <div class="dash-number"><?= formatCurrency(getValorInventario()) ?></div>
                <div class="dash-label">Valor Total</div>
            </div>
        </div>

        <div class="data-table-wrapper" style="margin-bottom:32px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Stock Actual</th>
                        <th>Ajustar Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><strong><?= sanitize($p['nombre']) ?></strong></td>
                        <td><?= sanitize($p['categoria_nombre']) ?></td>
                        <td>
                            <span class="stock-badge <?= $p['stock'] == 0 ? 'out' : ($p['stock'] <= 5 ? 'low' : 'ok') ?>">
                                <?= $p['stock'] ?> <?= $p['unidad_medida'] ?><?= $p['stock'] != 1 ? 's' : '' ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                                <input type="number" name="nuevo_stock" min="0" value="<?= $p['stock'] ?>" style="width:80px;padding:6px 10px;border:2px solid #f0e0d6;border-radius:8px;font-size:0.85rem;">
                                <input type="text" name="motivo" placeholder="Motivo..." style="width:140px;padding:6px 10px;border:2px solid #f0e0d6;border-radius:8px;font-size:0.8rem;">
                                <button type="submit" class="btn btn-sm btn-success" style="width:auto;padding:6px 12px;">Actualizar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (count($historial) > 0): ?>
        <div class="data-table-wrapper">
            <h3 style="padding:16px 20px 0;font-family:'Playfair Display',serif;font-size:1.1rem;">&#128203; Historial de Movimientos</h3>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Anterior</th>
                        <th>Nuevo</th>
                        <th>Cambio</th>
                        <th>Motivo</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($historial as $h): ?>
                    <tr>
                        <td style="font-size:0.8rem;color:#795548;"><?= date('d/m/Y H:i', strtotime($h['created_at'])) ?></td>
                        <td><strong><?= sanitize($h['producto_nombre']) ?></strong></td>
                        <td><?= $h['cantidad_anterior'] ?></td>
                        <td><?= $h['cantidad_nueva'] ?></td>
                        <td>
                            <?php
                            $diff = $h['cantidad_nueva'] - $h['cantidad_anterior'];
                            $color = $diff > 0 ? '#16a34a' : ($diff < 0 ? '#dc2626' : '#795548');
                            ?>
                            <span style="color:<?= $color ?>;font-weight:600;">
                                <?= $diff > 0 ? '+' . $diff : $diff ?>
                            </span>
                        </td>
                        <td style="font-size:0.85rem;"><?= sanitize($h['motivo'] ?: '-') ?></td>
                        <td style="font-size:0.85rem;"><?= sanitize($h['usuario_nombre'] ?? 'Sistema') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
