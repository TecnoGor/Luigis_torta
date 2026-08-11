<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatCurrency($amount) {
    return '$' . number_format($amount, 2, '.', ',');
}

function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}

function getProductos($categoriaId = null, $busqueda = null, $destacados = false) {
    $sql = 'SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            JOIN categorias c ON p.categoria_id = c.id 
            WHERE p.disponible = 1 AND c.activa = 1';
    $params = [];

    if ($categoriaId) {
        $sql .= ' AND p.categoria_id = ?';
        $params[] = $categoriaId;
    }
    if ($busqueda) {
        $sql .= ' AND (p.nombre LIKE ? OR p.descripcion LIKE ?)';
        $params[] = "%$busqueda%";
        $params[] = "%$busqueda%";
    }
    if ($destacados) {
        $sql .= ' AND p.destacado = 1';
    }
    $sql .= ' ORDER BY p.nombre ASC';
    return fetchAll($sql, $params);
}

function getCategorias($soloActivas = true) {
    $sql = 'SELECT c.*, COUNT(p.id) as total_productos 
            FROM categorias c 
            LEFT JOIN productos p ON c.id = p.categoria_id AND p.disponible = 1';
    if ($soloActivas) {
        $sql .= ' WHERE c.activa = 1';
    }
    $sql .= ' GROUP BY c.id ORDER BY c.orden ASC';
    return fetchAll($sql);
}

function getCategoria($id) {
    return fetchOne('SELECT * FROM categorias WHERE id = ?', [$id]);
}

function getProducto($id) {
    return fetchOne('SELECT p.*, c.nombre as categoria_nombre 
                     FROM productos p 
                     JOIN categorias c ON p.categoria_id = c.id 
                     WHERE p.id = ?', [$id]);
}

function getTotalProductos() {
    return fetchOne('SELECT COUNT(*) as total FROM productos WHERE disponible = 1')['total'];
}

function getTotalStockBajo($limite = 5) {
    return fetchOne('SELECT COUNT(*) as total FROM productos WHERE stock <= ? AND disponible = 1', [$limite])['total'];
}

function getTotalCategorias() {
    return fetchOne('SELECT COUNT(*) as total FROM categorias WHERE activa = 1')['total'];
}

function getValorInventario() {
    return fetchOne('SELECT COALESCE(SUM(precio * stock), 0) as total FROM productos WHERE disponible = 1')['total'];
}

function uploadImage($file) {
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed)) {
        return false;
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('prod_') . '.' . strtolower($ext);
    $destination = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    return false;
}

function deleteImage($filename) {
    if ($filename && file_exists(UPLOAD_DIR . $filename)) {
        unlink(UPLOAD_DIR . $filename);
    }
}

function registrarMovimiento($productoId, $cantidadAnterior, $cantidadNueva, $motivo = '', $usuarioId = null) {
    insertId(
        'INSERT INTO historial_inventario (producto_id, cantidad_anterior, cantidad_nueva, motivo, usuario_id) VALUES (?, ?, ?, ?, ?)',
        [$productoId, $cantidadAnterior, $cantidadNueva, $motivo, $usuarioId]
    );
}

function getProductosBajoStock($limite = 5) {
    return fetchAll(
        'SELECT p.*, c.nombre as categoria_nombre 
         FROM productos p 
         JOIN categorias c ON p.categoria_id = c.id 
         WHERE p.stock <= ? AND p.disponible = 1 
         ORDER BY p.stock ASC',
        [$limite]
    );
}

function getSaboresByProducto($productoId) {
    return fetchAll(
        'SELECT * FROM producto_sabores WHERE producto_id = ? ORDER BY orden ASC, nombre ASC',
        [$productoId]
    );
}

function getSabor($id) {
    return fetchOne('SELECT * FROM producto_sabores WHERE id = ?', [$id]);
}

function getHistorialInventario($productoId = null, $limit = 50) {
    $sql = 'SELECT h.*, p.nombre as producto_nombre, u.nombre_completo as usuario_nombre 
            FROM historial_inventario h 
            JOIN productos p ON h.producto_id = p.id 
            LEFT JOIN usuarios u ON h.usuario_id = u.id';
    $params = [];
    if ($productoId) {
        $sql .= ' WHERE h.producto_id = ?';
        $params[] = $productoId;
    }
    $sql .= ' ORDER BY h.created_at DESC LIMIT ?';
    $params[] = $limit;
    return fetchAll($sql, $params);
}

/* ============================================
   CHAT INTERNO Y ÓRDENES
   ============================================ */

function getCliente($id) {
    return fetchOne('SELECT * FROM clientes WHERE id = ?', [$id]);
}

function getConversacion($id) {
    return fetchOne(
        'SELECT c.*, cl.nombre as cliente_nombre, cl.telefono as cliente_telefono, cl.email as cliente_email 
         FROM conversaciones c 
         JOIN clientes cl ON c.cliente_id = cl.id 
         WHERE c.id = ?',
        [$id]
    );
}

function getConversacionDeCliente($clienteId) {
    return fetchOne(
        'SELECT * FROM conversaciones WHERE cliente_id = ? ORDER BY id DESC LIMIT 1',
        [$clienteId]
    );
}

function enviarMensaje($conversacionId, $remitente, $texto, $tipo = 'texto', $ordenId = null) {
    $lc = 1;
    $la = 1;
    if ($remitente === 'cliente') {
        $lc = 1;
        $la = 0;
    } elseif ($remitente === 'admin') {
        $lc = 0;
        $la = 1;
    }
    execute('UPDATE conversaciones SET updated_at = NOW() WHERE id = ?', [$conversacionId]);
    return insertId(
        'INSERT INTO mensajes (conversacion_id, remitente, tipo, texto, orden_id, leido_cliente, leido_admin) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$conversacionId, $remitente, $tipo, $texto, $ordenId, $lc, $la]
    );
}

function getOrdenData($ordenId) {
    $orden = fetchOne('SELECT * FROM ordenes WHERE id = ?', [$ordenId]);
    if (!$orden) {
        return null;
    }
    $detalles = fetchAll('SELECT * FROM orden_detalles WHERE orden_id = ? ORDER BY id ASC', [$ordenId]);
    return [
        'id' => (int)$orden['id'],
        'estado' => $orden['estado'],
        'total' => $orden['total'],
        'total_formato' => formatCurrency($orden['total']),
        'nota' => $orden['nota'],
        'created_at' => $orden['created_at'],
        'items' => array_map(function ($d) {
            return [
                'nombre' => $d['producto_nombre'],
                'cantidad' => (int)$d['cantidad'],
                'precio_unitario' => $d['precio_unitario'],
                'precio_formato' => formatCurrency($d['precio_unitario']),
                'subtotal' => $d['precio_unitario'] * $d['cantidad'],
                'subtotal_formato' => formatCurrency($d['precio_unitario'] * $d['cantidad']),
            ];
        }, $detalles),
    ];
}

function getMensajes($conversacionId) {
    $msgs = fetchAll(
        'SELECT * FROM mensajes WHERE conversacion_id = ? ORDER BY id ASC',
        [$conversacionId]
    );
    $result = [];
    foreach ($msgs as $m) {
        $item = [
            'id' => (int)$m['id'],
            'remitente' => $m['remitente'],
            'tipo' => $m['tipo'],
            'texto' => $m['texto'],
            'hora' => date('H:i', strtotime($m['created_at'])),
            'created_at' => $m['created_at'],
            'leido_cliente' => (bool)$m['leido_cliente'],
            'leido_admin' => (bool)$m['leido_admin'],
        ];
        if ($m['tipo'] === 'orden' && $m['orden_id']) {
            $item['orden'] = getOrdenData($m['orden_id']);
        }
        $result[] = $item;
    }
    return $result;
}

function marcarLeidosAdmin($conversacionId) {
    execute(
        'UPDATE mensajes SET leido_admin = 1 WHERE conversacion_id = ? AND remitente = "cliente"',
        [$conversacionId]
    );
}

function marcarLeidosCliente($conversacionId) {
    execute(
        'UPDATE mensajes SET leido_cliente = 1 WHERE conversacion_id = ? AND remitente IN ("admin","sistema")',
        [$conversacionId]
    );
}

function getNoLeidosCliente($conversacionId) {
    return (int)fetchOne(
        'SELECT COUNT(*) as total FROM mensajes WHERE conversacion_id = ? AND remitente IN ("admin","sistema") AND leido_cliente = 0',
        [$conversacionId]
    )['total'];
}

function getNoLeidosAdminTotal() {
    return (int)fetchOne(
        'SELECT COUNT(*) as total 
         FROM mensajes m 
         WHERE m.remitente = "cliente" AND m.leido_admin = 0'
    )['total'];
}

function getConversacionesAdmin() {
    $rows = fetchAll(
        'SELECT c.id, c.estado, c.updated_at, cl.nombre as cliente_nombre, cl.telefono as cliente_telefono,
            (SELECT COUNT(*) FROM mensajes m WHERE m.conversacion_id = c.id AND m.remitente = "cliente" AND m.leido_admin = 0) as no_leidos,
            (SELECT m.texto FROM mensajes m WHERE m.conversacion_id = c.id ORDER BY m.id DESC LIMIT 1) as ultimo_mensaje,
            (SELECT m.remitente FROM mensajes m WHERE m.conversacion_id = c.id ORDER BY m.id DESC LIMIT 1) as ultimo_remitente
         FROM conversaciones c 
         JOIN clientes cl ON c.cliente_id = cl.id
         ORDER BY no_leidos > 0 DESC, c.updated_at DESC'
    );
    return array_map(function ($r) {
        return [
            'id' => (int)$r['id'],
            'estado' => $r['estado'],
            'cliente_nombre' => $r['cliente_nombre'],
            'cliente_telefono' => $r['cliente_telefono'],
            'no_leidos' => (int)$r['no_leidos'],
            'ultimo_mensaje' => $r['ultimo_mensaje'],
            'ultimo_remitente' => $r['ultimo_remitente'],
            'fecha' => date('d/m H:i', strtotime($r['updated_at'])),
        ];
    }, $rows);
}

function crearOrden($conversacionId, $items, $nota = '') {
    $conv = getConversacion($conversacionId);
    if (!$conv || empty($items)) {
        return null;
    }
    $detalles = [];
    $total = 0;
    foreach ($items as $it) {
        $productoId = (int)($it['producto_id'] ?? 0);
        $cantidad = max(1, (int)($it['cantidad'] ?? 1));
        if ($productoId <= 0) {
            continue;
        }
        $producto = fetchOne(
            'SELECT * FROM productos WHERE id = ? AND disponible = 1',
            [$productoId]
        );
        if (!$producto) {
            continue;
        }
        $detalles[] = [
            'producto' => $producto,
            'cantidad' => $cantidad,
        ];
        $total += $producto['precio'] * $cantidad;
    }
    if (empty($detalles)) {
        return null;
    }
    $ordenId = insertId(
        'INSERT INTO ordenes (conversacion_id, cliente_id, total, nota) VALUES (?, ?, ?, ?)',
        [$conversacionId, $conv['cliente_id'], $total, $nota ?: null]
    );
    foreach ($detalles as $d) {
        execute(
            'INSERT INTO orden_detalles (orden_id, producto_id, producto_nombre, precio_unitario, cantidad) VALUES (?, ?, ?, ?, ?)',
            [$ordenId, $d['producto']['id'], $d['producto']['nombre'], $d['producto']['precio'], $d['cantidad']]
        );
    }
    return $ordenId;
}

function actualizarEstadoOrden($ordenId, $estado) {
    $validos = ['pendiente', 'confirmada', 'cancelada', 'entregada'];
    if (!in_array($estado, $validos)) {
        return false;
    }
    execute('UPDATE ordenes SET estado = ? WHERE id = ?', [$estado, $ordenId]);
    return true;
}
