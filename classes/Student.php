<?php
require_once '../classes/User.php';

class Student extends User {
    private $student_id;
    private $full_name;
    private $course;
    private $year_level;

    public function __construct() {
        parent::__construct();
        $this->role = 'student';
    }

    // Getters and setters
    public function getStudentId() { return $this->student_id; }
    public function getFullName() { return $this->full_name; }
    public function getCourse() { return $this->course; }
    public function getYearLevel() { return $this->year_level; }

    public function setStudentId($student_id) { $this->student_id = $student_id; }
    public function setFullName($full_name) { $this->full_name = $full_name; }
    public function setCourse($course) { $this->course = $course; }
    public function setYearLevel($year_level) { $this->year_level = $year_level; }

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

        if ($stmt->execute()) {
            $user_id = $this->db->lastInsertId();

            // Insert into students table
            $query = "INSERT INTO students SET user_id=:user_id, student_id=:student_id, full_name=:full_name, course=:course, year_level=:year_level";
            $stmt = $this->db->prepare($query);

            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":student_id", $data['student_id']);
            $stmt->bindParam(":full_name", $data['full_name']);
            $stmt->bindParam(":course", $data['course']);
            $stmt->bindParam(":year_level", $data['year_level']);

            return $stmt->execute();
        }

        return false;
    }

   public function login($username, $password) {
    $user = $this->getUserByUsername($username);

    error_log("Student login - User found: " . print_r($user, true));

    if ($user && password_verify($password, $user['password']) && $user['role'] == 'student') {
        // Get student details
        $query = "SELECT s.* FROM students s WHERE s.user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":user_id", $user['id']);
        $stmt->execute();

        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        error_log("Student details: " . print_r($student, true));

        if ($student) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_data'] = $student;

            error_log("Session variables set: " . print_r($_SESSION, true));

            return true;
        }
    }

    error_log("Student login failed");
    return false;
}

    // File attendance
    public function fileAttendance($student_id) {
        $current_time = date('H:i:s');
        $is_late = ($current_time > '08:00:00') ? 1 : 0;
        $date = date('Y-m-d');

        // Check if already attended today
        $query = "SELECT id FROM attendance WHERE student_id = :student_id AND date = :date";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":date", $date);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return false; // Already attended today
        }

        // Insert attendance
        $query = "INSERT INTO attendance SET student_id=:student_id, date=:date, time_in=:time_in, is_late=:is_late";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":time_in", $current_time);
        $stmt->bindParam(":is_late", $is_late);

        return $stmt->execute();
    }

    // Get attendance history
    public function getAttendanceHistory($student_id) {
        $query = "SELECT * FROM attendance WHERE student_id = :student_id ORDER BY date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
