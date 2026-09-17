<?php
declare(strict_types=1);
require_once __DIR__ . '/conexion.php';

function movimientos(string $method, ?int $id): never
{
    $pdo = db();

    if ($method === 'GET') {
        $sql = 'SELECT m.*, p.name AS product FROM movimientos m JOIN productos p ON p.id = m.product_id';
        $params = [];
        if ($id !== null) {
            $sql .= ' WHERE m.id = ?';
            $params[] = $id;
        }
        $sql .= ' ORDER BY m.created_at DESC LIMIT 100';
        $statement = $pdo->prepare($sql);
        $statement->execute($params);
        $rows = $statement->fetchAll();
        respond($id === null ? $rows : ($rows[0] ?? ['error' => 'Movimiento no encontrado']), $id !== null && !$rows ? 404 : 200);
    }

    if ($method === 'POST') {
        $data = jsonInput();
        $productId = (int)($data['product_id'] ?? 0);
        $type = $data['type'] ?? '';
        $quantity = (int)($data['quantity'] ?? 0);
        if ($productId < 1 || !in_array($type, ['Entrada', 'Salida'], true) || $quantity < 1) {
            respond(['error' => 'product_id, type y quantity son obligatorios'], 400);
        }

        $pdo->beginTransaction();
        try {
            $change = $type === 'Entrada' ? $quantity : -$quantity;
            $statement = $pdo->prepare('UPDATE productos SET available = available + ? WHERE id = ? AND active = 1 AND available + ? >= 0');
            $statement->execute([$change, $productId, $change]);
            if ($statement->rowCount() !== 1) throw new RuntimeException('Producto inexistente o stock insuficiente');
            $statement = $pdo->prepare('INSERT INTO movimientos (product_id, type, quantity, notes) VALUES (?, ?, ?, ?)');
            $statement->execute([$productId, $type, $quantity, $data['notes'] ?? null]);
            $newId = (int)$pdo->lastInsertId();
            $pdo->commit();
            respond(['id' => $newId, 'message' => 'Movimiento registrado'], 201);
        } catch (Throwable $error) {
            $pdo->rollBack();
            respond(['error' => $error->getMessage()], 409);
        }
    }

    if (($method === 'PUT' || $method === 'PATCH') && $id !== null) {
        $data = jsonInput();
        $pdo->beginTransaction();
        try {
            $statement = $pdo->prepare('SELECT product_id, type, quantity, notes FROM movimientos WHERE id = ? FOR UPDATE');
            $statement->execute([$id]);
            $old = $statement->fetch();
            if (!$old) respond(['error' => 'Movimiento no encontrado'], 404);

            $newProductId = (int)($data['product_id'] ?? $old['product_id']);
            $newType = $data['type'] ?? $old['type'];
            $newQuantity = (int)($data['quantity'] ?? $old['quantity']);
            $newNotes = $data['notes'] ?? $old['notes'];
            if ($newProductId < 1 || !in_array($newType, ['Entrada', 'Salida'], true) || $newQuantity < 1) {
                respond(['error' => 'Datos de movimiento inválidos'], 400);
            }

            $oldChange = $old['type'] === 'Entrada' ? -(int)$old['quantity'] : (int)$old['quantity'];
            $newChange = $newType === 'Entrada' ? $newQuantity : -$newQuantity;
            $statement = $pdo->prepare('UPDATE productos SET available = available + ? WHERE id = ? AND available + ? >= 0');
            $statement->execute([$oldChange, $old['product_id'], $oldChange]);
            if ($statement->rowCount() !== 1) throw new RuntimeException('No se pudo revertir el movimiento anterior');
            $statement = $pdo->prepare('UPDATE productos SET available = available + ? WHERE id = ? AND active = 1 AND available + ? >= 0');
            $statement->execute([$newChange, $newProductId, $newChange]);
            if ($statement->rowCount() !== 1) throw new RuntimeException('Producto inexistente o stock insuficiente');
            $statement = $pdo->prepare('UPDATE movimientos SET product_id = ?, type = ?, quantity = ?, notes = ? WHERE id = ?');
            $statement->execute([$newProductId, $newType, $newQuantity, $newNotes, $id]);
            $pdo->commit();
            respond(['id' => $id, 'message' => 'Movimiento actualizado']);
        } catch (Throwable $error) {
            $pdo->rollBack();
            respond(['error' => $error->getMessage()], 409);
        }
    }

    if ($method === 'DELETE' && $id !== null) {
        $pdo->beginTransaction();
        try {
            $statement = $pdo->prepare('SELECT product_id, type, quantity FROM movimientos WHERE id = ? FOR UPDATE');
            $statement->execute([$id]);
            $movement = $statement->fetch();
            if (!$movement) respond(['error' => 'Movimiento no encontrado'], 404);
            $change = $movement['type'] === 'Entrada' ? -(int)$movement['quantity'] : (int)$movement['quantity'];
            $statement = $pdo->prepare('UPDATE productos SET available = available + ? WHERE id = ? AND available + ? >= 0');
            $statement->execute([$change, $movement['product_id'], $change]);
            $pdo->prepare('DELETE FROM movimientos WHERE id = ?')->execute([$id]);
            $pdo->commit();
            respond(null, 204);
        } catch (Throwable $error) {
            $pdo->rollBack();
            respond(['error' => $error->getMessage()], 409);
        }
    }

    respond(['error' => 'Método o ruta no permitidos'], 405);
}
