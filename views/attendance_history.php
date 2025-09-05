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

// Get attendance history
$attendance_history = $attendance->getAttendanceHistory($student_id);
$attendance_stats = $attendance->getAttendanceStats($student_id);

// Get current month and year for filter
$current_month = date('m');
$current_year = date('Y');
?>

<?php require_once '../includes/header.php'; ?>

<h2>Attendance History</h2>

<div class="stats-section">
    <div class="stat-card">
        <h3>Total Days</h3>
        <p class="stat"><?php echo $attendance_stats['total_days']; ?></p>
    </div>

    <div class="stat-card">
        <h3>On Time</h3>
        <p class="stat"><?php echo $attendance_stats['on_time_days']; ?></p>
    </div>

    <div class="stat-card">
        <h3>Late</h3>
        <p class="stat"><?php echo $attendance_stats['late_days']; ?></p>
    </div>

    <div class="stat-card">
        <h3>Percentage</h3>
        <p class="stat">
            <?php
            $percentage = ($attendance_stats['total_days'] > 0)
                ? round(($attendance_stats['on_time_days'] / $attendance_stats['total_days']) * 100, 2)
                : 0;
            echo $percentage . '%';
            ?>
        </p>
    </div>
</div>

<div class="filter-section">
    <h3>Filter by Month</h3>
    <form method="get" action="">
        <div class="form-group">
            <label for="month">Month:</label>
            <select id="month" name="month">
                <option value="01" <?php echo ($current_month == '01') ? 'selected' : ''; ?>>January</option>
                <option value="02" <?php echo ($current_month == '02') ? 'selected' : ''; ?>>February</option>
                <option value="03" <?php echo ($current_month == '03') ? 'selected' : ''; ?>>March</option>
                <option value="04" <?php echo ($current_month == '04') ? 'selected' : ''; ?>>April</option>
                <option value="05" <?php echo ($current_month == '05') ? 'selected' : ''; ?>>May</option>
                <option value="06" <?php echo ($current_month == '06') ? 'selected' : ''; ?>>June</option>
                <option value="07" <?php echo ($current_month == '07') ? 'selected' : ''; ?>>July</option>
                <option value="08" <?php echo ($current_month == '08') ? 'selected' : ''; ?>>August</option>
                <option value="09" <?php echo ($current_month == '09') ? 'selected' : ''; ?>>September</option>
                <option value="10" <?php echo ($current_month == '10') ? 'selected' : ''; ?>>October</option>
                <option value="11" <?php echo ($current_month == '11') ? 'selected' : ''; ?>>November</option>
                <option value="12" <?php echo ($current_month == '12') ? 'selected' : ''; ?>>December</option>
            </select>
        </div>

        <div class="form-group">
            <label for="year">Year:</label>
            <select id="year" name="year">
                <?php
                $current_year = date('Y');
                for ($y = $current_year; $y >= $current_year - 5; $y--) {
                    echo "<option value='$y'" . (($y == $current_year) ? ' selected' : '') . ">$y</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn">Filter</button>
        </div>
    </form>
</div>

<div class="table-section">
    <h3>Attendance Records</h3>
    <?php if (count($attendance_history) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Time In</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_history as $record): ?>
                    <tr>
                        <td><?php echo Utilities::formatDate($record['date']); ?></td>
                        <td><?php echo date('l', strtotime($record['date'])); ?></td>
                        <td><?php echo Utilities::formatTime($record['time_in']); ?></td>
                        <td>
                            <span class="status <?php echo $record['is_late'] ? 'late' : 'on-time'; ?>">
                                <?php echo $record['is_late'] ? 'Late' : 'On Time'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No attendance records found.</p>
    <?php endif; ?>
</div>

<div class="back-link">
    <a href="student_dashboard.php">&larr; Back to Dashboard</a>
</div>

<?php require_once '../includes/footer.php'; ?>
