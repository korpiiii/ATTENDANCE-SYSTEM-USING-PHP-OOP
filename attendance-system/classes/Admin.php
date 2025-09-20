<?php
require_once 'User.php';

class Admin extends User {
    public function register($username, $email, $password, $full_name, $role = 'admin') {
        try {
            // Check if admin already exists
            if ($this->adminExists($username)) {
                return "admin_exists";
            }

            // Check if email already exists
            if ($this->emailExists($email)) {
                return "email_exists";
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO admin (username, email, password, full_name, role)
                    VALUES (:username, :email, :password, :full_name, :role)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "success";
            }
            return "error";

        } catch (PDOException $e) {
            error_log("Admin registration error: " . $e->getMessage());
            return "database_error";
        }
    }

    public function login($username, $password) {
        try {
            $sql = "SELECT * FROM admin WHERE username = :username";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($admin && password_verify($password, $admin['password'])) {
                // Remove password from returned array for security
                unset($admin['password']);
                return $admin;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Admin login error: " . $e->getMessage());
            return false;
        }
    }

    public function getAllStudents($course_id = null, $year_level = null) {
        try {
            $sql = "SELECT s.*, c.course_code, c.course_name
                    FROM students s
                    JOIN courses c ON s.course_id = c.id";

            $params = [];

            if ($course_id || $year_level) {
                $sql .= " WHERE";
                $conditions = [];

                if ($course_id) {
                    $conditions[] = " s.course_id = :course_id";
                    $params[':course_id'] = $course_id;
                }

                if ($year_level) {
                    $conditions[] = " s.year_level = :year_level";
                    $params[':year_level'] = $year_level;
                }

                $sql .= implode(" AND", $conditions);
            }

            $sql .= " ORDER BY s.name";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all students error: " . $e->getMessage());
            return [];
        }
    }

    public function getAllAttendance($course_id = null, $year_level = null, $date = null) {
        try {
            $sql = "SELECT a.*, s.name, s.year_level, c.course_code, c.course_name
                    FROM attendance a
                    JOIN students s ON a.student_id = s.student_id
                    JOIN courses c ON s.course_id = c.id";

            $params = [];
            $conditions = [];

            if ($course_id) {
                $conditions[] = " s.course_id = :course_id";
                $params[':course_id'] = $course_id;
            }

            if ($year_level) {
                $conditions[] = " s.year_level = :year_level";
                $params[':year_level'] = $year_level;
            }

            if ($date) {
                $conditions[] = " a.date = :date";
                $params[':date'] = $date;
            }

            if (!empty($conditions)) {
                $sql .= " WHERE" . implode(" AND", $conditions);
            }

            $sql .= " ORDER BY a.date DESC, a.time DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all attendance error: " . $e->getMessage());
            return [];
        }
    }

    public function getAttendanceStats($course_id = null, $year_level = null, $startDate = null, $endDate = null) {
        try {
            $sql = "SELECT
                    COUNT(DISTINCT s.student_id) as total_students,
                    COUNT(*) as total_records,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                    DATE(a.date) as attendance_date
                    FROM attendance a
                    JOIN students s ON a.student_id = s.student_id";

            $params = [];
            $conditions = [];

            if ($course_id) {
                $conditions[] = " s.course_id = :course_id";
                $params[':course_id'] = $course_id;
            }

            if ($year_level) {
                $conditions[] = " s.year_level = :year_level";
                $params[':year_level'] = $year_level;
            }

            if ($startDate && $endDate) {
                $conditions[] = " a.date BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }

            if (!empty($conditions)) {
                $sql .= " WHERE" . implode(" AND", $conditions);
            }

            $sql .= " GROUP BY DATE(a.date) ORDER BY attendance_date DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get attendance stats error: " . $e->getMessage());
            return [];
        }
    }

    public function getAllCourses() {
        try {
            $sql = "SELECT * FROM courses ORDER BY course_code";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all courses error: " . $e->getMessage());
            return [];
        }
    }

    public function addCourse($course_code, $course_name, $description = null) {
        try {
            // Check if course already exists
            if ($this->courseExists($course_code)) {
                return "course_exists";
            }

            $sql = "INSERT INTO courses (course_code, course_name, description)
                    VALUES (:course_code, :course_name, :description)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':course_code', $course_code, PDO::PARAM_STR);
            $stmt->bindParam(':course_name', $course_name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);

            return $stmt->execute() ? "success" : "error";

        } catch (PDOException $e) {
            error_log("Add course error: " . $e->getMessage());
            return "database_error";
        }
    }

    public function updateCourse($course_id, $course_code, $course_name, $description = null) {
        try {
            $sql = "UPDATE courses SET course_code = :course_code, course_name = :course_name,
                    description = :description WHERE id = :course_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':course_code', $course_code, PDO::PARAM_STR);
            $stmt->bindParam(':course_name', $course_name, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update course error: " . $e->getMessage());
            return false;
        }
    }

    public function getExcuseLetters($course_id = null, $status = null) {
        try {
            $sql = "SELECT el.*, s.name as student_name, s.student_id, c.course_code, c.course_name
                    FROM excuse_letters el
                    JOIN students s ON el.student_id = s.student_id
                    JOIN courses c ON el.course_id = c.id";

            $params = [];
            $conditions = [];

            if ($course_id) {
                $conditions[] = " el.course_id = :course_id";
                $params[':course_id'] = $course_id;
            }

            if ($status) {
                $conditions[] = " el.status = :status";
                $params[':status'] = $status;
            }

            if (!empty($conditions)) {
                $sql .= " WHERE" . implode(" AND", $conditions);
            }

            $sql .= " ORDER BY el.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get excuse letters error: " . $e->getMessage());
            return [];
        }
    }

    public function updateExcuseLetterStatus($letter_id, $status, $admin_notes = null) {
        try {
            $sql = "UPDATE excuse_letters SET status = :status, admin_notes = :admin_notes
                    WHERE id = :letter_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':admin_notes', $admin_notes, PDO::PARAM_STR);
            $stmt->bindParam(':letter_id', $letter_id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update excuse letter status error: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalStudents() {
        try {
            $sql = "SELECT COUNT(*) as total FROM students";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (PDOException $e) {
            error_log("Get total students error: " . $e->getMessage());
            return 0;
        }
    }

    public function getTodayAttendanceCount() {
        try {
            $today = date('Y-m-d');
            $sql = "SELECT COUNT(DISTINCT student_id) as count FROM attendance WHERE date = :date";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':date', $today, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (PDOException $e) {
            error_log("Get today attendance count error: " . $e->getMessage());
            return 0;
        }
    }

    public function getPendingExcuseLettersCount() {
        try {
            $sql = "SELECT COUNT(*) as count FROM excuse_letters WHERE status = 'pending'";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        } catch (PDOException $e) {
            error_log("Get pending excuse letters count error: " . $e->getMessage());
            return 0;
        }
    }

    private function adminExists($username) {
        try {
            $sql = "SELECT id FROM admin WHERE username = :username";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Admin exists check error: " . $e->getMessage());
            return false;
        }
    }

    private function emailExists($email) {
        try {
            $sql = "SELECT id FROM admin WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Email exists check error: " . $e->getMessage());
            return false;
        }
    }

    private function courseExists($course_code) {
        try {
            $sql = "SELECT id FROM courses WHERE course_code = :course_code";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':course_code', $course_code, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Course exists check error: " . $e->getMessage());
            return false;
        }
    }
}
?>
