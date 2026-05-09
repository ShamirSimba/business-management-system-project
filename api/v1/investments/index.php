<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../../classes/Investment.php';
require_once __DIR__ . '/../../helpers/response.php';

$investmentModel = new Investment($conn);
$business_id = $_GET['business_id'] ?? null;

if (!$business_id) {
    ApiResponse::error('business_id parameter required', 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = $investmentModel->getAll($business_id);
    ApiResponse::success($data);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }
    
    if (!isset($input['amount']) || !isset($input['type']) || !isset($input['date'])) {
        ApiResponse::error('Missing required fields: amount, type, date', 400);
    }
    
    if (!in_array($input['type'], ['capital', 'expense'])) {
        ApiResponse::error('Invalid type. Must be capital or expense', 400);
    }
    
    $input['business_id'] = $business_id;
    
    $result = $investmentModel->create($input);
    if ($result) {
        ApiResponse::success(null, 'Investment created successfully', 201);
    } else {
        ApiResponse::error('Failed to create investment', 500);
    }
}

ApiResponse::error('Invalid request method', 405);