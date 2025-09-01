<?php
session_start();
require_once 'database.php';
require_once 'functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to add items to cart']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['itemID']) || !isset($input['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$itemID = (int)$input['itemID'];
$quantity = (int)$input['quantity'];
$userID = $_SESSION['user_id'];

try {
    $pdo = getDB();
    
    // Check if item exists and is active
    $stmt = $pdo->prepare("SELECT * FROM tblitems WHERE itemID = ? AND is_active = 1");
    $stmt->execute([$itemID]);
    $item = $stmt->fetch();
    
    if (!$item) {
        echo json_encode(['success' => false, 'message' => 'Item not found or unavailable']);
        exit;
    }
    
    // Check if item is already in cart
    $stmt = $pdo->prepare("SELECT * FROM tblbasket WHERE userID = ? AND itemID = ? AND orderID IS NULL");
    $stmt->execute([$userID, $itemID]);
    $existingItem = $stmt->fetch();
    
    if ($existingItem) {
        // Update quantity
        $newQuantity = $existingItem['numitems'] + $quantity;
        $stmt = $pdo->prepare("UPDATE tblbasket SET numitems = ? WHERE basketID = ?");
        $stmt->execute([$newQuantity, $existingItem['basketID']]);
    } else {
        // Add new item to cart
        $stmt = $pdo->prepare("INSERT INTO tblbasket (userID, itemID, numitems, deliveryarea) VALUES (?, ?, ?, 1)");
        $stmt->execute([$userID, $itemID, $quantity]);
    }
    
    // Get updated cart count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM tblbasket WHERE userID = ? AND orderID IS NULL");
    $stmt->execute([$userID]);
    $cartCount = $stmt->fetch()['count'];
    
    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully',
        'cartCount' => $cartCount
    ]);
    
} catch (Exception $e) {
    error_log("Add to cart error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error adding item to cart']);
}
?>
