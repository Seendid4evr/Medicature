<?php
require_once '../includes/session.php';
require_once '../config/database.php';
requireLogin();

header('Content-Type: application/json');

$userId = getUserId();
$db = new Database();
$conn = $db->getConnection();

$now       = new DateTime();
$soon      = (clone $now)->modify('+60 minutes');

$nowTime   = $now->format('H:i:s');
$soonTime  = $soon->format('H:i:s');
$today     = $now->format('Y-m-d');

$stmt = $conn->prepare("
    SELECT 
        m.id as medicine_id,
        m.name as medicine_name,
        m.dosage,
        ms.time as scheduled_time,
        ms.id as schedule_id,
        CASE 
            WHEN ms.time <= ? AND ms.time >= ? THEN 'DUE_SOON'
            WHEN ms.time < ?  THEN 'OVERDUE'
            ELSE 'UPCOMING'
        END as status,
        -- Check if already taken today
        COALESCE((
            SELECT COUNT(*) FROM medicine_logs ml 
            WHERE ml.medicine_id = m.id 
            AND ml.taken_at >= ? 
            AND DATE(ml.taken_at) = ?
        ), 0) as taken_today
    FROM medicines m
    JOIN medicine_schedules ms ON m.id = ms.medicine_id
    WHERE m.user_id = ? 
      AND m.active = 1
      AND (ms.time BETWEEN ? AND ? OR ms.time < ?)
    ORDER BY ms.time ASC
");
$stmt->execute([
    $soonTime, $nowTime,    
    $nowTime,               
    $now->format('Y-m-d H:i:s'), $today,  
    $userId,
    $nowTime, $soonTime, $nowTime  
]);

$dues = $stmt->fetchAll();

$alerts = array_values(array_filter($dues, fn($d) => $d['taken_today'] == 0));

echo json_encode([
    'alerts' => $alerts,
    'now'    => $nowTime,
    'count'  => count($alerts),
]);
