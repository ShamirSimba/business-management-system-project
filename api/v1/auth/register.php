<?php
// Register API endpoint for BMS
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../classes/User.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/validator.php';
require_once __DIR__ . '/../../../includes/functions.php';

$user = new User($conn);
$validator = new Validator();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    ApiResponse::error('Invalid JSON input', 400);
}

$errors = array_merge(
    $validator->required(['name', 'email', 'password', 'role'], $data),
    $validator->email($data['email'] ?? ''),
    $validator->min_length($data['password'] ?? '', 6)
);

if (!empty($errors)) {
    ApiResponse::error(implode(', ', $errors), 400);
}

// Check if email already exists
$existing = $user->findByEmail($data['email']);
if ($existing) {
    ApiResponse::error('Email already registered', 400);
}

$user_data = [
    'name' => sanitize_input($data['name']),
    'email' => sanitize_input($data['email']),
    'password' => $data['password'],
    'role' => in_array($data['role'], ['admin', 'owner', 'staff']) ? $data['role'] : 'staff'
];

if ($user->create($user_data)) {
    ApiResponse::success(null, 'User registered successfully', 201);
} else {
    ApiResponse::error('Registration failed', 500);
}