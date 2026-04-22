<?php
class Database {
    public function __construct() {
        date_default_timezone_set('Asia/Dhaka');
    }
    private $host = 'localhost';
    private $db_name = 'medicure';
    private $username = 'root'; // Change for production
    private $password = '';     // Change for production
    private $conn;

    public function getConnection() {
        if ($this->conn !== null) {
            return $this->conn; // reuse existing connection
        }
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+06:00'"
                ]
            );
        } catch(PDOException $e) {
            error_log("Connection error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
        return $this->conn;
    }
}
?>
