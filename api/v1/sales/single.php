<?php
// Single sale API endpoint for BMS
header('Content-Type: application/json');

require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../../classes/Sale.php';
require_once __DIR__ . '/../../helpers/response.php';

$saleModel = new Sale($conn);
$id = $_GET['id'] ?? null;

if (!$id) {
    ApiResponse::error('Sale ID required', 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sale = $saleModel->getById($id);
    if (!$sale) {
        ApiResponse::error('Sale not found', 404);
    }
    ApiResponse::success($sale);
}

ApiResponse::error('Invalid request method', 405);