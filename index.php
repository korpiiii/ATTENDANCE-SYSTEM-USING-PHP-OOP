<?php
require_once 'config.php';

// Redirect based on user role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'student') {
        header("Location: views/student_dashboard.php");
    } else {
        header("Location: views/admin_dashboard.php");
    }
} else {
    header("Location: views/login.php");
}
exit();
?>
