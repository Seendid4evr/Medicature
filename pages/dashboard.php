<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$userId = getUserId();

// Get today's medicines with schedules
$today = date('Y-m-d');
$stmt = $conn->prepare("
    SELECT 
        m.id, m.name, m.dosage, m.notes,
        bm.generic,
        d.name as dependent_name,
        s.id as schedule_id, s.time_of_day,
        COALESCE(r.status, 'pending') as status,
        r.taken_at
    FROM medicines m
    LEFT JOIN bd_medicines bm ON m.bd_medicine_id = bm.id
    LEFT JOIN dependents d ON m.dependent_id = d.id
    JOIN schedules s ON m.id = s.medicine_id
    LEFT JOIN reminders r ON r.medicine_id = m.id 
        AND r.schedule_id = s.id 
        AND DATE(r.reminder_datetime) = ?
    WHERE m.user_id = ? 
    AND m.active = 1
    AND m.start_date <= ?
    AND (m.end_date IS NULL OR m.end_date >= ?)
    ORDER BY s.time_of_day ASC
");
$stmt->execute([$today, $userId, $today, $today]);
$todaySchedule = $stmt->fetchAll();

// Get total active medicines count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM medicines WHERE user_id = ? AND active = 1");
$stmt->execute([$userId]);
$medicineCount = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Medicature</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Welcome back, <?php echo htmlspecialchars(getUserName()); ?>! 👋</h1>
            <p>Here's your medication schedule for today</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💊</div>
                <div class="stat-content">
                    <h3><?php echo $medicineCount; ?></h3>
                    <p>Active Medicines</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⏰</div>
                <div class="stat-content">
                    <h3><?php echo count($todaySchedule); ?></h3>
                    <p>Doses Today</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <h3><?php echo count(array_filter($todaySchedule, fn($s) => $s['status'] === 'taken')); ?></h3>
                    <p>Taken</p>
                </div>
            </div>
        </div>
        
        <div class="schedule-section">
            <div class="section-header">
                <h2>Today's Schedule</h2>
                <a href="medicines.php" class="btn btn-primary">+ Add Medicine</a>
            </div>
            
            <?php if (empty($todaySchedule)): ?>
                <div class="empty-state">
                    <div class="empty-icon">💊</div>
                    <h3>No medications scheduled for today</h3>
                    <p>Add your first medicine to get started</p>
                    <a href="medicines.php" class="btn btn-primary">Add Medicine</a>
                </div>
            <?php else: ?>
                <div class="schedule-list">
                    <?php foreach ($todaySchedule as $item): ?>
                        <div class="schedule-item <?php echo $item['status']; ?>">
                            <div class="schedule-time">
                                <span class="time"><?php echo date('g:i A', strtotime($item['time_of_day'])); ?></span>
                            </div>
                            <div class="schedule-content">
                                <h3>
                                    <?php echo htmlspecialchars($item['name']); ?>
                                    <?php if (!empty($item['dependent_name'])): ?>
                                        <span class="badge" style="background:#f3e8ff;color:#9333ea;font-size:0.7rem;vertical-align:middle;margin-left:0.5rem;padding:0.15rem 0.5rem;">
                                            <?php echo htmlspecialchars($item['dependent_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                </h3>
                                <?php if (!empty($item['generic'])): ?>
                                    <p style="color:var(--primary-color);font-size:0.9em;margin:0;"><?php echo htmlspecialchars($item['generic']); ?></p>
                                <?php endif; ?>
                                <p class="dosage"><?php echo htmlspecialchars($item['dosage']); ?></p>
                                <?php if ($item['notes']): ?>
                                    <p class="notes"><?php echo htmlspecialchars($item['notes']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="schedule-action">
                                <?php if ($item['status'] === 'taken'): ?>
                                    <span class="badge badge-success">✅ Taken</span>
                                    <small>at <?php echo date('g:i A', strtotime($item['taken_at'])); ?></small>
                                <?php else: ?>
                                    <button class="btn btn-success btn-sm mark-taken" 
                                            data-medicine-id="<?php echo $item['id']; ?>"
                                            data-schedule-id="<?php echo $item['schedule_id']; ?>">
                                        Mark as Taken
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
</body>
</html>
