<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$totalProductos = getTotalProductos();
$totalCategorias = getTotalCategorias();
$totalBajoStock = getTotalStockBajo();
$valorInventario = getValorInventario();
$bajoStock = getProductosBajoStock(5);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= SITE_NAME ?></title>
    <link rel="icon" href="../assets/images/favico.png" type="image/x-icon">
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
            <a href="index.php" class="active">&#128202; Dashboard</a>
            <a href="productos.php">&#127856; Productos</a>
            <a href="categorias.php">&#128193; Categorías</a>
            <a href="inventario.php">&#128230; Inventario</a>
            <a href="../" target="_blank">&#128196; Ver Catálogo</a>
            <a href="logout.php">&#128682; Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-topbar">
            <h2>Dashboard</h2>
            <span>Bienvenido, <?= sanitize($_SESSION['user_name']) ?></span>
        </div>

        <?php if ($msg = flash('success')): ?>
            <div class="alert alert-success"><?= $msg ?></div>
        <?php endif; ?>

        <div class="dashboard-cards">
            <div class="dash-card">
                <div class="dash-icon">&#127856;</div>
                <div class="dash-number"><?= $totalProductos ?></div>
                <div class="dash-label">Productos Activos</div>
            </div>
            <div class="dash-card">
                <div class="dash-icon">&#128193;</div>
                <div class="dash-number"><?= $totalCategorias ?></div>
                <div class="dash-label">Categorías</div>
            </div>
            <div class="dash-card">
                <div class="dash-icon">&#9888;&#65039;</div>
                <div class="dash-number" style="color:<?= $totalBajoStock > 0 ? '#e65100' : '#16a34a' ?>"><?= $totalBajoStock ?></div>
                <div class="dash-label">Stock Bajo</div>
            </div>
            <div class="dash-card">
                <div class="dash-icon">&#128176;</div>
                <div class="dash-number"><?= formatCurrency($valorInventario) ?></div>
                <div class="dash-label">Valor del Inventario</div>
            </div>
        </div>

        <?php if (count($bajoStock) > 0): ?>
        <div class="low-stock-card">
            <div class="card-header">&#9888;&#65039; Productos con Stock Bajo</div>
            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bajoStock as $p): ?>
                        <tr>
                            <td><strong><?= sanitize($p['nombre']) ?></strong></td>
                            <td><?= sanitize($p['categoria_nombre']) ?></td>
                            <td>
                                <span class="stock-badge <?= $p['stock'] == 0 ? 'out' : 'low' ?>">
                                    <?= $p['stock'] ?> <?= $p['unidad_medida'] ?><?= $p['stock'] != 1 ? 's' : '' ?>
                                </span>
                            </td>
                            <td>
                                <a href="producto_form.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-primary" style="width:auto;">Actualizar Stock</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
