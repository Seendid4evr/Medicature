<?php
require_once 'auth.php';

$db = new Database();
$conn = $db->getConnection();
$userId = getUserId();
$email = getUserEmail();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "New password must be at least 8 characters long.";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = :id");
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        if ($user && password_verify($current_password, $user['password_hash'])) {
            // Update password
            $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $conn->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            if ($update_stmt->execute(['hash' => $new_hash, 'id' => $userId])) {
                $success = "Password updated successfully.";
            } else {
                $error = "Failed to update password. Please try again.";
            }
        } else {
            $error = "Incorrect current password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Medicature Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: grid; grid-template-columns: 220px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #1e293b; padding: 2rem 0; color: white; }
        .admin-sidebar .brand { padding: 0 1.5rem 2rem; font-size: 1.25rem; font-weight: bold; border-bottom: 1px solid #334155; }
        .admin-sidebar a { display: block; padding: 0.75rem 1.5rem; color: #94a3b8; text-decoration: none; transition: all 0.2s; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: #334155; color: white; }
        .admin-main { background: #f8fafc; padding: 2rem; }
        .settings-card { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 600px; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #334155; }
        .form-group input { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem; }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    </style>
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="brand">💊 Medicature Admin</div>
        <a href="index.php">🏠 Dashboard</a>
        <a href="broadcast.php">📧 Email Broadcast</a>
        <a href="users.php">👥 Registered Patients</a>
        <a href="settings.php" class="active">⚙️ Settings</a>
        <a href="../pages/dashboard.php">?? Back to App</a>
    </aside>
    <main class="admin-main">
        <h1 style="margin-bottom: 2rem;">⚙️ Settings</h1>
        
        <div class="settings-card">
            <h2 style="margin-top: 0; margin-bottom: 0.5rem;">Change Password</h2>
            <p style="color: #64748b; margin-bottom: 1.5rem;">Update the password for your admin account (<?php echo htmlspecialchars($email); ?>).</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="update_password" value="1">
                
                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="new_password">New Password (min 8 chars)</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                </div>

                <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
