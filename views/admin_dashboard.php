<?php
require_once '../config.php';
require_once '../includes/auth.php';
require_once '../includes/utilities.php';
require_once '../classes/Admin.php';
require_once '../classes/Attendance.php';

// Check if user is admin
$auth = new Auth();
$auth->requireRole('admin');

$admin = new Admin();
$attendance = new Attendance();

// Get statistics
$courses = $admin->getCourses();
$recent_attendance = $attendance->getRecentAttendance(5);
$attendance_summary = $attendance->getAttendanceSummary();
?>

<?php require_once '../includes/header.php'; ?>

<h2>Admin Dashboard</h2>

<div class="dashboard-cards">
    <div class="card">
        <h3>Total Courses</h3>
        <p class="stat"><?php echo count($courses); ?></p>
        <a href="course_management.php" class="btn">Manage Courses</a>
    </div>

    <div class="card">
        <h3>Attendance Summary</h3>
        <p>Total Records: <?php echo array_sum(array_column($attendance_summary, 'total_attendance_records')); ?></p>
        <a href="view_attendance.php" class="btn">View All</a>
    </div>

    <div class="card">
        <h3>Quick Actions</h3>
        <a href="course_management.php" class="btn">Add Course</a>
        <a href="view_attendance.php" class="btn">View Attendance</a>
    </div>
</div>

<div class="dashboard-section">
    <h3>Recent Attendance</h3>
    <?php if (count($recent_attendance) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Course</th>
                    <th>Year Level</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_attendance as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($record['course']); ?></td>
                        <td><?php echo htmlspecialchars($record['year_level']); ?></td>
                        <td><?php echo Utilities::formatDate($record['date']); ?></td>
                        <td><?php echo Utilities::formatTime($record['time_in']); ?></td>
                        <td><?php echo $record['is_late'] ? 'Late' : 'On Time'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No recent attendance records found.</p>
    <?php endif; ?>
</div>

<div class="dashboard-section">
    <h3>Attendance Summary by Course</h3>
    <?php if (count($attendance_summary) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Year Level</th>
                    <th>Total Students</th>
                    <th>Total Records</th>
                    <th>On Time</th>
                    <th>Late</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance_summary as $summary): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($summary['course']); ?></td>
                        <td><?php echo htmlspecialchars($summary['year_level']); ?></td>
                        <td><?php echo $summary['total_students']; ?></td>
                        <td><?php echo $summary['total_attendance_records']; ?></td>
                        <td><?php echo $summary['on_time_records']; ?></td>
                        <td><?php echo $summary['late_records']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No attendance summary available.</p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
