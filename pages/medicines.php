<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$userId = getUserId();

// Get all medicines with their schedules
$stmt = $conn->prepare("
    SELECT 
        m.*,
        bm.generic,
        bm.manufacturer,
        d.name as dependent_name,
        d.relationship as dependent_relationship,
        GROUP_CONCAT(s.time_of_day ORDER BY s.time_of_day) as times
    FROM medicines m
    LEFT JOIN bd_medicines bm ON m.bd_medicine_id = bm.id
    LEFT JOIN dependents d ON m.dependent_id = d.id
    LEFT JOIN schedules s ON m.id = s.medicine_id
    WHERE m.user_id = ? AND m.active = 1
    GROUP BY m.id
    ORDER BY m.created_at DESC
");
$stmt->execute([$userId]);
$medicines = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Medicines - Medicature</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="section-header">
            <div>
                <h1>My Medicines</h1>
                <p>Manage your medication list</p>
            </div>
            <a href="add_medicine.php" class="btn btn-primary">+ Add New Medicine</a>
        </div>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo htmlspecialchars($_SESSION['success']); 
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo htmlspecialchars($_SESSION['error']); 
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($medicines)): ?>
            <div class="empty-state">
                <div class="empty-icon">💊</div>
                <h3>No medicines added yet</h3>
                <p>Start by adding your first medication</p>
                <a href="add_medicine.php" class="btn btn-primary">Add Medicine</a>
            </div>
        <?php else: ?>
            <div style="background: var(--card-bg); border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow-md);">
                <table class="medicine-table">
                    <thead>
                        <tr>
                            <th>Medicine Name</th>
                            <th>Dosage</th>
                            <th>Schedule Times</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicines as $medicine): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($medicine['name']); ?></strong>
                                    <?php if (!empty($medicine['dependent_name'])): ?>
                                        <span class="badge" style="background:#f3e8ff;color:#9333ea;font-size:0.75rem;margin-left:0.5rem;padding:0.15rem 0.5rem;">
                                            For: <?php echo htmlspecialchars($medicine['dependent_name']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if (!empty($medicine['generic'])): ?>
                                        <br><span style="color:var(--primary-color);font-size:0.9em;"><?php echo htmlspecialchars($medicine['generic']); ?></span>
                                        <br><small style="color:#666;"><?php echo htmlspecialchars($medicine['manufacturer']); ?></small>
                                    <?php endif; ?>
                                    <?php if ($medicine['notes']): ?>
                                        <br><small><?php echo htmlspecialchars($medicine['notes']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($medicine['dosage']); ?></td>
                                <td>
                                    <?php 
                                    if ($medicine['times']) {
                                        $times = explode(',', $medicine['times']);
                                        foreach ($times as $time) {
                                            echo '<span class="badge" style="margin-right: 0.5rem;">' . date('g:i A', strtotime($time)) . '</span>';
                                        }
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php echo formatDate($medicine['start_date']); ?>
                                    <?php if ($medicine['end_date']): ?>
                                        <br>to <?php echo formatDate($medicine['end_date']); ?>
                                    <?php else: ?>
                                        <br><small>Ongoing</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if (!empty($medicine['bd_medicine_id'])): ?>
                                            <a href="pharmacy.php?refill_id=<?php echo $medicine['id']; ?>" class="btn btn-sm" style="background-color:#16a34a;color:white;font-weight:bold;">🛒 Order Refill</a>
                                            <a href="medicine_details.php?id=<?php echo $medicine['bd_medicine_id']; ?>" class="btn btn-primary btn-sm" style="background-color:var(--primary-color);">View Info</a>
                                        <?php endif; ?>
                                        <a href="edit_medicine.php?id=<?php echo $medicine['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                        <a href="delete_med_action.php?id=<?php echo $medicine['id']; ?>" onclick="return confirm('Delete this medicine? This cannot be undone.')"
                                           class="btn btn-danger btn-sm">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
</body>
</html>
