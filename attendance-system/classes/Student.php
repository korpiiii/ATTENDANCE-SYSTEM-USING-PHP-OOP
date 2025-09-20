<?php
require_once 'User.php';

class Student extends User {

    public function updateStudentProfile($student_id, $name, $email, $course_id, $year_level) {
        try {
            $sql = "UPDATE students SET name = :name, email = :email,
                    course_id = :course_id, year_level = :year_level,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE student_id = :student_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->bindParam(':year_level', $year_level, PDO::PARAM_STR);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Update profile error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Change student password
     */
    public function changePassword($student_id, $current_password, $new_password) {
        try {
            // First verify current password
            $sql = "SELECT password FROM students WHERE student_id = :student_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->execute();

            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student || !password_verify($current_password, $student['password'])) {
                return "invalid_current";
            }

            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE students SET password = :password WHERE student_id = :student_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);

            return $stmt->execute() ? "success" : "error";
        } catch (PDOException $e) {
            error_log("Change password error: " . $e->getMessage());
            return "error";
        }
    }



    public function register($student_id, $name, $email, $password, $course_id, $year_level) {
        try {
            // Check if student already exists
            if ($this->studentExists($student_id)) {
                return "student_exists";
            }

            // Check if email already exists
            if ($this->emailExists($email)) {
                return "email_exists";
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO students (student_id, name, email, password, course_id, year_level)
                    VALUES (:student_id, :name, :email, :password, :course_id, :year_level)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->bindParam(':year_level', $year_level, PDO::PARAM_STR);

            if ($stmt->execute()) {
                return "success";
            }
            return "error";

        } catch (PDOException $e) {
            error_log("Student registration error: " . $e->getMessage());
            return "database_error";
        }
    }

    public function login($student_id, $password) {
        try {
            $sql = "SELECT * FROM students WHERE student_id = :student_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->execute();

            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student && password_verify($password, $student['password'])) {
                // Remove password from returned array for security
                unset($student['password']);
                return $student;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Student login error: " . $e->getMessage());
            return false;
        }
    }

    public function markAttendance($student_id, $status = 'present') {
        try {
            $date = date('Y-m-d');
            $time = date('H:i:s');

            // Check if already attended today
            $check_sql = "SELECT * FROM attendance WHERE student_id = :student_id AND date = :date";
            $check_stmt = $this->pdo->prepare($check_sql);
            $check_stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $check_stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $check_stmt->execute();

            if ($check_stmt->rowCount() > 0) {
                return "already_attended";
            }

            $sql = "INSERT INTO attendance (student_id, date, time, status)
                    VALUES (:student_id, :date, :time, :status)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':time', $time, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);

            return $stmt->execute() ? "success" : "error";
        } catch (PDOException $e) {
            error_log("Attendance error: " . $e->getMessage());
            return "error";
        }
    }

    public function getAttendanceHistory($student_id, $limit = 30) {
        try {
            $sql = "SELECT a.*, c.course_code, c.course_name
                    FROM attendance a
                    JOIN students s ON a.student_id = s.student_id
                    JOIN courses c ON s.course_id = c.id
                    WHERE a.student_id = :student_id
                    ORDER BY a.date DESC, a.time DESC
                    LIMIT :limit";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get attendance history error: " . $e->getMessage());
            return [];
        }
    }

    public function submitExcuseLetter($student_id, $course_id, $date, $reason, $attachment = null) {
        try {
            $sql = "INSERT INTO excuse_letters (student_id, course_id, date, reason, attachment)
                    VALUES (:student_id, :course_id, :date, :reason, :attachment)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
            $stmt->bindParam(':attachment', $attachment, PDO::PARAM_STR);

            return $stmt->execute() ? "success" : "error";
        } catch (PDOException $e) {
            error_log("Submit excuse letter error: " . $e->getMessage());
            return "error";
        }
    }

    public function getExcuseLetters($student_id) {
        try {
            $sql = "SELECT el.*, c.course_code, c.course_name
                    FROM excuse_letters el
                    JOIN courses c ON el.course_id = c.id
                    WHERE el.student_id = :student_id
                    ORDER BY el.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get excuse letters error: " . $e->getMessage());
            return [];
        }
    }

    private function studentExists($student_id) {
        try {
            $sql = "SELECT student_id FROM students WHERE student_id = :student_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Student exists check error: " . $e->getMessage());
            return false;
        }
    }

    private function emailExists($email) {
        try {
            $sql = "SELECT student_id FROM students WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Email exists check error: " . $e->getMessage());
            return false;
        }
    }

    public function getStudentCourses($student_id) {
        try {
            $sql = "SELECT c.* FROM courses c
                    JOIN students s ON c.id = s.course_id
                    WHERE s.student_id = :student_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get student courses error: " . $e->getMessage());
            return false;
        }
    }

    public function getTodayAttendance($student_id) {
        try {
            $date = date('Y-m-d');

            $sql = "SELECT * FROM attendance
                    WHERE student_id = :student_id AND date = :date";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Today attendance error: " . $e->getMessage());
            return false;
        }
    }
}
?>
