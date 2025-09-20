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
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get report data
$attendance_stats = $admin->getAttendanceStats($course_filter, $year_filter, $start_date, $end_date);
$courses = $admin->getAllCourses();

// Calculate totals
$total_days = count($attendance_stats);
$total_present = array_sum(array_column($attendance_stats, 'present_count'));
$total_late = array_sum(array_column($attendance_stats, 'late_count'));
$total_absent = array_sum(array_column($attendance_stats, 'absent_count'));
$total_records = $total_present + $total_late + $total_absent;

$attendance_rate = $total_records > 0 ? round((($total_present + $total_late) / $total_records) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Header -->
    <?php include '../templates/admin_header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Attendance Reports</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filter Form -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">End Date</label>
                                        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Course</label>
                                        <select class="form-select" name="course">
                                            <option value="">All Courses</option>
                                            <?php foreach ($courses as $c): ?>
                                                <option value="<?php echo $c['id']; ?>" <?php echo ($course_filter == $c['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $c['course_code']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Year Level</label>
                                        <select class="form-select" name="year">
                                            <option value="">All Years</option>
                                            <option value="1st Year" <?php echo ($year_filter == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                                            <option value="2nd Year" <?php echo ($year_filter == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                                            <option value="3rd Year" <?php echo ($year_filter == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                                            <option value="4th Year" <?php echo ($year_filter == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                                            <option value="5th Year" <?php echo ($year_filter == '5th Year') ? 'selected' : ''; ?>>5th Year</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-filter me-2"></i>Generate
                                        </button>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">&nbsp;</label>
                                        <a href="reports.php" class="btn btn-outline-secondary w-100">
                                            <i class="fas fa-sync"></i>
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Summary Statistics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card text-white bg-success mb-3">
                                    <div class="card-body text-center">
                                        <h5><i class="fas fa-check-circle fa-2x mb-2"></i></h5>
                                        <h5>Present</h5>
                                        <h3><?php echo $total_present; ?></h3>
                                        <small><?php echo $total_records > 0 ? round(($total_present / $total_records) * 100, 1) : 0; ?>%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-warning mb-3">
                                    <div class="card-body text-center">
                                        <h5><i class="fas fa-clock fa-2x mb-2"></i></h5>
                                        <h5>Late</h5>
                                        <h3><?php echo $total_late; ?></h3>
                                        <small><?php echo $total_records > 0 ? round(($total_late / $total_records) * 100, 1) : 0; ?>%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-danger mb-3">
                                    <div class="card-body text-center">
                                        <h5><i class="fas fa-times-circle fa-2x mb-2"></i></h5>
                                        <h5>Absent</h5>
                                        <h3><?php echo $total_absent; ?></h3>
                                        <small><?php echo $total_records > 0 ? round(($total_absent / $total_records) * 100, 1) : 0; ?>%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-white bg-info mb-3">
                                    <div class="card-body text-center">
                                        <h5><i class="fas fa-chart-line fa-2x mb-2"></i></h5>
                                        <h5>Attendance Rate</h5>
                                        <h3><?php echo $attendance_rate; ?>%</h3>
                                        <small>Overall</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Attendance Distribution</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="attendanceChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Daily Trend</h6>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="trendChart" height="250"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed Report -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-table me-2"></i>Detailed Report</h6>
                            </div>
                            <div class="card-body">
                                <?php if ($attendance_stats): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Present</th>
                                                    <th>Late</th>
                                                    <th>Absent</th>
                                                    <th>Total</th>
                                                    <th>Attendance Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($attendance_stats as $stat): ?>
                                                    <?php
                                                    $day_total = $stat['present_count'] + $stat['late_count'] + $stat['absent_count'];
                                                    $day_rate = $day_total > 0 ? round((($stat['present_count'] + $stat['late_count']) / $day_total) * 100, 1) : 0;
                                                    ?>
                                                    <tr>
                                                        <td><?php echo date('M j, Y', strtotime($stat['attendance_date'])); ?></td>
                                                        <td class="text-success"><?php echo $stat['present_count']; ?></td>
                                                        <td class="text-warning"><?php echo $stat['late_count']; ?></td>
                                                        <td class="text-danger"><?php echo $stat['absent_count']; ?></td>
                                                        <td><strong><?php echo $day_total; ?></strong></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $day_rate >= 90 ? 'success' : ($day_rate >= 75 ? 'warning' : 'danger'); ?>">
                                                                <?php echo $day_rate; ?>%
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot class="table-dark">
                                                <tr>
                                                    <th>Total</th>
                                                    <th class="text-success"><?php echo $total_present; ?></th>
                                                    <th class="text-warning"><?php echo $total_late; ?></th>
                                                    <th class="text-danger"><?php echo $total_absent; ?></th>
                                                    <th><?php echo $total_records; ?></th>
                                                    <th><?php echo $attendance_rate; ?>%</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <!-- Export Button -->
                                    <div class="mt-3">
                                        <a href="export_report.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&course=<?php echo $course_filter; ?>&year=<?php echo $year_filter; ?>" class="btn btn-outline-success">
                                            <i class="fas fa-download me-2"></i>Export to Excel
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No data available for the selected period</h5>
                                        <p class="text-muted">Try adjusting your date range or filters.</p>
                                    </div>
                                <?php endif; ?>
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
    <script>
        // Pie Chart - Attendance Distribution
        const pieCtx = document.getElementById('attendanceChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Present', 'Late', 'Absent'],
                datasets: [{
                    data: [<?php echo $total_present; ?>, <?php echo $total_late; ?>, <?php echo $total_absent; ?>],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Bar Chart - Daily Trend
        const barCtx = document.getElementById('trendChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(', ', array_map(fn($s) => "'" . date('M j', strtotime($s['attendance_date'])) . "'", $attendance_stats)); ?>],
                datasets: [{
                    label: 'Present',
                    data: [<?php echo implode(', ', array_column($attendance_stats, 'present_count')); ?>],
                    backgroundColor: '#28a745'
                }, {
                    label: 'Late',
                    data: [<?php echo implode(', ', array_column($attendance_stats, 'late_count')); ?>],
                    backgroundColor: '#ffc107'
                }, {
                    label: 'Absent',
                    data: [<?php echo implode(', ', array_column($attendance_stats, 'absent_count')); ?>],
                    backgroundColor: '#dc3545'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>
