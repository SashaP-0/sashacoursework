<?php
session_start();
require_once 'database.php';
require_once 'functions.php';

$categories = getCategories();
$selectedCategory = $_GET['category'] ?? '';

// Get products based on selected category
if ($selectedCategory) {
    $products = getProductsByCategory($selectedCategory);
} else {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT i.*, c.categoryname 
        FROM tblitems i 
        JOIN tblcategories c ON i.categoryID = c.categoryID 
        WHERE i.is_active = 1 
        ORDER BY c.displayorder, i.itemname
    ");
    $stmt->execute();
    $products = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - The Village Grocers</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Caveat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <div class="logo-illustration">
                        🌾
                    </div>
                    <div>
                        <h1>The Village Grocers</h1>
                        <p>Fresh • Local • Sustainable</p>
                    </div>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <li><a href="products.php" class="nav-link active">Products</a></li>
                    <li><a href="about.php" class="nav-link">About</a></li>
                    <li><a href="delivery.php" class="nav-link">Delivery</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="account.php" class="nav-link">My Account</a></li>
                        <li><a href="logout.php" class="nav-link">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-link">Login</a></li>
                    <?php endif; ?>
                </ul>
                <div class="nav-cart">
                    <a href="cart.php" class="cart-icon">
                        <span class="cart-count"><?php echo getCartItemCount(); ?></span>
                        🛒
                    </a>
                </div>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Our Products</h1>
            <p>Discover our selection of fresh, locally sourced goods</p>
        </div>
    </section>

    <!-- Category Filter -->
    <section class="category-filter">
        <div class="container">
            <div class="filter-buttons">
                <a href="products.php" class="filter-btn <?php echo !$selectedCategory ? 'active' : ''; ?>">
                    All Products
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="products.php?category=<?php echo $category['categoryID']; ?>" 
                       class="filter-btn <?php echo $selectedCategory == $category['categoryID'] ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($category['categoryname']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="products-section">
        <div class="container">
            <?php if (empty($products)): ?>
                <div class="no-products">
                    <h3>No products found</h3>
                    <p>We're currently updating our product selection. Please check back soon!</p>
                </div>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['itemname']); ?>">
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['itemname']); ?></h3>
                                <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                                <div class="product-meta">
                                    <span class="product-category"><?php echo htmlspecialchars($product['categoryname']); ?></span>
                                    <?php if ($product['dietryrequirements']): ?>
                                        <span class="dietary-badge"><?php echo htmlspecialchars($product['dietryrequirements']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-price">£<?php echo number_format($product['itemprice'], 2); ?></div>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button class="btn btn-add-to-cart" onclick="addToCart(<?php echo $product['itemID']; ?>)">
                                        Add to Cart
                                    </button>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-outline">Login to Order</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>The Village Grocers</h3>
                    <p>Bringing fresh, local produce to your community since 2020.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="homepage.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="delivery.php">Delivery Areas</a></li>
                        <li><a href="about.php">About Us</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p>📍 Market Stall, Village Square</p>
                    <p>📞 01234 567890</p>
                    <p>✉️ info@villagegrocers.com</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 The Village Grocers. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="main.js"></script>
</body>
</html>
