<?php
require_once 'auth.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Name, Email, and Password are required.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters.";
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, raw_password, phone, is_admin) VALUES (?, ?, ?, ?, ?, 0)");
            
            try {
                $stmt->execute([$name, $email, $passwordHash, $password, $phone ?: null]);
                $success = "Patient successfully added!";
                // Clear post variables to avoid resubmission
                $_POST = array();
            } catch (PDOException $e) {
                $error = "Failed to add patient.";
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
    <title>Add Patient - Medicature Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: grid; grid-template-columns: 220px 1fr; min-height: 100vh; }
        .admin-sidebar { background: #1e293b; padding: 2rem 0; color: white; }
        .admin-sidebar .brand { padding: 0 1.5rem 2rem; font-size: 1.25rem; font-weight: bold; border-bottom: 1px solid #334155; }
        .admin-sidebar a { display: block; padding: 0.75rem 1.5rem; color: #94a3b8; text-decoration: none; transition: all 0.2s; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background: #334155; color: white; }
        .admin-main { background: #f8fafc; padding: 2rem; }
        .form-card { background: white; border-radius: 12px; padding: 2.5rem; max-width: 600px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
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
        
        <div class="form-card">
            <h1 style="margin-bottom: 1.5rem;">Add New Patient</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success" style="margin-bottom: 1rem;"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password * (min. 8 characters)</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>

                <button type="submit" class="btn btn-primary" style="background: #10b981; margin-top: 1rem;">
                    ➕ Add Patient
                </button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
