<?php
require_once '../includes/session.php';
require_once '../config/database.php';
requireLogin();

header('Content-Type: application/json');

$userId = getUserId();
$db     = new Database();
$conn   = $db->getConnection();

$now      = new DateTime();
$soon     = (clone $now)->modify('+60 minutes');

$nowTime  = $now->format('H:i:s');
$soonTime = $soon->format('H:i:s');
$today    = $now->format('Y-m-d');

$stmt = $conn->prepare("
    SELECT
        m.id   AS medicine_id,
        m.name AS medicine_name,
        m.dosage,
        s.time_of_day AS scheduled_time,
        s.id          AS schedule_id,
        CASE
            WHEN s.time_of_day BETWEEN ? AND ? THEN 'DUE_SOON'
            WHEN s.time_of_day < ?             THEN 'OVERDUE'
            ELSE 'UPCOMING'
        END AS status,
        COALESCE((
            SELECT COUNT(*)
            FROM reminders r
            WHERE r.medicine_id  = m.id
              AND r.schedule_id  = s.id
              AND DATE(r.reminder_datetime) = ?
              AND r.status = 'taken'
        ), 0) AS taken_today
    FROM medicines m
    JOIN schedules s ON m.id = s.medicine_id
    WHERE m.user_id = ?
      AND m.active  = 1
      AND m.start_date <= ?
      AND (m.end_date IS NULL OR m.end_date >= ?)
      AND (s.time_of_day BETWEEN ? AND ? OR s.time_of_day < ?)
    ORDER BY s.time_of_day ASC
");

$stmt->execute([
    $nowTime, $soonTime,
    $nowTime,
    $today,
    $userId,
    $today, $today,
    $nowTime, $soonTime, $nowTime
]);

$dues   = $stmt->fetchAll(PDO::FETCH_ASSOC);
$alerts = array_values(array_filter($dues, fn($d) => $d['taken_today'] == 0));

echo json_encode([
    'alerts' => $alerts,
    'now'    => $nowTime,
    'count'  => count($alerts),
]);
