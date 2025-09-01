<?php
session_start();
require_once 'database.php';
require_once 'functions.php';

header('Content-Type: application/json');

if (!isset($_GET['q']) || empty($_GET['q'])) {
    echo json_encode([]);
    exit();
}

$query = trim($_GET['q']);
$pdo = getDB();

try {
    $stmt = $pdo->prepare("
        SELECT i.*, c.categoryname 
        FROM tblitems i 
        JOIN tblcategories c ON i.categoryID = c.categoryID 
        WHERE i.is_active = 1 
        AND (i.itemname LIKE ? OR i.description LIKE ?)
        ORDER BY i.itemname 
        LIMIT 10
    ");
    
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();
    
    echo json_encode($results);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>
