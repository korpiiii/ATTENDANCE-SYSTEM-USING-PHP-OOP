<?php
session_start();
require_once '../classes/Student.php';

// Redirect if not logged in as student
if (!isset($_SESSION['student_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ../index.php');
    exit();
}

$student = new Student();
$attendance_history = $student->getAttendanceHistory($_SESSION['student_id'], 100);

// Calculate statistics
$total_days = count($attendance_history);
$present_count = 0;
$late_count = 0;
$absent_count = 0;

foreach ($attendance_history as $record) {
    if ($record['status'] === 'present') $present_count++;
    if ($record['status'] === 'late') $late_count++;
    if ($record['status'] === 'absent') $absent_count++;
}

$attendance_rate = $total_days > 0 ? round((($present_count + $late_count) / $total_days) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <?php include '../templates/student_header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-history me-2"></i>Attendance History</h4>
                    </div>
                    <div class="card-body">
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
                                <div class="card text-white bg-info mb-3">
                                    <div class="card-body text-center">
                                        <h5><i class="fas fa-chart-line fa-2x mb-2"></i></h5>
                                        <h5>Attendance Rate</h5>
                                        <h3><?php echo $attendance_rate; ?>%</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Records -->
                        <?php if ($attendance_history): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Day</th>
                                            <th>Time</th>
                                            <th>Status</th>
                                            <th>Course</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($attendance_history as $record): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y', strtotime($record['date'])); ?></td>
                                                <td><?php echo date('l', strtotime($record['date'])); ?></td>
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
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No attendance records found</h5>
                                <p class="text-muted">Your attendance history will appear here once you start marking your attendance.</p>
                                <a href="attendance.php" class="btn btn-primary">
                                    <i class="fas fa-calendar-check me-2"></i>Mark Attendance
                                </a>
                            </div>
                        <?php endif; ?>
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
