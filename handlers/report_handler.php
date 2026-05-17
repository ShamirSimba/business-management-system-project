<?php
require_once '../config/database.php';
require_once '../classes/Sale.php';
require_once '../classes/Product.php';
require_once '../classes/Profit.php';
require_once '../classes/Report.php';
require_once '../auth/session.php';

$report_type = $_POST['report_type'] ?? '';
$format = $_POST['format'] ?? 'pdf';
$business_id = $_POST['business_id'] ?? 0;

if (!$business_id) {
    die('Business ID is required');
}

$reportModel = new Report($conn);

try {
    if ($report_type === 'sales') {
        $from = $_POST['from'] ?? date('Y-m-01');
        $to = $_POST['to'] ?? date('Y-m-d');
        $payment_method = $_POST['payment_method'] ?? '';
        
        $saleModel = new Sale($conn);
        $sales = $saleModel->getAll($business_id, $from, $to, $payment_method);
        
        if ($format === 'pdf') {
            $reportModel->exportSalesToPDF($sales, $from, $to, $payment_method);
        } else {
            $reportModel->exportSalesToExcel($sales, $from, $to, $payment_method);
        }
        
    } elseif ($report_type === 'inventory') {
        $category = $_POST['category'] ?? '';
        
        $productModel = new Product($conn);
        $products = $productModel->getAll($business_id, $category);
        
        if ($format === 'pdf') {
            $reportModel->exportInventoryToPDF($products, $category);
        } else {
            $reportModel->exportInventoryToExcel($products, $category);
        }
        
    } elseif ($report_type === 'profit') {
        $from = $_POST['from'] ?? date('Y-m-01');
        $to = $_POST['to'] ?? date('Y-m-d');
        
        $profitModel = new Profit($conn);
        $profit_data = $profitModel->calculate($business_id, $from, $to);
        $monthly = $profitModel->getMonthlyBreakdown($business_id, date('Y', strtotime($from)));
        
        if ($format === 'pdf') {
            $reportModel->exportProfitToPDF($profit_data, $monthly, $from, $to);
        } else {
            $reportModel->exportProfitToExcel($profit_data, $monthly, $from, $to);
        }
    } else {
        die('Invalid report type');
    }
} catch (Exception $e) {
    error_log("Report generation error: " . $e->getMessage());
    die("Error generating report: " . $e->getMessage());
}