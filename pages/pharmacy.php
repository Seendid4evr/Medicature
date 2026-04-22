<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$userId = getUserId();
$success = '';
$error = '';

// Handle creating an order from a refill request
if (isset($_GET['refill_id']) && is_numeric($_GET['refill_id'])) {
    $refillId = $_GET['refill_id'];
    
    // Verify medicine belongs to user
    $stmt = $conn->prepare("
        SELECT m.name
        FROM medicines m
        WHERE m.id = ? AND m.user_id = ?
    ");
    $stmt->execute([$refillId, $userId]);
    $medicine = $stmt->fetch();
    
    if ($medicine) {
        // Simulate a realistic price (BDT) since bd_medicines has no price column
        $price = rand(50, 500);
        $quantity = 1; // Default to 1 pack/box
        
        try {
            $conn->beginTransaction();
            
            // Create Order
            $stmtOrder = $conn->prepare("
                INSERT INTO orders (user_id, status, total_amount, shipping_address) 
                VALUES (?, 'Pending', ?, 'Default User Address')
            ");
            $total = $price * $quantity;
            // Add a simple 50 BDT delivery fee
            $totalAmount = $total + 50; 
            $stmtOrder->execute([$userId, $totalAmount]);
            $orderId = $conn->lastInsertId();
            
            // Create Order Item
            $stmtItem = $conn->prepare("
                INSERT INTO order_items (order_id, medicine_name, quantity, price) 
                VALUES (?, ?, ?, ?)
            ");
            $stmtItem->execute([$orderId, $medicine['name'], $quantity, $price]);
            
            $conn->commit();
            $success = "Successfully requested refill for " . htmlspecialchars($medicine['name']) . ". A partner pharmacy will dispatch it soon.";
        } catch (PDOException $e) {
            $conn->rollBack();
            $error = "Failed to process order. Please try again later.";
        }
    } else {
        $error = "Invalid prescription selected for refill.";
    }
}

// Fetch user's orders history
$stmtOrders = $conn->prepare("
    SELECT o.*, 
           (SELECT GROUP_CONCAT(CONCAT(quantity, 'x ', medicine_name) SEPARATOR ', ') 
            FROM order_items oi WHERE oi.order_id = o.id) as items
    FROM orders o
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmtOrders->execute([$userId]);
$orders = $stmtOrders->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Pharmacy Orders - Medicature</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
    <style>
        .pharmacy-header {
            background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
            color: white;
            padding: 3rem 2rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            text-align: center;
        }
        .order-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: var(--shadow-sm);
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }
        .order-id {
            font-weight: bold;
            font-size: 1.1rem;
        }
        .order-date {
            color: #666;
            font-size: 0.9em;
        }
        .order-details {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1rem;
        }
        .order-items {
            color: #333;
        }
        .order-total {
            text-align: right;
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .status-Pending { background: #fef9c3; color: #a16207; }
        .status-Processing { background: #e0f2fe; color: #0369a1; }
        .status-Shipped { background: #f3e8ff; color: #7e22ce; }
        .status-Delivered { background: #dcfce7; color: #15803d; }
        .status-Cancelled { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="pharmacy-header">
            <h1 style="color: white; margin-bottom: 0.5rem;">Medicature Pharmacy Partner Network</h1>
            <p>Get your prescriptions refilled and delivered directly to your doorstep.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>My Orders</h2>
            <a href="medicines.php" class="btn btn-primary">Browse My Prescriptions</a>
        </div>

        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="empty-icon">🛒</div>
                <h3>No orders yet</h3>
                <p>Click "Order Refill" on any of your active medications to start an order.</p>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-id">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></div>
                            <div class="order-date">Placed on <?php echo date('M j, Y, g:i A', strtotime($order['created_at'])); ?></div>
                        </div>
                        <span class="status-badge status-<?php echo htmlspecialchars($order['status']); ?>">
                            <?php echo htmlspecialchars($order['status']); ?>
                        </span>
                    </div>
                    <div class="order-details">
                        <div class="order-items">
                            <strong>Items:</strong><br>
                            <?php echo htmlspecialchars($order['items']); ?>
                            <br><small style="color: #666; display: block; margin-top: 0.5rem;">+ Home Delivery Fee</small>
                        </div>
                        <div class="order-total">
                            ৳<?php echo number_format($order['total_amount'], 2); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
</body>
</html>
