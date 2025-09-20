<?php
class Attendance {
    private $pdo;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->pdo = $pdo;
        } else {
            $database = Database::getInstance();
            $this->pdo = $database->getConnection();
        }
    }

    public function record($student_id, $status = 'present') {
        try {
            $date = date('Y-m-d');
            $time = date('H:i:s');

            // Check if already attended today
            if ($this->hasAttendedToday($student_id)) {
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
            error_log("Record attendance error: " . $e->getMessage());
            return "error";
        }
    }

    public function hasAttendedToday($student_id) {
        try {
            $date = date('Y-m-d');

            $sql = "SELECT id FROM attendance
                    WHERE student_id = :student_id AND date = :date";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':student_id', $student_id, PDO::PARAM_STR);
            $stmt->bindParam(':date', $date, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Check today attendance error: " . $e->getMessage());
            return false;
        }
    }

    public function getStudentHistory($student_id, $limit = 30) {
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
            error_log("Get student attendance history error: " . $e->getMessage());
            return [];
        }
    }

    public function getCourseAttendance($course_id, $date = null) {
        try {
            $sql = "SELECT a.*, s.name, s.year_level
                    FROM attendance a
                    JOIN students s ON a.student_id = s.student_id
                    WHERE s.course_id = :course_id";

            $params = [':course_id' => $course_id];

            if ($date) {
                $sql .= " AND a.date = :date";
                $params[':date'] = $date;
            }

            $sql .= " ORDER BY a.date DESC, a.time DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get course attendance error: " . $e->getMessage());
            return [];
        }
    }

    public function getDateRangeAttendance($start_date, $end_date, $course_id = null, $year_level = null) {
        try {
            $sql = "SELECT a.*, s.name, s.year_level, c.course_code, c.course_name
                    FROM attendance a
                    JOIN students s ON a.student_id = s.student_id
                    JOIN courses c ON s.course_id = c.id
                    WHERE a.date BETWEEN :start_date AND :end_date";

            $params = [
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ];

            if ($course_id) {
                $sql .= " AND s.course_id = :course_id";
                $params[':course_id'] = $course_id;
            }

            if ($year_level) {
                $sql .= " AND s.year_level = :year_level";
                $params[':year_level'] = $year_level;
            }

            $sql .= " ORDER BY a.date DESC, a.time DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get date range attendance error: " . $e->getMessage());
            return [];
        }
    }
}
?>
