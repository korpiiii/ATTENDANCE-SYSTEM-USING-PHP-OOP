<?php
require_once '../config.php';
require_once '../includes/auth.php';
require_once '../includes/utilities.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $username = Utilities::sanitizeInput($_POST['username']);
    $email = Utilities::sanitizeInput($_POST['email']);
    $password = Utilities::sanitizeInput($_POST['password']);
    $confirm_password = Utilities::sanitizeInput($_POST['confirm_password']);
    $role = Utilities::sanitizeInput($_POST['role']);

    // Validate input
    $auth = new Auth();

    // Check if passwords match
    if ($password !== $confirm_password) {
        Utilities::redirect('../views/register.php', 'Passwords do not match', 'error');
    }

    // Validate password strength
    if (!$auth->validatePassword($password)) {
        Utilities::redirect('../views/register.php', 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number', 'error');
    }

    // Validate email format
    if (!$auth->validateEmail($email)) {
        Utilities::redirect('../views/register.php', 'Invalid email format', 'error');
    }

    // Check if username is available
    if (!$auth->isUsernameAvailable($username)) {
        Utilities::redirect('../views/register.php', 'Username already taken', 'error');
    }

    // Check if email is available
    if (!$auth->isEmailAvailable($email)) {
        Utilities::redirect('../views/register.php', 'Email already registered', 'error');
    }

    // Prepare registration data
    $data = [
        'username' => $username,
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT)
    ];

    // Add student-specific fields if registering as student
    if ($role === 'student') {
        $student_id = Utilities::sanitizeInput($_POST['student_id']);
        $full_name = Utilities::sanitizeInput($_POST['full_name']);
        $course = Utilities::sanitizeInput($_POST['course']);
        $year_level = Utilities::sanitizeInput($_POST['year_level']);

        // Check if student ID is available
        if (!$auth->isStudentIdAvailable($student_id)) {
            Utilities::redirect('../views/register.php', 'Student ID already registered', 'error');
        }

        $data['student_id'] = $student_id;
        $data['full_name'] = $full_name;
        $data['course'] = $course;
        $data['year_level'] = $year_level;
    }

    // Attempt registration
    $success = $auth->register($data, $role);

    if ($success) {
        // Fix the redirect path
        header("Location: " . BASE_URL . "views/login.php?message=Registration successful! Please login.&type=success");
        exit();
    } else {
        // Fix the redirect path
        header("Location: " . BASE_URL . "views/register.php?message=Registration failed. Please try again.&type=error");
        exit();
    }
} else {
    // Fix the redirect path
    header("Location: " . BASE_URL . "views/register.php?message=Invalid request method&type=error");
    exit();
}
?>
