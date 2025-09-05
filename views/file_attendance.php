<?php
require_once '../config.php';
require_once '../includes/auth.php';
require_once '../includes/utilities.php';
require_once '../classes/Student.php';
require_once '../classes/Attendance.php';

// Check if user is student
$auth = new Auth();
$auth->requireRole('student');

if (!isset($_SESSION['student_id'])) {
    Utilities::redirect('student_dashboard.php', 'Student data not found', 'error');
}

$student_id = $_SESSION['student_id'];
$attendance = new Attendance();

// Check if already attended today
$today = date('Y-m-d');
$has_attended_today = $attendance->hasAttendance($student_id, $today);
?>

<?php require_once '../includes/header.php'; ?>

<h2>File Attendance</h2>

<?php Utilities::displayFlash(); ?>

<div class="attendance-section">
    <?php if ($has_attended_today): ?>
        <div class="alert alert-info">
            <p>You have already filed your attendance for today.</p>
            <p>You can file attendance again tomorrow.</p>
        </div>

        <?php
        // Get today's attendance record
        $attendance_history = $attendance->getAttendanceHistory($student_id);
        $today_record = null;

        foreach ($attendance_history as $record) {
            if ($record['date'] == $today) {
                $today_record = $record;
                break;
            }
        }
        ?>

        <?php if ($today_record): ?>
            <div class="attendance-details">
                <h3>Today's Attendance Details</h3>
                <p><strong>Date:</strong> <?php echo Utilities::formatDate($today_record['date']); ?></p>
                <p><strong>Time In:</strong> <?php echo Utilities::formatTime($today_record['time_in']); ?></p>
                <p><strong>Status:</strong> <?php echo $today_record['is_late'] ? 'Late' : 'On Time'; ?></p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="attendance-form">
            <p>Click the button below to file your attendance for today.</p>
            <p>Current time: <?php echo date('g:i A'); ?></p>

            <?php
            $current_time = date('H:i:s');
            $is_late = ($current_time > '08:00:00');

            if ($is_late): ?>
                <div class="alert alert-warning">
                    <p>Note: Filing attendance after 8:00 AM will be marked as <strong>Late</strong>.</p>
                </div>
            <?php endif; ?>

            <form action="../processes/attendance_process.php" method="post">
                <input type="hidden" name="action" value="file_attendance">

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">File Attendance Now</button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<div class="back-link">
    <a href="student_dashboard.php">&larr; Back to Dashboard</a>
</div>

<?php require_once '../includes/footer.php'; ?>
