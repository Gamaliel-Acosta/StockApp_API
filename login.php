<?php
/**
 * API de Login - PHP + MySQL
 * Endpoint: POST /login.php
 * Body JSON: {"email":"usuario@correo.com","password":"123456"}
 */

declare(strict_types=1);
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido. Usa POST.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = db();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión con la base de datos. Revisa conexion.php.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ==============================================
// RECIBIR JSON
// ==============================================
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'El cuerpo de la solicitud debe ser JSON válido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$email = trim($input['email'] ?? '');
$userPassword = $input['password'] ?? '';

// ==============================================
// VALIDACIONES
// ==============================================
if ($email === '' || $userPassword === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'El correo y la contraseña son obligatorios.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'El correo electrónico no es válido.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Consulta preparada para evitar SQL Injection.
    $stmt = $pdo->prepare(
        'SELECT id, name, email, password, role, status
         FROM users
         WHERE email = :email
         LIMIT 1'
    );

    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // No revelar si existe el correo o no.
    if (!$user || !password_verify($userPassword, $user['password'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Correo o contraseña incorrectos.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($user['status'] !== 'active') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'La cuenta está inactiva.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Respuesta sin exponer el hash de la contraseña.
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Login exitoso.',
        'user' => [
            'id' => (int) $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role']
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor.'
    ], JSON_UNESCAPED_UNICODE);
}
