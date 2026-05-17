<?php
// Report class for BMS - Simplified export without external dependencies

class Report {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getSalesReport($business_id, $from, $to) {
        $stmt = $this->conn->prepare("
            SELECT s.*, u.name as user_name, 
                   GROUP_CONCAT(CONCAT(p.name, ' (', si.qty, ' x ', si.unit_price, ')') SEPARATOR '; ') as items
            FROM sales s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN sale_items si ON s.id = si.sale_id
            LEFT JOIN products p ON si.product_id = p.id
            WHERE s.business_id = ? AND DATE(s.created_at) BETWEEN ? AND ?
            GROUP BY s.id
            ORDER BY s.created_at DESC
        ");
        $stmt->bind_param("iss", $business_id, $from, $to);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getInventoryReport($business_id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE business_id = ? ORDER BY stock_qty ASC");
        $stmt->bind_param("i", $business_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getProfitReport($business_id, $from, $to) {
        require_once __DIR__ . '/Profit.php';
        $profit = new Profit($this->conn);
        return $profit->calculate($business_id, $from, $to);
    }

    public function exportSalesToPDF($sales, $from, $to, $payment_method = '', $business_id = 0) {
        $total = 0;
        foreach ($sales as $sale) {
            $total += $sale['total_amount'];
        }
        
        $html = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Sales Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #3b82f6; color: white; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .summary { margin-top: 30px; font-size: 18px; font-weight: bold; }
        @media print {
            body { margin: 0; padding: 20px; }
        }
    </style>
</head>
<body>
    <h1>Sales Report</h1>
    <p><strong>Period:</strong> $from to $to</p>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Sale ID</th>
                <th>Items</th>
                <th>Amount (TZS)</th>
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody>";
        
        foreach ($sales as $sale) {
            $stmt_items = $this->conn->prepare("SELECT COUNT(*) as count FROM sale_items WHERE sale_id = ?");
            $stmt_items->bind_param("i", $sale['id']);
            $stmt_items->execute();
            $items_result = $stmt_items->get_result()->fetch_assoc();
            $stmt_items->close();
            
            $date = date('Y-m-d H:i', strtotime($sale['created_at']));
            $amount = number_format($sale['total_amount'], 2);
            $payment = ucfirst($sale['payment_method']);
            $count = $items_result['count'];
            
            $html .= "<tr>
                <td>$date</td>
                <td>{$sale['id']}</td>
                <td>$count</td>
                <td>$amount</td>
                <td>$payment</td>
            </tr>";
        }
        
        $total_formatted = number_format($total, 2);
        $html .= "</tbody>
        </table>
        <div class='summary'>
            <p>Total Revenue: TZS $total_formatted</p>
            <p>Total Records: " . count($sales) . "</p>
        </div>
        <p style='text-align:center; margin-top: 30px; color: #666; font-size: 12px;'>
            Generated on " . date('Y-m-d H:i:s') . " | Print this page to save as PDF
        </p>
        <script>
            window.print();
        </script>
    </body>
</html>";
        
        echo $html;
        exit;
    }

    public function exportSalesToExcel($sales, $from, $to, $payment_method = '') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Sales_Report_' . date('YmdHis') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['Date', 'Sale ID', 'Items', 'Amount (TZS)', 'Payment Method']);
        
        $total = 0;
        foreach ($sales as $sale) {
            $stmt_items = $this->conn->prepare("SELECT COUNT(*) as count FROM sale_items WHERE sale_id = ?");
            $stmt_items->bind_param("i", $sale['id']);
            $stmt_items->execute();
            $items_result = $stmt_items->get_result()->fetch_assoc();
            $stmt_items->close();
            
            $date = date('Y-m-d H:i', strtotime($sale['created_at']));
            fputcsv($output, [
                $date,
                $sale['id'],
                $items_result['count'],
                $sale['total_amount'],
                $sale['payment_method']
            ]);
            $total += $sale['total_amount'];
        }
        
        fputcsv($output, []);
        fputcsv($output, ['Total Revenue', number_format($total, 2)]);
        
        fclose($output);
        exit;
    }

    public function exportInventoryToPDF($products, $category_filter = '') {
        $total_stock_value = 0;
        foreach ($products as $prod) {
            $total_stock_value += ($prod['cost_price'] * $prod['stock_qty']);
        }
        
        $html = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Inventory Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #3b82f6; color: white; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .summary { margin-top: 30px; font-size: 18px; font-weight: bold; }
        @media print {
            body { margin: 0; padding: 20px; }
        }
    </style>
</head>
<body>
    <h1>Inventory Report</h1>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Cost Price (TZS)</th>
                <th>Selling Price (TZS)</th>
                <th>Stock Qty</th>
                <th>Stock Value (TZS)</th>
            </tr>
        </thead>
        <tbody>";
        
        foreach ($products as $prod) {
            $stock_value = $prod['cost_price'] * $prod['stock_qty'];
            $cost_price = number_format($prod['cost_price'], 2);
            $selling_price = number_format($prod['selling_price'], 2);
            $stock_value_formatted = number_format($stock_value, 2);
            
            $html .= "<tr>
                <td>" . htmlspecialchars($prod['name']) . "</td>
                <td>" . htmlspecialchars($prod['category']) . "</td>
                <td>$cost_price</td>
                <td>$selling_price</td>
                <td>{$prod['stock_qty']}</td>
                <td>$stock_value_formatted</td>
            </tr>";
        }
        
        $total_value_formatted = number_format($total_stock_value, 2);
        $html .= "</tbody>
        </table>
        <div class='summary'>
            <p>Total Stock Value: TZS $total_value_formatted</p>
            <p>Total Products: " . count($products) . "</p>
        </div>
        <p style='text-align:center; margin-top: 30px; color: #666; font-size: 12px;'>
            Generated on " . date('Y-m-d H:i:s') . " | Print this page to save as PDF
        </p>
        <script>
            window.print();
        </script>
    </body>
</html>";
        
        echo $html;
        exit;
    }

    public function exportInventoryToExcel($products, $category_filter = '') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Inventory_Report_' . date('YmdHis') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['Product', 'Category', 'Cost Price (TZS)', 'Selling Price (TZS)', 'Stock Qty', 'Stock Value (TZS)']);
        
        $total_stock_value = 0;
        foreach ($products as $prod) {
            $stock_value = $prod['cost_price'] * $prod['stock_qty'];
            fputcsv($output, [
                $prod['name'],
                $prod['category'],
                $prod['cost_price'],
                $prod['selling_price'],
                $prod['stock_qty'],
                $stock_value
            ]);
            $total_stock_value += $stock_value;
        }
        
        fputcsv($output, []);
        fputcsv($output, ['Total Stock Value', number_format($total_stock_value, 2)]);
        
        fclose($output);
        exit;
    }

    public function exportProfitToPDF($profit_data, $monthly, $from = '', $to = '') {
        $html = "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Profit Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { text-align: center; }
        .summary-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 20px 0; }
        .card { border: 2px solid #3b82f6; padding: 15px; border-radius: 8px; }
        .card-label { font-weight: bold; color: #666; }
        .card-value { font-size: 24px; font-weight: bold; color: #3b82f6; margin-top: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #3b82f6; color: white; }
        tr:nth-child(even) { background-color: #f9fafb; }
        @media print {
            body { margin: 0; padding: 20px; }
            .summary-cards { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <h1>Profit Report</h1>";
    
    if (!empty($from) && !empty($to)) {
        $html .= "<p><strong>Period:</strong> $from to $to</p>";
    }
    
    $revenue = number_format($profit_data['revenue'], 2);
    $cogs = number_format($profit_data['cogs'], 2);
    $expenses = number_format($profit_data['expenses'], 2);
    $gross = number_format($profit_data['gross_profit'], 2);
    $net = number_format($profit_data['net_profit'], 2);
    
    $html .= "<div class='summary-cards'>
        <div class='card'>
            <div class='card-label'>Total Revenue</div>
            <div class='card-value'>TZS $revenue</div>
        </div>
        <div class='card'>
            <div class='card-label'>Total COGS</div>
            <div class='card-value'>TZS $cogs</div>
        </div>
        <div class='card'>
            <div class='card-label'>Total Expenses</div>
            <div class='card-value'>TZS $expenses</div>
        </div>
        <div class='card'>
            <div class='card-label'>Gross Profit</div>
            <div class='card-value'>TZS $gross</div>
        </div>
    </div>";
    
    if (!empty($monthly)) {
        $html .= "<h2>Monthly Breakdown</h2>
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Revenue (TZS)</th>
                    <th>COGS (TZS)</th>
                    <th>Expenses (TZS)</th>
                    <th>Gross Profit (TZS)</th>
                    <th>Net Profit (TZS)</th>
                </tr>
            </thead>
            <tbody>";
        
        foreach ($monthly as $m) {
            $html .= "<tr>
                <td>" . htmlspecialchars($m['month_name']) . "</td>
                <td>" . number_format($m['revenue'], 2) . "</td>
                <td>" . number_format($m['cogs'], 2) . "</td>
                <td>" . number_format($m['expenses'], 2) . "</td>
                <td>" . number_format($m['gross_profit'], 2) . "</td>
                <td>" . number_format($m['net_profit'], 2) . "</td>
            </tr>";
        }
        
        $html .= "</tbody></table>";
    }
    
    $html .= "<p style='text-align:center; margin-top: 30px; color: #666; font-size: 12px;'>
        Generated on " . date('Y-m-d H:i:s') . " | Print this page to save as PDF
    </p>
    <script>
        window.print();
    </script>
    </body>
</html>";
    
    echo $html;
    exit;
    }

    public function exportProfitToExcel($profit_data, $monthly, $from = '', $to = '') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Profit_Report_' . date('YmdHis') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, ['Profit Report']);
        fputcsv($output, []);
        fputcsv($output, ['Metric', 'Amount (TZS)']);
        fputcsv($output, ['Total Revenue', $profit_data['revenue']]);
        fputcsv($output, ['Total COGS', $profit_data['cogs']]);
        fputcsv($output, ['Total Expenses', $profit_data['expenses']]);
        fputcsv($output, ['Gross Profit', $profit_data['gross_profit']]);
        fputcsv($output, ['Net Profit', $profit_data['net_profit']]);
        fputcsv($output, []);
        
        if (!empty($monthly)) {
            fputcsv($output, ['Monthly Breakdown']);
            fputcsv($output, ['Month', 'Revenue (TZS)', 'COGS (TZS)', 'Expenses (TZS)', 'Gross Profit (TZS)', 'Net Profit (TZS)']);
            
            foreach ($monthly as $m) {
                fputcsv($output, [
                    $m['month_name'],
                    $m['revenue'],
                    $m['cogs'],
                    $m['expenses'],
                    $m['gross_profit'],
                    $m['net_profit']
                ]);
            }
        }
        
        fclose($output);
        exit;
    }
}
