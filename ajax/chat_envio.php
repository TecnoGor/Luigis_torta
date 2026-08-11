<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$conversacionId = (int)($_POST['conversacion_id'] ?? 0);
$texto = trim($_POST['texto'] ?? '');

if ($conversacionId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Conversación inválida']);
    exit;
}
if ($texto === '') {
    http_response_code(400);
    echo json_encode(['error' => 'El mensaje no puede estar vacío']);
    exit;
}
$texto = mb_substr($texto, 0, 2000);

if (isLoggedIn()) {
    $conv = getConversacion($conversacionId);
    if (!$conv) {
        http_response_code(404);
        echo json_encode(['error' => 'Conversación no encontrada']);
        exit;
    }
    $mensajeId = enviarMensaje($conversacionId, 'admin', $texto);
    echo json_encode(['ok' => true, 'mensaje_id' => $mensajeId]);
    exit;
}

if (!isset($_SESSION['cliente_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Cliente no identificado']);
    exit;
}

$conv = getConversacionDeCliente($_SESSION['cliente_id']);
if (!$conv || (int)$conv['id'] !== $conversacionId) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

$mensajeId = enviarMensaje($conversacionId, 'cliente', $texto);
echo json_encode(['ok' => true, 'mensaje_id' => $mensajeId]);
