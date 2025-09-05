<?php
require_once '../config.php';
require_once '../auth.php';
require_once '../includes/utilities.php';
require_once '../classes/Admin.php';

// Check if user is admin
$auth = new Auth();
$auth->requireRole('admin');

$admin = new Admin();
$courses = $admin->getCourses();
?>

<?php require_once '../includes/header.php'; ?>

<h2>Course Management</h2>

<?php Utilities::displayFlash(); ?>

<div class="form-section">
    <h3>Add New Course</h3>
    <form action="../processes/course_process.php" method="post">
        <input type="hidden" name="action" value="add">

        <div class="form-group">
            <label for="course_name">Course Name:</label>
            <input type="text" id="course_name" name="course_name" required>
        </div>

        <div class="form-group">
            <button type="submit" class="btn">Add Course</button>
        </div>
    </form>
</div>

<div class="table-section">
    <h3>Existing Courses</h3>
    <?php if (count($courses) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?php echo $course['id']; ?></td>
                        <td><?php echo htmlspecialchars($course['course_name']); ?></td>
                        <td><?php echo Utilities::formatDate($course['created_at'], 'M j, Y'); ?></td>
                        <td>
                            <button class="btn btn-sm" onclick="editCourse(<?php echo $course['id']; ?>, '<?php echo htmlspecialchars($course['course_name']); ?>')">Edit</button>
                            <form action="../processes/course_process.php" method="post" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this course?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No courses found.</p>
    <?php endif; ?>
</div>

<!-- Edit Course Modal -->
<div id="editCourseModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Edit Course</h3>
        <form action="../processes/course_process.php" method="post">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" id="edit_course_id" name="course_id" value="">

            <div class="form-group">
                <label for="edit_course_name">Course Name:</label>
                <input type="text" id="edit_course_name" name="course_name" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn">Update Course</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCourse(id, name) {
    document.getElementById('edit_course_id').value = id;
    document.getElementById('edit_course_name').value = name;

    var modal = document.getElementById('editCourseModal');
    modal.style.display = 'block';
}

// Close modal when clicking on X
document.querySelector('.close').addEventListener('click', function() {
    document.getElementById('editCourseModal').style.display = 'none';
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    var modal = document.getElementById('editCourseModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
