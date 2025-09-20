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

// Get student data
$student_data = $student->getStudentCourses($_SESSION['student_id']);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Check if password change is requested
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        }
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long.";
        }

        if (empty($errors)) {
            $result = $student->changePassword($_SESSION['student_id'], $current_password, $new_password);

            if ($result === 'success') {
                header('Location: profile.php?status=password_updated');
                exit();
            } elseif ($result === 'invalid_current') {
                $errors[] = "Current password is incorrect.";
            } else {
                $errors[] = "Error updating password. Please try again.";
            }
        }
    }

    // Update profile if no errors
    if (empty($errors)) {
        $result = $student->updateStudentProfile($_SESSION['student_id'], $name, $email, $student_data['id'], $_SESSION['year_level']);

        if ($result) {
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            header('Location: profile.php?status=profile_updated');
            exit();
        } else {
            $errors[] = "Error updating profile. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Attendance System</title>
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
                        <h4 class="mb-0"><i class="fas fa-user me-2"></i>Student Profile</h4>
                    </div>
                    <div class="card-body">
                        <!-- Status Messages -->
                        <?php if (isset($_GET['status'])): ?>
                            <?php if ($_GET['status'] === 'profile_updated'): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>Profile updated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php elseif ($_GET['status'] === 'password_updated'): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle me-2"></i>Password updated successfully!
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php foreach ($errors as $error): ?>
                                    <div><?php echo $error; ?></div>
                                <?php endforeach; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Profile Form -->
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Student ID</label>
                                        <input type="text" class="form-control" value="<?php echo $_SESSION['student_id']; ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Course</label>
                                        <input type="text" class="form-control" value="<?php echo $student_data['course_code'] . ' - ' . $student_data['course_name']; ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" name="name" value="<?php echo $_SESSION['name']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Year Level</label>
                                        <input type="text" class="form-control" value="<?php echo $_SESSION['year_level']; ?>" readonly>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address *</label>
                                <input type="email" class="form-control" name="email" value="<?php echo $_SESSION['email']; ?>" required>
                            </div>

                            <hr>

                            <h5 class="mb-3"><i class="fas fa-lock me-2"></i>Change Password</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="current_password">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" name="confirm_password">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted">Leave password fields blank if you don't want to change your password.</small>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Profile
                            </button>
                        </form>
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
