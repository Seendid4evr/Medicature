<?php
require_once __DIR__ . '/../config/database.php';

// Singleton DB connection — avoids creating a new connection (+ init_command) on every auth call
function getDbConnection() {
    static $conn = null;
    if ($conn === null) {
        $db = new Database();
        $conn = $db->getConnection();
    }
    return $conn;
}

function registerUser($name, $email, $password, $phone = null) {
    $conn = getDbConnection();
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters'];
    }

    // Block reserved admin emails
    $adminEmails = ['seendidpc@gmail.com', 'salehkabir236@gmail.com', 'seendidsalehs@outlook.com'];
    if (in_array(strtolower(trim($email)), $adminEmails)) {
        return ['success' => false, 'message' => 'This email address is reserved.'];
    }
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered'];
    }
    
    // Hash password and insert user
    // Cost 10 is the bcrypt default and safe for production; lower to 9 locally if login feels slow
    $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, raw_password, phone, is_admin) VALUES (?, ?, ?, ?, ?, 0)");
    
    try {
        $stmt->execute([$name, $email, $passwordHash, $password, $phone ?: null]);
        return ['success' => true, 'message' => 'Registration successful'];
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}


function loginUser($email, $password) {
    $conn = getDbConnection();
    
    $stmt = $conn->prepare("SELECT id, name, email, password_hash, is_admin FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_admin']   = (bool)$user['is_admin'];
        
        return ['success' => true];
    }
    
    return ['success' => false, 'message' => 'Invalid email or password'];
}



function logoutUser() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
?>
