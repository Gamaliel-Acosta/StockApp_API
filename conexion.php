<?php
declare(strict_types=1);

/**
 * Conexión compartida para todas las API.
 *
 * En Laragon usa por defecto:
 *   servidor: 127.0.0.1
 *   puerto:   3306
 *   base:     inventario_computadoras
 *   usuario:  root
 *   clave:    vacía
 *
 * También admite las variables DB_HOST, DB_PORT, DB_NAME, DB_USER y DB_PASS.
 */
function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME') ?: 'inventario_computadoras';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function jsonInput(): array
{
    $data = json_decode(file_get_contents('php://input'), true);
    if (!is_array($data)) {
        respond(['error' => 'El cuerpo debe ser JSON válido'], 400);
    }
    return $data;
}

function respond(mixed $data, int $status = 200): never
{
    http_response_code($status);
    if ($status !== 204) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    exit;
}

function productPayload(array $data, bool $partial = false): array
{
    $allowed = ['name', 'code', 'price', 'available', 'minimum_stock', 'category', 'brand', 'description', 'active'];
    $payload = array_intersect_key($data, array_flip($allowed));

    if (!$partial) {
        foreach (['name', 'code', 'price'] as $field) {
            if (!array_key_exists($field, $payload)) {
                respond(['error' => "Falta el campo {$field}"], 400);
            }
        }
    }

    return $payload;
}
