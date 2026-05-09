<?php
require_once '../config/database.php';
require_once '../classes/Sale.php';
require_once '../classes/Product.php';
require_once '../classes/Profit.php';
require_once '../classes/Report.php';
session_start();

$report_type = $_POST['report_type'] ?? '';
$format = $_POST['format'] ?? 'pdf';
$user_id = $_SESSION['user']['id'] ?? null;

if (!$user_id) {
    die('Unauthorized');
}

$reportModel = new Report($conn);
$filename = '';

if ($report_type === 'sales') {
    $from = $_POST['from'] ?? date('Y-m-01');
    $to = $_POST['to'] ?? date('Y-m-d');
    $payment_method = $_POST['payment_method'] ?? '';
    
    $saleModel = new Sale($conn);
    $sales = $saleModel->getAll($user_id, $from, $to, $payment_method);
    
    $filename = 'Sales_Report_' . date('YmdHis') . ($format === 'pdf' ? '.pdf' : '.xlsx');
    
    if ($format === 'pdf') {
        $reportModel->exportSalesToPDF($sales, $filename);
    } else {
        $reportModel->exportSalesToExcel($sales, $filename);
    }
    
} elseif ($report_type === 'inventory') {
    $category = $_POST['category'] ?? '';
    
    $productModel = new Product($conn);
    $products = $productModel->getAll($user_id, $category);
    
    $filename = 'Inventory_Report_' . date('YmdHis') . ($format === 'pdf' ? '.pdf' : '.xlsx');
    
    if ($format === 'pdf') {
        $reportModel->exportInventoryToPDF($products, $filename);
    } else {
        $reportModel->exportInventoryToExcel($products, $filename);
    }
    
} elseif ($report_type === 'profit') {
    $from = $_POST['from'] ?? date('Y-m-01');
    $to = $_POST['to'] ?? date('Y-m-d');
    
    $profitModel = new Profit($conn);
    $profit_data = $profitModel->calculateDateRange($user_id, $from, $to);
    $monthly = $profitModel->getMonthlyBreakdownRange($user_id, $from, $to);
    
    $filename = 'Profit_Report_' . date('YmdHis') . ($format === 'pdf' ? '.pdf' : '.xlsx');
    
    if ($format === 'pdf') {
        $reportModel->exportProfitToPDF($profit_data, $monthly, $filename);
    } else {
        $reportModel->exportProfitToExcel($profit_data, $monthly, $filename);
    }
}

// Output file for download
if ($filename && file_exists('../exports/' . $filename)) {
    header('Content-Type: application/' . ($format === 'pdf' ? 'pdf' : 'vnd.openxmlformats-officedocument.spreadsheetml.sheet'));
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize('../exports/' . $filename));
    readfile('../exports/' . $filename);
    exit;
}