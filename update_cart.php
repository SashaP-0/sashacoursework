<?php
session_start();
require_once 'database.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['itemID']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$itemID = (int)$input['itemID'];
$quantity = (int)$input['quantity'];

if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1']);
    exit();
}

// For now, just return success since we don't have a full cart system implemented
// This would typically update the cart in the database or session
echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
?>
