<?php
// index.php - Main application entry point
session_start();

// Include configuration and classes
require_once 'config/database.php';
require_once 'classes/Database.php';
require_once 'classes/Student.php';
require_once 'classes/Admin.php';
require_once 'classes/Course.php';

// Check for logout success message
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $success = "You have been successfully logged out!";
}

// Initialize database and create tables if they don't exist
try {
    $database = Database::getInstance();
    $database->createTables();
} catch (Exception $e) {
    die("Database initialization failed: " . $e->getMessage());
}

// Check if user is already logged in
if (isset($_SESSION['student_id'])) {
    header('Location: student/dashboard.php');
    exit();
} elseif (isset($_SESSION['admin_logged_in'])) {
    header('Location: admin/dashboard.php');
    exit();
}

// Get courses for registration form
$course = new Course();
$courses = $course->getAll();

// Initialize variables
$error = '';
$page = isset($_GET['page']) ? $_GET['page'] : 'login';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $role = $_POST['role'];
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];

    if ($role === 'student') {
        $student = new Student();
        $user = $student->login($identifier, $password);

        if ($user) {
            $_SESSION['student_id'] = $user['student_id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['course_id'] = $user['course_id'];
            $_SESSION['year_level'] = $user['year_level'];
            $_SESSION['logged_in'] = true;

            header('Location: student/dashboard.php');
            exit();
        } else {
            $error = "Invalid Student ID or password!";
        }
    } elseif ($role === 'admin') {
        $admin = new Admin();
        $user = $admin->login($identifier, $password);

        if ($user) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_logged_in'] = true;

            header('Location: admin/dashboard.php');
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    }
}

// Process student registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_student'])) {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $course_id = $_POST['course_id'];
    $year_level = $_POST['year_level'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $student = new Student();
        $result = $student->register($student_id, $name, $email, $password, $course_id, $year_level);

        switch ($result) {
            case 'success':
                $success = "Registration successful! You can now login.";
                break;
            case 'student_exists':
                $error = "Student ID already exists!";
                break;
            case 'email_exists':
                $error = "Email address already registered!";
                break;
            default:
                $error = "Registration failed. Please try again.";
        }
    }
}

// Process admin registration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_admin'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $role = $_POST['role'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $admin = new Admin();
        $result = $admin->register($username, $email, $password, $full_name, $role);

        switch ($result) {
            case 'success':
                $success = "Admin registration successful! You can now login.";
                break;
            case 'admin_exists':
                $error = "Username already exists!";
                break;
            case 'email_exists':
                $error = "Email address already registered!";
                break;
            default:
                $error = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance System - Smart Campus Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome@6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --card-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 60px;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.1) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            transition: var(--transition);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: var(--primary-gradient);
            border-radius: 20px 20px 0 0 !important;
            font-weight: 600;
            padding: 1.5rem;
            text-align: center;
        }

        .card-body {
            padding: 2rem;
        }

        .btn {
            border-radius: 12px;
            font-weight: 500;
            transition: var(--transition);
            padding: 12px 24px;
            border: none;
        }

        .btn-primary {
            background: var(--primary-gradient);
        }

        .btn-success {
            background: var(--success-gradient);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 12px 16px;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-select {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 12px 16px;
        }

        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .nav-tabs .nav-link {
            border-radius: 12px 12px 0 0;
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-tabs .nav-link.active {
            background: var(--primary-gradient);
            color: white;
            border: none;
        }

        .password-toggle {
            cursor: pointer;
            transition: var(--transition);
        }

        .password-toggle:hover {
            color: #667eea;
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-text {
            text-align: center;
            color: white;
            margin-bottom: 2rem;
        }

        .welcome-text h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .welcome-text p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        footer {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            margin-top: auto;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }

            .welcome-text h1 {
                font-size: 2rem;
            }

            .welcome-text p {
                font-size: 1rem;
            }
        }

        /* Animation for alerts */
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert {
            animation: slideIn 0.3s ease-out;
        }

        /* Custom checkbox */
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-calendar-check me-2"></i>
                <span class="fw-bold">AttendanceSystem</span>
            </a>
            <div class="navbar-nav ms-auto">
                <a href="index.php?page=login" class="nav-link <?php echo $page === 'login' ? 'active' : ''; ?>">
                    <i class="fas fa-sign-in-alt me-1"></i>Login
                </a>
                <a href="index.php?page=register" class="nav-link <?php echo $page === 'register' ? 'active' : ''; ?>">
                    <i class="fas fa-user-plus me-1"></i>Register
                </a>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="container">
            <!-- Welcome Section -->
            <div class="row justify-content-center mb-4">
                <div class="col-12 text-center">
                    <div class="welcome-text">
                        <h1><i class="fas fa-graduation-cap me-3"></i>Smart Attendance System</h1>
                        <p>Streamline your campus attendance management with our advanced tracking system</p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <!-- Success Messages -->
                    <?php if (isset($success) && !empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-3 fs-4"></i>
                                <div><?php echo $success; ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Error Messages -->
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-3 fs-4"></i>
                                <div><?php echo $error; ?></div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($page === 'login'): ?>
                        <!-- Login Form -->
                        <div class="card">
                            <div class="card-header text-white">
                                <h4 class="mb-0"><i class="fas fa-sign-in-alt me-2"></i>Welcome Back</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="login" value="1">

                                    <div class="text-center mb-4">
                                        <i class="fas fa-user-lock feature-icon"></i>
                                        <p class="text-muted">Sign in to access your account</p>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Login As</label>
                                        <select class="form-select" name="role" required>
                                            <option value="student" selected>Student</option>
                                            <option value="admin">Administrator</option>
                                        </select>
                                    </div>

<div class="mb-4">
    <label class="form-label fw-semibold" id="identifier-label">Student ID</label>
    <div class="input-group">
        <span class="input-group-text bg-transparent">
            <i class="fas fa-id-card" id="identifier-icon"></i>
        </span>
        <input type="text" class="form-control" name="identifier" required
               placeholder="Enter your student ID" id="identifier-input">
    </div>
</div>


<script>
    // Password toggle functionality
    document.querySelectorAll('.password-toggle').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Update login fields based on role selection
    function updateLoginFields(role) {
        const label = document.getElementById('identifier-label');
        const icon = document.getElementById('identifier-icon');
        const input = document.getElementById('identifier-input');

        if (role === 'admin') {
            label.textContent = 'Username';
            icon.className = 'fas fa-user-shield';
            input.placeholder = 'Enter your username';
        } else {
            label.textContent = 'Student ID';
            icon.className = 'fas fa-id-card';
            input.placeholder = 'Enter your student ID';
        }
    }

    // Initialize on page load and add event listener
    document.addEventListener('DOMContentLoaded', function() {
        const roleSelect = document.querySelector('select[name="role"]');
        if (roleSelect) {
            // Set initial state
            updateLoginFields(roleSelect.value);

            // Add change event listener
            roleSelect.addEventListener('change', function() {
                updateLoginFields(this.value);
            });
        }
    });

    // Form validation
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const password = this.querySelector('input[name="password"]');
            const confirmPassword = this.querySelector('input[name="confirm_password"]');

            if (password && confirmPassword && password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('Passwords do not match!');
                confirmPassword.focus();
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Smooth scrolling for better UX
    if (window.location.hash) {
        const element = document.querySelector(window.location.hash);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    }
</script>
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-transparent">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <input type="password" class="form-control" name="password" required
                                                   placeholder="Enter your password">
                                            <button type="button" class="btn btn-outline-secondary password-toggle">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-4 form-check">
                                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">Remember me</label>
                                        <a href="#" class="float-end text-decoration-none">Forgot Password?</a>
                                    </div>

                                    <button type="submit" class="btn btn-primary w-100 mb-4 py-2 fw-semibold">
                                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                    </button>

                                    <div class="text-center">
                                        <p class="mb-2 text-muted">Don't have an account?</p>
                                        <a href="index.php?page=register" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-user-plus me-2"></i>Create New Account
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                    <?php elseif ($page === 'register'): ?>
                        <!-- Registration Form -->
                        <div class="card">
                            <div class="card-header text-white">
                                <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Create Account</h4>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <i class="fas fa-user-graduate feature-icon"></i>
                                    <p class="text-muted">Join our attendance management system</p>
                                </div>

                                <ul class="nav nav-tabs nav-justified mb-4" id="registerTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="student-tab" data-bs-toggle="tab" data-bs-target="#student" type="button" role="tab">
                                            <i class="fas fa-user-graduate me-2"></i>Student
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab">
                                            <i class="fas fa-user-shield me-2"></i>Administrator
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="registerTabsContent">
                                    <!-- Student Registration -->
                                    <div class="tab-pane fade show active" id="student" role="tabpanel">
                                        <form method="POST" action="">
                                            <input type="hidden" name="register_student" value="1">

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Student ID *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-transparent">
                                                            <i class="fas fa-id-card"></i>
                                                        </span>
                                                        <input type="text" class="form-control" name="student_id" required
                                                               placeholder="e.g., STU001">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Full Name *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-transparent">
                                                            <i class="fas fa-user"></i>
                                                        </span>
                                                        <input type="text" class="form-control" name="name" required
                                                               placeholder="Enter your full name">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Email Address *</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-transparent">
                                                        <i class="fas fa-envelope"></i>
                                                    </span>
                                                    <input type="email" class="form-control" name="email" required
                                                           placeholder="your.email@school.edu">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Password *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-transparent">
                                                            <i class="fas fa-lock"></i>
                                                        </span>
                                                        <input type="password" class="form-control" name="password" required
                                                               placeholder="Min. 6 characters" minlength="6">
                                                        <button type="button" class="btn btn-outline-secondary password-toggle">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Confirm Password *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-transparent">
                                                            <i class="fas fa-lock"></i>
                                                        </span>
                                                        <input type="password" class="form-control" name="confirm_password" required
                                                               placeholder="Confirm your password">
                                                        <button type="button" class="btn btn-outline-secondary password-toggle">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Course *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-transparent">
                                                            <i class="fas fa-graduation-cap"></i>
                                                        </span>
                                                        <select class="form-select" name="course_id" required>
                                                            <option value="">Select Course</option>
                                                            <?php foreach ($courses as $course): ?>
                                                                <option value="<?php echo $course['id']; ?>">
                                                                    <?php echo $course['course_code'] . ' - ' . $course['course_name']; ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Year Level *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-transparent">
                                                            <i class="fas fa-calendar-alt"></i>
                                                        </span>
                                                        <select class="form-select" name="year_level" required>
                                                            <option value="">Select Year Level</option>
                                                            <option value="1st Year">1st Year</option>
                                                            <option value="2nd Year">2nd Year</option>
                                                            <option value="3rd Year">3rd Year</option>
                                                            <option value="4th Year">4th Year</option>
                                                            <option value="5th Year">5th Year</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                                                <i class="fas fa-user-plus me-2"></i>Register as Student
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Admin Registration -->
                                    <div class="tab-pane fade" id="admin" role="tabpanel">
                                        <form method="POST" action="">
                                            <input type="hidden" name="register_admin" value="1">

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Username *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-transparent">
                                                            <i class="fas fa-user-shield"></i>
                                                        </span>
                                                        <input type="text" class="form-control" name="username" required
                                                               placeholder="Choose a username">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Full Name *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-transparent">
                                                            <i class="fas fa-user"></i>
                                                        </span>
                                                        <input type="text" class="form-control" name="full_name" required
                                                               placeholder="Enter your full name">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Email Address *</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-transparent">
                                                        <i class="fas fa-envelope"></i>
                                                    </span>
                                                    <input type="email" class="form-control" name="email" required
                                                           placeholder="admin@school.edu">
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Password *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-transparent">
                                                            <i class="fas fa-lock"></i>
                                                        </span>
                                                        <input type="password" class="form-control" name="password" required
                                                               placeholder="Min. 6 characters" minlength="6">
                                                        <button type="button" class="btn btn-outline-secondary password-toggle">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label fw-semibold">Confirm Password *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-transparent">
                                                            <i class="fas fa-lock"></i>
                                                        </span>
                                                        <input type="password" class="form-control" name="confirm_password" required
                                                               placeholder="Confirm your password">
                                                        <button type="button" class="btn btn-outline-secondary password-toggle">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label fw-semibold">Role *</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-transparent">
                                                        <i class="fas fa-user-tag"></i>
                                                    </span>
                                                    <select class="form-select" name="role" required>
                                                        <option value="admin">Admin</option>
                                                        <option value="super_admin">Super Admin</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                                                <i class="fas fa-user-shield me-2"></i>Register as Administrator
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <p class="text-muted">Already have an account?</p>
                                    <a href="index.php?page=login" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-sign-in-alt me-2"></i>Sign In Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> Attendance System. All rights reserved.</p>
            <small class="text-muted">Smart Campus Management Solution</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password toggle functionality
        document.querySelectorAll('.password-toggle').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentElement.querySelector('input');
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Update login label based on role selection
        document.querySelector('select[name="role"]')?.addEventListener('change', function() {
            const label = document.getElementById('identifier-label');
            const icon = this.parentElement.querySelector('.input-group-text i');

            if (this.value === 'admin') {
                label.textContent = 'Username';
                icon.className = 'fas fa-user-shield';
            } else {
                label.textContent = 'Student ID';
                icon.className = 'fas fa-id-card';
            }
        });

        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const password = this.querySelector('input[name="password"]');
                const confirmPassword = this.querySelector('input[name="confirm_password"]');

                if (password && confirmPassword && password.value !== confirmPassword.value) {
                    e.preventDefault();
                    alert('Passwords do not match!');
                    confirmPassword.focus();
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Smooth scrolling for better UX
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash) {
                const element = document.querySelector(window.location.hash);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    </script>
</body>
</html>
