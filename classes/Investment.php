<?php
// Investment class for BMS

class Investment {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO investments (business_id, amount, type, note, date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("idsss", $data['business_id'], $data['amount'], $data['type'], $data['note'], $data['date']);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getAll($business_id) {
        $stmt = $this->conn->prepare("SELECT * FROM investments WHERE business_id = ? ORDER BY date DESC");
        $stmt->bind_param("i", $business_id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM investments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function update($id, $data) {
        $fields = [];
        $types = "";
        $values = [];
        if (isset($data['amount'])) {
            $fields[] = "amount = ?";
            $types .= "d";
            $values[] = $data['amount'];
        }
        if (isset($data['type'])) {
            $fields[] = "type = ?";
            $types .= "s";
            $values[] = $data['type'];
        }
        if (isset($data['note'])) {
            $fields[] = "note = ?";
            $types .= "s";
            $values[] = $data['note'];
        }
        if (isset($data['date'])) {
            $fields[] = "date = ?";
            $types .= "s";
            $values[] = $data['date'];
        }
        if (empty($fields)) return false;
        $query = "UPDATE investments SET " . implode(", ", $fields) . " WHERE id = ?";
        $types .= "i";
        $values[] = $id;
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM investments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getTotalCapital($business_id) {
        $stmt = $this->conn->prepare("SELECT SUM(amount) as total FROM investments WHERE business_id = ? AND type = 'capital'");
        $stmt->bind_param("i", $business_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0;
    }

    public function getTotalExpenses($business_id) {
        $stmt = $this->conn->prepare("SELECT SUM(amount) as total FROM investments WHERE business_id = ? AND type = 'expense'");
        $stmt->bind_param("i", $business_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['total'] ?? 0;
    }
}