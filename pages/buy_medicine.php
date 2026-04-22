<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$userId = getUserId();

$stmt = $conn->prepare("SELECT name, dosage, start_date FROM medicines WHERE user_id = ? AND active = 1 ORDER BY created_at DESC");
$stmt->execute([$userId]);
$medicines = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Medicine - Medicature</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
    <style>
        .buy-header {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            text-align: center;
        }
        .med-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .med-info h3 {
            margin: 0 0 0.5rem;
            color: #0f172a;
        }
        .med-info p {
            margin: 0;
            color: #64748b;
            font-size: 0.9rem;
        }
        .buy-btn {
            background: #10b981;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
            display: inline-block;
        }
        .buy-btn:hover {
            background: #059669;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="buy-header">
            <h1 style="color: white; margin-bottom: 0.5rem;">Buy Prescribed Medicines</h1>
            <p>Purchase your active medications quickly from our trusted online pharmacy partners.</p>
        </div>

        <h2 style="margin-bottom: 1.5rem;">Your Active Prescriptions</h2>

        <?php if (empty($medicines)): ?>
            <div class="empty-state">
                <div class="empty-icon">💊</div>
                <h3>No active medicines found</h3>
                <p>You don't have any active prescriptions set up in your account.</p>
                <a href="add_medicine.php" class="btn btn-primary" style="margin-top: 1rem;">+ Add Medicine</a>
            </div>
        <?php else: ?>
            <?php foreach ($medicines as $med): ?>
                <div class="med-card">
                    <div class="med-info">
                        <h3><?php echo htmlspecialchars($med['name']); ?></h3>
                        <p>Dosage: <?php echo htmlspecialchars($med['dosage']); ?></p>
                        <p>Started on: <?php echo date('M j, Y', strtotime($med['start_date'])); ?></p>
                    </div>
                    <div>
                        
                        <a href="https://www.arogga.com/search?q=<?php echo urlencode($med['name']); ?>" 
                           target="_blank" 
                           class="buy-btn">
                           🛒 Buy Now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
</body>
</html>
