<?php
session_start();
require_once 'database.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Village Grocers - Fresh Local Produce</title>
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
                    <li><a href="index.php" class="nav-link active">Home</a></li>
                    <li><a href="products.php" class="nav-link">Products</a></li>
                    <li><a href="about.php" class="nav-link">About</a></li>
                    <li><a href="delivery.php" class="nav-link">Delivery</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="account.php" class="nav-link">My Account</a></li>
                        <li><a href="logout.php" class="nav-link">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php" class="nav-link">Login</a></li>
                        <li><a href="register.php" class="nav-link">Register</a></li>
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Organic, Local<br>Food and Groceries</h1>
            <p>Supporting local farmers and bringing you the finest quality bread, dairy, and seasonal goods from our village to yours.</p>
            <div class="hero-buttons">
                <a href="products.php" class="btn btn-primary">Shop Now!</a>
                <a href="delivery.php" class="btn btn-secondary">Check Delivery Areas</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="hero-illustration">
                <div class="bread-icon">🍞</div>
                <div class="milk-icon">🥛</div>
                <div class="honey-icon">🍯</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <h2>Why Choose The Village Grocers?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🌾</div>
                    <h3>Locally Sourced</h3>
                    <p>All our products come from local farms and producers within your community.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🚚</div>
                    <h3>Fresh Delivery</h3>
                    <p>Freshly baked goods delivered to your door or available for market pickup.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">♻️</div>
                    <h3>Sustainable</h3>
                    <p>Eco-friendly packaging and sustainable farming practices.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Community Focused</h3>
                    <p>Supporting local businesses and building stronger communities.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2>Featured Products</h2>
            <div class="products-grid">
                <?php
                $featuredProducts = getFeaturedProducts();
                foreach ($featuredProducts as $product):
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo $product['image'] ?: 'default.jpg'; ?>" alt="<?php echo htmlspecialchars($product['itemname']); ?>">
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
                        <button class="btn btn-add-to-cart" onclick="addToCart(<?php echo $product['itemID']; ?>)">
                            Add to Cart
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="view-all-products">
                <a href="products.php" class="btn btn-outline">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Membership Section -->
    <section class="membership">
        <div class="container">
            <div class="membership-content">
                <div class="membership-text">
                    <h2>Join Our Membership Program</h2>
                    <p>Get weekly deliveries of your favorite items like fresh bread and milk. Set up recurring orders and never run out of essentials again.</p>
                    <ul class="membership-benefits">
                        <li>✓ Weekly recurring orders</li>
                        <li>✓ Priority delivery slots</li>
                        <li>✓ Member-only discounts</li>
                        <li>✓ Easy order management</li>
                    </ul>
                    <a href="membership.php" class="btn btn-primary">Learn More</a>
                </div>
                <div class="membership-image">
                    <div class="membership-illustration">
                        <div class="calendar-icon">📅</div>
                        <div class="repeat-icon">🔄</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>The Village Grocers</h3>
                    <p>Bringing fresh, local produce to your community since 2020.</p>
                    <div class="social-links">
                        <a href="#" class="social-link">📘 Facebook</a>
                        <a href="#" class="social-link">📷 Instagram</a>
                        <a href="#" class="social-link">🐦 Twitter</a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="delivery.php">Delivery Areas</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <p>📍 Market Stall, Village Square</p>
                    <p>📞 01234 567890</p>
                    <p>✉️ info@villagegrocers.com</p>
                </div>
                <div class="footer-section">
                    <h4>Delivery Days</h4>
                    <p>🕐 Wednesday & Friday</p>
                    <p>🕐 Market Pickup: Saturdays</p>
                    <p>⏰ Orders close 2AM day of delivery</p>
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
