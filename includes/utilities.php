<?php
require_once '../config.php';
require_once '../classes/Database.php';
class Utilities {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }


    // Sanitize input data
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map('self::sanitizeInput', $data);
        }

        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');

        return $data;
    }

    // Redirect to a specific page
  public static function redirect($url, $message = null, $type = null) {
    if ($message && $type) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }

    // Ensure the URL is absolute
    if (strpos($url, 'http') !== 0) {
        $url = BASE_URL . $url;
    }

    header("Location: $url");
    exit();
}
    // Display flash messages
    public static function displayFlash() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            $type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : 'info';

            echo "<div class='alert alert-$type'>$message</div>";

            // Clear the message after displaying
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
        }
    }

    // Format date for display
    public static function formatDate($date, $format = 'F j, Y') {
        if (empty($date) || $date == '0000-00-00') {
            return 'N/A';
        }

        $timestamp = strtotime($date);
        return date($format, $timestamp);
    }

    // Format time for display
    public static function formatTime($time, $format = 'g:i A') {
        if (empty($time) || $time == '00:00:00') {
            return 'N/A';
        }

        $timestamp = strtotime($time);
        return date($format, $timestamp);
    }

    // Get current academic year
    public static function getAcademicYear() {
        $current_month = date('n');
        $current_year = date('Y');

        // If current month is after June, academic year spans two calendar years
        if ($current_month >= 8) { // August to December
            return $current_year . '-' . ($current_year + 1);
        } else { // January to July
            return ($current_year - 1) . '-' . $current_year;
        }
    }

    // Get all year levels
    public static function getYearLevels() {
        return [
            '1st Year',
            '2nd Year',
            '3rd Year',
            '4th Year',
            '5th Year'
        ];
    }

    // Get all courses
    public function getCourses() {
        $query = "SELECT * FROM courses ORDER BY course_name";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get course name by ID
    public function getCourseName($course_id) {
        $query = "SELECT course_name FROM courses WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $course_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['course_name'] : 'N/A';
    }

    // Get student name by ID
    public function getStudentName($student_id) {
        $query = "SELECT full_name FROM students WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $student_id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['full_name'] : 'N/A';
    }

    // Get student details by ID
    public function getStudentDetails($student_id) {
        $query = "SELECT s.*, u.email
                  FROM students s
                  JOIN users u ON s.user_id = u.id
                  WHERE s.id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":id", $student_id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Check if a string is a valid date
    public static function isValidDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    // Check if a string is a valid time
    public static function isValidTime($time) {
        return preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]$/', $time);
    }

    // Generate a random password
    public static function generatePassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }

    // Calculate percentage
    public static function calculatePercentage($part, $whole) {
        if ($whole == 0) {
            return 0;
        }

        return round(($part / $whole) * 100, 2);
    }

    // Get days between two dates
    public static function getDaysBetweenDates($start_date, $end_date) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);

        return $interval->days;
    }

    // Get academic weeks between two dates
    public static function getAcademicWeeks($start_date, $end_date, $exclude_weekends = true) {
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $end = $end->modify('+1 day'); // Include end date in interval

        $interval = new DateInterval('P1D');
        $date_range = new DatePeriod($start, $interval, $end);

        $weekdays = 0;

        foreach ($date_range as $date) {
            if ($exclude_weekends) {
                // Skip weekends (Saturday and Sunday)
                if ($date->format('N') < 6) {
                    $weekdays++;
                }
            } else {
                $weekdays++;
            }
        }

        return ceil($weekdays / 5); // 5 days in a week
    }

    // Get month name from number
    public static function getMonthName($month_number) {
        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        ];

        return isset($months[$month_number]) ? $months[$month_number] : 'Invalid Month';
    }

    // Get current semester
    public static function getCurrentSemester() {
        $current_month = date('n');

        if ($current_month >= 1 && $current_month <= 5) {
            return 'Second Semester';
        } elseif ($current_month >= 8 && $current_month <= 12) {
            return 'First Semester';
        } else {
            return 'Summer';
        }
    }

    // Log activity
    public static function logActivity($user_id, $action, $details = null) {
        $database = new Database();
        $db = $database->getConnection();

        $query = "INSERT INTO activity_logs SET user_id=:user_id, action=:action, details=:details, ip_address=:ip_address";
        $stmt = $db->prepare($query);

        $ip_address = $_SERVER['REMOTE_ADDR'];

        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":action", $action);
        $stmt->bindParam(":details", $details);
        $stmt->bindParam(":ip_address", $ip_address);

        return $stmt->execute();
    }

    // Get pagination parameters
    public static function getPaginationParams($current_page, $total_items, $items_per_page = 10) {
        $total_pages = ceil($total_items / $items_per_page);
        $current_page = max(1, min($current_page, $total_pages));
        $offset = ($current_page - 1) * $items_per_page;

        return [
            'current_page' => $current_page,
            'total_pages' => $total_pages,
            'items_per_page' => $items_per_page,
            'offset' => $offset
        ];
    }

    // Generate pagination HTML
    public static function generatePagination($current_page, $total_pages, $url_pattern) {
        if ($total_pages <= 1) {
            return '';
        }

        $html = '<ul class="pagination">';

        // Previous button
        if ($current_page > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $current_page - 1) . '">Previous</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
        }

        // Page numbers
        $start_page = max(1, $current_page - 2);
        $end_page = min($total_pages, $start_page + 4);

        if ($end_page - $start_page < 4) {
            $start_page = max(1, $end_page - 4);
        }

        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $current_page) {
                $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $i) . '">' . $i . '</a></li>';
            }
        }

        // Next button
        if ($current_page < $total_pages) {
            $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $current_page + 1) . '">Next</a></li>';
        } else {
            $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
        }

        $html .= '</ul>';

        return $html;
    }
}

// Create global utilities instance
$utilities = new Utilities();
?>
