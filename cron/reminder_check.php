<?php
require_once __DIR__ . '/../config/database.php';

$db = new Database();
$conn = $db->getConnection();

// Get current datetime
$now = date('Y-m-d H:i:00');
$currentDate = date('Y-m-d');
$currentTime = date('H:i:00');

// Find medicines that should trigger reminders now
$sql = "
    SELECT 
        m.id as medicine_id,
        m.user_id,
        m.name as medicine_name,
        m.dosage,
        s.id as schedule_id,
        s.time_of_day,
        u.email,
        u.name as user_name
    FROM medicines m
    JOIN schedules s ON m.id = s.medicine_id
    JOIN users u ON m.user_id = u.id
    WHERE m.active = 1
    AND m.start_date <= ?
    AND (m.end_date IS NULL OR m.end_date >= ?)
    AND s.time_of_day = ?
";

$stmt = $conn->prepare($sql);
$stmt->execute([$currentDate, $currentDate, $currentTime]);
$reminders = $stmt->fetchAll();

foreach ($reminders as $reminder) {
    // Check if reminder already sent for this datetime
    $checkStmt = $conn->prepare("
        SELECT id FROM reminders 
        WHERE user_id = ? 
        AND medicine_id = ? 
        AND schedule_id = ?
        AND DATE(reminder_datetime) = ?
        AND TIME(reminder_datetime) = ?
    ");
    $checkStmt->execute([
        $reminder['user_id'],
        $reminder['medicine_id'],
        $reminder['schedule_id'],
        $currentDate,
        $currentTime
    ]);
    
    if (!$checkStmt->fetch()) {
        // Insert new reminder
        $insertStmt = $conn->prepare("
            INSERT INTO reminders 
            (user_id, medicine_id, schedule_id, reminder_datetime, status)
            VALUES (?, ?, ?, ?, 'sent')
        ");
        $insertStmt->execute([
            $reminder['user_id'],
            $reminder['medicine_id'],
            $reminder['schedule_id'],
            $now
        ]);
        
        // Send email notification
        sendReminderEmail($reminder);
    }
}

function sendReminderEmail($reminder) {
    $to = $reminder['email'];
    $subject = "Medicature Reminder: Time to take " . $reminder['medicine_name'];
    $message = "Hello " . $reminder['user_name'] . ",\n\n";
    $message .= "This is a reminder to take your medication:\n\n";
    $message .= "Medicine: " . $reminder['medicine_name'] . "\n";
    $message .= "Dosage: " . $reminder['dosage'] . "\n";
    $message .= "Time: " . date('g:i A', strtotime($reminder['time_of_day'])) . "\n\n";
    $message .= "Stay healthy!\n\nMedicature Team";
    
    $headers = "From: noreply@medicature.local\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    mail($to, $subject, $message, $headers);
}

echo "Reminder check completed at " . date('Y-m-d H:i:s') . "\n";
?>
