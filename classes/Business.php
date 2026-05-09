<?php
// Business class for BMS

class Business {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO businesses (user_id, name, type, description, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $data['user_id'], $data['name'], $data['type'], $data['description'], $data['status']);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getAll($user_id) {
        $stmt = $this->conn->prepare("SELECT * FROM businesses WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM businesses WHERE id = ?");
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
        if (isset($data['type'])) {
            $fields[] = "type = ?";
            $types .= "s";
            $values[] = $data['type'];
        }
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $types .= "s";
            $values[] = $data['description'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $types .= "s";
            $values[] = $data['status'];
        }
        if (empty($fields)) return false;
        $query = "UPDATE businesses SET " . implode(", ", $fields) . " WHERE id = ?";
        $types .= "i";
        $values[] = $id;
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM businesses WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getSummary($id) {
        // Total investments (capital)
        $stmt = $this->conn->prepare("SELECT SUM(amount) as total_investments FROM investments WHERE business_id = ? AND type = 'capital'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $investments = $stmt->get_result()->fetch_assoc()['total_investments'] ?? 0;

        // Total sales
        $stmt = $this->conn->prepare("SELECT SUM(total_amount) as total_sales FROM sales WHERE business_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $sales = $stmt->get_result()->fetch_assoc()['total_sales'] ?? 0;

        // Total expenses
        $stmt = $this->conn->prepare("SELECT SUM(amount) as total_expenses FROM investments WHERE business_id = ? AND type = 'expense'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $expenses = $stmt->get_result()->fetch_assoc()['total_expenses'] ?? 0;

        $profit = $sales - $expenses;

        return [
            'total_investments' => $investments,
            'total_sales' => $sales,
            'total_expenses' => $expenses,
            'profit' => $profit
        ];
    }
}