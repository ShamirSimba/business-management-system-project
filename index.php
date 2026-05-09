<?php
// App Entry Point
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/auth/session.php';

// If user is logged in, redirect to dashboard
if ($is_logged_in) {
    header('Location: modules/dashboard/index.php');
    exit;
}

// If not logged in, redirect to login
header('Location: auth/login.php');
exit;