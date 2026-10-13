<?php
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    respond(null, 204);
}

function profilePayload(array $data): array
{
    $allowed = ['name', 'username', 'email', 'phone', 'role'];
    $payload = array_intersect_key($data, array_flip($allowed));

    foreach ($payload as $field => $value) {
        if (!is_string($value)) {
            respond(['error' => "El campo {$field} debe ser texto."], 400);
        }
        $payload[$field] = trim($value);
    }

    if (array_key_exists('name', $payload) && $payload['name'] === '') {
        respond(['error' => 'El nombre es obligatorio.'], 400);
    }
    if (array_key_exists('email', $payload) && !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
        respond(['error' => 'El correo electrónico no es válido.'], 400);
    }
    if (array_key_exists('username', $payload) && $payload['username'] === '') {
        $payload['username'] = null;
    }
    if (array_key_exists('phone', $payload) && $payload['phone'] === '') {
        $payload['phone'] = null;
    }
    if (array_key_exists('role', $payload) && !in_array($payload['role'], ['admin', 'empleado'], true)) {
        respond(['error' => 'El rol no es válido.'], 400);
    }

    return $payload;
}

function profileData(array $user): array
{
    return [
        'id' => (int)$user['id'],
        'name' => $user['name'],
        'username' => $user['username'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'role' => $user['role'],
        'createdAt' => $user['createdAt'],
        'lastLoginAt' => $user['lastLoginAt'],
    ];
}

function findProfile(PDO $pdo, int $id): ?array
{
    $statement = $pdo->prepare(
        "SELECT id, name, username, email, phone, role,
                DATE_FORMAT(created_at, '%Y-%m-%dT%H:%i:%sZ') AS createdAt,
                DATE_FORMAT(last_login_at, '%Y-%m-%dT%H:%i:%sZ') AS lastLoginAt
         FROM users WHERE id = ? LIMIT 1"
    );
    $statement->execute([$id]);
    $user = $statement->fetch();
    return $user ?: null;
}

$id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
if (!$id && $_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $body = jsonInput();
    $id = filter_var($body['id'] ?? null, FILTER_VALIDATE_INT);
} else {
    $body = [];
}

if (!$id || $id < 1) {
    respond(['error' => 'El user_id es obligatorio.'], 400);
}

try {
    $pdo = db();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $user = findProfile($pdo, $id);
        respond($user ? ['success' => true, 'data' => profileData($user)] : ['error' => 'Usuario no encontrado'], $user ? 200 : 404);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        $payload = profilePayload($body);
        unset($payload['id']);
        if ($payload === []) {
            respond(['error' => 'No hay campos para actualizar.'], 400);
        }

        $sets = implode(', ', array_map(static fn(string $field): string => "{$field} = ?", array_keys($payload)));
        $values = array_values($payload);
        $values[] = $id;
        $statement = $pdo->prepare("UPDATE users SET {$sets} WHERE id = ?");
        $statement->execute($values);
        $user = findProfile($pdo, $id);
        respond($user ? ['success' => true, 'message' => 'Perfil actualizado correctamente.', 'data' => profileData($user)] : ['error' => 'Usuario no encontrado'], $user ? 200 : 404);
    }

    respond(['error' => 'Método no permitido. Usa GET o PATCH.'], 405);
} catch (PDOException $error) {
    if ((int)$error->errorInfo[1] === 1062) {
        respond(['error' => 'El correo o nombre de usuario ya está registrado.'], 409);
    }
    respond(['error' => 'No fue posible consultar o actualizar el perfil.'], 500);
}
