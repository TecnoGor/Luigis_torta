<?php
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

$identificado = isset($_SESSION['cliente_id']);

if ($identificado) {
    $conv = getConversacionDeCliente($_SESSION['cliente_id']);
    if (!$conv) {
        $convId = insertId(
            'INSERT INTO conversaciones (cliente_id, asunto) VALUES (?, ?)',
            [(int)$_SESSION['cliente_id'], 'Consulta desde el catálogo']
        );
        enviarMensaje(
            $convId,
            'sistema',
            '¡Hola ' . ($_SESSION['cliente_nombre'] ?? 'Cliente') . '! Bienvenido(a) a ' . SITE_NAME . '. ¿En qué podemos ayudarte?'
        );
        $conv = getConversacionDeCliente($_SESSION['cliente_id']);
    }
    echo json_encode([
        'identificado' => true,
        'cliente' => [
            'id' => (int)$_SESSION['cliente_id'],
            'nombre' => $_SESSION['cliente_nombre'] ?? 'Cliente',
        ],
        'conversacion_id' => $conv ? (int)$conv['id'] : null,
    ]);
} else {
    echo json_encode([
        'identificado' => false,
        'cliente' => null,
        'conversacion_id' => null,
    ]);
}
