<?php
require_once '../classes/User.php';

class Admin extends User {
    public function __construct() {
        parent::__construct();
        $this->role = 'admin';
    }

    // Implement abstract methods
    public function register($data) {
        // Check if user already exists
        if ($this->userExists($data['username'], $data['email'])) {
            return false;
        }

        // Insert into users table
        $query = "INSERT INTO users SET username=:username, password=:password, email=:email, role=:role";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(":username", $data['username']);
        $stmt->bindParam(":password", $data['password']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":role", $this->role);

        return $stmt->execute();
    }

   public function login($username, $password) {
    $user = $this->getUserByUsername($username);

    error_log("Admin login - User found: " . print_r($user, true));

    if ($user && password_verify($password, $user['password']) && $user['role'] == 'admin') {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        error_log("Session variables set: " . print_r($_SESSION, true));

        return true;
    }

    error_log("Admin login failed");
    return false;
}

    // Course management methods
    public function addCourse($course_name) {
        $query = "INSERT INTO courses SET course_name=:course_name";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":course_name", $course_name);

        return $stmt->execute();
    }

    public function getCourses() {
        $query = "SELECT * FROM courses ORDER BY course_name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get attendance by course and year level
    public function getAttendanceByCourseAndYear($course, $year_level) {
        $query = "SELECT a.*, s.full_name, s.student_id
                  FROM attendance a
                  JOIN students s ON a.student_id = s.id
                  WHERE s.course = :course AND s.year_level = :year_level
                  ORDER BY a.date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":year_level", $year_level);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get all students by course and year level
    public function getStudentsByCourseAndYear($course, $year_level) {
        $query = "SELECT * FROM students WHERE course = :course AND year_level = :year_level ORDER BY full_name";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":year_level", $year_level);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
