<?php
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
    $products = $productModel->getAll($business_id);
    
    $total_stock_value = 0;
    $low_stock_count = 0;
    
    foreach ($products as $prod) {
        $total_stock_value += ($prod['cost_price'] * $prod['stock_qty']);
        if ($prod['stock_qty'] <= $prod['low_stock_threshold']) {
            $low_stock_count++;
        }
    }
    
    $report = [
        'products' => $products,
        'summary' => [
            'total_products' => count($products),
            'total_stock_value' => $total_stock_value,
            'low_stock_items' => $low_stock_count
        ]
    ];
    
    ApiResponse::success($report);
}

ApiResponse::error('Invalid request method', 405);