<?php
require_once '../includes/session.php';
require_once '../config/database.php';
requireLogin();

header('Content-Type: application/json');

$id = $_GET['id'] ?? '';

if (empty($id) || !is_numeric($id)) {
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    $stmt = $conn->prepare("
        SELECT *
        FROM bd_medicines 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $medicine = $stmt->fetch();
    
    if (!$medicine) {
        echo json_encode(['error' => 'Medicine not found']);
        exit;
    }

    if (!empty($medicine['generic'])) {
        $stmtGen = $conn->prepare("
            SELECT *
            FROM bd_generics
            WHERE name = ?
        ");
        $stmtGen->execute([$medicine['generic']]);
        $genericInfo = $stmtGen->fetch();
        if ($genericInfo) {
            $medicine['generic_details'] = $genericInfo;
        }
    }
    
    echo json_encode($medicine);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
