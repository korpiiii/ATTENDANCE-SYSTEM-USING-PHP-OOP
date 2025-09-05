<?php
require_once '../config.php';
require_once '../classes/Database.php'; // Add this line
require_once '../includes/utilities.php'; // Add this line
require_once '../includes/header.php';

// Get courses for student registration
$database = new Database();
$db = $database->getConnection();
$stmt = $db->query("SELECT * FROM courses ORDER BY course_name");
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Register</h2>

<?php Utilities::displayFlash(); ?>

<form action="../processes/register_process.php" method="post" id="registrationForm">
    <div>
        <label for="role">Register as:</label>
        <select id="role" name="role" required onchange="toggleFields()">
            <option value="student">Student</option>
            <option value="admin">Admin</option>
        </select>
    </div>

    <div>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
    </div>

    <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
    </div>

    <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>

    <div>
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>

    <!-- Student-specific fields -->
    <div id="studentFields">
        <div>
            <label for="student_id">Student ID:</label>
            <input type="text" id="student_id" name="student_id">
        </div>

        <div>
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name">
        </div>

        <div>
            <label for="course">Course/Program:</label>
            <select id="course" name="course">
                <option value="">Select Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo $course['course_name']; ?>"><?php echo $course['course_name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label for="year_level">Year Level:</label>
            <select id="year_level" name="year_level">
                <option value="">Select Year Level</option>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
            </select>
        </div>
    </div>

    <div>
        <button type="submit">Register</button>
    </div>
</form>

<p>Already have an account? <a href="login.php">Login here</a></p>

<script>
function toggleFields() {
    var role = document.getElementById('role').value;
    var studentFields = document.getElementById('studentFields');

    if (role === 'student') {
        studentFields.style.display = 'block';
        // Set required attribute for student fields
        document.getElementById('student_id').required = true;
        document.getElementById('full_name').required = true;
        document.getElementById('course').required = true;
        document.getElementById('year_level').required = true;
    } else {
        studentFields.style.display = 'none';
        // Remove required attribute for student fields
        document.getElementById('student_id').required = false;
        document.getElementById('full_name').required = false;
        document.getElementById('course').required = false;
        document.getElementById('year_level').required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleFields();
});
</script>

<?php require_once '../includes/footer.php'; ?>
