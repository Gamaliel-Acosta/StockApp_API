<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    respond(null, 204);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(['success' => false, 'message' => 'Método no permitido'], 405);
}

try {
    db()->query('SELECT 1');
    respond([
        'success' => true,
        'message' => 'Conexión exitosa con la base de datos',
    ]);
} catch (PDOException $error) {
    respond([
        'success' => false,
        'message' => 'No se pudo conectar con la base de datos. Revisa conexion.php.',
    ], 500);
}
