<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$productoId = (int)($_GET['producto_id'] ?? 0);

if ($productoId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de producto inválido']);
    exit;
}

$producto = getProducto($productoId);
if (!$producto) {
    http_response_code(404);
    echo json_encode(['error' => 'Producto no encontrado']);
    exit;
}

$sabores = getSaboresByProducto($productoId);
$uploadUrl = UPLOAD_URL;

echo json_encode([
    'producto' => [
        'id' => $producto['id'],
        'nombre' => $producto['nombre'],
        'descripcion' => $producto['descripcion'],
        'categoria_nombre' => $producto['categoria_nombre'],
    ],
    'sabores' => array_map(function ($s) use ($uploadUrl) {
        return [
            'id' => $s['id'],
            'nombre' => $s['nombre'],
            'precio' => formatCurrency($s['precio']),
            'precio_raw' => $s['precio'],
            'stock' => (int)$s['stock'],
            'imagen' => $s['imagen'] ? $uploadUrl . $s['imagen'] : null,
            'stock_label' => $s['stock'] > 0 ? $s['stock'] . ' disponibles' : 'Agotado',
            'stock_low' => $s['stock'] <= 3 && $s['stock'] > 0,
        ];
    }, $sabores),
    'tiene_sabores' => !empty($sabores),
]);
