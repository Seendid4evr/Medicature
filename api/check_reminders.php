<?php
require_once '../includes/session.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    $userId = getUserId();
    
    
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');
    $fifteenMinsAgo = date('H:i:s', strtotime('-15 minutes'));
    
    
    $sql = "
        SELECT 
            m.id as medicine_id,
            m.user_id,
            s.id as schedule_id,
            s.time_of_day
        FROM medicines m
        JOIN schedules s ON m.id = s.medicine_id
        WHERE m.user_id = ?
        AND m.active = 1
        AND m.start_date <= ?
        AND (m.end_date IS NULL OR m.end_date >= ?)
        AND s.time_of_day BETWEEN ? AND ?
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId, $currentDate, $currentDate, $fifteenMinsAgo, $currentTime]);
    $dueSchedules = $stmt->fetchAll();
    
    foreach ($dueSchedules as $schedule) {
        
        $checkStmt = $conn->prepare("
            SELECT id FROM reminders 
            WHERE user_id = ? 
            AND medicine_id = ? 
            AND schedule_id = ?
            AND DATE(reminder_datetime) = ?
        ");
        $checkStmt->execute([$userId, $schedule['medicine_id'], $schedule['schedule_id'], $currentDate]);
        
        if (!$checkStmt->fetch()) {
            
            $reminderTime = $currentDate . ' ' . $schedule['time_of_day'];
            $req = $conn->prepare("
                INSERT INTO reminders (user_id, medicine_id, schedule_id, reminder_datetime, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $req->execute([$userId, $schedule['medicine_id'], $schedule['schedule_id'], $reminderTime]);
        }
    }

    
    $stmt = $conn->prepare("
        SELECT 
            r.id,
            r.reminder_datetime,
            m.name as medicine_name,
            m.dosage
        FROM reminders r
        JOIN medicines m ON r.medicine_id = m.id
        WHERE r.user_id = ?
        AND r.status IN ('pending') 
        AND r.reminder_datetime >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
        AND r.reminder_datetime <= NOW()
        ORDER BY r.reminder_datetime DESC
    ");
    $stmt->execute([$userId]);
    $reminders = $stmt->fetchAll();
    
    
    if (!empty($reminders)) {
        $ids = array_column($reminders, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $conn->prepare("UPDATE reminders SET status = 'sent' WHERE id IN ($placeholders)");
        $stmt->execute($ids);
    }
    
    echo json_encode([
        'success' => true,
        'reminders' => $reminders
    ]);
    
} catch (PDOException $e) {
    error_log("Check reminders error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
