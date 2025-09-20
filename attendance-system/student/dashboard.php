<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../classes/Student.php';
require_once '../classes/Course.php';

// Redirect if not logged in as student
if (!isset($_SESSION['student_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ../index.php');
    exit();
}

$student = new Student();
$course = new Course();

// Get student data
$student_data = $student->getStudentCourses($_SESSION['student_id']);
$today_attendance = $student->getTodayAttendance($_SESSION['student_id']);
$attendance_history = $student->getAttendanceHistory($_SESSION['student_id'], 5);
$excuse_letters = $student->getExcuseLetters($_SESSION['student_id']);

// Mark attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $status = 'present';
    $current_time = date('H:i:s');

    // Check if late (after 8:00 AM)
    if ($current_time > '08:00:00') {
        $status = 'late';
    }

    $result = $student->markAttendance($_SESSION['student_id'], $status);

    if ($result === 'success') {
        header('Location: dashboard.php?attendance=marked');
        exit();
    } elseif ($result === 'already_attended') {
        header('Location: dashboard.php?attendance=already_marked');
        exit();
    } else {
        header('Location: dashboard.php?attendance=error');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <?php include '../templates/student_header.php'; ?>

    <div class="container mt-4">
        <!-- Welcome Message -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title">Welcome, <?php echo $_SESSION['name']; ?>!</h2>
                        <p class="card-text">
                            <strong>Student ID:</strong> <?php echo $_SESSION['student_id']; ?> |
                            <strong>Course:</strong> <?php echo $student_data['course_code'] . ' - ' . $student_data['course_name']; ?> |
                            <strong>Year Level:</strong> <?php echo $_SESSION['year_level']; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Status -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-calendar-check fa-2x mb-2"></i></h5>
                        <h5>Today's Status</h5>
                        <h3>
                            <?php if ($today_attendance): ?>
                                <span class="badge bg-success"><?php echo ucfirst($today_attendance['status']); ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Absent</span>
                            <?php endif; ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-clock fa-2x mb-2"></i></h5>
                        <h5>Current Time</h5>
                        <h3><?php echo date('h:i A'); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-file-alt fa-2x mb-2"></i></h5>
                        <h5>Excuse Letters</h5>
                        <h3><?php echo count($excuse_letters); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mark Attendance -->
        <?php if (!$today_attendance): ?>
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Mark Today's Attendance</h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">You haven't marked your attendance for today yet.</p>
                        <form method="POST">
                            <button type="submit" name="mark_attendance" class="btn btn-success">
                                <i class="fas fa-check-circle me-2"></i>Mark Attendance
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Attendance -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Attendance</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($attendance_history): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($attendance_history as $record): ?>
                                            <tr>
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
                            <a href="history.php" class="btn btn-outline-primary btn-sm">View Full History</a>
                        <?php else: ?>
                            <p class="text-muted">No attendance records found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="attendance.php" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-check me-2"></i>Mark Attendance
                            </a>
                            <a href="history.php" class="btn btn-outline-success">
                                <i class="fas fa-history me-2"></i>View Attendance History
                            </a>
                            <a href="excuse_letter.php" class="btn btn-outline-warning">
                                <i class="fas fa-file-alt me-2"></i>Submit Excuse Letter
                            </a>
                            <a href="profile.php" class="btn btn-outline-info">
                                <i class="fas fa-user me-2"></i>Update Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Excuse Letters -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Recent Excuse Letters</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($excuse_letters): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Course</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($excuse_letters, 0, 3) as $letter): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y', strtotime($letter['date'])); ?></td>
                                                <td><?php echo $letter['course_code']; ?></td>
                                                <td><?php echo substr($letter['reason'], 0, 50) . '...'; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                        echo $letter['status'] === 'approved' ? 'success' :
                                                             ($letter['status'] === 'rejected' ? 'danger' : 'warning');
                                                    ?>">
                                                        <?php echo ucfirst($letter['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <a href="excuse_letter.php" class="btn btn-outline-warning btn-sm">View All Letters</a>
                        <?php else: ?>
                            <p class="text-muted">No excuse letters submitted yet.</p>
                            <a href="excuse_letter.php" class="btn btn-warning btn-sm">Submit Your First Excuse Letter</a>
                        <?php endif; ?>
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
