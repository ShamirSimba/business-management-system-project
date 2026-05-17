<?php
// Sale class for BMS

class Sale {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($sale_data, $items_array) {
        $this->conn->begin_transaction();
        try {
            // Calculate total from items
            $total_amount = 0;
            foreach ($items_array as $item) {
                $total_amount += $item['subtotal'];
            }
            
            // Set status and total
            $status = $sale_data['status'] ?? 'completed';
            
            // Insert sale with calculated total
            $stmt = $this->conn->prepare("INSERT INTO sales (business_id, user_id, total_amount, payment_method, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iidss", 
                $sale_data['business_id'], 
                $sale_data['user_id'], 
                $total_amount, 
                $sale_data['payment_method'], 
                $status
            );
            $stmt->execute();
            $sale_id = $this->conn->insert_id;
            $stmt->close();

            // Insert sale items and update stock
            $stmt_item = $this->conn->prepare("INSERT INTO sale_items (sale_id, product_id, qty, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt_stock = $this->conn->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ?");
            foreach ($items_array as $item) {
                $stmt_item->bind_param("iiidd", 
                    $sale_id, 
                    $item['product_id'], 
                    $item['qty'], 
                    $item['unit_price'], 
                    $item['subtotal']
                );
                $stmt_item->execute();
                
                $stmt_stock->bind_param("ii", $item['qty'], $item['product_id']);
                $stmt_stock->execute();
            }
            $stmt_item->close();
            $stmt_stock->close();

            $this->conn->commit();
            return $sale_id;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("Sale creation error: " . $e->getMessage());
            return false;
        }
    }

    public function getAll($business_id, $from = '', $to = '', $payment_method = '') {
        $query = "SELECT * FROM sales WHERE business_id = ?";
        $params = [$business_id];
        $types = "i";

        if (!empty($from) && !empty($to)) {
            $query .= " AND DATE(created_at) BETWEEN ? AND ?";
            $params[] = $from;
            $params[] = $to;
            $types .= "ss";
        }

        if (!empty($payment_method)) {
            $query .= " AND payment_method = ?";
            $params[] = $payment_method;
            $types .= "s";
        }

        $query .= " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM sales WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $sale = $stmt->get_result()->fetch_assoc();
        if ($sale) {
            $stmt_items = $this->conn->prepare("SELECT si.*, p.name as product_name FROM sale_items si JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
            $stmt_items->bind_param("i", $id);
            $stmt_items->execute();
            $sale['items'] = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt_items->close();
        }
        $stmt->close();
        return $sale;
    }

    public function getByDateRange($business_id, $from, $to) {
        $stmt = $this->conn->prepare("SELECT * FROM sales WHERE business_id = ? AND DATE(created_at) BETWEEN ? AND ? ORDER BY created_at DESC");
        $stmt->bind_param("iss", $business_id, $from, $to);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getTodaySales($business_id) {
        $today = date('Y-m-d');
        return $this->getByDateRange($business_id, $today, $today);
    }

    public function getMonthlySales($business_id, $year) {
        $sales = [];
        for ($month = 1; $month <= 12; $month++) {
            $from = sprintf('%04d-%02d-01', $year, $month);
            $to = date('Y-m-t', strtotime($from));
            $stmt = $this->conn->prepare("SELECT SUM(total_amount) as total FROM sales WHERE business_id = ? AND DATE(created_at) BETWEEN ? AND ?");
            $stmt->bind_param("iss", $business_id, $from, $to);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $sales[$month] = $result['total'] ?? 0;
        }
        return $sales;
    }

    public function getSummary($business_id) {
        $today = date('Y-m-d');
        $month_start = date('Y-m-01');
        $month_end = date('Y-m-t');

        // Today's revenue
        $stmt = $this->conn->prepare("SELECT SUM(total_amount) as total FROM sales WHERE business_id = ? AND DATE(created_at) = ?");
        $stmt->bind_param("is", $business_id, $today);
        $stmt->execute();
        $today_revenue = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        // Month's revenue
        $stmt = $this->conn->prepare("SELECT SUM(total_amount) as total FROM sales WHERE business_id = ? AND DATE(created_at) BETWEEN ? AND ?");
        $stmt->bind_param("iss", $business_id, $month_start, $month_end);
        $stmt->execute();
        $month_revenue = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

        // Total sales count
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM sales WHERE business_id = ?");
        $stmt->bind_param("i", $business_id);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_assoc()['count'] ?? 0;

        return [
            'today' => $today_revenue,
            'month' => $month_revenue,
            'count' => $count
        ];
    }

    public function delete($id) {
        $this->conn->begin_transaction();
        try {
            // Get items to restore stock
            $stmt_items = $this->conn->prepare("SELECT product_id, qty FROM sale_items WHERE sale_id = ?");
            $stmt_items->bind_param("i", $id);
            $stmt_items->execute();
            $items = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt_items->close();

            // Restore stock
            $stmt_stock = $this->conn->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?");
            foreach ($items as $item) {
                $stmt_stock->bind_param("ii", $item['qty'], $item['product_id']);
                $stmt_stock->execute();
            }
            $stmt_stock->close();

            // Delete items using prepared statement
            $stmt_delete_items = $this->conn->prepare("DELETE FROM sale_items WHERE sale_id = ?");
            $stmt_delete_items->bind_param("i", $id);
            $stmt_delete_items->execute();
            $stmt_delete_items->close();

            // Delete sale
            $stmt = $this->conn->prepare("DELETE FROM sales WHERE id = ?");
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();
            $stmt->close();

            $this->conn->commit();
            return $result;
        } catch (Exception $e) {
            $this->conn->rollback();
            return false;
        }
    }
}