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
