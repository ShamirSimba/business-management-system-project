<?php
// Authentication middleware for BMS API

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../classes/User.php';
require_once __DIR__ . '/../helpers/response.php';

header('Content-Type: application/json');

$user = new User($conn);

// Get authorization header - works with both Apache and FastCGI
$auth_header = '';
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
} elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
    $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
} elseif (function_exists('apache_request_headers')) {
    $headers = apache_request_headers();
    $auth_header = $headers['Authorization'] ?? '';
}

if (empty($auth_header)) {
    ApiResponse::error('Authorization header missing', 401);
}

if (!preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
    ApiResponse::error('Authorization header format invalid. Use: Bearer <token>', 401);
}

$token = sanitize_string($matches[1]);
$auth_user = $user->validateToken($token);

if (!$auth_user) {
    ApiResponse::error('Invalid or expired token', 401);
}

function sanitize_string($str) {
    return trim(htmlspecialchars($str, ENT_QUOTES, 'UTF-8'));
}

// $auth_user is now available for use in the endpoint