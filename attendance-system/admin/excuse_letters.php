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
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';

// Get excuse letters
$excuse_letters = $admin->getExcuseLetters($course_filter, $status_filter);
$courses = $admin->getAllCourses();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $letter_id = $_POST['letter_id'];
    $status = $_POST['status'];
    $admin_notes = trim($_POST['admin_notes']);

    $result = $admin->updateExcuseLetterStatus($letter_id, $status, $admin_notes);

    if ($result) {
        header('Location: excuse_letters.php?status=updated');
        exit();
    } else {
        header('Location: excuse_letters.php?status=error');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excuse Letters - Attendance System</title>
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
                    <div class="card-header bg-warning text-white">
                        <h4 class="mb-0"><i class="fas fa-file-alt me-2"></i>Excuse Letters Management</h4>
                    </div>
                    <div class="card-body">
                        <!-- Status Messages -->
                        <?php if (isset($_GET['status'])): ?>
                            <?php if ($_GET['status'] === 'updated'): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>Status updated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif ($_GET['status'] === 'error'): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-times-circle me-2"></i>Error updating status!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <!-- Filter Form -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-5">
                                        <select class="form-select" name="course">
                                            <option value="">All Courses</option>
                                            <?php foreach ($courses as $c): ?>
                                                <option value="<?php echo $c['id']; ?>" <?php echo ($course_filter == $c['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $c['course_code']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" name="status">
                                            <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="approved" <?php echo ($status_filter == 'approved') ? 'selected' : ''; ?>>Approved</option>
                                            <option value="rejected" <?php echo ($status_filter == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                            <option value="" <?php echo ($status_filter == '') ? 'selected' : ''; ?>>All Status</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-outline-warning w-100">
                                            <i class="fas fa-filter me-2"></i>Filter
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="h-100 d-flex align-items-center justify-content-end">
                                    <span class="badge bg-secondary me-2">Total: <?php echo count($excuse_letters); ?></span>
                                    <span class="badge bg-warning me-2">Pending: <?php echo count(array_filter($excuse_letters, fn($l) => $l['status'] === 'pending')); ?></span>
                                    <span class="badge bg-success">Approved: <?php echo count(array_filter($excuse_letters, fn($l) => $l['status'] === 'approved')); ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Excuse Letters Table -->
                        <?php if ($excuse_letters): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Student</th>
                                            <th>Course</th>
                                            <th>Absence Date</th>
                                            <th>Submitted</th>
                                            <th>Reason</th>
                                            <th>Attachment</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($excuse_letters as $letter): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($letter['student_name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo $letter['student_id']; ?></small>
                                                </td>
                                                <td><?php echo $letter['course_code']; ?></td>
                                                <td><?php echo date('M j, Y', strtotime($letter['date'])); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($letter['created_at'])); ?></td>
                                                <td><?php echo substr($letter['reason'], 0, 50) . (strlen($letter['reason']) > 50 ? '...' : ''); ?></td>
                                                <td>
                                                    <?php if ($letter['attachment']): ?>
                                                        <a href="../uploads/excuse_letters/<?php echo $letter['attachment']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">None</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php
                                                        echo $letter['status'] === 'approved' ? 'success' :
                                                             ($letter['status'] === 'rejected' ? 'danger' : 'warning');
                                                    ?>">
                                                        <?php echo ucfirst($letter['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $letter['id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#statusModal<?php echo $letter['id']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- View Modal -->
                                            <div class="modal fade" id="viewModal<?php echo $letter['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Excuse Letter Details</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <p><strong>Student:</strong> <?php echo $letter['student_name']; ?> (<?php echo $letter['student_id']; ?>)</p>
                                                                    <p><strong>Course:</strong> <?php echo $letter['course_code']; ?> - <?php echo $letter['course_name']; ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <p><strong>Absence Date:</strong> <?php echo date('F j, Y', strtotime($letter['date'])); ?></p>
                                                                    <p><strong>Submitted:</strong> <?php echo date('F j, Y g:i A', strtotime($letter['created_at'])); ?></p>
                                                                </div>
                                                            </div>
                                                            <hr>
                                                            <h6>Reason for Absence:</h6>
                                                            <p class="border p-3 rounded"><?php echo nl2br(htmlspecialchars($letter['reason'])); ?></p>

                                                            <?php if ($letter['attachment']): ?>
                                                                <h6>Attachment:</h6>
                                                                <a href="../uploads/excuse_letters/<?php echo $letter['attachment']; ?>" target="_blank" class="btn btn-outline-primary">
                                                                    <i class="fas fa-download me-2"></i>Download Attachment
                                                                </a>
                                                            <?php endif; ?>

                                                            <?php if ($letter['admin_notes']): ?>
                                                                <hr>
                                                                <h6>Admin Notes:</h6>
                                                                <p class="border p-3 rounded bg-light"><?php echo nl2br(htmlspecialchars($letter['admin_notes'])); ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Status Modal -->
                                            <div class="modal fade" id="statusModal<?php echo $letter['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Update Status</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form method="POST">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="letter_id" value="<?php echo $letter['id']; ?>">

                                                                <div class="mb-3">
                                                                    <label class="form-label">Current Status</label>
                                                                    <p>
                                                                        <span class="badge bg-<?php
                                                                            echo $letter['status'] === 'approved' ? 'success' :
                                                                                 ($letter['status'] === 'rejected' ? 'danger' : 'warning');
                                                                        ?>">
                                                                            <?php echo ucfirst($letter['status']); ?>
                                                                        </span>
                                                                    </p>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Update Status *</label>
                                                                    <select class="form-select" name="status" required>
                                                                        <option value="pending" <?php echo ($letter['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                                                        <option value="approved" <?php echo ($letter['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                                                                        <option value="rejected" <?php echo ($letter['status'] == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Admin Notes</label>
                                                                    <textarea class="form-control" name="admin_notes" rows="3" placeholder="Optional notes for the student..."><?php echo $letter['admin_notes']; ?></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No excuse letters found</h5>
                                <p class="text-muted">Try adjusting your filters or check back later.</p>
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
