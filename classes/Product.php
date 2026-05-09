<?php
// Product class for BMS

class Product {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO products (business_id, name, category, cost_price, selling_price, stock_qty, low_stock_threshold) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issddii", $data['business_id'], $data['name'], $data['category'], $data['cost_price'], $data['selling_price'], $data['stock_qty'], $data['low_stock_threshold']);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getAll($business_id, $category_filter = '', $search = '') {
        $query = "SELECT * FROM products WHERE business_id = ?";
        $params = [$business_id];
        $types = "i";

        if (!empty($category_filter)) {
            $query .= " AND category = ?";
            $params[] = $category_filter;
            $types .= "s";
        }

        if (!empty($search)) {
            $query .= " AND name LIKE ?";
            $params[] = "%$search%";
            $types .= "s";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function update($id, $data) {
        $fields = [];
        $types = "";
        $values = [];
        if (isset($data['name'])) {
            $fields[] = "name = ?";
            $types .= "s";
            $values[] = $data['name'];
        }
        if (isset($data['category'])) {
            $fields[] = "category = ?";
            $types .= "s";
            $values[] = $data['category'];
        }
        if (isset($data['cost_price'])) {
            $fields[] = "cost_price = ?";
            $types .= "d";
            $values[] = $data['cost_price'];
        }
        if (isset($data['selling_price'])) {
            $fields[] = "selling_price = ?";
            $types .= "d";
            $values[] = $data['selling_price'];
        }
        if (isset($data['stock_qty'])) {
            $fields[] = "stock_qty = ?";
            $types .= "i";
            $values[] = $data['stock_qty'];
        }
        if (isset($data['low_stock_threshold'])) {
            $fields[] = "low_stock_threshold = ?";
            $types .= "i";
            $values[] = $data['low_stock_threshold'];
        }
        if (empty($fields)) return false;
        $query = "UPDATE products SET " . implode(", ", $fields) . " WHERE id = ?";
        $types .= "i";
        $values[] = $id;
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function updateStock($id, $qty_change) {
        $stmt = $this->conn->prepare("UPDATE products SET stock_qty = stock_qty + ? WHERE id = ?");
        $stmt->bind_param("ii", $qty_change, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getLowStock($business_id) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE business_id = ? AND stock_qty <= low_stock_threshold");
        $stmt->bind_param("i", $business_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function search($business_id, $keyword) {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE business_id = ? AND (name LIKE ? OR category LIKE ?)");
        $like = "%$keyword%";
        $stmt->bind_param("iss", $business_id, $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getCategories($business_id) {
        $stmt = $this->conn->prepare("SELECT DISTINCT category FROM products WHERE business_id = ? ORDER BY category ASC");
        $stmt->bind_param("i", $business_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row['category'];
        }
        return $categories;
    }
}