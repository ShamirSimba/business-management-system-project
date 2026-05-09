<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../api/helpers/validator.php';

$user = new User($conn);
$validator = new Validator();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $errors = $validator->required(['email', 'password'], ['email' => $email, 'password' => $password]);
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header("Location: " . BASE_URL . "auth/login.php");
            exit;
        }

        $user_data = $user->login($email, $password);
        if ($user_data) {
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['user_name'] = $user_data['name'];
            $_SESSION['user_email'] = $user_data['email'];
            $_SESSION['user_role'] = $user_data['role'];
            header("Location: " . BASE_URL . "modules/dashboard/index.php");
            exit;
        } else {
            $_SESSION['error'] = 'Invalid email or password';
            header("Location: " . BASE_URL . "auth/login.php");
            exit;
        }
    } elseif ($action === 'register') {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = sanitize_input($_POST['role'] ?? 'staff');
        
        $errors = array_merge(
            $validator->required(['name', 'email', 'password', 'confirm_password', 'role'], 
                                ['name' => $name, 'email' => $email, 'password' => $password, 
                                 'confirm_password' => $confirm_password, 'role' => $role]),
            $validator->email($email),
            $validator->min_length($password, 6)
        );

        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            header("Location: " . BASE_URL . "auth/register.php");
            exit;
        }

        $data = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ];

        if ($user->create($data)) {
            $_SESSION['success'] = 'Registration successful. Please login.';
            header("Location: " . BASE_URL . "auth/login.php");
            exit;
        } else {
            $_SESSION['error'] = 'Registration failed. Email may already exist.';
            header("Location: " . BASE_URL . "auth/register.php");
            exit;
        }
    }
}

header("Location: " . BASE_URL . "auth/login.php");
exit;