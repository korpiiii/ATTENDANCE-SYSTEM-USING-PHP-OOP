<?php
require_once '../config.php';
require_once '../includes/auth.php';
require_once '../classes/Student.php';
require_once '../classes/Attendance.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? Utilities::sanitizeInput($_POST['action']) : '';

    if ($action === 'file_attendance') {
        // Check if user is student
        $auth = new Auth();
        $auth->requireRole('student');

        if (!isset($_SESSION['student_id'])) {
            Utilities::redirect('../views/student_dashboard.php', 'Student data not found', 'error');
        }

        $student_id = $_SESSION['student_id'];
        $attendance = new Attendance();

        $success = $attendance->fileAttendance($student_id);

        if ($success) {
            Utilities::redirect('../views/student_dashboard.php', 'Attendance filed successfully!', 'success');
        } else {
            Utilities::redirect('../views/student_dashboard.php', 'You have already filed attendance for today', 'error');
        }
    } elseif ($action === 'filter_attendance') {
        // Check if user is admin
        $auth = new Auth();
        $auth->requireRole('admin');

        $course = isset($_POST['course']) ? Utilities::sanitizeInput($_POST['course']) : '';
        $year_level = isset($_POST['year_level']) ? Utilities::sanitizeInput($_POST['year_level']) : '';
        $start_date = isset($_POST['start_date']) ? Utilities::sanitizeInput($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? Utilities::sanitizeInput($_POST['end_date']) : '';

        // Store filter values in session for persistence
        $_SESSION['attendance_filter'] = [
            'course' => $course,
            'year_level' => $year_level,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];

        Utilities::redirect('../views/view_attendance.php');
    } else {
        Utilities::redirect('../views/student_dashboard.php', 'Invalid action', 'error');
    }
} else {
    Utilities::redirect('../views/student_dashboard.php', 'Invalid request method', 'error');
}
?>
