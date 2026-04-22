<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
requireLogin();

$db = new Database();
$conn = $db->getConnection();
$userId = getUserId();

// Fetch dependents for the filter dropdown
$stmt = $conn->prepare("SELECT id, name FROM dependents WHERE user_id = ? ORDER BY name ASC");
$stmt->execute([$userId]);
$dependents = $stmt->fetchAll();

// Determine whose report we are generating
$targetDependentId = $_GET['dependent_id'] ?? '';
$targetName = "My"; // Default for primary user

$filterQuery = " AND m.dependent_id IS NULL ";
$params = [$userId];

if ($targetDependentId === 'all') {
    $filterQuery = ""; // No dependent filter, show all
    $targetName = "All Family";
} else if (is_numeric($targetDependentId)) {
    $filterQuery = " AND m.dependent_id = ? ";
    $params[] = $targetDependentId;
    
    // Find the dependent's name
    foreach ($dependents as $dep) {
        if ($dep['id'] == $targetDependentId) {
            $targetName = $dep['name'] . "'s";
            break;
        }
    }
}

// Fetch active medicines based on filter
$stmt = $conn->prepare("
    SELECT 
        m.*,
        bm.generic,
        d.name as dependent_name,
        GROUP_CONCAT(s.time_of_day ORDER BY s.time_of_day) as times
    FROM medicines m
    LEFT JOIN bd_medicines bm ON m.bd_medicine_id = bm.id
    LEFT JOIN dependents d ON m.dependent_id = d.id
    LEFT JOIN schedules s ON m.id = s.medicine_id
    WHERE m.user_id = ? AND m.active = 1 $filterQuery
    GROUP BY m.id
    ORDER BY d.name ASC, m.name ASC
");
$stmt->execute($params);
$activeMedicines = $stmt->fetchAll();

// Fetch last 7 days adherence stats (Simple Version)
$sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));
$stmt = $conn->prepare("
    SELECT 
        m.name,
        d.name as dependent_name,
        COUNT(r.id) as total_scheduled,
        SUM(CASE WHEN r.status = 'taken' THEN 1 ELSE 0 END) as total_taken
    FROM medicines m
    LEFT JOIN dependents d ON m.dependent_id = d.id
    LEFT JOIN schedules s ON m.id = s.medicine_id
    LEFT JOIN reminders r ON r.medicine_id = m.id AND r.schedule_id = s.id
    WHERE m.user_id = ? $filterQuery AND DATE(r.reminder_datetime) >= ?
    GROUP BY m.id
");
$statsParams = array_merge($params, [$sevenDaysAgo]);
$stmt->execute($statsParams);
$adherenceStats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medication Report - Medicature</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
    <style>
        .report-container {
            background: #fff;
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-md);
            margin-top: 1.5rem;
        }
        .report-header {
            text-align: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 1.5rem;
            margin-bottom: 2rem;
        }
        .report-section {
            margin-bottom: 2.5rem;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .report-table th {
            background-color: #f8fafc;
            font-weight: 600;
        }
        .filter-bar {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: var(--radius);
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .report-container, .report-container * {
                visibility: visible;
            }
            .report-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Medication Reports</h1>
                <p>Generate a summary for your doctor</p>
            </div>
            <button class="btn btn-primary no-print" onclick="window.print()">🖨️ Print Report</button>
        </div>

        <div class="filter-bar no-print">
            <strong>Filter By:</strong>
            <form method="GET" action="" style="display: flex; gap: 1rem;">
                <select name="dependent_id" onchange="this.form.submit()" style="padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="" <?php echo $targetDependentId === '' ? 'selected' : ''; ?>>Myself (Primary User)</option>
                    <option value="all" <?php echo $targetDependentId === 'all' ? 'selected' : ''; ?>>All Family Members</option>
                    <?php foreach ($dependents as $dep): ?>
                        <option value="<?php echo $dep['id']; ?>" <?php echo $targetDependentId == $dep['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dep['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="report-container">
            <div class="report-header">
                <h1 style="color: var(--primary-color); margin-bottom: 0.5rem;">Medicature Health Report</h1>
                <h2><?php echo htmlspecialchars($targetName); ?> Active Medications</h2>
                <p style="color: #666;">Generated on: <?php echo date('F j, Y, g:i a'); ?></p>
            </div>

            <div class="report-section">
                <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Current Prescriptions</h3>
                <?php if (empty($activeMedicines)): ?>
                    <p>No active medications found for this profile.</p>
                <?php else: ?>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <?php if ($targetDependentId === 'all'): ?><th>Patient</th><?php endif; ?>
                                <th>Medicine Name</th>
                                <th>Generic Name</th>
                                <th>Dosage</th>
                                <th>Schedule</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeMedicines as $med): ?>
                                <tr>
                                    <?php if ($targetDependentId === 'all'): ?>
                                        <td><?php echo htmlspecialchars($med['dependent_name'] ?? 'Primary User'); ?></td>
                                    <?php endif; ?>
                                    <td><strong><?php echo htmlspecialchars($med['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($med['generic'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($med['dosage']); ?></td>
                                    <td>
                                        <?php 
                                        if ($med['times']) {
                                            $times = explode(',', $med['times']);
                                            $formattedTimes = array_map(function($t) { return date('g:i A', strtotime($t)); }, $times);
                                            echo implode(', ', $formattedTimes);
                                        } else {
                                            echo 'As needed';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div class="report-section">
                <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">7-Day Adherence Summary</h3>
                <?php if (empty($adherenceStats)): ?>
                    <p>No adherence data recorded for the last 7 days.</p>
                <?php else: ?>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <?php if ($targetDependentId === 'all'): ?><th>Patient</th><?php endif; ?>
                                <th>Medicine</th>
                                <th>Scheduled Doses</th>
                                <th>Doses Taken</th>
                                <th>Adherence Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($adherenceStats as $stat): 
                                $rate = $stat['total_scheduled'] > 0 ? round(($stat['total_taken'] / $stat['total_scheduled']) * 100) : 0;
                            ?>
                                <tr>
                                    <?php if ($targetDependentId === 'all'): ?>
                                        <td><?php echo htmlspecialchars($stat['dependent_name'] ?? 'Primary User'); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($stat['name']); ?></td>
                                    <td><?php echo $stat['total_scheduled']; ?></td>
                                    <td><?php echo $stat['total_taken']; ?></td>
                                    <td>
                                        <strong style="color: <?php echo $rate >= 80 ? '#15803d' : ($rate >= 50 ? '#b45309' : '#b91c1c'); ?>">
                                            <?php echo $rate; ?>%
                                        </strong>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
</body>
</html>
