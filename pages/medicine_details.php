<?php
require_once '../includes/session.php';
require_once '../config/database.php';
requireLogin();

$id = $_GET['id'] ?? '';

if (empty($id) || !is_numeric($id)) {
    header("Location: medicines.php");
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Fetch the user's medicine to ensure they have access to it, 
// though viewing general drug info could be open, let's just fetch the generic info.
$stmt = $conn->prepare("
    SELECT * 
    FROM bd_medicines 
    WHERE id = ?
");
$stmt->execute([$id]);
$medicine = $stmt->fetch();

if (!$medicine) {
    header("Location: medicines.php");
    exit;
}

$generic = null;
if (!empty($medicine['generic'])) {
    $stmtGen = $conn->prepare("
        SELECT *
        FROM bd_generics
        WHERE name = ?
    ");
    $stmtGen->execute([$medicine['generic']]);
    $generic = $stmtGen->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($medicine['brand_name']); ?> - Drug Information</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
    <style>
        .info-card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }
        .info-section {
            margin-bottom: 1.5rem;
        }
        .info-section h3 {
            color: var(--primary-color);
            border-bottom: 2px solid #eee;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .info-section p, .info-section div {
            line-height: 1.6;
            color: #444;
        }
        .badge-pill {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 9999px;
            font-size: 0.875rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="section-header" style="justify-content: flex-start; gap: 1rem;">
            <a href="medicines.php" class="btn btn-secondary">&larr; Back</a>
            <div>
                <h1><?php echo htmlspecialchars($medicine['brand_name']); ?></h1>
                <?php if ($medicine['strength'] || $medicine['dosage_form']): ?>
                    <p style="font-size: 1.1rem; color: #666; margin-top: 0.25rem;">
                        <?php echo htmlspecialchars($medicine['strength'] . ' ' . $medicine['dosage_form']); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <div class="info-card">
            <div class="info-section">
                <h3>Basic Information</h3>
                <p><strong>Generic Name:</strong> <?php echo htmlspecialchars($medicine['generic'] ?? 'N/A'); ?></p>
                <p><strong>Manufacturer:</strong> <?php echo htmlspecialchars($medicine['manufacturer'] ?? 'N/A'); ?></p>
                <?php if (!empty($medicine['type'])): ?>
                    <p><strong>Type:</strong> <span class="badge-pill"><?php echo htmlspecialchars($medicine['type']); ?></span></p>
                <?php endif; ?>
            </div>

            <?php if ($generic): ?>
                <?php if (!empty($generic['drug_class'])): ?>
                    <div class="info-section">
                        <h3>Drug Class</h3>
                        <p><?php echo htmlspecialchars($generic['drug_class']); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($generic['indication']) || !empty($generic['indication_description'])): ?>
                    <div class="info-section">
                        <h3>Indications (Uses)</h3>
                        <?php if (!empty($generic['indication'])): ?>
                            <p><strong>Primary Uses:</strong> <?php echo htmlspecialchars($generic['indication']); ?></p>
                        <?php endif; ?>
                        <div><?php echo $generic['indication_description'] ?? ''; ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($generic['dosage_description'])): ?>
                    <div class="info-section">
                        <h3>Dosage & Administration</h3>
                        <div><?php echo $generic['dosage_description']; ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($generic['side_effects_description'])): ?>
                    <div class="info-section">
                        <h3>Side Effects</h3>
                        <div><?php echo $generic['side_effects_description']; ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($generic['precautions_description'])): ?>
                    <div class="info-section" style="border-left: 4px solid #f59e0b; padding-left: 1rem;">
                        <h3 style="color: #d97706;">Warnings & Precautions</h3>
                        <div><?php echo $generic['precautions_description']; ?></div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($generic['pregnancy_and_lactation_description'])): ?>
                    <div class="info-section">
                        <h3>Pregnancy & Lactation</h3>
                        <div><?php echo $generic['pregnancy_and_lactation_description']; ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($generic['interaction_description'])): ?>
                    <div class="info-section">
                        <h3>Drug Interactions</h3>
                        <div><?php echo $generic['interaction_description']; ?></div>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert">Detailed pharmacological information is not available for this medicine.</div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
</body>
</html>
