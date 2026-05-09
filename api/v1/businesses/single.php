<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../../classes/Business.php';
require_once __DIR__ . '/../../helpers/response.php';

$businessModel = new Business($conn);
$id = $_GET['id'] ?? null;

if (!$id) {
    ApiResponse::error('Business ID required', 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = $businessModel->getById($id);
    if (!$data) {
        ApiResponse::error('Business not found', 404);
    }
    ApiResponse::success($data);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }
    
    $result = $businessModel->update($id, $input);
    if ($result) {
        ApiResponse::success(null, 'Business updated successfully');
    } else {
        ApiResponse::error('Failed to update business', 500);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $result = $businessModel->delete($id);
    if ($result) {
        ApiResponse::success(null, 'Business deleted successfully');
    } else {
        ApiResponse::error('Failed to delete business', 500);
    }
}

ApiResponse::error('Invalid request method', 405);