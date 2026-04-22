<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$error = '';
$success = '';

$db = new Database();
$conn = $db->getConnection();
$userId = getUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_dependent') {
    $name = trim($_POST['name'] ?? '');
    $relationship = trim($_POST['relationship'] ?? '');
    
    if (empty($name) || empty($relationship)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO dependents (user_id, name, relationship) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $name, $relationship]);
            $success = "Family member added successfully!";
        } catch (PDOException $e) {
            $error = "Failed to add family member.";
        }
    }
}

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM dependents WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['delete'], $userId]);
        $success = "Family member removed.";
    } catch (PDOException $e) {
        $error = "Failed to remove family member.";
    }
}

$stmt = $conn->prepare("
    SELECT d.*, COUNT(m.id) as med_count 
    FROM dependents d
    LEFT JOIN medicines m ON d.id = m.dependent_id AND m.active = 1
    WHERE d.user_id = ?
    GROUP BY d.id
    ORDER BY d.created_at DESC
");
$stmt->execute([$userId]);
$dependents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Management - Medicature</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
    <style>
        .dependent-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            transition: box-shadow 0.2s;
        }
        .dependent-card:hover {
            box-shadow: var(--shadow-md);
        }
        .dependent-info h3 {
            margin-bottom: 0.25rem;
            color: var(--primary-color);
        }
        .dependent-stats {
            margin-top: 0.5rem;
            font-size: 0.9em;
            color: #666;
        }
        .badge-pill {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .family-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            align-items: start;
            margin-top: 2rem;
        }
        .add-form-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
        }
        .add-form-card h3 {
            margin-bottom: 1rem;
            font-size: 1.1rem;
            color: var(--primary-color);
        }
        @media (max-width: 768px) {
            .family-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <div>
                <h1>👨‍👩‍👦 Family Profiles</h1>
                <p>Manage medications for your family members and dependents</p>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="family-grid">
            
            <div class="add-form-card">
                <h3>➕ Add Family Member</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_dependent">
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" required placeholder="e.g., Saleh Kabir">
                    </div>
                    <div class="form-group">
                        <label for="relationship">Relationship *</label>
                        <select id="relationship" name="relationship" required>
                            <option value="">Select Relationship</option>
                            <option value="Patient">Patient</option>
                            <option value="Parent">Parent</option>
                            <option value="Child">Child</option>
                            <option value="Spouse">Spouse</option>
                            <option value="Sibling">Sibling</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Add Profile</button>
                </form>
            </div>

            
            <div>
                <?php if (empty($dependents)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">👨‍👩‍👦</div>
                        <h3>No family members added</h3>
                        <p>Add dependents to manage their prescriptions and medication schedules</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($dependents as $dep): ?>
                        <div class="dependent-card">
                            <div class="dependent-info">
                                <h3><?php echo htmlspecialchars($dep['name']); ?></h3>
                                <span class="badge-pill"><?php echo htmlspecialchars($dep['relationship']); ?></span>
                                <div class="dependent-stats">
                                    💊 <?php echo $dep['med_count']; ?> Active Medication<?php echo $dep['med_count'] != 1 ? 's' : ''; ?>
                                </div>
                            </div>
                            <div class="action-buttons">
                                <a href="medicines.php?dependent_id=<?php echo $dep['id']; ?>" 
                                   class="btn btn-primary btn-sm">View Medicines</a>
                                <a href="?delete=<?php echo $dep['id']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Remove this dependent? Assigned medications will lose their association.')">Remove</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
</body>
</html>
