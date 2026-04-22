<?php
require_once '../includes/session.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$userId = getUserId();
$user = getUser($conn, $userId);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Profile Update
    if (isset($_POST['update_profile'])) {
        $name = sanitizeInput($_POST['name'] ?? '');
        if (empty($name)) {
            $error = 'Name cannot be empty.';
        } else {
            try {
                $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
                $stmt->execute([$name, $userId]);
                $_SESSION['user_name'] = $name;
                $success = 'Profile updated successfully!';
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
            } catch (PDOException $e) {
                error_log("Profile update error: " . $e->getMessage());
                $error = 'Failed to update profile.';
            }
        }
    }
    
    // Handle Password Change
    if (isset($_POST['change_password'])) {
        $currentPwd = $_POST['current_password'] ?? '';
        $newPwd = $_POST['new_password'] ?? '';
        $confirmPwd = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPwd) || empty($newPwd) || empty($confirmPwd)) {
            $error = 'All password fields are required.';
        } elseif ($newPwd !== $confirmPwd) {
            $error = 'New passwords do not match.';
        } elseif (strlen($newPwd) < 8) {
            $error = 'New password must be at least 8 characters.';
        } else {
            // Verify current password
            if (password_verify($currentPwd, $user['password_hash'])) {
                $newHash = password_hash($newPwd, PASSWORD_BCRYPT, ['cost' => 10]);
                try {
                    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$newHash, $userId]);
                    $success = 'Password changed successfully!';
                } catch (PDOException $e) {
                    $error = 'Failed to change password.';
                }
            } else {
                $error = 'Incorrect current password.';
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
    <title>Profile - Medicature</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>User Profile</h1>
            <p>Manage your account settings</p>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
            
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <!-- Profile Form -->
                <div style="background: var(--card-bg); border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow-md);">
                    <h3>Personal Information</h3>
                    <form method="POST" action="">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background-color: #f3f4f6; cursor: not-allowed;">
                            <small>Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Info</button>
                    </form>
                </div>

                <!-- Password Change Form -->
                <div style="background: var(--card-bg); border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow-md);">
                    <h3>Change Password</h3>
                    <form method="POST" action="" data-validate>
                        <input type="hidden" name="change_password" value="1">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required minlength="8">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                        </div>
                        
                        <button type="submit" class="btn btn-warning">Update Password</button>
                    </form>
                </div>
            </div>
            
            <!-- Notification Settings -->
            <div style="background: var(--card-bg); border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow-md); height: fit-content;">
                <h3>Notifications & Sound</h3>
                <p>Manage your medication reminders.</p>
                
                <div class="form-group" id="notification-settings-area">
                    <div id="notification-status" style="display: none; padding: 1rem; background: #d1fae5; color: #065f46; border-radius: var(--radius); margin-bottom: 1rem; align-items: center; gap: 0.5rem;">
                        <span>🔔 Notifications are active for this device.</span>
                    </div>

                    <button id="enable-notifications-btn" class="btn btn-secondary btn-block">
                        Enable Browser Notifications
                    </button>
                    <p id="notification-help" style="margin-top: 0.5rem; color: var(--text-secondary); font-size: 0.9em;">
                        Click to allow medication reminders on this device.
                    </p>
                </div>
            </div>

            <!-- Contact for Deletion -->
            <div style="background: var(--card-bg); border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow-md);">
                <h3>Account Management</h3>
                <p>For account deletion and other queries, please contact us at:</p>
                <ul style="list-style: none; padding: 0; margin-top: 1rem;">
                    <li style="margin-bottom: 0.5rem;">📧 <a href="mailto:x@gmail.com">x@gmail.com</a></li>
                    <li>📱 017xxxxxxxx</li>
                </ul>
            </div>


        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
    <script>
        // Profile specific scripts
        // Notification status check
        function updateNotificationUI() {
            const btn = document.getElementById('enable-notifications-btn');
            const statusMsg = document.getElementById('notification-status');
            const helpText = document.getElementById('notification-help');
            
            if (!('Notification' in window)) {
                btn.disabled = true;
                btn.textContent = "Notifications not supported";
                return;
            }

            if (Notification.permission === 'granted') {
                btn.style.display = 'block'; // Keep button visible
                btn.textContent = "Notifications Enabled";
                btn.disabled = true; // Optional: disable since already enabled
                helpText.style.display = 'none';
                statusMsg.style.display = 'flex';
            } else if (Notification.permission === 'denied') {
                btn.disabled = true;
                btn.textContent = "Notifications blocked";
                helpText.textContent = "Please enable notifications in your browser settings to receive reminders.";
            } else {
                // Default
                btn.style.display = 'block';
                statusMsg.style.display = 'none';
            }
        }

        // Run on load
        updateNotificationUI();

        document.getElementById('enable-notifications-btn').addEventListener('click', () => {
             if ('Notification' in window) {
                Notification.requestPermission().then(permission => {
                    updateNotificationUI();
                    if (permission === 'granted') {
                        // Optional: Play sound to confirm and ensure audio context is allowed
                        const audio = new Audio('../assets/sound/alarm.mp3');
                        audio.play().catch(e => console.log('Autoplay prevented until interaction'));
                        showBrowserNotification('Notifications Enabled', {
                            body: 'You will now receive medication reminders.',
                            requireInteraction: false
                        });
                    }
                });
            }
        });
    </script>
</body>
</html>
