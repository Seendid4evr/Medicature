<?php
require_once 'includes/session.php';
require_once 'config/database.php';

if (isLoggedIn()) { header('Location: pages/dashboard.php'); exit(); }

$token = $_GET['token'] ?? '';
$error = '';
$valid = false;
$userId = null;

$db = new Database();
$conn = $db->getConnection();

if ($token) {
    $stmt = $conn->prepare("SELECT user_id FROM password_reset_tokens WHERE token = ? AND expires_at > NOW() AND used = 0");
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    if ($row) {
        $valid = true;
        $userId = $row['user_id'];
    } else {
        $error = "This reset link is invalid or has expired. Please request a new one.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid) {
    $newPass  = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (strlen($newPass) < 8) {
        $error = "Password must be at least 8 characters.";
    } elseif ($newPass !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($newPass, PASSWORD_BCRYPT);
        $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $userId]);
        $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?")->execute([$token]);
        header('Location: login.php?reset=1');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Medicature</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php include 'includes/pwa_head.php'; ?>
    <style>
        .strength-bar { height: 5px; border-radius: 3px; margin-top: 5px; background: #e2e8f0; }
        .strength-fill { height: 100%; border-radius: 3px; transition: all 0.3s; }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="logo">
            <h1>💊 Medicature</h1>
            <p>Create New Password</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php if (!$valid): ?>
                <p style="text-align:center;"><a href="forgot_password.php" class="btn btn-primary">Request New Link</a></p>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($valid): ?>
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Min. 8 characters" oninput="checkStrength(this.value)">
                    <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                    <small id="strengthLabel" style="color:#64748b;"></small>
                </div>
                <div class="form-group">
                    <label for="confirm">Confirm New Password</label>
                    <input type="password" id="confirm" name="confirm" required placeholder="Repeat your password">
                </div>
                <button type="submit" class="btn btn-primary btn-block">🔐 Reset Password</button>
            </form>
        <?php endif; ?>

        <p class="auth-footer" style="text-align:center;margin-top:1rem;">
            <a href="login.php">⬅ Back to Login</a>
        </p>
    </div>
</div>
<script>
function checkStrength(p) {
    let score = 0;
    if (p.length >= 8) score++;
    if (/[A-Z]/.test(p)) score++;
    if (/[0-9]/.test(p)) score++;
    if (/[^A-Za-z0-9]/.test(p)) score++;
    const colors = ['#ef4444','#f59e0b','#3b82f6','#16a34a'];
    const labels = ['Weak','Fair','Good','Strong'];
    const w = [25, 50, 75, 100];
    document.getElementById('strengthFill').style.width = (score > 0 ? w[score-1] : 0) + '%';
    document.getElementById('strengthFill').style.background = score > 0 ? colors[score-1] : '';
    document.getElementById('strengthLabel').textContent = score > 0 ? labels[score-1] : '';
}
</script>
</body>
</html>
