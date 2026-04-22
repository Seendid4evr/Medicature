<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$userId = getUserId();
$medicineId = $_GET['id'] ?? null;

if (!$medicineId) {
    header('Location: medicines.php');
    exit();
}

$stmt = $conn->prepare("
    SELECT m.*, GROUP_CONCAT(s.time_of_day) as times
    FROM medicines m
    LEFT JOIN schedules s ON m.id = s.medicine_id
    WHERE m.id = ? AND m.user_id = ?
    GROUP BY m.id
");
$stmt->execute([$medicineId, $userId]);
$medicine = $stmt->fetch();

if (!$medicine) {
    header('Location: medicines.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $dosage = sanitizeInput($_POST['dosage'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? null;
    $times = $_POST['times'] ?? [];
    
    if (empty($name) || empty($dosage) || empty($startDate) || empty($times)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            $conn->beginTransaction();
            
            
            $stmt = $conn->prepare("
                UPDATE medicines 
                SET name = ?, dosage = ?, notes = ?, start_date = ?, end_date = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$name, $dosage, $notes, $startDate, $endDate ?: null, $medicineId, $userId]);
            
            
            $stmt = $conn->prepare("DELETE FROM schedules WHERE medicine_id = ?");
            $stmt->execute([$medicineId]);
            
            $stmt = $conn->prepare("INSERT INTO schedules (medicine_id, time_of_day) VALUES (?, ?)");
            foreach ($times as $time) {
                if (!empty($time)) {
                    $stmt->execute([$medicineId, $time]);
                }
            }
            
            $conn->commit();
            $success = 'Medicine updated successfully!';
            
            
            $stmt = $conn->prepare("
                SELECT m.*, GROUP_CONCAT(s.time_of_day) as times
                FROM medicines m
                LEFT JOIN schedules s ON m.id = s.medicine_id
                WHERE m.id = ? AND m.user_id = ?
                GROUP BY m.id
            ");
            $stmt->execute([$medicineId, $userId]);
            $medicine = $stmt->fetch();
            
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Edit medicine error: " . $e->getMessage());
            $error = 'Failed to update medicine. Please try again.';
        }
    }
}

$currentTimes = explode(',', $medicine['times']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Medicine - Medicature</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
</head>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Edit Medicine</h1>
            <p>Update medication details</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div style="background: var(--card-bg); border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow-md); max-width: 800px;">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Medicine Name *</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($medicine['name']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="dosage">Dosage *</label>
                    <input type="text" id="dosage" name="dosage" required value="<?php echo htmlspecialchars($medicine['dosage']); ?>">
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes / Instructions</label>
                    <textarea id="notes" name="notes"><?php echo htmlspecialchars($medicine['notes']); ?></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="start_date">Start Date *</label>
                        <input type="date" id="start_date" name="start_date" required value="<?php echo $medicine['start_date']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">End Date (optional)</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $medicine['end_date']; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Schedule Times * <small>(when to take medicine)</small></label>
                    <div id="times-container">
                        <?php foreach ($currentTimes as $index => $time): ?>
                            <div class="time-input" style="margin-bottom: 0.5rem;">
                                <label>Time <?php echo $index + 1; ?></label>
                                <div style="display: flex; gap: 0.5rem;">
                                    <input type="time" name="times[]" required value="<?php echo $time; ?>">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.time-input').remove()">Remove</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addTimeInput()" style="margin-top: 0.5rem;">+ Add Another Time</button>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Update Medicine</button>
                    <a href="medicines.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
</body>
</html>
