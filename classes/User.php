<?php
// User class for BMS

class User {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function create($data) {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $data['name'], $data['email'], $hashed_password, $data['role']);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                unset($user['password']);
                return $user;
            }
        }
        return false;
    }

    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateToken($user_id, $token) {
        $stmt = $this->conn->prepare("UPDATE users SET api_token = ? WHERE id = ?");
        $stmt->bind_param("si", $token, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function validateToken($token) {
        $stmt = $this->conn->prepare("SELECT id, name, email, role FROM users WHERE api_token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function getAll() {
        $result = $this->conn->query("SELECT id, name, email, role, created_at FROM users");
        return $result->fetch_all(MYSQLI_ASSOC);
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
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $types .= "s";
            $values[] = $data['email'];
        }
        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $types .= "s";
            $values[] = $data['role'];
        }
        if (isset($data['password'])) {
            $fields[] = "password = ?";
            $types .= "s";
            $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if (empty($fields)) return false;
        $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        $types .= "i";
        $values[] = $id;
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}