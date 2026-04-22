<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    header(getUserIsAdmin() ? 'Location: admin/index.php' : 'Location: pages/dashboard.php');
    exit();
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name            = trim($_POST['name'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $phone           = trim($_POST['phone'] ?? '');
    $password        = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    
    $adminEmails = ['seendidpc@gmail.com', 'salehkabir236@gmail.com'];
    if (in_array(strtolower($email), $adminEmails)) {
        $error = 'This email address is reserved. Please use a different email.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $result = registerUser($name, $email, $password, $phone);
        if ($result['success']) {
            header('Location: login.php?registered=1');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Patient Account - Medicature</title>
    <meta name="description" content="Register for a free Medicature patient account. Manage medications, set reminders, and track your health in Bangladesh's most advanced digital health platform.">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php include 'includes/pwa_head.php'; ?>
    <style>
        .register-wrapper {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            background: #f0f4ff;
        }
        .register-brand {
            background: linear-gradient(160deg, #059669, #065f46);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 3rem;
            text-align: center;
        }
        .register-brand .big-icon { font-size: 5rem; margin-bottom: 1rem; }
        .register-brand h1 { font-size: 2rem; font-weight: 800; margin: 0 0 0.5rem; }
        .register-brand p  { opacity: 0.88; max-width: 320px; line-height: 1.6; }
        .register-brand ul { list-style: none; padding: 0; margin-top: 1.5rem; text-align: left; width: 100%; max-width: 280px; }
        .register-brand ul li { margin: 0.5rem 0; opacity: 0.9; }
        .register-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .auth-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }
        .patient-badge {
            background: linear-gradient(135deg, #059669, #047857);
            color: white;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            text-align: center;
            margin-bottom: 1.2rem;
            font-size: 0.9rem;
        }
        .strength-bar-wrap {
            height: 5px;
            background: #e2e8f0;
            border-radius: 3px;
            margin-top: 5px;
        }
        .strength-bar {
            height: 100%;
            border-radius: 3px;
            width: 0;
            transition: all 0.3s;
        }
        @media (max-width: 768px) {
            .register-wrapper { grid-template-columns: 1fr; }
            .register-brand { display: none; }
        }
    </style>
</head>
<body>
<div class="register-wrapper">
    
    <div class="register-brand">
        <div class="big-icon">🏥</div>
        <h1>Welcome to Medicature</h1>
        <p>Create your free patient account and start managing your health smarter with Bangladesh's most advanced digital health platform.</p>
        <ul>
            <li>✅ Free forever for patients</li>
            <li>✅ 21,000+ Bangladesh drug database</li>
            <li>✅ Smart medication reminders</li>
            <li>✅ Family health management</li>
            <li>✅ AI-powered symptom triage</li>
        </ul>
    </div>

    
    <div class="register-panel">
        <div class="auth-card">
            <h2 style="margin: 0 0 0.5rem; color: #0f172a;">Create Patient Account</h2>
            <p style="color: #64748b; margin-bottom: 1.2rem; font-size: 0.9rem;">For medical professionals or admins, please contact us directly.</p>

            <div class="patient-badge">🏥 Patient Registration — Free Access</div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form" id="registerForm">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required
                           placeholder="Your full name" autofocus
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required
                           placeholder="your.email@example.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number <span style="color:#94a3b8;">(optional)</span></label>
                    <input type="tel" id="phone" name="phone"
                           placeholder="+880 1X00-000000"
                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password * <span style="color:#94a3b8; font-size:0.85rem;">(min. 8 characters)</span></label>
                    <input type="password" id="password" name="password" required
                           placeholder="Create a strong password" minlength="8"
                           oninput="checkStrength(this.value)">
                    <div class="strength-bar-wrap">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <small id="strengthLabel" style="color:#64748b;"></small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           placeholder="Repeat your password" minlength="8">
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="registerBtn"
                        style="background: linear-gradient(135deg,#059669,#047857);">
                    🏥 Create My Patient Account
                </button>
            </form>

            <p class="auth-footer" style="text-align:center; margin-top:1.5rem;">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>
</div>
<script>
function checkStrength(p) {
    let s = 0;
    if (p.length >= 8) s++;
    if (/[A-Z]/.test(p)) s++;
    if (/[0-9]/.test(p)) s++;
    if (/[^A-Za-z0-9]/.test(p)) s++;
    const c = ['#ef4444','#f59e0b','#3b82f6','#059669'];
    const l = ['Weak','Fair','Good','Strong'];
    const w = [25, 50, 75, 100];
    const bar = document.getElementById('strengthBar');
    const lbl = document.getElementById('strengthLabel');
    bar.style.width   = (s > 0 ? w[s-1] : 0) + '%';
    bar.style.background = s > 0 ? c[s-1] : '';
    lbl.textContent   = s > 0 ? l[s-1] : '';
}

// Prevent double-submit
document.getElementById('registerForm').addEventListener('submit', function() {
    const btn = document.getElementById('registerBtn');
    btn.disabled = true;
    btn.textContent = '⏳ Creating account...';
});
</script>
</body>
</html>
