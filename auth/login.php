<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/constants.php';

// If already logged in redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/modules/dashboard/index.php');
    exit();
}

// Flash messages
$error   = $_SESSION['flash_error']   ?? '';
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BMS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/auth.css">
</head>
<body>

<div class="auth-container">

    <!-- LEFT SIDE -->
    <div class="left-panel">

        <h1>Hello Friend!</h1>

        <p>
            Create your account and start using the modern BMS platform today.
        </p>

        <a class="switch-link" href="<?= BASE_URL ?>/auth/register.php">
            Register
        </a>

    </div>

    <!-- RIGHT SIDE -->
    <div class="right-panel">

        <h2 class="form-title">Login</h2>

        <?php if ($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/handlers/auth_handler.php" method="POST">

            <input type="hidden" name="action" value="login">

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" class="auth-btn">
                Login
            </button>

        </form>

    </div>

</div>

</body>
</html>

