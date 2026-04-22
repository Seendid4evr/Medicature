<?php
require_once 'auth.php';

$db = new Database();
$conn = $db->getConnection();
$success = '';
$error = '';

// Get user count for display
$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $messageBody = trim($_POST['message'] ?? '');
    $targetGroup = $_POST['target'] ?? 'all';

    if (empty($subject) || empty($messageBody)) {
        $error = "Subject and message body are required.";
    } else {
        // Build recipient list
        if ($targetGroup === 'all') {
            $stmt = $conn->query("SELECT name, email FROM users WHERE email IS NOT NULL AND email != ''");
        } elseif ($targetGroup === 'active') {
            // Users with active medicines (engaged users)
            $stmt = $conn->query("SELECT DISTINCT u.name, u.email FROM users u JOIN medicines m ON u.id = m.user_id WHERE m.active = 1 AND u.email IS NOT NULL");
        } else {
            // Specific user
            $stmt = $conn->prepare("SELECT name, email FROM users WHERE email = ?");
            $stmt->execute([$targetGroup]);
        }
        $recipients = $stmt->fetchAll();

        if (empty($recipients)) {
            $error = "No recipients found for the selected group.";
        } else {
            // Load mailer config
            $mailConfig = require '../config/mail.php';
            
            // Load PHPMailer
            require_once '../vendor/phpmailer/phpmailer/src/PHPMailer.php';
            require_once '../vendor/phpmailer/phpmailer/src/SMTP.php';
            require_once '../vendor/phpmailer/phpmailer/src/Exception.php';

            $sent = 0;
            $failed = 0;

            foreach ($recipients as $recipient) {
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
                    $mail->addAddress($recipient['email'], $recipient['name']);

                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body    = '
                        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                            <div style="background: linear-gradient(135deg, #1e3a8a, #3b82f6); padding: 2rem; text-align: center; border-radius: 8px 8px 0 0;">
                                <h1 style="color: white; margin: 0;">💊 Medicature</h1>
                            </div>
                            <div style="background: #f8fafc; padding: 2rem; border-radius: 0 0 8px 8px;">
                                <p>Dear ' . htmlspecialchars($recipient['name']) . ',</p>
                                <div style="line-height: 1.6;">' . nl2br(htmlspecialchars($messageBody)) . '</div>
                                <hr style="margin: 2rem 0; border: 1px solid #e2e8f0;">
                                <p style="color: #64748b; font-size: 0.85rem;">
                                    You are receiving this because you are a registered Medicature user.<br>
                                    Visit us at <a href="http://localhost/medicature">Medicature</a>
                                </p>
                            </div>
                        </div>';
                    $mail->AltBody = strip_tags($messageBody);
                    $mail->send();
                    $sent++;
                } catch (Exception $e) {
                    $failed++;
                }
            }

            if ($sent > 0) {
                $success = "? Successfully sent to $sent user(s)." . ($failed > 0 ? " $failed failed (check SMTP config)." : "");
            } else {
                $error = "Failed to send emails. Please check your SMTP configuration in config/mail.php. Error: Check SMTP credentials.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Broadcast - Medicature Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: grid; grid-template-columns: 220px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #1e293b; padding: 2rem 0; color: white; }
        .admin-sidebar .brand { padding: 0 1.5rem 2rem; font-size: 1.25rem; font-weight: bold; border-bottom: 1px solid #334155; }
        .admin-sidebar a { display: block; padding: 0.75rem 1.5rem; color: #94a3b8; text-decoration: none; transition: all 0.2s; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: #334155; color: white; }
        .admin-main { background: #f8fafc; padding: 2rem; }
        .compose-card { background: white; border-radius: 12px; padding: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); max-width: 800px; }
        textarea { min-height: 200px; resize: vertical; }
        .preview-note { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 1rem; margin-top: 1rem; font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="brand">💊 Medicature Admin</div>
        <a href="index.php">🏠 Dashboard</a>
        <a href="broadcast.php" class="active">📧 Email Broadcast</a>
        <a href="users.php">👥 Users</a>
        <a href="../pages/dashboard.php">?? Back to App</a>
    </aside>
    <main class="admin-main">
        <h1 style="margin-bottom: 0.5rem;">📧 Email Broadcast</h1>
        <p style="color: #64748b; margin-bottom: 2rem;">Send a message directly to your <?php echo $totalUsers; ?> registered users.</p>
        
        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom: 1.5rem;"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: 1.5rem;"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="compose-card">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="target">Send To</label>
                    <select name="target" id="target">
                        <?php if (isset($_GET['email'])): ?>
                            <option value="<?php echo htmlspecialchars($_GET['email']); ?>" selected>Specific User: <?php echo htmlspecialchars($_GET['email']); ?></option>
                        <?php endif; ?>
                        <option value="all">All Registered Users (<?php echo $totalUsers; ?> users)</option>
                        <option value="active">Active Users (have medicines added)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subject">Email Subject *</label>
                    <input type="text" id="subject" name="subject" required placeholder="e.g., Important Health Update from Medicature" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="message">Message Body *</label>
                    <textarea id="message" name="message" required placeholder="Write your message here..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                </div>

                <div class="preview-note">
                    ?? <strong>Tip:</strong> Each email will be personalized with the user's name. The email will automatically include a professional Medicature header and footer. Make sure your SMTP credentials are configured in <code>config/mail.php</code> before sending.
                </div>

                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Send this email to all selected users?')">
                        ?? Send Broadcast Email
                    </button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
