<?php
declare(strict_types=1);
require_once __DIR__ . '/conexion.php';

function dashboard(): never
{
    $pdo = db();
    $summary = $pdo->query(
        'SELECT COUNT(*) totalProducts,
                COALESCE(SUM(CASE WHEN available > 0 AND available <= minimum_stock THEN 1 ELSE 0 END), 0) lowStock,
                COALESCE(SUM(CASE WHEN available = 0 THEN 1 ELSE 0 END), 0) outOfStock,
                COALESCE(ROUND(100 * SUM(CASE WHEN available > 0 THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0)), 0) healthyPercentage
         FROM productos WHERE active = 1'
    )->fetch();

    $movements = $pdo->query(
        "SELECT m.id, m.product_id AS productId, p.name AS product, m.type, m.quantity,
                DATE_FORMAT(m.created_at, '%Y-%m-%dT%H:%i:%sZ') AS date,
                'barcode-outline' AS icon
         FROM movimientos m JOIN productos p ON p.id = m.product_id
         ORDER BY m.created_at DESC LIMIT 20"
    )->fetchAll();

    respond([
        'totalProducts' => (int)$summary['totalProducts'],
        'lowStock' => (int)$summary['lowStock'],
        'outOfStock' => (int)$summary['outOfStock'],
        'healthyPercentage' => (int)$summary['healthyPercentage'],
        'recentMovements' => $movements,
        'updatedAt' => date(DATE_ATOM),
    ]);
}
