<?php
// Base User class
abstract class User {
    protected $id;
    protected $username;
    protected $password;
    protected $email;
    protected $role;
    protected $created_at;

    // Database connection
    protected $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Getters and setters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function getCreatedAt() { return $this->created_at; }

    public function setUsername($username) { $this->username = $username; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = password_hash($password, PASSWORD_DEFAULT); }

    // Abstract methods
    abstract public function register($data);
    abstract public function login($username, $password);

    // Common method to check if user exists
    protected function userExists($username, $email = null) {
        $query = "SELECT id FROM users WHERE username = :username";
        if ($email) {
            $query .= " OR email = :email";
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        if ($email) {
            $stmt->bindParam(":email", $email);
        }

        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Common method to get user by username
    protected function getUserByUsername($username) {
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
