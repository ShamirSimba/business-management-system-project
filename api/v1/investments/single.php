<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../../classes/Investment.php';
require_once __DIR__ . '/../../helpers/response.php';

$investmentModel = new Investment($conn);
$id = $_GET['id'] ?? null;

if (!$id) {
    ApiResponse::error('Investment ID required', 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = $investmentModel->getById($id);
    if (!$data) {
        ApiResponse::error('Investment not found', 404);
    }
    ApiResponse::success($data);
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        ApiResponse::error('Invalid JSON input', 400);
    }
    
    $result = $investmentModel->update($id, $input);
    if ($result) {
        ApiResponse::success(null, 'Investment updated successfully');
    } else {
        ApiResponse::error('Failed to update investment', 500);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $result = $investmentModel->delete($id);
    if ($result) {
        ApiResponse::success(null, 'Investment deleted successfully');
    } else {
        ApiResponse::error('Failed to delete investment', 500);
    }
}

ApiResponse::error('Invalid request method', 405);