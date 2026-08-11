<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$ordenId = (int)($_POST['orden_id'] ?? 0);
$estado = trim($_POST['estado'] ?? '');

$validos = ['pendiente', 'confirmada', 'cancelada', 'entregada'];
if ($ordenId <= 0 || !in_array($estado, $validos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Parámetros inválidos']);
    exit;
}

$orden = fetchOne(
    'SELECT o.*, c.cliente_id, c.id as conversacion_id 
     FROM ordenes o 
     JOIN conversaciones c ON o.conversacion_id = c.id 
     WHERE o.id = ?',
    [$ordenId]
);
if (!$orden) {
    http_response_code(404);
    echo json_encode(['error' => 'Orden no encontrada']);
    exit;
}

if (isLoggedIn()) {
    $admin = true;
} else {
    if (!isset($_SESSION['cliente_id']) || (int)$_SESSION['cliente_id'] !== (int)$orden['cliente_id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Acceso denegado']);
        exit;
    }
    $admin = false;
    if ($orden['estado'] !== 'pendiente' || !in_array($estado, ['confirmada', 'cancelada'])) {
        http_response_code(403);
        echo json_encode(['error' => 'No puedes realizar esta acción']);
        exit;
    }
}

actualizarEstadoOrden($ordenId, $estado);

$quien = $admin ? 'El administrador' : 'El cliente';
$etiquetas = [
    'pendiente' => 'Pendiente',
    'confirmada' => 'Confirmada',
    'cancelada' => 'Cancelada',
    'entregada' => 'Entregada',
];
enviarMensaje(
    $orden['conversacion_id'],
    'sistema',
    $quien . ' marcó la orden #' . $ordenId . ' como "' . $etiquetas[$estado] . '".'
);

echo json_encode([
    'ok' => true,
    'orden' => getOrdenData($ordenId),
]);
