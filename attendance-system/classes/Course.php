<?php
class Course {
    private $pdo;

    public function __construct($pdo = null) {
        if ($pdo instanceof PDO) {
            $this->pdo = $pdo;
        } else {
            $database = Database::getInstance();
            $this->pdo = $database->getConnection();
        }
    }

    public function getAll() {
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

    public function getById($course_id) {
        try {
            $sql = "SELECT * FROM courses WHERE id = :course_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':course_id', $course_id, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get course by ID error: " . $e->getMessage());
            return false;
        }
    }

    public function getByCode($course_code) {
        try {
            $sql = "SELECT * FROM courses WHERE course_code = :course_code";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':course_code', $course_code, PDO::PARAM_STR);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get course by code error: " . $e->getMessage());
            return false;
        }
    }
}
?>
