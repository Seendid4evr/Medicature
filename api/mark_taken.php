<?php
require_once '../includes/session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$medicineId = $data['medicine_id'] ?? null;
$scheduleId = $data['schedule_id'] ?? null;
$takenAt = $data['taken_at'] ?? date('Y-m-d H:i:s');
$userId = getUserId();

if (!$medicineId || !$scheduleId) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    
    $stmt = $conn->prepare("SELECT id FROM medicines WHERE id = ? AND user_id = ?");
    $stmt->execute([$medicineId, $userId]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Medicine not found']);
        exit();
    }
    
    
    $today = date('Y-m-d');
    $stmt = $conn->prepare("
        SELECT id FROM reminders 
        WHERE user_id = ? 
        AND medicine_id = ? 
        AND schedule_id = ?
        AND DATE(reminder_datetime) = ?
    ");
    $stmt->execute([$userId, $medicineId, $scheduleId, $today]);
    $reminder = $stmt->fetch();
    
    if ($reminder) {
        
        $stmt = $conn->prepare("
            UPDATE reminders 
            SET status = 'taken', taken_at = ? 
            WHERE id = ?
        ");
        $stmt->execute([$takenAt, $reminder['id']]);
    } else {
        
        $stmt = $conn->prepare("
            INSERT INTO reminders 
            (user_id, medicine_id, schedule_id, reminder_datetime, status, taken_at)
            VALUES (?, ?, ?, ?, 'taken', ?)
        ");
        $stmt->execute([$userId, $medicineId, $scheduleId, date('Y-m-d H:i:s'), $takenAt]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Marked as taken',
        'taken_at' => $takenAt
    ]);
    
} catch (PDOException $e) {
    error_log("Mark taken error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
