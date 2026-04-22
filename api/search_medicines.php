<?php
require_once '../includes/session.php';
require_once '../config/database.php';
requireLogin();

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    
    $stmt = $conn->prepare("
        SELECT id, brand_name, generic, dosage_form, strength, manufacturer
        FROM bd_medicines 
        WHERE brand_name LIKE :q1 
           OR generic LIKE :q2
        ORDER BY 
            CASE WHEN brand_name LIKE :exact THEN 1 ELSE 2 END,
            brand_name ASC
        LIMIT 20
    ");
    
    $searchTerm = '%' . $query . '%';
    $exactTerm = $query . '%';
    $stmt->bindParam(':q1', $searchTerm);
    $stmt->bindParam(':q2', $searchTerm);
    $stmt->bindParam(':exact', $exactTerm);
    $stmt->execute();
    
    $results = $stmt->fetchAll();
    
    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
