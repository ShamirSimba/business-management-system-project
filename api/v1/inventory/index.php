<?php
// Inventory API index for BMS
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
    $search = $_GET['search'] ?? '';
    
    if ($search) {
        $products = $productModel->search($business_id, $search);
    } else {
        $products = $productModel->getAll($business_id);
    }
    
    ApiResponse::success($products);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }
    
    if (!isset($input['name']) || !isset($input['cost_price']) || !isset($input['selling_price'])) {
        ApiResponse::error('Missing required fields: name, cost_price, selling_price', 400);
    }
    
    $input['business_id'] = $business_id;
    $input['stock_qty'] = $input['stock_qty'] ?? 0;
    $input['low_stock_threshold'] = $input['low_stock_threshold'] ?? 10;
    
    $result = $productModel->create($input);
    if ($result) {
        ApiResponse::success(null, 'Product created successfully', 201);
    } else {
        ApiResponse::error('Failed to create product', 500);
    }
}

ApiResponse::error('Invalid request method', 405);