<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../middleware/auth_middleware.php';
require_once __DIR__ . '/../../../classes/Sale.php';
require_once __DIR__ . '/../../helpers/response.php';

$saleModel = new Sale($conn);
$business_id = $_GET['business_id'] ?? null;

if (!$business_id) {
    ApiResponse::error('business_id parameter required', 400);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $from = $_GET['from'] ?? date('Y-m-01');
    $to = $_GET['to'] ?? date('Y-m-d');
    
    $sales = $saleModel->getByDateRange($business_id, $from, $to);
    
    $total_revenue = 0;
    foreach ($sales as $sale) {
        $total_revenue += $sale['total_amount'];
    }
    
    $report = [
        'sales' => $sales,
        'summary' => [
            'total_sales' => count($sales),
            'total_revenue' => $total_revenue,
            'average_sale' => count($sales) > 0 ? $total_revenue / count($sales) : 0,
            'period' => "$from to $to"
        ]
    ];
    
    ApiResponse::success($report);
}

ApiResponse::error('Invalid request method', 405);