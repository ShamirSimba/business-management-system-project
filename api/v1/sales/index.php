<?php
// Sales API index for BMS
header('Content-Type: application/json');

require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../../classes/Sale.php';
require_once __DIR__ . '/../../../classes/Product.php';
require_once __DIR__ . '/../../helpers/response.php';

$saleModel = new Sale($conn);
$productModel = new Product($conn);
$user_id = $auth_user['id'];
$business_id = $_GET['business_id'] ?? null;

if (!$business_id) {
    ApiResponse::error('business_id parameter required', 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $from = $_GET['from'] ?? date('Y-m-01');
    $to = $_GET['to'] ?? date('Y-m-d');
    
    $sales = $saleModel->getByDateRange($business_id, $from, $to);
    ApiResponse::success($sales);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }
    
    $payment_method = $input['payment_method'] ?? null;
    $items = $input['items'] ?? [];
    
    if (!$payment_method || !is_array($items) || empty($items)) {
        ApiResponse::error('Missing required fields: payment_method, items array', 400);
    }
    
    // Validate items and calculate total
    $items_array = [];
    foreach ($items as $item) {
        if (!isset($item['product_id']) || !isset($item['qty']) || !isset($item['unit_price'])) {
            ApiResponse::error('Each item must have product_id, qty, and unit_price', 400);
        }
        
        $prod = $productModel->getById($item['product_id']);
        if (!$prod) {
            ApiResponse::error('Product not found: ' . $item['product_id'], 404);
        }
        
        if ($prod['stock_qty'] < $item['qty']) {
            ApiResponse::error('Insufficient stock for: ' . $prod['name'] . ' (Available: ' . $prod['stock_qty'] . ')', 400);
        }
        
        $items_array[] = [
            'product_id' => $item['product_id'],
            'qty' => intval($item['qty']),
            'unit_price' => floatval($item['unit_price']),
            'subtotal' => floatval($item['qty'] * $item['unit_price'])
        ];
    }
    
    // Prepare sale data
    $sale_data = [
        'user_id' => $user_id,
        'business_id' => $business_id,
        'payment_method' => $payment_method,
        'status' => 'completed'
    ];
    
    $new_id = $saleModel->create($sale_data, $items_array);
    if ($new_id) {
        $sale = $saleModel->getById($new_id);
        ApiResponse::success($sale, 'Sale recorded successfully', 201);
    } else {
        ApiResponse::error('Failed to record sale', 500);
    }
}

ApiResponse::error('Invalid request method', 405);