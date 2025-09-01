<?php
// Essential helper functions for The Village Grocers website

/**
 * Get featured products for homepage
 */
function getFeaturedProducts() {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT i.*, c.categoryname 
        FROM tblitems i 
        JOIN tblcategories c ON i.categoryID = c.categoryID 
        WHERE i.is_active = 1 
        ORDER BY i.created_date DESC 
        LIMIT 6
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get cart item count for current user
 */
function getCartItemCount() {
    if (!isset($_SESSION['user_id'])) {
        return 0;
    }
    
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM tblbasket 
        WHERE userID = ? AND orderID IS NULL
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();
    return $result['count'] ?? 0;
}

/**
 * Get all product categories
 */
function getCategories() {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT * FROM tblcategories 
        ORDER BY displayorder, categoryname
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get products by category
 */
function getProductsByCategory($categoryID) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT i.*, c.categoryname 
        FROM tblitems i 
        JOIN tblcategories c ON i.categoryID = c.categoryID 
        WHERE i.categoryID = ? AND i.is_active = 1
        ORDER BY i.itemname
    ");
    $stmt->execute([$categoryID]);
    return $stmt->fetchAll();
}

/**
 * Get delivery areas
 */
function getDeliveryAreas() {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT * FROM tblareas 
        ORDER BY deliveryday, areaname
    ");
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Check if postcode is in delivery area
 */
function checkDeliveryArea($postcode) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT da.*, p.postcode 
        FROM tblareas da 
        JOIN tblpostcodes p ON da.deliveryarea = p.deliveryarea 
        WHERE p.postcode = ?
    ");
    $stmt->execute([$postcode]);
    return $stmt->fetch();
}

/**
 * Get user orders
 */
function getUserOrders($userID) {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT o.*, da.areaname 
        FROM tblorders o 
        JOIN tblareas da ON o.deliveryarea = da.deliveryarea 
        WHERE o.userID = ? 
        ORDER BY o.orderdate DESC
    ");
    $stmt->execute([$userID]);
    return $stmt->fetchAll();
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generate random string for tokens
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 1;
}

/**
 * Redirect with message
 */
function redirectWithMessage($url, $message, $type = 'info') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: $url");
    exit();
}

/**
 * Display message if exists
 */
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $message = $_SESSION['message'];
        unset($_SESSION['message'], $_SESSION['message_type']);
        
        return "<div class='alert alert-$type'>$message</div>";
    }
    return '';
}
?>
