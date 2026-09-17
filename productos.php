<?php
declare(strict_types=1);
require_once __DIR__ . '/conexion.php';

function productos(string $method, ?int $id): never
{
    $pdo = db();

    if ($method === 'GET' && $id === null) {
        $search = trim((string)($_GET['search'] ?? ''));
        $sql = 'SELECT * FROM productos WHERE active = 1';
        $params = [];
        if ($search !== '') {
            $sql .= ' AND (name LIKE :search OR code LIKE :search OR brand LIKE :search OR category LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        $sql .= ' ORDER BY name';
        $statement = $pdo->prepare($sql);
        $statement->execute($params);
        respond($statement->fetchAll());
    }

    if ($method === 'GET' && $id !== null) {
        $statement = $pdo->prepare('SELECT * FROM productos WHERE id = ? AND active = 1');
        $statement->execute([$id]);
        $product = $statement->fetch();
        respond($product ?: ['error' => 'Producto no encontrado'], $product ? 200 : 404);
    }

    if ($method === 'POST') {
        $data = productPayload(jsonInput());
        $columns = array_keys($data);
        $sql = 'INSERT INTO productos (' . implode(',', $columns) . ') VALUES (' . implode(',', array_fill(0, count($columns), '?')) . ')';
        $pdo->prepare($sql)->execute(array_values($data));
        $newId = (int)$pdo->lastInsertId();
        $statement = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
        $statement->execute([$newId]);
        respond($statement->fetch(), 201);
    }

    if (($method === 'PUT' || $method === 'PATCH') && $id !== null) {
        $data = productPayload(jsonInput(), $method === 'PATCH');
        if ($data === []) respond(['error' => 'No hay campos para actualizar'], 400);
        $sets = implode(', ', array_map(static fn(string $field): string => "{$field} = ?", array_keys($data)));
        $values = array_values($data);
        $values[] = $id;
        $statement = $pdo->prepare("UPDATE productos SET {$sets} WHERE id = ?");
        $statement->execute($values);
        if ($statement->rowCount() === 0) respond(['error' => 'Producto no encontrado'], 404);
        $statement = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
        $statement->execute([$id]);
        respond($statement->fetch());
    }

    if ($method === 'DELETE' && $id !== null) {
        $statement = $pdo->prepare('UPDATE productos SET active = 0 WHERE id = ?');
        $statement->execute([$id]);
        respond(null, $statement->rowCount() ? 204 : 404);
    }

    respond(['error' => 'Método o ruta no permitidos'], 405);
}
