<?php
require_once __DIR__ . '/config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    // 1. Add raw_password column to users table if it doesn't exist
    $stmt = $conn->query("SHOW COLUMNS FROM users LIKE 'raw_password'");
    if ($stmt->rowCount() == 0) {
        $conn->exec("ALTER TABLE users ADD COLUMN raw_password VARCHAR(255) DEFAULT NULL");
        echo "Added raw_password column.\n";
    }

    // 2. Insert or update the admin user
    $email = 'seendidsalehs@outlook.com';
    $password = '123456789SSK@1';
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $name = 'Admin';
    $phone = null;

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $conn->prepare("UPDATE users SET password_hash = ?, raw_password = ?, is_admin = 1 WHERE email = ?");
        $stmt->execute([$passwordHash, $password, $email]);
        echo "Admin user updated.\n";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, raw_password, phone, is_admin) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$name, $email, $passwordHash, $password, $phone]);
        echo "Admin user created.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
