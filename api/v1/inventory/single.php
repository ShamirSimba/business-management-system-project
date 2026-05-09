<?php
// Single inventory API endpoint for BMS
header('Content-Type: application/json');

require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../../classes/Product.php';
require_once __DIR__ . '/../../helpers/response.php';

$productModel = new Product($conn);
$id = $_GET['id'] ?? null;

if (!$id) {
    ApiResponse::error('Product ID required', 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = $productModel->getById($id);
    if (!$data) {
        ApiResponse::error('Product not found', 404);
    }
    ApiResponse::success($data);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }
    
    $result = $productModel->update($id, $input);
    if ($result) {
        ApiResponse::success(null, 'Product updated successfully');
    } else {
        ApiResponse::error('Failed to update product', 500);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $result = $productModel->delete($id);
    if ($result) {
        ApiResponse::success(null, 'Product deleted successfully');
    } else {
        ApiResponse::error('Failed to delete product', 500);
    }
}

ApiResponse::error('Invalid request method', 405);