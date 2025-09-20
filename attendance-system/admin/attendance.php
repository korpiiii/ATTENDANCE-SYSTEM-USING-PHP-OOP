<?php
session_start();
require_once '../classes/Admin.php';
require_once '../classes/Course.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

$admin = new Admin();
$course = new Course();

// Get filter parameters
$course_filter = isset($_GET['course']) ? $_GET['course'] : null;
$year_filter = isset($_GET['year']) ? $_GET['year'] : null;
$date_filter = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Get attendance data
$attendance = $admin->getAllAttendance($course_filter, $year_filter, $date_filter);
$courses = $admin->getAllCourses();
$attendance_stats = $admin->getAttendanceStats($course_filter, $year_filter, $date_filter, $date_filter);

// Calculate statistics
$present_count = 0;
$late_count = 0;
$absent_count = 0;

if (!empty($attendance_stats)) {
    $stats = $attendance_stats[0];
    $present_count = $stats['present_count'];
    $late_count = $stats['late_count'];
    $absent_count = $stats['absent_count'];
}

$total_records = $present_count + $late_count + $absent_count;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Records - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <?php include '../templates/admin_header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Attendance Records</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filter Form -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-3">
                                        <input type="date" class="form-control" name="date" value="<?php echo $date_filter; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" name="course">
                                            <option value="">All Courses</option>
                                            <?php foreach ($courses as $c): ?>
                                                <option value="<?php echo $c['id']; ?>" <?php echo ($course_filter == $c['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $c['course_code']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select" name="year">
                                            <option value="">All Year Levels</option>
                                            <option value="1st Year" <?php echo ($year_filter == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                                            <option value="2nd Year" <?php echo ($year_filter == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                                            <option value="3rd Year" <?php echo ($year_filter == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                                            <option value="4th Year" <?php echo ($year_filter == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                                            <option value="5th Year" <?php echo ($year_filter == '5th Year') ? 'selected' : ''; ?>>5th Year</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-outline-info w-100">
                                            <i class="fas fa-filter me-2"></i>Filter
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="reports.php" class="btn btn-success">
                                    <i class="fas fa-chart-bar me-2"></i>Generate Report
                                </a>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card text-white bg-success mb-3">
                                    <div class="card-body text-center">
                                        <h5><i class="fas fa-check-circle fa-2x mb-2"></i></h5>
                                        <h5>Present</h5>
                                        <h3><?php echo $present_count; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-warning mb-3">
                                    <div class="card-body text-center">
                                        <h5><i class="fas fa-clock fa-2x mb-2"></i></h5>
                                        <h5>Late</h5>
                                        <h3><?php echo $late_count; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-danger mb-3">
                                    <div class="card-body text-center">
                                        <h5><i class="fas fa-times-circle fa-2x mb-2"></i></h5>
                                        <h5>Absent</h5>
                                        <h3><?php echo $absent_count; ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-primary mb-3">
                                    <div class="card-body text-center">
                                        <h5><i class="fas fa-users fa-2x mb-2"></i></h5>
                                        <h5>Total</h5>
                                        <h3><?php echo $total_records; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Table -->
                        <?php if ($attendance): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Course</th>
                                            <th>Year Level</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($attendance as $record): ?>
                                            <tr>
                                                <td><?php echo $record['student_id']; ?></td>
                                                <td><?php echo htmlspecialchars($record['name']); ?></td>
                                                <td><?php echo $record['course_code']; ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $record['year_level']; ?></span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($record['date'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($record['time'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                        echo $record['status'] === 'present' ? 'success' :
                                                             ($record['status'] === 'late' ? 'warning' : 'danger');
                                                    ?>">
                                                        <?php echo ucfirst($record['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Export Button -->
                            <div class="mt-3">
                                <a href="export_attendance.php?date=<?php echo $date_filter; ?>&course=<?php echo $course_filter; ?>&year=<?php echo $year_filter; ?>" class="btn btn-outline-success">
                                    <i class="fas fa-download me-2"></i>Export to CSV
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No attendance records found</h5>
                                <p class="text-muted">Try adjusting your filters or select a different date.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
