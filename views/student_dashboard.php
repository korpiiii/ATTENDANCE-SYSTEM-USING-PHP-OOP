<?php
require_once '../config.php';
require_once '../classes/Student.php';
require_once '../includes/header.php';

// Check if user is student
if ($_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$student = new Student();
$attendance_history = $student->getAttendanceHistory($_SESSION['student_id']);
$today = date('Y-m-d');
$attended_today = false;

foreach ($attendance_history as $record) {
    if ($record['date'] == $today) {
        $attended_today = true;
        break;
    }
}
?>

<h2>Student Dashboard</h2>

<div class="dashboard-cards">
    <div class="card">
        <h3>Welcome, <?php echo $_SESSION['student_data']['full_name']; ?></h3>
        <p>Course: <?php echo $_SESSION['student_data']['course']; ?></p>
        <p>Year Level: <?php echo $_SESSION['student_data']['year_level']; ?></p>
    </div>

    <div class="card">
        <h3>Today's Status</h3>
        <?php if ($attended_today): ?>
            <p class="success">You have already filed your attendance for today.</p>
        <?php else: ?>
            <p class="warning">You haven't filed your attendance for today.</p>
            <a href="file_attendance.php" class="btn">File Attendance Now</a>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Attendance Summary</h3>
        <p>Total Attendance Records: <?php echo count($attendance_history); ?></p>
        <a href="attendance_history.php" class="btn">View Full History</a>
    </div>
</div>

<h3>Recent Attendance</h3>
<?php if (count($attendance_history) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Time In</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $i < min(5, count($attendance_history)); $i++): ?>
                <tr>
                    <td><?php echo $attendance_history[$i]['date']; ?></td>
                    <td><?php echo $attendance_history[$i]['time_in']; ?></td>
                    <td><?php echo $attendance_history[$i]['is_late'] ? 'Late' : 'On Time'; ?></td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No attendance records found.</p>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
