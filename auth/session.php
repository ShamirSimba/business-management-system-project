<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load constants using __DIR__ - always reliable
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/constants.php';
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/auth/login.php');
    exit();
}

// Make current user available everywhere
$current_user = [
    'id'    => $_SESSION['user_id'],
    'name'  => $_SESSION['user_name'],
    'email' => $_SESSION['user_email'] ?? '',
    'role'  => $_SESSION['user_role'] ?? 'staff'
];