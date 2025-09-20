<?php
session_start();
require_once '../classes/Admin.php';
require_once '../classes/Student.php';
require_once '../classes/Attendance.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../index.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: students.php');
    exit();
}

$admin = new Admin();
$student = new Student();
$attendance = new Attendance();

$student_id = $_GET['id'];
$student_data = $admin->getAllStudents(null, null, $student_id);
$attendance_history = $attendance->getStudentHistory($student_id, 100);
$excuse_letters = $student->getExcuseLetters($student_id);

if (empty($student_data)) {
    header('Location: students.php');
    exit();
}

$student_data = $student_data[0]; // Get first (and only) student
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - Attendance System</title>
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
                <!-- Student Information -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-user me-2"></i>Student Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Student ID:</strong> <?php echo $student_data['student_id']; ?></p>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($student_data['name']); ?></p>
                                <p><strong>Email:</strong> <?php echo $student_data['email']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Course:</strong> <?php echo $student_data['course_code'] . ' - ' . $student_data['course_name']; ?></p>
                                <p><strong>Year Level:</strong> <span class="badge bg-info"><?php echo $student_data['year_level']; ?></span></p>
                                <p><strong>Registered:</strong> <?php echo date('F j, Y', strtotime($student_data['created_at'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Statistics -->
                <div class="row mb-4">
                    <?php
                    $present_count = 0;
                    $late_count = 0;
                    $absent_count = 0;

                    foreach ($attendance_history as $record) {
                        if ($record['status'] === 'present') $present_count++;
                        if ($record['status'] === 'late') $late_count++;
                        if ($record['status'] === 'absent') $absent_count++;
                    }

                    $total_days = count($attendance_history);
                    $attendance_rate = $total_days > 0 ? round((($present_count + $late_count) / $total_days) * 100, 2) : 0;
                    ?>

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

                <!-- Attendance History -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Attendance History</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($attendance_history): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Day</th>
                                            <th>Time</th>
                                            <th>Status</th>
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
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No attendance records found.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Excuse Letters -->
                <div class="card shadow">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Excuse Letters</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($excuse_letters): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date Submitted</th>
                                            <th>Absence Date</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Admin Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($excuse_letters as $letter): ?>
                                            <tr>
                                                <td><?php echo date('M j, Y', strtotime($letter['created_at'])); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($letter['date'])); ?></td>
                                                <td><?php echo substr($letter['reason'], 0, 50) . (strlen($letter['reason']) > 50 ? '...' : ''); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                        echo $letter['status'] === 'approved' ? 'success' :
                                                             ($letter['status'] === 'rejected' ? 'danger' : 'warning');
                                                    ?>">
                                                        <?php echo ucfirst($letter['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo $letter['admin_notes'] ?: 'No notes'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No excuse letters submitted.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="students.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Students
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
