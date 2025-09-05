<?php
require_once '../config.php';
require_once '../includes/auth.php';

// Debug: Log POST data
error_log("Login POST data: " . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $username = isset($_POST['username']) ? Utilities::sanitizeInput($_POST['username']) : '';
    $password = isset($_POST['password']) ? Utilities::sanitizeInput($_POST['password']) : '';
    $role = isset($_POST['role']) ? Utilities::sanitizeInput($_POST['role']) : '';

    // Debug: Log input values
    error_log("Login attempt - Username: $username, Role: $role");

    // Validate input
    if (empty($username) || empty($password) || empty($role)) {
        error_log("Login failed: Missing required fields");
        Utilities::redirect('../views/login.php', 'Please fill all required fields', 'error');
    }

    // Attempt login
    $auth = new Auth();
    $success = $auth->login($username, $password, $role);

    if ($success) {
        // Fix the redirect path
        if ($role === 'student') {
            header("Location: " . BASE_URL . "views/student_dashboard.php?message=Login successful!&type=success");
        } else {
            header("Location: " . BASE_URL . "views/admin_dashboard.php?message=Login successful!&type=success");
        }
        exit();
    } else {
        // Fix the redirect path
        header("Location: " . BASE_URL . "views/login.php?message=Invalid username or password&type=error");
        exit();
    }
} else {
    // Fix the redirect path
    header("Location: " . BASE_URL . "views/login.php?message=Invalid request method&type=error");
    exit();
}
?>
