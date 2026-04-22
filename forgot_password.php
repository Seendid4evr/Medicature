<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'config/database.php';

if (isLoggedIn()) {
    header('Location: pages/dashboard.php');
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $db = new Database();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            
            $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?")->execute([$user['id']]);

            
            $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)")
                 ->execute([$user['id'], $token, $expires]);

            
            $mailConfig = require 'config/mail.php';
            require_once 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
            require_once 'vendor/phpmailer/phpmailer/src/SMTP.php';
            require_once 'vendor/phpmailer/phpmailer/src/Exception.php';

            $resetLink = "http://localhost:8000/medicure/reset_password.php?token=" . $token;

            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $mailConfig['smtp_host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $mailConfig['smtp_user'];
                $mail->Password   = $mailConfig['smtp_pass'];
                $mail->SMTPSecure = $mailConfig['smtp_secure'];
                $mail->Port       = $mailConfig['smtp_port'];
                $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
                $mail->addAddress($email, $user['name']);
                $mail->isHTML(true);
                $mail->Subject = 'Medicature � Reset Your Password';
                $mail->Body = '
                    <div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;">
                        <div style="background:linear-gradient(135deg,#1e3a8a,#3b82f6);padding:2rem;border-radius:8px 8px 0 0;text-align:center;">
                            <h1 style="color:white;margin:0;">💊 Medicature</h1>
                        </div>
                        <div style="background:#f8fafc;padding:2rem;border-radius:0 0 8px 8px;">
                            <p>Hi ' . htmlspecialchars($user['name']) . ',</p>
                            <p>We received a request to reset your password. Click the button below to set a new password:</p>
                            <div style="text-align:center;margin:2rem 0;">
                                <a href="' . $resetLink . '" style="background:#2563eb;color:white;padding:0.75rem 2rem;border-radius:8px;text-decoration:none;font-weight:bold;">Reset My Password</a>
                            </div>
                            <p style="color:#64748b;font-size:0.9rem;">This link expires in <strong>1 hour</strong>. If you did not request this, ignore this email.</p>
                        </div>
                    </div>';
                $mail->AltBody = "Reset your Medicature password here: $resetLink (expires in 1 hour)";
                $mail->send();
                $success = "Password reset link sent to your email. Check your inbox!";
            } catch (Exception $e) {
                
                $success = "Reset link generated (SMTP not configured). Your reset link: <a href='$resetLink'>Click here to reset</a>";
            }
        } else {
            
            $success = "If that email is registered, you will receive a reset link shortly.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Medicature</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php include 'includes/pwa_head.php'; ?>
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="logo">
            <h1>💊 Medicature</h1>
            <p>Reset Your Password</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <p style="text-align:center;margin-top:1rem;">
                <a href="login.php" class="btn btn-secondary">⬅ Back to Login</a>
            </p>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <p style="color:#64748b;margin-bottom:1.5rem;">Enter your registered email address and we'll send you a link to reset your password.</p>

            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required
                           placeholder="your.email@example.com" autofocus>
                </div>
                <button type="submit" class="btn btn-primary btn-block">📧 Send Reset Link</button>
            </form>

            <p class="auth-footer" style="text-align:center;margin-top:1rem;">
                <a href="login.php">⬅ Back to Login</a>
            </p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
