<?php require_once __DIR__ . '/includes/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Catálogo de Repostería</title>
    <link rel="icon" href="assets/images/favico.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<header class="site-header">
    <div class="container">
        <div class="header-content">
            <div class="brand">
                <div class="brand-icon">
                    <img class="brand-icon-logo" src="assets/images/Luigis_torta_logo.png">
                </div>
                <div>
                    <h1 class="brand-name">Luigi's Tortas</h1>
                    <p class="brand-tagline">Dulces momentos, creados con amor</p>
                </div>
            </div>
            <nav class="nav-links">
                <a href="#catalogo">Catálogo</a>
                <a href="#destacados">Destacados</a>
                <a href="#contacto">Contacto</a>
            </nav>
        </div>
    </div>
</header>

<section class="hero">
    <div class="container">
        <h2 class="hero-title">Nuestros Postres Artesanales</h2>
        <p class="hero-subtitle">Cada pieza hecha con ingredientes frescos y mucho cariño</p>
        <div class="search-bar">
            <input type="text" id="buscador" placeholder="Buscar productos..." autocomplete="off">
            <button class="btn-search" onclick="buscarProductos()">&#128269;</button>
        </div>
    </div>
</section>

<section class="categorias-bar" id="catalogo">
    <div class="container">
        <div class="cat-filters">
            <button class="cat-btn active" onclick="filtrarCategoria(null, this)">Todos</button>
            <?php foreach (getCategorias() as $cat): ?>
                <button class="cat-btn" onclick="filtrarCategoria(<?= $cat['id'] ?>, this)">
                    <?= sanitize($cat['nombre']) ?>
                    <span class="cat-count"><?= $cat['total_productos'] ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="destacados" id="destacados">
    <div class="container">
        <h2 class="section-title">&#11088; Destacados</h2>
        <div class="products-grid">
            <?php foreach (getProductos(null, null, true) as $p): ?>
                <div class="product-card featured" data-categoria="<?= $p['categoria_id'] ?>" data-id="<?= $p['id'] ?>">
                    <div class="product-badge">Destacado</div>
                    <div class="product-image">
                        <?php if ($p['imagen']): ?>
                            <img src="<?= UPLOAD_URL . $p['imagen'] ?>" alt="<?= sanitize($p['nombre']) ?>">
                        <?php else: ?>
                            <div class="product-placeholder">
                                <span>&#127856;</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?= sanitize($p['categoria_nombre']) ?></span>
                        <h3 class="product-name"><?= sanitize($p['nombre']) ?></h3>
                        <p class="product-desc"><?= sanitize($p['descripcion']) ?></p>
                        <div class="product-footer">
                            <span class="product-price"><?= formatCurrency($p['precio']) ?></span>
                            <span class="product-stock <?= $p['stock'] <= 3 ? 'low' : '' ?>">
                                <?= $p['stock'] > 0 ? $p['stock'] . ' disponibles' : 'Agotado' ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="catalogo-completo">
    <div class="container">
        <h2 class="section-title">Catálogo Completo</h2>
        <div class="products-grid" id="productos-grid">
            <?php foreach (getProductos() as $p): ?>
                <div class="product-card" data-categoria="<?= $p['categoria_id'] ?>" data-nombre="<?= strtolower($p['nombre'] . ' ' . $p['descripcion']) ?>" data-id="<?= $p['id'] ?>">
                    <div class="product-image">
                        <?php if ($p['imagen']): ?>
                            <img src="<?= UPLOAD_URL . $p['imagen'] ?>" alt="<?= sanitize($p['nombre']) ?>">
                        <?php else: ?>
                            <div class="product-placeholder">
                                <span>&#127856;</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <span class="product-category"><?= sanitize($p['categoria_nombre']) ?></span>
                        <h3 class="product-name"><?= sanitize($p['nombre']) ?></h3>
                        <p class="product-desc"><?= sanitize($p['descripcion']) ?></p>
                        <div class="product-footer">
                            <span class="product-price"><?= formatCurrency($p['precio']) ?></span>
                            <span class="product-stock <?= $p['stock'] <= 3 ? 'low' : '' ?>">
                                <?= $p['stock'] > 0 ? $p['stock'] . ' disponibles' : 'Agotado' ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div id="no-results" class="no-results" style="display:none;">
            <span>&#128270;</span>
            <p>No se encontraron productos</p>
        </div>
    </div>
</section>

<section class="contacto" id="contacto">
    <div class="container">
        <h2 class="section-title">Contáctanos</h2>
        <div class="contact-grid">
            <div class="contact-card">
                <div class="contact-icon">&#128222;</div>
                <h3>Teléfono</h3>
                <p>Reserva tu pedido por llamada o WhatsApp</p>
            </div>
            <div class="contact-card">
                <div class="contact-icon">&#128205;</div>
                <h3>Ubicación</h3>
                <p>Haz tu pedido y acorde a tu zona de entrega</p>
            </div>
            <div class="contact-card">
                <div class="contact-icon">&#128247;</div>
                <h3>Redes Sociales</h3>
                <p>Síguenos para ver nuestras creaciones</p>
            </div>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container">
        <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Todos los derechos reservados.</p>
        <p class="footer-sub">Hecho con &#10084;&#65039; para endulzarte el día</p>
    </div>
</footer>

<div id="producto-modal" class="modal-overlay" style="display:none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2 id="modal-titulo" class="modal-title"></h2>
            <button class="modal-close" onclick="cerrarModal()">&times;</button>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="modal-loading" id="modal-loading">
                <span class="loading-spinner"></span>
                <p>Cargando sabores...</p>
            </div>
            <div class="sabores-grid" id="sabores-grid"></div>
        </div>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
