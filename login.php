<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    
    if (getUserIsAdmin()) {
        header('Location: admin/index.php');
    } else {
        header('Location: pages/dashboard.php');
    }
    exit();
}

$error = '';
$mode = $_GET['mode'] ?? 'customer'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $postMode = $_POST['mode'] ?? 'customer';

    $result = loginUser($email, $password);
    if ($result['success']) {
        
        if ($postMode === 'admin' && !getUserIsAdmin()) {
            
            session_unset();
            session_destroy();
            session_start();
            $error = "Access denied. You do not have administrator privileges.";
        } else {
            if (getUserIsAdmin()) {
                header('Location: admin/index.php');
            } else {
                header('Location: pages/dashboard.php');
            }
            exit();
        }
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Medicature</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php include 'includes/pwa_head.php'; ?>
    <style>
        .login-wrapper {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1.1fr;
            background: #f0f4ff;
        }
        .login-brand {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 3rem;
            text-align: center;
        }
        .login-brand .big-icon { font-size: 5rem; margin-bottom: 1rem; }
        .login-brand h1 { font-size: 2.5rem; font-weight: 800; margin: 0 0 0.5rem; }
        .login-brand p { font-size: 1.1rem; opacity: 0.85; max-width: 320px; line-height: 1.6; }
        .login-features { margin-top: 2rem; text-align: left; width: 100%; max-width: 320px; }
        .login-features li { margin: 0.6rem 0; opacity: 0.9; list-style: none; padding-left: 0; }
        .login-panel {
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
            max-width: 450px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }
        .role-tabs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 1.5rem;
        }
        .role-tab {
            padding: 0.75rem;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s;
            color: #64748b;
            border: none;
            background: transparent;
        }
        .role-tab.active-customer {
            background: #2563eb;
            color: white;
            box-shadow: 0 2px 8px rgba(37,99,235,0.3);
        }
        .role-tab.active-admin {
            background: #f59e0b;
            color: white;
            box-shadow: 0 2px 8px rgba(245,158,11,0.3);
        }
        .admin-badge {
            background: linear-gradient(135deg,#f59e0b,#d97706);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }
        .forgot-link {
            text-align: right;
            margin-top: -0.5rem;
            margin-bottom: 1rem;
        }
        .forgot-link a { color: #2563eb; font-size: 0.9rem; text-decoration: none; }
        .forgot-link a:hover { text-decoration: underline; }
        @media (max-width: 768px) {
            .login-wrapper { grid-template-columns: 1fr; }
            .login-brand { display: none; }
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    
    <div class="login-brand">
        <div class="big-icon">💊</div>
        <h1>Medicature</h1>
        <p>Bangladesh's most advanced digital health platform. Track medicines, get AI-powered insights, and order refills — all in one place.</p>
        <ul class="login-features">
            <li>✅ 21,000+ Bangladesh drug database</li>
            <li>✅ AI symptom triage & analysis</li>
            <li>✅ Smart medication alarms</li>
            <li>✅ E-pharmacy refill ordering</li>
            <li>✅ Family health management</li>
        </ul>
    </div>

    
    <div class="login-panel">
        <div class="auth-card">
            <h2 style="margin: 0 0 1.5rem; color: #0f172a;">Welcome Back</h2>

            
            <div class="role-tabs">
                <button type="button" class="role-tab <?php echo $mode !== 'admin' ? 'active-customer' : ''; ?>"
                        onclick="switchMode('customer')">🏥 Patient Login</button>
                <button type="button" class="role-tab <?php echo $mode === 'admin' ? 'active-admin' : ''; ?>"
                        onclick="switchMode('admin')">⚙️ Admin Login</button>
            </div>

            <div id="admin-badge" style="<?php echo $mode === 'admin' ? '' : 'display:none;'; ?>">
                <div class="admin-badge">⚠️ Admin access only. Actions are logged.</div>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['registered'])): ?>
                <div class="alert alert-success">Registration successful! Please login.</div>
            <?php endif; ?>
            <?php if (isset($_GET['reset'])): ?>
                <div class="alert alert-success">Password reset successful! Please login with your new password.</div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <input type="hidden" name="mode" id="modeInput" value="<?php echo htmlspecialchars($mode); ?>">

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required
                           placeholder="your.email@example.com" autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password">
                </div>

                <div class="forgot-link">
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                    🏥 Patient Login
                </button>
            </form>

            <p class="auth-footer" style="margin-top: 1.5rem; text-align: center;">
                Don't have an account? <a href="register.php">Register for free</a>
            </p>
        </div>
    </div>
</div>

<script>
function switchMode(mode) {
    document.getElementById('modeInput').value = mode;
    const tabs = document.querySelectorAll('.role-tab');
    const badge = document.getElementById('admin-badge');
    const btn = document.getElementById('submitBtn');

    if (mode === 'admin') {
        tabs[0].className = 'role-tab';
        tabs[1].className = 'role-tab active-admin';
        badge.style.display = 'block';
        btn.textContent = '⚙️ Admin Login';
        btn.style.background = 'linear-gradient(135deg,#f59e0b,#d97706)';
    } else {
        tabs[0].className = 'role-tab active-customer';
        tabs[1].className = 'role-tab';
        badge.style.display = 'none';
        btn.textContent = '🏥 Patient Login';
        btn.style.background = '';
    }
}
// Init on page load
switchMode('<?php echo $mode === 'admin' ? 'admin' : 'customer'; ?>');
</script>
</body>
</html>
