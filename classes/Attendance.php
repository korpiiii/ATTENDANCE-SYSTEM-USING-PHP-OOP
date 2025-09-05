<?php
require_once '../classes/Database.php';

class Attendance {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // File attendance for a student
    public function fileAttendance($student_id, $date = null, $time_in = null, $is_late = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }

        if ($time_in === null) {
            $time_in = date('H:i:s');
        }

        if ($is_late === null) {
            // Determine if late (after 8:00 AM)
            $is_late = ($time_in > '08:00:00') ? 1 : 0;
        }

        // Check if attendance already filed for today
        if ($this->hasAttendance($student_id, $date)) {
            return false;
        }

        $query = "INSERT INTO attendance SET student_id=:student_id, date=:date, time_in=:time_in, is_late=:is_late";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":time_in", $time_in);
        $stmt->bindParam(":is_late", $is_late);

        return $stmt->execute();
    }

    // Check if student already has attendance for a specific date
    public function hasAttendance($student_id, $date) {
        $query = "SELECT id FROM attendance WHERE student_id = :student_id AND date = :date";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":date", $date);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Get attendance history for a student
    public function getAttendanceHistory($student_id) {
        $query = "SELECT * FROM attendance WHERE student_id = :student_id ORDER BY date DESC, time_in DESC";
        $stmt = $this->db->prepare($query);

        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get attendance by course and year level
    public function getAttendanceByCourseAndYear($course, $year_level, $start_date = null, $end_date = null) {
        $query = "SELECT a.*, s.student_id, s.full_name, s.course, s.year_level
                  FROM attendance a
                  JOIN students s ON a.student_id = s.id
                  WHERE s.course = :course AND s.year_level = :year_level";

        // Add date range if provided
        if ($start_date && $end_date) {
            $query .= " AND a.date BETWEEN :start_date AND :end_date";
        }

        $query .= " ORDER BY a.date DESC, a.time_in DESC";

        $stmt = $this->db->prepare($query);

        $stmt->bindParam(":course", $course);
        $stmt->bindParam(":year_level", $year_level);

        if ($start_date && $end_date) {
            $stmt->bindParam(":start_date", $start_date);
            $stmt->bindParam(":end_date", $end_date);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get attendance statistics for a student
    public function getAttendanceStats($student_id) {
        $query = "SELECT
                    COUNT(*) as total_days,
                    SUM(CASE WHEN is_late = 1 THEN 1 ELSE 0 END) as late_days,
                    SUM(CASE WHEN is_late = 0 THEN 1 ELSE 0 END) as on_time_days
                  FROM attendance
                  WHERE student_id = :student_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get monthly attendance report for a student
    public function getMonthlyReport($student_id, $month, $year) {
        $start_date = date("$year-$month-01");
        $end_date = date("$year-$month-t", strtotime($start_date));

        $query = "SELECT * FROM attendance
                  WHERE student_id = :student_id
                  AND date BETWEEN :start_date AND :end_date
                  ORDER BY date ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id);
        $stmt->bindParam(":start_date", $start_date);
        $stmt->bindParam(":end_date", $end_date);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get attendance summary by course and year level
    public function getAttendanceSummary($course = null, $year_level = null) {
        $query = "SELECT
                    s.course,
                    s.year_level,
                    COUNT(DISTINCT s.id) as total_students,
                    COUNT(a.id) as total_attendance_records,
                    SUM(CASE WHEN a.is_late = 1 THEN 1 ELSE 0 END) as late_records,
                    SUM(CASE WHEN a.is_late = 0 THEN 1 ELSE 0 END) as on_time_records
                  FROM students s
                  LEFT JOIN attendance a ON s.id = a.student_id";

        $conditions = [];
        $params = [];

        if ($course) {
            $conditions[] = "s.course = :course";
            $params[":course"] = $course;
        }

        if ($year_level) {
            $conditions[] = "s.year_level = :year_level";
            $params[":year_level"] = $year_level;
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        $query .= " GROUP BY s.course, s.year_level
                    ORDER BY s.course, s.year_level";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get recent attendance records (for dashboard)
    public function getRecentAttendance($limit = 10) {
        $query = "SELECT a.*, s.student_id, s.full_name, s.course, s.year_level
                  FROM attendance a
                  JOIN students s ON a.student_id = s.id
                  ORDER BY a.date DESC, a.time_in DESC
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":limit", (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
