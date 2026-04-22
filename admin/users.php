<?php
require_once 'auth.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("
    SELECT u.id, u.name, u.email, u.created_at,
           GROUP_CONCAT(DISTINCT m.name SEPARATOR ', ') as active_meds_list,
           GROUP_CONCAT(DISTINCT oi.medicine_name SEPARATOR ', ') as ordered_meds_list
    FROM users u
    LEFT JOIN medicines m ON u.id = m.user_id AND m.active = 1
    LEFT JOIN orders o ON u.id = o.user_id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE u.is_admin = 0
    GROUP BY u.id
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Medicature Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: grid; grid-template-columns: 220px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #1e293b; padding: 2rem 0; color: white; }
        .admin-sidebar .brand { padding: 0 1.5rem 2rem; font-size: 1.25rem; font-weight: bold; border-bottom: 1px solid #334155; }
        .admin-sidebar a { display: block; padding: 0.75rem 1.5rem; color: #94a3b8; text-decoration: none; transition: all 0.2s; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: #334155; color: white; }
        .admin-main { background: #f8fafc; padding: 2rem; }
        .med-tags { display: flex; flex-wrap: wrap; gap: 0.4rem; }
        .med-tag { font-size: 0.8rem; padding: 0.2rem 0.6rem; border-radius: 6px; background: #e2e8f0; color: #334155; }
        .active-med-tag { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .ordered-med-tag { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .empty-text { font-size: 0.85rem; color: #94a3b8; font-style: italic; }
    </style>
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="brand">💊 Medicature Admin</div>
        <a href="index.php">🏠 Dashboard</a>
        <a href="broadcast.php">📧 Email Broadcast</a>
        <a href="users.php" class="active">👥 Registered Patients</a>
        <a href="settings.php">⚙️ Settings</a>
        <a href="../pages/dashboard.php">?? Back to App</a>
    </aside>
    <main class="admin-main">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="margin: 0;">👥 Patient Directory (<?php echo count($users); ?>)</h1>
            <a href="add_user.php" class="btn btn-primary" style="background: #10b981;">+ Add New Patient</a>
        </div>
        
        <div style="background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <table class="medicines-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f1f5f9;">
                        <th style="padding: 1rem; text-align: left; width: 20%;">Patient Info</th>
                        <th style="padding: 1rem; text-align: left; width: 30%;">Active Medicines</th>
                        <th style="padding: 1rem; text-align: left; width: 25%;">Ordered Medicines</th>
                        <th style="padding: 1rem; text-align: left; width: 25%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) === 0): ?>
                    <tr><td colspan="4" style="padding: 2rem; text-align: center; color: #64748b;">No patients registered yet.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($users as $user): ?>
                    <tr style="border-top: 1px solid #f1f5f9;">
                        <td style="padding: 1rem;">
                            <strong style="display: block; color: #0f172a; margin-bottom: 0.2rem;"><?php echo htmlspecialchars($user['name']); ?></strong>
                            <span style="color: #64748b; font-size: 0.85rem;"><?php echo htmlspecialchars($user['email']); ?></span>
                        </td>
                        <td style="padding: 1rem;">
                            <?php if (!empty($user['active_meds_list'])): ?>
                                <div class="med-tags">
                                    <?php foreach (explode(',', $user['active_meds_list']) as $med): ?>
                                        <span class="med-tag active-med-tag"><?php echo htmlspecialchars(trim($med)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="empty-text">None active</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem;">
                            <?php if (!empty($user['ordered_meds_list'])): ?>
                                <div class="med-tags">
                                    <?php foreach (explode(',', $user['ordered_meds_list']) as $med): ?>
                                        <span class="med-tag ordered-med-tag"><?php echo htmlspecialchars(trim($med)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="empty-text">No orders yet</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 1rem; color: #64748b; font-size: 0.9rem;">
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <a href="user_details.php?id=<?php echo $user['id']; ?>" class="btn" style="padding: 0.25rem 0.6rem; font-size: 0.8rem; background: #3b82f6; color: white;">
                                    👁️ View Details
                                </a>
                                <a href="broadcast.php?email=<?php echo urlencode($user['email']); ?>" class="btn" style="padding: 0.25rem 0.6rem; font-size: 0.8rem; background: #f59e0b; color: white;">
                                    📧 Notify
                                </a>
                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to completely delete this patient and all their data?');" class="btn" style="padding: 0.25rem 0.6rem; font-size: 0.8rem; background: #ef4444; color: white;">
                                    🗑️ Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
