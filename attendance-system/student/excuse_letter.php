<?php
session_start();
require_once '../classes/Student.php';
require_once '../classes/Course.php';

// Redirect if not logged in as student
if (!isset($_SESSION['student_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ../index.php');
    exit();
}

$student = new Student();
$course = new Course();

$student_course = $student->getStudentCourses($_SESSION['student_id']);
$excuse_letters = $student->getExcuseLetters($_SESSION['student_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_excuse'])) {
    $date = $_POST['date'];
    $reason = trim($_POST['reason']);
    $attachment = null;

    // Handle file upload
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/excuse_letters/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = time() . '_' . basename($_FILES['attachment']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $file_path)) {
            $attachment = $file_name;
        }
    }

    $result = $student->submitExcuseLetter($_SESSION['student_id'], $student_course['id'], $date, $reason, $attachment);

    if ($result === 'success') {
        header('Location: excuse_letter.php?status=submitted');
        exit();
    } else {
        header('Location: excuse_letter.php?status=error');
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
    <?php include '../templates/student_header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-file-alt me-2"></i>Submit Excuse Letter</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['status'])): ?>
                            <?php if ($_GET['status'] === 'submitted'): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>Excuse letter submitted successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif ($_GET['status'] === 'error'): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-times-circle me-2"></i>Error submitting excuse letter. Please try again.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Course</label>
                                <input type="text" class="form-control" value="<?php echo $student_course['course_code'] . ' - ' . $student_course['course_name']; ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date of Absence *</label>
                                <input type="date" class="form-control" name="date" required max="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Reason for Absence *</label>
                                <textarea class="form-control" name="reason" rows="5" required placeholder="Please provide a detailed reason for your absence..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Attachment (Optional)</label>
                                <input type="file" class="form-control" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <div class="form-text">Supported formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 5MB)</div>
                            </div>
                            <button type="submit" name="submit_excuse" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Submit Excuse Letter
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Excuse Letter Guidelines</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Submit within 3 days of absence</li>
                            <li class="list-group-item">Provide valid and truthful reasons</li>
                            <li class="list-group-item">Attach supporting documents if available</li>
                            <li class="list-group-item">Admin will review within 2-3 business days</li>
                            <li class="list-group-item">Check status regularly for updates</li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow mt-4">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Submissions</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($excuse_letters): ?>
                            <?php foreach (array_slice($excuse_letters, 0, 3) as $letter): ?>
                                <div class="mb-3 p-2 border rounded">
                                    <strong>Date: </strong><?php echo date('M j, Y', strtotime($letter['date'])); ?><br>
                                    <strong>Status: </strong>
                                    <span class="badge bg-<?php
                                        echo $letter['status'] === 'approved' ? 'success' :
                                             ($letter['status'] === 'rejected' ? 'danger' : 'warning');
                                    ?>">
                                        <?php echo ucfirst($letter['status']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                            <a href="#letters-list" class="btn btn-outline-warning btn-sm w-100">View All</a>
                        <?php else: ?>
                            <p class="text-muted">No excuse letters submitted yet.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Excuse Letters List -->
        <div class="row mt-4" id="letters-list">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-list me-2"></i>Your Excuse Letters</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($excuse_letters): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date Submitted</th>
                                            <th>Absence Date</th>
                                            <th>Reason</th>
                                            <th>Attachment</th>
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
                                                    <?php if ($letter['attachment']): ?>
                                                        <a href="../uploads/excuse_letters/<?php echo $letter['attachment']; ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-download me-1"></i>View
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
                                                <td><?php echo $letter['admin_notes'] ?: 'No notes yet'; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No excuse letters submitted yet</h5>
                                <p class="text-muted">Submit your first excuse letter using the form above.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-3">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../templates/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
