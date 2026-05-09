<?php
// Login API endpoint for BMS
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../classes/User.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/validator.php';

$user = new User($conn);
$validator = new Validator();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    ApiResponse::error('Invalid JSON input', 400);
}

$errors = array_merge(
    $validator->required(['email', 'password'], $data),
    $validator->email($data['email'] ?? '')
);

if (!empty($errors)) {
    ApiResponse::error(implode(', ', $errors), 400);
}

$user_data = $user->login($data['email'], $data['password']);
if (!$user_data) {
    ApiResponse::error('Invalid credentials', 401);
}

$token = bin2hex(random_bytes(32));
$user->updateToken($user_data['id'], $token);

ApiResponse::success([
    'token' => $token,
    'user' => $user_data
], 'Login successful', 200);