<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../../classes/Business.php';
require_once __DIR__ . '/../../helpers/response.php';

$businessModel = new Business($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $auth_user['id'];
    $data = $businessModel->getAll($user_id);
    ApiResponse::success($data);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }
    
    if (!isset($input['name']) || !isset($input['type'])) {
        ApiResponse::error('Missing required fields: name, type', 400);
    }
    
    $input['user_id'] = $auth_user['id'];
    $input['status'] = $input['status'] ?? 'active';
    
    $result = $businessModel->create($input);
    if ($result) {
        ApiResponse::success(null, 'Business created successfully', 201);
    } else {
        ApiResponse::error('Failed to create business', 500);
    }
}

ApiResponse::error('Invalid request method', 405);