<?php
// Redirect if not logged in
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'register.php') {
    header("Location: login.php");
    exit();
}

// Redirect based on role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'student' && basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') {
        header("Location: student_dashboard.php");
        exit();
    } elseif ($_SESSION['role'] == 'admin' && basename($_SERVER['PHP_SELF']) == 'student_dashboard.php') {
        header("Location: admin_dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EAC Attendance System</title>
    <link rel="stylesheet" href="/attendance-system/css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Emilio Aguinaldo College Attendance System</h1>
           <nav>
    <?php if (isset($_SESSION['user_id'])): ?>
        <span>Welcome, <?php echo $_SESSION['username']; ?> (<?php echo $_SESSION['role']; ?>)</span>
        <a href="<?php echo BASE_URL; ?>processes/logout.php">Logout</a>

        <?php if ($_SESSION['role'] == 'student'): ?>
            <a href="<?php echo BASE_URL; ?>views/student_dashboard.php">Dashboard</a>
            <a href="<?php echo BASE_URL; ?>views/file_attendance.php">File Attendance</a>
            <a href="<?php echo BASE_URL; ?>views/attendance_history.php">Attendance History</a>
        <?php elseif ($_SESSION['role'] == 'admin'): ?>
            <a href="<?php echo BASE_URL; ?>views/admin_dashboard.php">Dashboard</a>
            <a href="<?php echo BASE_URL; ?>views/course_management.php">Course Management</a>
            <a href="<?php echo BASE_URL; ?>views/view_attendance.php">View Attendance</a>
        <?php endif; ?>
    <?php else: ?>
        <a href="<?php echo BASE_URL; ?>views/login.php">Login</a>
        <a href="<?php echo BASE_URL; ?>views/register.php">Register</a>
    <?php endif; ?>
</nav>
        </div>
    </header>

    <main class="container"></main>
