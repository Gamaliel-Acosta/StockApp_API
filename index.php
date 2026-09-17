<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/productos.php';
require_once __DIR__ . '/movimientos.php';
require_once __DIR__ . '/dashboard.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    respond(null, 204);
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = preg_replace('#^/api(?:/index\.php)?#', '', $path);
$path = trim($path, '/');
$parts = $path === '' ? [] : explode('/', $path);
$resource = $parts[0] ?? '';
$id = isset($parts[1]) && ctype_digit($parts[1]) ? (int)$parts[1] : null;
$method = $_SERVER['REQUEST_METHOD'];

try {
    match ($resource) {
        'productos' => productos($method, $id),
        'movimientos' => movimientos($method, $id),
        'dashboard' => dashboard(),
        default => respond(['error' => 'Ruta no encontrada'], 404),
    };
} catch (PDOException $error) {
    respond(['error' => 'No fue posible conectarse o consultar la base de datos'], 500);
}
