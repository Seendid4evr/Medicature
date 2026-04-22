<?php
require_once '../includes/session.php';
require_once '../config/database.php';
requireLogin();

$medicineId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
$userId     = getUserId();

if ($medicineId) {
    try {
        $db   = new Database();
        $conn = $db->getConnection();

        $conn->beginTransaction();

        
        $stmt = $conn->prepare("SELECT id, name FROM medicines WHERE id = ? AND user_id = ?");
        $stmt->execute([$medicineId, $userId]);
        $medicine = $stmt->fetch();

        if ($medicine) {
            
            $conn->prepare("DELETE FROM reminders WHERE medicine_id = ?")->execute([$medicineId]);
            $conn->prepare("DELETE FROM schedules WHERE medicine_id = ?")->execute([$medicineId]);
            $conn->prepare("DELETE FROM medicines WHERE id = ?")->execute([$medicineId]);

            $conn->commit();
            $_SESSION['success'] = 'Medicine "' . htmlspecialchars($medicine['name']) . '" deleted successfully.';
        } else {
            $conn->rollBack();
            $_SESSION['error'] = 'Medicine not found or you do not have permission to delete it.';
        }
    } catch (Throwable $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Delete medicine error: " . $e->getMessage());
        $_SESSION['error'] = 'A system error occurred. Please try again.';
    }
} else {
    $_SESSION['error'] = 'No valid medicine ID provided.';
}

header('Location: medicines.php');
exit();
?>
