<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Se requiere iniciar sesión']);
    exit;
}

echo json_encode([
    'conversaciones' => getConversacionesAdmin(),
]);
