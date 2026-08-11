<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Se requiere iniciar sesión']);
    exit;
}

$conversacionId = (int)($_POST['conversacion_id'] ?? 0);
$nota = trim($_POST['nota'] ?? '');
$itemsRaw = $_POST['items'] ?? '[]';

if (is_string($itemsRaw)) {
    $items = json_decode($itemsRaw, true);
    if (!is_array($items)) {
        $items = [];
    }
} else {
    $items = $itemsRaw;
}

if ($conversacionId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Conversación inválida']);
    exit;
}

$conv = getConversacion($conversacionId);
if (!$conv) {
    http_response_code(404);
    echo json_encode(['error' => 'Conversación no encontrada']);
    exit;
}

if (empty($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'Selecciona al menos un producto']);
    exit;
}

$ordenId = crearOrden($conversacionId, $items, mb_substr($nota, 0, 500));
if (!$ordenId) {
    http_response_code(400);
    echo json_encode(['error' => 'No se pudieron procesar los productos seleccionados']);
    exit;
}

enviarMensaje($conversacionId, 'admin', '', 'orden', $ordenId);

echo json_encode([
    'ok' => true,
    'orden' => getOrdenData($ordenId),
]);
