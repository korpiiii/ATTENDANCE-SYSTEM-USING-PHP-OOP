<?php
// classes/User.php
require_once 'Database.php';

abstract class User {
    protected $pdo;
    protected $userData;

    public function __construct($pdo = null) {
        // Ensure database configuration is loaded
        if (!defined('DB_HOST') && file_exists('config/database.php')) {
            require_once 'config/database.php';
        }

        if ($pdo instanceof PDO) {
            $this->pdo = $pdo;
        } else {
            $database = Database::getInstance();
            $this->pdo = $database->getConnection();
        }
    }

    // Common user methods
    abstract public function login($identifier, $password);

    public function logout() {
        session_destroy();
        header('Location: ../index.php');
        exit();
    }

    public function changePassword($userId, $currentPassword, $newPassword) {
        // Implementation would be in child classes
    }

    public function getProfile($userId) {
        // Implementation would be in child classes
    }

    public function updateProfile($userId, $data) {
        // Implementation would be in child classes
    }

    protected function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    protected function validatePassword($password) {
        return strlen($password) >= 6;
    }
}
?>
