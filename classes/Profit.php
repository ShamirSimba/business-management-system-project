<?php
// Profit class for BMS

class Profit {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function calculate($business_id, $from, $to) {
        // Revenue: sum of sales total_amount
        $stmt = $this->conn->prepare("SELECT SUM(total_amount) as revenue FROM sales WHERE business_id = ? AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->bind_param("iss", $business_id, $from, $to);
        $stmt->execute();
        $revenue = $stmt->get_result()->fetch_assoc()['revenue'] ?? 0;

        // COGS: sum of (sale_items.qty * products.cost_price)
        $stmt = $this->conn->prepare("
            SELECT SUM(si.qty * p.cost_price) as cogs
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            JOIN products p ON si.product_id = p.id
            WHERE s.business_id = ? AND DATE(s.created_at) BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $business_id, $from, $to);
        $stmt->execute();
        $cogs = $stmt->get_result()->fetch_assoc()['cogs'] ?? 0;

        // Expenses: sum of investments where type='expense'
        $stmt = $this->conn->prepare("SELECT SUM(amount) as expenses FROM investments WHERE business_id = ? AND type = 'expense' AND date BETWEEN ? AND ?");
        $stmt->bind_param("iss", $business_id, $from, $to);
        $stmt->execute();
        $expenses = $stmt->get_result()->fetch_assoc()['expenses'] ?? 0;

        $gross_profit = $revenue - $cogs;
        $net_profit = $gross_profit - $expenses;

        return [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'expenses' => $expenses,
            'gross_profit' => $gross_profit,
            'net_profit' => $net_profit
        ];
    }

    public function getMonthlyBreakdown($business_id, $year) {
        $breakdown = [];
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $month_shorts = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        for ($month = 1; $month <= 12; $month++) {
            $from = sprintf('%04d-%02d-01', $year, $month);
            $to = date('Y-m-t', strtotime($from));
            $profit_data = $this->calculate($business_id, $from, $to);
            $breakdown[] = [
                'month_name' => $months[$month - 1],
                'month_short' => $month_shorts[$month - 1],
                'revenue' => $profit_data['revenue'] ?? 0,
                'cogs' => $profit_data['cogs'] ?? 0,
                'expenses' => $profit_data['expenses'] ?? 0,
                'gross_profit' => $profit_data['gross_profit'] ?? 0,
                'net_profit' => $profit_data['net_profit'] ?? 0
            ];
        }
        return $breakdown;
    }

    public function getYearlySummary($business_id) {
        $year = date('Y');
        $from = "$year-01-01";
        $to = "$year-12-31";
        return $this->calculate($business_id, $from, $to);
    }
}