<?php
require_once 'auth.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$userId = $_GET['id'];
$db = new Database();
$conn = $db->getConnection();

$stmtUser = $conn->prepare("SELECT id, name, email, phone, raw_password, created_at FROM users WHERE id = ? AND is_admin = 0");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch();

if (!$user) {
    die("User not found or is an admin.");
}

$stmtFamily = $conn->prepare("SELECT name, relationship FROM dependents WHERE user_id = ?");
$stmtFamily->execute([$userId]);
$family = $stmtFamily->fetchAll();

$stmtActiveMeds = $conn->prepare("SELECT name, dosage, start_date, end_date FROM medicines WHERE user_id = ? AND active = 1");
$stmtActiveMeds->execute([$userId]);
$activeMeds = $stmtActiveMeds->fetchAll();

$stmtInactiveMeds = $conn->prepare("SELECT name, dosage, start_date, end_date FROM medicines WHERE user_id = ? AND active = 0");
$stmtInactiveMeds->execute([$userId]);
$inactiveMeds = $stmtInactiveMeds->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Details - Medicature Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: grid; grid-template-columns: 220px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #1e293b; padding: 2rem 0; color: white; }
        .admin-sidebar .brand { padding: 0 1.5rem 2rem; font-size: 1.25rem; font-weight: bold; border-bottom: 1px solid #334155; }
        .admin-sidebar a { display: block; padding: 0.75rem 1.5rem; color: #94a3b8; text-decoration: none; transition: all 0.2s; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: #334155; color: white; }
        .admin-main { background: #f8fafc; padding: 2rem; }
        .details-card { background: white; border-radius: 12px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; }
        h2 { border-bottom: 2px solid #e2e8f0; padding-bottom: 0.5rem; margin-bottom: 1.5rem; color: #0f172a; }
        .info-row { margin-bottom: 1rem; }
        .info-label { font-weight: 600; color: #64748b; display: block; margin-bottom: 0.25rem; }
        .info-value { color: #0f172a; font-size: 1.1rem; }
        .badge { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 500; }
        .badge-active { background: #dcfce7; color: #16a34a; }
        .badge-inactive { background: #f1f5f9; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        th, td { padding: 0.75rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        th { background: #f8fafc; color: #64748b; font-weight: 600; }
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
        <div style="margin-bottom: 2rem;">
            <a href="users.php" style="color: #3b82f6; text-decoration: none;">&larr; Back to Patient Directory</a>
        </div>
        
        <h1 style="margin-bottom: 2rem;">Patient Profile: <?php echo htmlspecialchars($user['name']); ?></h1>

        <div class="details-grid">
            
            <div>
                <div class="details-card">
                    <h2>👤 Personal Information</h2>
                    <div class="info-row">
                        <span class="info-label">Full Name</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email Address</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Raw Password</span>
                        <span class="info-value" style="font-family: monospace; background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px;">
                            <?php echo htmlspecialchars($user['raw_password'] ?? 'Not available'); ?>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Joined Date</span>
                        <span class="info-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>

                <div class="details-card">
                    <h2>👨‍👩‍👧‍👦 Family Members</h2>
                    <?php if (count($family) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Relationship</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($family as $member): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($member['name']); ?></td>
                                        <td><?php echo htmlspecialchars($member['relationship']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color: #64748b;">No family members added yet.</p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div>
                <div class="details-card">
                    <h2>💊 Currently Scheduled Medicines</h2>
                    <?php if (count($activeMeds) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Medicine</th>
                                    <th>Dosage</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activeMeds as $med): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($med['name']); ?></td>
                                        <td><?php echo htmlspecialchars($med['dosage']); ?></td>
                                        <td><span class="badge badge-active">Active</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color: #64748b;">No active medicines currently.</p>
                    <?php endif; ?>
                </div>

                <div class="details-card">
                    <h2>📜 Medicine History</h2>
                    <?php if (count($inactiveMeds) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Medicine</th>
                                    <th>Date Taken</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inactiveMeds as $med): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($med['name']); ?></td>
                                        <td>
                                            <?php echo date('M j, Y', strtotime($med['start_date'])); ?> 
                                            <?php echo $med['end_date'] ? ' - ' . date('M j, Y', strtotime($med['end_date'])) : ''; ?>
                                        </td>
                                        <td><span class="badge badge-inactive">Past</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color: #64748b;">No medicine history available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </main>
</div>
</body>
</html>
