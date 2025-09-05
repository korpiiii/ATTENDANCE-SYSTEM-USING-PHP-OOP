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

// Get filter values from session or set defaults
$filter_course = isset($_SESSION['attendance_filter']['course']) ? $_SESSION['attendance_filter']['course'] : '';
$filter_year_level = isset($_SESSION['attendance_filter']['year_level']) ? $_SESSION['attendance_filter']['year_level'] : '';
$filter_start_date = isset($_SESSION['attendance_filter']['start_date']) ? $_SESSION['attendance_filter']['start_date'] : '';
$filter_end_date = isset($_SESSION['attendance_filter']['end_date']) ? $_SESSION['attendance_filter']['end_date'] : '';

// Get all courses and year levels
$courses = $admin->getCourses();
$year_levels = Utilities::getYearLevels();

// Get attendance data based on filters
$attendance_data = [];
if (!empty($filter_course) && !empty($filter_year_level)) {
    $attendance_data = $attendance->getAttendanceByCourseAndYear(
        $filter_course,
        $filter_year_level,
        $filter_start_date,
        $filter_end_date
    );
}

// Get students for the selected course and year level
$students = [];
if (!empty($filter_course) && !empty($filter_year_level)) {
    $students = $admin->getStudentsByCourseAndYear($filter_course, $filter_year_level);
}
?>

<?php require_once '../includes/header.php'; ?>

<h2>View Attendance</h2>

<div class="filter-section">
    <h3>Filter Attendance</h3>
    <form action="../processes/attendance_process.php" method="post">
        <input type="hidden" name="action" value="filter_attendance">

        <div class="form-group">
            <label for="course">Course:</label>
            <select id="course" name="course" required>
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['course_name']; ?>"
                        <?php echo ($filter_course == $course['course_name']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['course_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="year_level">Year Level:</label>
            <select id="year_level" name="year_level" required>
                <option value="">Select Year Level</option>
                <?php foreach ($year_levels as $level): ?>
                    <option value="<?php echo $level; ?>"
                        <?php echo ($filter_year_level == $level) ? 'selected' : ''; ?>>
                        <?php echo $level; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="start_date">Start Date (optional):</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo $filter_start_date; ?>">
        </div>

        <div class="form-group">
            <label for="end_date">End Date (optional):</label>
            <input type="date" id="end_date" name="end_date" value="<?php echo $filter_end_date; ?>">
        </div>

        <div class="form-group">
            <button type="submit" class="btn">Apply Filter</button>
        </div>
    </form>
</div>

<?php if (!empty($filter_course) && !empty($filter_year_level)): ?>
    <div class="results-section">
        <h3>Attendance for <?php echo htmlspecialchars($filter_course); ?> - <?php echo htmlspecialchars($filter_year_level); ?></h3>

        <?php if (!empty($filter_start_date) || !empty($filter_end_date)): ?>
            <p>
                Date range:
                <?php echo !empty($filter_start_date) ? Utilities::formatDate($filter_start_date) : 'Start'; ?>
                to
                <?php echo !empty($filter_end_date) ? Utilities::formatDate($filter_end_date) : 'End'; ?>
            </p>
        <?php endif; ?>

        <div class="summary-stats">
            <p>
                Total Students: <?php echo count($students); ?> |
                Total Records: <?php echo count($attendance_data); ?>
            </p>
        </div>

        <?php if (count($attendance_data) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance_data as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($record['full_name']); ?></td>
                            <td><?php echo Utilities::formatDate($record['date']); ?></td>
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
            <p>No attendance records found for the selected filters.</p>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="back-link">
    <a href="admin_dashboard.php">&larr; Back to Dashboard</a>
</div>

<?php require_once '../includes/footer.php'; ?>
