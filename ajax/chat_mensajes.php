<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$conversacionId = (int)($_GET['conversacion_id'] ?? 0);

if (isLoggedIn()) {
    if ($conversacionId > 0) {
        $conv = getConversacion($conversacionId);
        if (!$conv) {
            http_response_code(404);
            echo json_encode(['error' => 'Conversación no encontrada']);
            exit;
        }
        marcarLeidosAdmin($conversacionId);
        echo json_encode([
            'admin' => true,
            'conversacion' => [
                'id' => (int)$conv['id'],
                'cliente_nombre' => $conv['cliente_nombre'],
                'cliente_telefono' => $conv['cliente_telefono'],
                'estado' => $conv['estado'],
            ],
            'mensajes' => getMensajes($conversacionId),
        ]);
    } else {
        echo json_encode([
            'admin' => true,
            'conversacion' => null,
            'mensajes' => [],
            'conversaciones' => getConversacionesAdmin(),
        ]);
    }
    exit;
}

if (!isset($_SESSION['cliente_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Cliente no identificado']);
    exit;
}

$conv = getConversacionDeCliente($_SESSION['cliente_id']);
if (!$conv) {
    echo json_encode([
        'identificado' => true,
        'conversacion_id' => null,
        'mensajes' => [],
        'no_leidos' => 0,
    ]);
    exit;
}

$convId = (int)$conv['id'];
if ($conversacionId > 0 && $conversacionId !== $convId) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

$noLeidos = getNoLeidosCliente($convId);

if (isset($_GET['leer']) && $_GET['leer'] == 1) {
    marcarLeidosCliente($convId);
    $noLeidos = 0;
}

echo json_encode([
    'identificado' => true,
    'conversacion_id' => $convId,
    'cliente_nombre' => $_SESSION['cliente_nombre'] ?? 'Cliente',
    'mensajes' => getMensajes($convId),
    'no_leidos' => $noLeidos,
]);
