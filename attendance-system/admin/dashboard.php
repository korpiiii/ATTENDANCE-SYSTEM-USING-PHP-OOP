<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../classes/Admin.php';
require_once '../classes/Student.php';
require_once '../classes/Course.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit();

}

$admin = new Admin();
$student = new Student();
$course = new Course();

// Get statistics
$total_students = $admin->getTotalStudents();
$today_attendance = $admin->getTodayAttendanceCount();
$pending_excuses = $admin->getPendingExcuseLettersCount();
$total_courses = count($admin->getAllCourses());

// Get recent attendance
$recent_attendance = $admin->getAllAttendance(null, null, date('Y-m-d'));
$recent_excuses = $admin->getExcuseLetters(null, 'pending');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <?php include '../templates/admin_header.php'; ?>

    <div class="container mt-4">
        <!-- Welcome Message -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title">Welcome, <?php echo $_SESSION['admin_name']; ?>!</h2>
                        <p class="card-text">
                            <strong>Role:</strong> <?php echo ucfirst(str_replace('_', ' ', $_SESSION['admin_role'])); ?> |
                            <strong>Username:</strong> <?php echo $_SESSION['admin_username']; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-users fa-2x mb-2"></i></h5>
                        <h5>Total Students</h5>
                        <h3><?php echo $total_students; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-calendar-check fa-2x mb-2"></i></h5>
                        <h5>Today's Attendance</h5>
                        <h3><?php echo $today_attendance; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-file-alt fa-2x mb-2"></i></h5>
                        <h5>Pending Excuses</h5>
                        <h3><?php echo $pending_excuses; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-graduation-cap fa-2x mb-2"></i></h5>
                        <h5>Total Courses</h5>
                        <h3><?php echo $total_courses; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="students.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-users me-2"></i>Manage Students
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="courses.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-graduation-cap me-2"></i>Manage Courses
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="attendance.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-calendar-alt me-2"></i>View Attendance
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="excuse_letters.php" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-file-alt me-2"></i>Excuse Letters
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Attendance -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Today's Attendance</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_attendance): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Course</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($recent_attendance, 0, 5) as $record): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($record['name']); ?></td>
                                                <td><?php echo date('h:i A', strtotime($record['time'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                        echo $record['status'] === 'present' ? 'success' :
                                                             ($record['status'] === 'late' ? 'warning' : 'danger');
                                                    ?>">
                                                        <?php echo ucfirst($record['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $record['course_code']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="attendance.php" class="btn btn-outline-success btn-sm">View All Attendance</a>
                        <?php else: ?>
                            <p class="text-muted">No attendance records for today.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Pending Excuse Letters -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Pending Excuse Letters</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($recent_excuses): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Date</th>
                                            <th>Course</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($recent_excuses, 0, 5) as $letter): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($letter['student_name']); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($letter['date'])); ?></td>
                                                <td><?php echo $letter['course_code']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="excuse_letters.php" class="btn btn-outline-warning btn-sm">Review All Letters</a>
                        <?php else: ?>
                            <p class="text-muted">No pending excuse letters.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Overview -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>System Overview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                    <div>
                                        <h6 class="mb-0">Students</h6>
                                        <span class="text-muted">Total registered</span>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><?php echo $total_students; ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                    <div>
                                        <h6 class="mb-0">Courses</h6>
                                        <span class="text-muted">Available programs</span>
                                    </div>
                                    <span class="badge bg-success rounded-pill"><?php echo $total_courses; ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                    <div>
                                        <h6 class="mb-0">Pending Actions</h6>
                                        <span class="text-muted">Require attention</span>
                                    </div>
                                    <span class="badge bg-warning rounded-pill"><?php echo $pending_excuses; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
