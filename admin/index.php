<?php
require_once 'auth.php';

$db = new Database();
$conn = $db->getConnection();
$userId = getUserId();

$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalMeds = $conn->query("SELECT COUNT(*) FROM medicines WHERE active = 1")->fetchColumn();
$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Medicature</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: grid; grid-template-columns: 220px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #1e293b; padding: 2rem 0; color: white; }
        .admin-sidebar .brand { padding: 0 1.5rem 2rem; font-size: 1.25rem; font-weight: bold; border-bottom: 1px solid #334155; }
        .admin-sidebar a { display: block; padding: 0.75rem 1.5rem; color: #94a3b8; text-decoration: none; transition: all 0.2s; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: #334155; color: white; }
        .admin-main { background: #f8fafc; padding: 2rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .stat-num { font-size: 2.5rem; font-weight: bold; color: var(--primary-color); }
        .stat-label { color: #64748b; margin-top: 0.25rem; }
    </style>
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="brand">💊 Medicature Admin</div>
        <a href="index.php" class="active">🏠 Dashboard</a>
        <a href="broadcast.php">📧 Email Broadcast</a>
        <a href="users.php">👥 Users</a>
        <a href="settings.php">⚙️ Settings</a>
        <a href="../pages/dashboard.php">?? Back to App</a>
    </aside>
    <main class="admin-main">
        <h1 style="margin-bottom: 2rem;">Admin Dashboard</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-num"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Registered Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?php echo $totalMeds; ?></div>
                <div class="stat-label">Active Prescriptions</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?php echo $totalOrders; ?></div>
                <div class="stat-label">Pharmacy Orders</div>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <h2>Quick Actions</h2>
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                <a href="broadcast.php" class="btn btn-primary">📧 Send Email Broadcast</a>
                <a href="users.php" class="btn btn-secondary">👥 View All Users</a>
            </div>
        </div>
    </main>
</div>
</body>
</html>
