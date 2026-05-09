<?php
// Profits API endpoint for BMS
header('Content-Type: application/json');

require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../../classes/Profit.php';
require_once __DIR__ . '/../../helpers/response.php';

$profitModel = new Profit($conn);
$business_id = $_GET['business_id'] ?? null;

if (!$business_id) {
    ApiResponse::error('business_id parameter required', 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $from = $_GET['from'] ?? date('Y-m-01');
    $to = $_GET['to'] ?? date('Y-m-d');
    
    $profit_data = $profitModel->calculate($business_id, $from, $to);
    ApiResponse::success($profit_data);
}

ApiResponse::error('Invalid request method', 405);