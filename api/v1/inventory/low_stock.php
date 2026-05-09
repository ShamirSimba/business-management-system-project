<?php
// Low stock inventory API endpoint for BMS
header('Content-Type: application/json');

require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../../classes/Product.php';
require_once __DIR__ . '/../../helpers/response.php';

$productModel = new Product($conn);
$business_id = $_GET['business_id'] ?? null;

if (!$business_id) {
    ApiResponse::error('business_id parameter required', 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $low_stock = $productModel->getLowStock($business_id);
    ApiResponse::success($low_stock);
}

ApiResponse::error('Invalid request method', 405);