<?php
require_once '../includes/session.php';
require_once '../config/database.php';
requireLogin();

$medicineId = $_GET['id'] ?? null;
$userId = getUserId();

if ($medicineId) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Start transaction
        $conn->beginTransaction();

        // Check verification first
        $check = $conn->prepare("SELECT id FROM medicines WHERE id = ? AND user_id = ?");
        $check->execute([$medicineId, $userId]);
        
        $exists = $check->fetch();
        file_put_contents('debug_log.txt', "Check result for ID $medicineId User $userId: " . ($exists ? 'Found' : 'Not Found') . "\n", FILE_APPEND);

        if ($exists) {
            // Delete related reminders first
            $stmt = $conn->prepare("DELETE FROM reminders WHERE medicine_id = ?");
            $stmt->execute([$medicineId]);
            file_put_contents('debug_log.txt', "Deleted reminders\n", FILE_APPEND);

            // Delete related schedules
            $stmt = $conn->prepare("DELETE FROM schedules WHERE medicine_id = ?");
            $stmt->execute([$medicineId]);
            file_put_contents('debug_log.txt', "Deleted schedules\n", FILE_APPEND);

            // Delete the medicine
            $stmt = $conn->prepare("DELETE FROM medicines WHERE id = ?");
            $stmt->execute([$medicineId]);
            file_put_contents('debug_log.txt', "Deleted medicine\n", FILE_APPEND);
            
            $conn->commit();
            $_SESSION['success'] = 'Medicine deleted successfully.';
        } else {
            $conn->rollBack();
            file_put_contents('debug_log.txt', "Rollback - verify failed\n", FILE_APPEND);
            $_SESSION['error'] = 'Invalid medicine or unauthorized access.';
        }
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        file_put_contents('debug_log.txt', "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
        error_log("Delete medicine error: " . $e->getMessage());
        $_SESSION['error'] = 'Failed to delete medicine. Please try again.';
    }
}

header('Location: medicines.php');
exit();
?>
