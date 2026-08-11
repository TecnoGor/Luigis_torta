<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$productosOrden = array_map(function ($p) {
    return [
        'id' => (int)$p['id'],
        'nombre' => $p['nombre'],
        'precio' => $p['precio'],
        'precio_formato' => formatCurrency($p['precio']),
        'stock' => (int)$p['stock'],
        'unidad_medida' => $p['unidad_medida'],
    ];
}, getProductos());

$conversacionInicial = (int)($_GET['conversacion'] ?? 0);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - <?= SITE_NAME ?></title>
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
            <a href="index.php">&#128202; Dashboard</a>
            <a href="productos.php">&#127856; Productos</a>
            <a href="categorias.php">&#128193; Categorías</a>
            <a href="inventario.php">&#128230; Inventario</a>
            <a href="chat.php" class="active">&#128172; Mensajes<?php $chatNoLeidos = getNoLeidosAdminTotal(); if ($chatNoLeidos > 0): ?><span class="badge-chat"><?= $chatNoLeidos ?></span><?php endif; ?></a>
            <a href="../" target="_blank">&#128196; Ver Catálogo</a>
            <a href="logout.php">&#128682; Cerrar Sesión</a>
        </nav>
    </aside>

    <main class="admin-main admin-main-chat">
        <div class="admin-topbar">
            <h2>&#128172; Mensajes / Chat con Clientes</h2>
        </div>

        <div class="chat-admin-wrap">
            <aside class="chat-admin-convs">
                <div class="chat-admin-convs-header">
                    <h3>Conversaciones</h3>
                    <button id="btn-refresh-convs" class="btn btn-sm btn-secondary">Actualizar</button>
                </div>
                <div id="chat-convs-list" class="chat-convs-list">
                    <p class="chat-empty">Cargando conversaciones...</p>
                </div>
            </aside>

            <section class="chat-admin-thread" id="chat-thread">
                <div class="chat-admin-thread-header" id="chat-thread-header">
                    <div>
                        <strong id="chat-thread-nombre">Selecciona una conversación</strong>
                        <span id="chat-thread-sub" class="chat-thread-sub"></span>
                    </div>
                </div>
                <div class="chat-admin-messages" id="chat-admin-messages">
                    <p class="chat-empty">Selecciona una conversación para ver los mensajes.</p>
                </div>
                <div class="chat-admin-composer">
                    <textarea id="chat-admin-input" placeholder="Escribe una respuesta... (Enter para enviar)" rows="3"></textarea>
                    <button id="btn-chat-enviar" class="btn btn-success" title="Enviar">&#10148;</button>
                    <button id="btn-crear-orden" class="btn btn-primary btn-sm" title="Crear orden" disabled>&#128203; Orden</button>
                </div>
            </section>
        </div>
    </main>
</div>

<div id="orden-modal" class="modal-overlay" style="display:none;">
    <div class="modal-container modal-orden">
        <div class="modal-header">
            <h2 class="modal-title">&#128203; Nueva Orden</h2>
            <button class="modal-close" onclick="ChatAdmin.cerrarModalOrden()">&times;</button>
        </div>
        <div class="modal-body">
            <p class="orden-modal-sub">Agrega productos al pedido. Se enviará al cliente dentro de la conversación.</p>
            <div id="orden-productos" class="orden-productos"></div>
            <div class="form-group" style="margin-top:16px;">
                <label>Nota para el cliente (opcional)</label>
                <textarea id="orden-nota" rows="2" placeholder="Ej: Entrega el sábado por la tarde, decoración con mensaje..."></textarea>
            </div>
            <div id="orden-resumen" class="orden-resumen"></div>
            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:16px;">
                <button class="btn btn-secondary" onclick="ChatAdmin.cerrarModalOrden()">Cancelar</button>
                <button id="btn-orden-crear" class="btn btn-success" onclick="ChatAdmin.enviarOrden()">&#10004; Enviar Orden</button>
            </div>
        </div>
    </div>
</div>

<script>
    window.CHAT_CONFIG = {
        conversacionInicial: <?= $conversacionInicial ?>,
        ajaxBase: '../ajax/',
        productos: <?= json_encode($productosOrden, JSON_UNESCAPED_UNICODE) ?>,
    };
</script>
<script src="../assets/js/chat.js"></script>
</body>
</html>
