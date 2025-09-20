<?php
session_start();
require_once '../classes/Student.php';

// Redirect if not logged in as student
if (!isset($_SESSION['student_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ../index.php');
    exit();
}

$student = new Student();
$today_attendance = $student->getTodayAttendance($_SESSION['student_id']);

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
        header('Location: attendance.php?status=marked');
        exit();
    } elseif ($result === 'already_attended') {
        header('Location: attendance.php?status=already_marked');
        exit();
    } else {
        header('Location: attendance.php?status=error');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <?php include '../templates/student_header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Mark Attendance</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['status'])): ?>
                            <?php if ($_GET['status'] === 'marked'): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>Attendance marked successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif ($_GET['status'] === 'already_marked'): ?>
                                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-triangle me-2"></i>You have already marked your attendance for today.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif ($_GET['status'] === 'error'): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-times-circle me-2"></i>Error marking attendance. Please try again.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="text-center mb-4">
                            <h5>Today's Date: <?php echo date('F j, Y'); ?></h5>
                            <h5>Current Time: <?php echo date('h:i A'); ?></h5>
                        </div>

                        <?php if ($today_attendance): ?>
                            <div class="alert alert-info text-center">
                                <h4><i class="fas fa-check-circle me-2"></i>Attendance Already Marked</h4>
                                <p class="mb-1">Status: <span class="badge bg-<?php
                                    echo $today_attendance['status'] === 'present' ? 'success' :
                                         ($today_attendance['status'] === 'late' ? 'warning' : 'danger');
                                ?>"><?php echo ucfirst($today_attendance['status']); ?></span></p>
                                <p class="mb-0">Time: <?php echo date('h:i A', strtotime($today_attendance['time'])); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <p class="lead">You haven't marked your attendance for today yet.</p>
                                <form method="POST">
                                    <button type="submit" name="mark_attendance" class="btn btn-primary btn-lg">
                                        <i class="fas fa-calendar-check me-2"></i>Mark My Attendance
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <hr>

                        <div class="mt-4">
                            <h5><i class="fas fa-info-circle me-2"></i>Attendance Guidelines</h5>
                            <ul class="list-group">
                                <li class="list-group-item">Attendance is automatically marked as <span class="badge bg-success">Present</span> before 8:00 AM</li>
                                <li class="list-group-item">Attendance is marked as <span class="badge bg-warning">Late</span> after 8:00 AM</li>
                                <li class="list-group-item">If you forget to mark attendance, you can submit an excuse letter</li>
                                <li class="list-group-item">Multiple attendance markings for the same day are not allowed</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
