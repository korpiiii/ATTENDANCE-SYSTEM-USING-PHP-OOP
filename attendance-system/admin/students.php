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

// Get all students with filters
$students = $admin->getAllStudents($course_filter, $year_filter);
$courses = $admin->getAllCourses();

// Handle student deletion
if (isset($_GET['delete_student'])) {
    $student_id = $_GET['delete_student'];
    // Implementation would go here (you'd need to add a delete method in Admin class)
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Attendance System</title>
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
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="fas fa-users me-2"></i>Manage Students</h4>
                    </div>
                    <div class="card-body">
                        <!-- Filter Form -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-5">
                                        <select class="form-select" name="course">
                                            <option value="">All Courses</option>
                                            <?php foreach ($courses as $c): ?>
                                                <option value="<?php echo $c['id']; ?>" <?php echo ($course_filter == $c['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $c['course_code'] . ' - ' . $c['course_name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" name="year">
                                            <option value="">All Year Levels</option>
                                            <option value="1st Year" <?php echo ($year_filter == '1st Year') ? 'selected' : ''; ?>>1st Year</option>
                                            <option value="2nd Year" <?php echo ($year_filter == '2nd Year') ? 'selected' : ''; ?>>2nd Year</option>
                                            <option value="3rd Year" <?php echo ($year_filter == '3rd Year') ? 'selected' : ''; ?>>3rd Year</option>
                                            <option value="4th Year" <?php echo ($year_filter == '4th Year') ? 'selected' : ''; ?>>4th Year</option>
                                            <option value="5th Year" <?php echo ($year_filter == '5th Year') ? 'selected' : ''; ?>>5th Year</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-filter me-2"></i>Filter
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="add_student.php" class="btn btn-success">
                                    <i class="fas fa-user-plus me-2"></i>Add New Student
                                </a>
                            </div>
                        </div>

                        <!-- Students Table -->
                        <?php if ($students): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Student ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Course</th>
                                            <th>Year Level</th>
                                            <th>Registered</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo $student['student_id']; ?></td>
                                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                                <td><?php echo $student['email']; ?></td>
                                                <td><?php echo $student['course_code']; ?></td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $student['year_level']; ?></span>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($student['created_at'])); ?></td>
                                                <td>
                                                    <a href="view_student.php?id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit_student.php?id=<?php echo $student['student_id']; ?>" class="btn btn-sm btn-outline-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $student['student_id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>

                                            <!-- Delete Modal -->
                                            <div class="modal fade" id="deleteModal<?php echo $student['student_id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Confirm Delete</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to delete student <strong><?php echo htmlspecialchars($student['name']); ?></strong>?</p>
                                                            <p class="text-danger">This action cannot be undone and will delete all associated attendance records.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <a href="students.php?delete_student=<?php echo $student['student_id']; ?>" class="btn btn-danger">Delete</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Export Button -->
                            <div class="mt-3">
                                <a href="export_students.php?course=<?php echo $course_filter; ?>&year=<?php echo $year_filter; ?>" class="btn btn-outline-success">
                                    <i class="fas fa-download me-2"></i>Export to CSV
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No students found</h5>
                                <p class="text-muted">Try adjusting your filters or add new students.</p>
                                <a href="add_student.php" class="btn btn-primary">
                                    <i class="fas fa-user-plus me-2"></i>Add New Student
                                </a>
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
