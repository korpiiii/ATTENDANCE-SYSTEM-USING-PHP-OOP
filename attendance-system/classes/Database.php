<?php
if (!defined('DB_HOST')) {
    // Define default database configuration
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'attendance_system');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

class Database {
    private static $instance = null;
    private $pdo;
    private $connection_error = null;

    // Private constructor to prevent direct instantiation
    private function __construct() {
        try {
            // Define constants if they're not already defined
            if (!defined('DB_HOST')) {
                define('DB_HOST', 'localhost');
            }
            if (!defined('DB_NAME')) {
                define('DB_NAME', 'attendance_system');
            }
            if (!defined('DB_USER')) {
                define('DB_USER', 'root');
            }
            if (!defined('DB_PASS')) {
                define('DB_PASS', '');
            }

            $this->pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            $this->connection_error = $e->getMessage();
            error_log("Database Connection Error: " . $this->connection_error);
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Get the singleton instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Get the PDO connection
    public function getConnection() {
        if ($this->connection_error) {
            throw new Exception("Database not connected: " . $this->connection_error);
        }
        return $this->pdo;
    }

    // Check if database is connected
    public function isConnected() {
        return $this->pdo !== null && $this->connection_error === null;
    }

    // Create tables if they don't exist
    public function createTables() {
        try {
            // Students table
            $students_table = "
                CREATE TABLE IF NOT EXISTS students (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    student_id VARCHAR(20) UNIQUE NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    course_id INT,
                    year_level VARCHAR(20) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ";

            // Admin table
            $admin_table = "
                CREATE TABLE IF NOT EXISTS admin (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(100) UNIQUE NOT NULL,
                    password VARCHAR(255) NOT NULL,
                    full_name VARCHAR(100) NOT NULL,
                    role ENUM('super_admin', 'admin') DEFAULT 'admin',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ";

            // Courses table
            $courses_table = "
                CREATE TABLE IF NOT EXISTS courses (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    course_code VARCHAR(20) UNIQUE NOT NULL,
                    course_name VARCHAR(100) NOT NULL,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ";

            // Attendance table
            $attendance_table = "
                CREATE TABLE IF NOT EXISTS attendance (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    student_id VARCHAR(20) NOT NULL,
                    date DATE NOT NULL,
                    time TIME NOT NULL,
                    status ENUM('present', 'absent', 'late') DEFAULT 'present',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
                    UNIQUE KEY unique_attendance (student_id, date)
                )
            ";

            // Excuse letters table
            $excuse_letters_table = "
                CREATE TABLE IF NOT EXISTS excuse_letters (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    student_id VARCHAR(20) NOT NULL,
                    course_id INT NOT NULL,
                    date DATE NOT NULL,
                    reason TEXT NOT NULL,
                    attachment VARCHAR(255),
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    admin_notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
                    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
                )
            ";

            // Execute table creation
            $this->pdo->exec($students_table);
            $this->pdo->exec($admin_table);
            $this->pdo->exec($courses_table);
            $this->pdo->exec($attendance_table);
            $this->pdo->exec($excuse_letters_table);

            // Insert default admin if not exists
            $defaultAdminCheck = $this->pdo->query("SELECT id FROM admin WHERE username = 'admin'")->fetch();
            if (!$defaultAdminCheck) {
                $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
                $stmt = $this->pdo->prepare(
                    "INSERT INTO admin (username, email, password, full_name, role)
                     VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute(['admin', 'admin@attendance.system', $hashedPassword, 'System Administrator', 'super_admin']);
            }

            // Insert sample courses if not exists
            $coursesCheck = $this->pdo->query("SELECT id FROM courses")->fetch();
            if (!$coursesCheck) {
                $sampleCourses = [
                    ['BSCS', 'Bachelor of Science in Computer Science', 'Computer science program'],
                    ['BSIT', 'Bachelor of Science in Information Technology', 'Information technology program'],
                    ['BSCE', 'Bachelor of Science in Civil Engineering', 'Civil engineering program'],
                    ['BSME', 'Bachelor of Science in Mechanical Engineering', 'Mechanical engineering program'],
                    ['BSBA', 'Bachelor of Science in Business Administration', 'Business administration program']
                ];

                $stmt = $this->pdo->prepare(
                    "INSERT INTO courses (course_code, course_name, description) VALUES (?, ?, ?)"
                );

                foreach ($sampleCourses as $course) {
                    $stmt->execute($course);
                }
            }

            return true;

        } catch (PDOException $e) {
            error_log("Table creation error: " . $e->getMessage());
            return false;
        }
    }
}
?>
