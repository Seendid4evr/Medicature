<?php
require_once 'auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$userId = $_GET['id'];
$db = new Database();
$conn = $db->getConnection();

try {
    // Only delete non-admin users
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0");
    $stmt->execute([$userId]);
    
    // Redirect back with success (in a real app, use session flashing)
    header("Location: users.php?deleted=1");
    exit();
} catch (PDOException $e) {
    die("Failed to delete user. Please try again.");
}
?>
