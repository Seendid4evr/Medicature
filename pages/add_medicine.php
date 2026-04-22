<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../includes/functions.php';
requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $dosage = sanitizeInput($_POST['dosage'] ?? '');
    $notes = sanitizeInput($_POST['notes'] ?? '');
    $startDate = $_POST['start_date'] ?? '';
    $endDate = $_POST['end_date'] ?? null;
    $times = $_POST['times'] ?? [];
    $bdMedicineId = !empty($_POST['bd_medicine_id']) ? intval($_POST['bd_medicine_id']) : null;
    $dependentId = !empty($_POST['dependent_id']) ? intval($_POST['dependent_id']) : null;
    $userId = getUserId();
    
    if (empty($name) || empty($dosage) || empty($startDate) || empty($times)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            // Start transaction
            $conn->beginTransaction();
            
            // Handle prescription upload
            $prescriptionFile = null;
            if (!empty($_FILES['prescription']['name'])) {
                $upload = uploadPrescription($_FILES['prescription'], $userId);
                if ($upload['success']) {
                    $prescriptionFile = $upload['filename'];
                } else {
                    $error = $upload['message'];
                }
            }
            
            if (!$error) {
                // Insert medicine
                $stmt = $conn->prepare("
                    INSERT INTO medicines (user_id, name, dosage, notes, start_date, end_date, prescription_file, bd_medicine_id, dependent_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $name, $dosage, $notes, $startDate, $endDate ?: null, $prescriptionFile, $bdMedicineId, $dependentId]);
                $medicineId = $conn->lastInsertId();
                
                // Insert schedules
                $stmt = $conn->prepare("INSERT INTO schedules (medicine_id, time_of_day) VALUES (?, ?)");
                foreach ($times as $time) {
                    if (!empty($time)) {
                        $stmt->execute([$medicineId, $time]);
                    }
                }
                
                $conn->commit();
                $success = 'Medicine added successfully!';
                
                // Redirect after 2 seconds
                header("refresh:2;url=medicines.php");
            } else {
                $conn->rollBack();
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log("Add medicine error: " . $e->getMessage());
            $error = 'Failed to add medicine. Please try again.';
        }
    }
}

// Fetch user's dependents
$dependents = [];
try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT id, name, relationship FROM dependents WHERE user_id = ? ORDER BY name ASC");
    $stmt->execute([getUserId()]);
    $dependents = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignore error for dropdown population
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Medicine - Medicature</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php include '../includes/pwa_head.php'; ?>
    <style>
        .autocomplete-container {
            position: relative;
        }
        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .autocomplete-results.show {
            display: block;
        }
        .autocomplete-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .autocomplete-item:last-child {
            border-bottom: none;
        }
        .autocomplete-item:hover {
            background-color: #f5f5f5;
        }
        .autocomplete-item strong {
            display: block;
            color: #333;
        }
        .autocomplete-item small {
            color: #666;
            display: block;
            margin-top: 2px;
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Add New Medicine</h1>
            <p>Enter your medication details</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?> Redirecting...</div>
        <?php endif; ?>
        
        <div style="background: var(--card-bg); border-radius: var(--radius); padding: 2rem; box-shadow: var(--shadow-md); max-width: 800px;">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" id="bd_medicine_id" name="bd_medicine_id" value="">
                
                <div class="form-group">
                    <label for="dependent_id">Who is this for?</label>
                    <select id="dependent_id" name="dependent_id">
                        <option value="">Myself (Primary User)</option>
                        <?php foreach ($dependents as $dep): ?>
                            <option value="<?php echo $dep['id']; ?>">
                                <?php echo htmlspecialchars($dep['name'] . ' (' . $dep['relationship'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group autocomplete-container">
                    <label for="name">Medicine Name *</label>
                    <input type="text" id="name" name="name" required placeholder="e.g., Napa, Seclo, Aspirin" autocomplete="off">
                    <div id="autocomplete-results" class="autocomplete-results"></div>
                </div>
                
                <div class="form-group">
                    <label for="dosage">Dosage *</label>
                    <input type="text" id="dosage" name="dosage" required placeholder="e.g., 500mg, 2 tablets">
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes / Instructions</label>
                    <textarea id="notes" name="notes" placeholder="e.g., Take with food, Avoid alcohol"></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="start_date">Start Date *</label>
                        <input type="date" id="start_date" name="start_date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">End Date (optional)</label>
                        <input type="date" id="end_date" name="end_date">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Schedule Times * <small>(when to take medicine)</small></label>
                    <div id="times-container">
                        <div class="time-input" style="margin-bottom: 0.5rem;">
                            <input type="time" name="times[]" required>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="addTimeInput()" style="margin-top: 0.5rem;">+ Add Another Time</button>
                </div>
                
                <div class="form-group">
                    <label for="prescription">Upload Prescription (optional)</label>
                    <input type="file" id="prescription" name="prescription" accept="image/*,.pdf" onchange="previewFile(this)">
                    <small>Accepted formats: JPG, PNG, PDF (max 5MB)</small>
                    <div id="file-preview" style="margin-top: 1rem;"></div>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">Save Medicine</button>
                    <a href="medicines.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script src="../assets/js/alarm.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nameInput = document.getElementById('name');
            const dosageInput = document.getElementById('dosage');
            const idInput = document.getElementById('bd_medicine_id');
            const resultsContainer = document.getElementById('autocomplete-results');
            let timeout = null;

            nameInput.addEventListener('input', function() {
                clearTimeout(timeout);
                const query = this.value.trim();
                
                if (query.length < 2) {
                    resultsContainer.classList.remove('show');
                    idInput.value = ''; // Clear ID if user starts typing something else
                    return;
                }

                timeout = setTimeout(() => {
                    fetch(`../api/search_medicines.php?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.length === 0) {
                                resultsContainer.classList.remove('show');
                                return;
                            }

                            resultsContainer.innerHTML = '';
                            data.forEach(med => {
                                const div = document.createElement('div');
                                div.className = 'autocomplete-item';
                                div.innerHTML = `
                                    <strong>${med.brand_name} ${med.strength ? '(' + med.strength + ')' : ''}</strong>
                                    <small>${med.generic} | ${med.dosage_form} | ${med.manufacturer}</small>
                                `;
                                div.addEventListener('click', () => {
                                    nameInput.value = med.brand_name;
                                    dosageInput.value = med.strength ? med.strength + ' ' + med.dosage_form : med.dosage_form;
                                    idInput.value = med.id;
                                    resultsContainer.classList.remove('show');
                                });
                                resultsContainer.appendChild(div);
                            });
                            resultsContainer.classList.add('show');
                        })
                        .catch(err => {
                            console.error('Error fetching medicines:', err);
                        });
                }, 300); // 300ms debounce
            });

            // Close results when clicking outside
            document.addEventListener('click', function(e) {
                if (!nameInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                    resultsContainer.classList.remove('show');
                }
            });
        });
    </script>
</body>
</html>
