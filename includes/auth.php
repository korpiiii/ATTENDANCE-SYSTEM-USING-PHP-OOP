<?php
require_once '../config.php';
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/Student.php';
require_once '../classes/Admin.php';

class Auth {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    // Check if user has a specific role
    public function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    // Require user to be logged in
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            header("Location: " . BASE_URL . "views/login.php");
            exit();
        }
    }

    // Require user to have a specific role
    public function requireRole($role) {
        $this->requireLogin();

        if (!$this->hasRole($role)) {
            // Redirect based on actual role
            if ($this->hasRole('student')) {
                header("Location: " . BASE_URL . "views/student_dashboard.php");
            } else if ($this->hasRole('admin')) {
                header("Location: " . BASE_URL . "views/admin_dashboard.php");
            } else {
                header("Location: " . BASE_URL . "views/login.php");
            }
            exit();
        }
    }

    // Get current user data
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'];

        if ($role === 'student') {
            $query = "SELECT u.*, s.*
                      FROM users u
                      JOIN students s ON u.id = s.user_id
                      WHERE u.id = :user_id";
        } else {
            $query = "SELECT * FROM users WHERE id = :user_id";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Login user
    public function login($username, $password, $role) {
        // Debug: Log login attempt
        error_log("Login attempt: username=$username, role=$role");

        if ($role === 'student') {
            $user = new Student();
        } else if ($role === 'admin') {
            $user = new Admin();
        } else {
            error_log("Invalid role specified: $role");
            return false;
        }

        $result = $user->login($username, $password);

        // Debug: Log login result
        error_log("Login result: " . ($result ? "success" : "failed"));
        if ($result) {
            error_log("Session after login: " . print_r($_SESSION, true));
        }

        return $result;
    }

    // Register user
    public function register($data, $role) {
        if ($role === 'student') {
            $user = new Student();
        } else if ($role === 'admin') {
            $user = new Admin();
        } else {
            return false;
        }

        return $user->register($data);
    }

    // Logout user
    public function logout() {
        // Unset all session variables
        $_SESSION = array();

        // Destroy the session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        // Redirect to login page
        header("Location: " . BASE_URL . "views/login.php");
        exit();
    }

    // Validate password strength
    public function validatePassword($password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/";
        return preg_match($pattern, $password);
    }

    // Validate email format
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Check if username is available
    public function isUsernameAvailable($username) {
        $query = "SELECT id FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        return $stmt->rowCount() === 0;
    }

    // Check if email is available
    public function isEmailAvailable($email) {
        $query = "SELECT id FROM users WHERE email = :email";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        return $stmt->rowCount() === 0;
    }

    // Check if student ID is available
    public function isStudentIdAvailable($student_id) {
        $query = "SELECT id FROM students WHERE student_id = :student_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();

        return $stmt->rowCount() === 0;
    }
}

// Initialize auth object
$auth = new Auth();

// Redirect if not logged in and trying to access protected pages
$public_pages = ['login.php', 'register.php'];
$current_page = basename($_SERVER['PHP_SELF']);

// Debug: Log current page and session status
error_log("Current page: $current_page");
error_log("Session status: " . session_status());
error_log("Is logged in: " . ($auth->isLoggedIn() ? "yes" : "no"));
if ($auth->isLoggedIn()) {
    error_log("User role: " . $_SESSION['role']);
}

if (!$auth->isLoggedIn() && !in_array($current_page, $public_pages)) {
    error_log("Redirecting to login: not logged in and accessing protected page");
    header("Location: " . BASE_URL . "views/login.php");
    exit();
}

// Redirect based on role if accessing wrong dashboard
if ($auth->isLoggedIn()) {
    $role = $_SESSION['role'];

    if ($role === 'student' && $current_page === 'admin_dashboard.php') {
        error_log("Redirecting student from admin dashboard to student dashboard");
        header("Location: " . BASE_URL . "views/student_dashboard.php");
        exit();
    } else if ($role === 'admin' && $current_page === 'student_dashboard.php') {
        error_log("Redirecting admin from student dashboard to admin dashboard");
        header("Location: " . BASE_URL . "views/admin_dashboard.php");
        exit();
    }
}
?>
