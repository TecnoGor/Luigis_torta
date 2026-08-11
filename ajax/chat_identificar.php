<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

if ($nombre === '') {
    http_response_code(400);
    echo json_encode(['error' => 'El nombre es requerido']);
    exit;
}

$nombre = mb_substr($nombre, 0, 150);
$telefono = mb_substr($telefono, 0, 50);

$clienteId = insertId(
    'INSERT INTO clientes (nombre, telefono) VALUES (?, ?)',
    [$nombre, $telefono ?: null]
);

$_SESSION['cliente_id'] = $clienteId;
$_SESSION['cliente_nombre'] = $nombre;

$convId = insertId(
    'INSERT INTO conversaciones (cliente_id, asunto) VALUES (?, ?)',
    [$clienteId, 'Consulta desde el catálogo']
);

enviarMensaje(
    $convId,
    'sistema',
    '¡Hola ' . $nombre . '! Bienvenido(a) a ' . SITE_NAME . '. ¿En qué podemos ayudarte? Puedes consultar por nuestros productos, precios o hacer un pedido.'
);

echo json_encode([
    'ok' => true,
    'cliente_id' => (int)$clienteId,
    'cliente_nombre' => $nombre,
    'conversacion_id' => (int)$convId,
]);
