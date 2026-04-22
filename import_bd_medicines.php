<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "Starting import process...\n";

function importCSV($conn, $table, $filename, $mappingCallback) {
    if (!file_exists($filename)) {
        echo "Error: File $filename not found.\n";
        return;
    }

    echo "Importing $filename into $table...\n";
    
    $handle = fopen($filename, "r");
    if ($handle !== FALSE) {
        $header = fgetcsv($handle, 0, ",");
        
        $conn->beginTransaction();
        $count = 0;
        
        while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
            
            if (count($header) !== count($data)) {
                 if (count($data) > count($header)) {
                     $data = array_slice($data, 0, count($header));
                 } else {
                     $data = array_pad($data, count($header), '');
                 }
            }
            
            $row = array_combine($header, $data);
            if ($row === false) continue;

            try {
                $mappingCallback($conn, $row);
            } catch (Exception $e) {
                
                echo "Error inserting row: " . $e->getMessage() . "\n";
            }
            
            $count++;
            if ($count % 1000 == 0) {
                $conn->commit();
                echo "Inserted $count rows...\n";
                $conn->beginTransaction();
            }
        }
        
        $conn->commit();
        fclose($handle);
        echo "Finished importing $count rows from $filename.\n";
    }
}

$genericFile = 'archive/generic.csv';
$stmtGeneric = $conn->prepare("
    INSERT IGNORE INTO bd_generics (
        id, name, drug_class, indication, indication_description, 
        therapeutic_class_description, pharmacology_description, dosage_description, 
        administration_description, interaction_description, contraindications_description, 
        side_effects_description, pregnancy_and_lactation_description, precautions_description, 
        pediatric_usage_description, overdose_effects_description, duration_of_treatment_description, 
        reconstitution_description, storage_conditions_description
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

importCSV($conn, 'bd_generics', $genericFile, function($conn, $row) use ($stmtGeneric) {
    $stmtGeneric->execute([
        $row['generic id'],
        $row['generic name'],
        $row['drug class'] ?? '',
        $row['indication'] ?? '',
        $row['indication description'] ?? '',
        $row['therapeutic class description'] ?? '',
        $row['pharmacology description'] ?? '',
        $row['dosage description'] ?? '',
        $row['administration description'] ?? '',
        $row['interaction description'] ?? '',
        $row['contraindications description'] ?? '',
        $row['side effects description'] ?? '',
        $row['pregnancy and lactation description'] ?? '',
        $row['precautions description'] ?? '',
        $row['pediatric usage description'] ?? '',
        $row['overdose effects description'] ?? '',
        $row['duration of treatment description'] ?? '',
        $row['reconstitution description'] ?? '',
        $row['storage conditions description'] ?? ''
    ]);
});

$medicineFile = 'archive/medicine.csv';
$stmtMedicine = $conn->prepare("
    INSERT IGNORE INTO bd_medicines (
        id, brand_name, type, dosage_form, generic, strength, 
        manufacturer, package_container, package_size
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

importCSV($conn, 'bd_medicines', $medicineFile, function($conn, $row) use ($stmtMedicine) {
    if (empty($row['brand id'])) return; 
    $packageSize = isset($row['Package Size']) ? $row['Package Size'] : (isset($row['package size']) ? $row['package size'] : '');
    $stmtMedicine->execute([
        $row['brand id'],
        $row['brand name'],
        $row['type'] ?? '',
        $row['dosage form'] ?? '',
        $row['generic'] ?? '',
        $row['strength'] ?? '',
        $row['manufacturer'] ?? '',
        $row['package container'] ?? '',
        $packageSize
    ]);
});

echo "Import process completed successfully!\n";
?>
