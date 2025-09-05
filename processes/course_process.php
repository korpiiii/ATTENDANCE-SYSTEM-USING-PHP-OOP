<?php
require_once '../config.php';
require_once '../includes/auth.php';
require_once '../classes/Admin.php';

// Check if user is admin
$auth = new Auth();
$auth->requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? Utilities::sanitizeInput($_POST['action']) : '';

    $admin = new Admin();

    if ($action === 'add') {
        $course_name = Utilities::sanitizeInput($_POST['course_name']);

        if (!empty($course_name)) {
            $success = $admin->addCourse($course_name);

            if ($success) {
                Utilities::redirect('../views/course_management.php', 'Course added successfully!', 'success');
            } else {
                Utilities::redirect('../views/course_management.php', 'Failed to add course. It may already exist.', 'error');
            }
        } else {
            Utilities::redirect('../views/course_management.php', 'Course name cannot be empty', 'error');
        }
    } elseif ($action === 'edit') {
        $course_id = Utilities::sanitizeInput($_POST['course_id']);
        $course_name = Utilities::sanitizeInput($_POST['course_name']);

        if (!empty($course_id) && !empty($course_name)) {
            // Since our Admin class doesn't have an editCourse method with ID,
            // we'll implement it here directly
            $database = new Database();
            $db = $database->getConnection();

            $query = "UPDATE courses SET course_name = :course_name WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":course_name", $course_name);
            $stmt->bindParam(":id", $course_id);

            $success = $stmt->execute();

            if ($success) {
                Utilities::redirect('../views/course_management.php', 'Course updated successfully!', 'success');
            } else {
                Utilities::redirect('../views/course_management.php', 'Failed to update course', 'error');
            }
        } else {
            Utilities::redirect('../views/course_management.php', 'Course name cannot be empty', 'error');
        }
    } elseif ($action === 'delete') {
        $course_id = Utilities::sanitizeInput($_POST['course_id']);

        if (!empty($course_id)) {
            // Check if any students are enrolled in this course
            $database = new Database();
            $db = $database->getConnection();

            $query = "SELECT COUNT(*) as student_count FROM students WHERE course = (SELECT course_name FROM courses WHERE id = :id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(":id", $course_id);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['student_count'] > 0) {
                Utilities::redirect('../views/course_management.php', 'Cannot delete course. There are students enrolled in it.', 'error');
            } else {
                $query = "DELETE FROM courses WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(":id", $course_id);

                $success = $stmt->execute();

                if ($success) {
                    Utilities::redirect('../views/course_management.php', 'Course deleted successfully!', 'success');
                } else {
                    Utilities::redirect('../views/course_management.php', 'Failed to delete course', 'error');
                }
            }
        } else {
            Utilities::redirect('../views/course_management.php', 'Invalid course ID', 'error');
        }
    } else {
        Utilities::redirect('../views/course_management.php', 'Invalid action', 'error');
    }
} else {
    Utilities::redirect('../views/course_management.php', 'Invalid request method', 'error');
}
?>
