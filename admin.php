<?php
session_start();
require_once 'database.php';
require_once 'functions.php';

// Protect admin dashboard
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || (int)$_SESSION['role'] !== 1) {
    header('Location: index.php');
    exit();
}

$pdo = getDB();

// Fetch high-level stats
$stats = [
    'products' => 0,
    'active_products' => 0,
    'categories' => 0,
    'users' => 0,
    'orders' => 0,
    'pending_orders' => 0,
];

try {
    $stats['products'] = (int)$pdo->query("SELECT COUNT(*) FROM tblitems")->fetchColumn();
    $stats['active_products'] = (int)$pdo->query("SELECT COUNT(*) FROM tblitems WHERE is_active = 1")->fetchColumn();
    $stats['categories'] = (int)$pdo->query("SELECT COUNT(*) FROM tblcategories")->fetchColumn();
    $stats['users'] = (int)$pdo->query("SELECT COUNT(*) FROM tblusers")->fetchColumn();
    $stats['orders'] = (int)$pdo->query("SELECT COUNT(*) FROM tblorders")->fetchColumn();
    $stats['pending_orders'] = (int)$pdo->query("SELECT COUNT(*) FROM tblorders WHERE status = 0")->fetchColumn();
} catch (Throwable $e) {
    // Ignore metric errors to avoid blocking page load
}

// Latest 10 orders
$recentOrders = [];
try {
    $stmt = $pdo->prepare("SELECT o.orderID, o.orderdate, o.deliverydate, o.status, o.total, u.username, da.areaname
                           FROM tblorders o
                           JOIN tblusers u ON o.userID = u.userID
                           LEFT JOIN tblareas da ON o.deliveryarea = da.deliveryarea
                           ORDER BY o.orderdate DESC, o.orderID DESC
                           LIMIT 10");
    $stmt->execute();
    $recentOrders = $stmt->fetchAll();
} catch (Throwable $e) {
    $recentOrders = [];
}

// Today production list (items grouped for today's deliveries)
$todayItems = [];
try {
    $stmt = $pdo->prepare("SELECT i.itemname, SUM(b.numitems) AS qty
                           FROM tblbasket b
                           JOIN tblorders o ON b.orderID = o.orderID
                           JOIN tblitems i ON b.itemID = i.itemID
                           WHERE o.deliverydate = CURDATE()
                           GROUP BY i.itemID, i.itemname
                           ORDER BY i.itemname");
    $stmt->execute();
    $todayItems = $stmt->fetchAll();
} catch (Throwable $e) {
    $todayItems = [];
}

function renderStatusBadge($status)
{
    $labels = [
        0 => 'Pending',
        1 => 'Confirmed',
        2 => 'In Progress',
        3 => 'Ready',
        4 => 'Delivered',
        5 => 'Cancelled',
    ];
    $classes = [
        0 => 'badge-pending',
        1 => 'badge-confirmed',
        2 => 'badge-progress',
        3 => 'badge-ready',
        4 => 'badge-delivered',
        5 => 'badge-cancelled',
    ];
    $label = $labels[$status] ?? 'Unknown';
    $class = $classes[$status] ?? 'badge-pending';
    return "<span class=\"badge $class\">$label</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - The Village Grocers</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Caveat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-header { background-color: var(--accent-color); border-bottom: 3px solid var(--border-color); }
        .admin-title { display: flex; justify-content: space-between; align-items: center; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
        .stat-card { background: var(--white); border: 2px solid var(--border-color); border-radius: var(--border-radius); padding: 1.25rem; box-shadow: var(--shadow); }
        .stat-card h3 { margin: 0 0 0.5rem 0; color: var(--primary-color); font-family: var(--handwriting-font); }
        .stat-value { font-size: 2rem; font-weight: 700; color: var(--text-dark); }
        .admin-section { background: var(--white); border: 2px solid var(--border-color); border-radius: var(--border-radius); padding: 1.5rem; box-shadow: var(--shadow); }
        .admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 0.75rem; border-bottom: 1px solid var(--border-color); font-family: var(--handwriting-font); }
        th { color: var(--primary-color); }
        .badge { padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.85rem; color: #fff; }
        .badge-pending { background: #f39c12; }
        .badge-confirmed { background: #2980b9; }
        .badge-progress { background: #8e44ad; }
        .badge-ready { background: #16a085; }
        .badge-delivered { background: #2ecc71; }
        .badge-cancelled { background: #e74c3c; }
        .admin-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .admin-actions a { border: 2px solid var(--primary-color); padding: 0.5rem 0.75rem; border-radius: var(--border-radius); text-decoration: none; color: var(--primary-color); font-family: var(--handwriting-font); }
        .admin-actions a:hover { background: var(--primary-color); color: #fff; }
        @media (max-width: 900px) { .admin-grid { grid-template-columns: 1fr; } }
    </style>
    </head>
<body>
    <header class="header admin-header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <div class="logo-illustration">🌾</div>
                    <div>
                        <h1>The Village Grocers</h1>
                        <p>Admin Dashboard</p>
                    </div>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link">Site</a></li>
                    <li><a href="products.php" class="nav-link">Products</a></li>
                    <li><a href="logout.php" class="nav-link">Logout</a></li>
                </ul>
                <div class="nav-cart"></div>
                <div class="hamburger"><span></span><span></span><span></span></div>
            </div>
        </nav>
    </header>

    <main class="container" style="padding: 2rem 0;">
        <div class="admin-title" style="margin-bottom: 1rem;">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></h2>
            <div class="admin-actions">
                <a href="install.php" title="Re-run installer (dev only)">Run Installer</a>
                <a href="products.php">View Shop</a>
            </div>
        </div>

        <section class="stats-grid" style="margin-bottom: 1.5rem;">
            <div class="stat-card"><h3>Total Products</h3><div class="stat-value"><?php echo $stats['products']; ?></div></div>
            <div class="stat-card"><h3>Active Products</h3><div class="stat-value"><?php echo $stats['active_products']; ?></div></div>
            <div class="stat-card"><h3>Categories</h3><div class="stat-value"><?php echo $stats['categories']; ?></div></div>
            <div class="stat-card"><h3>Users</h3><div class="stat-value"><?php echo $stats['users']; ?></div></div>
            <div class="stat-card"><h3>Orders</h3><div class="stat-value"><?php echo $stats['orders']; ?></div></div>
            <div class="stat-card"><h3>Pending Orders</h3><div class="stat-value"><?php echo $stats['pending_orders']; ?></div></div>
        </section>

        <section class="admin-grid">
            <div class="admin-section">
                <h3>Latest Orders</h3>
                <?php if (empty($recentOrders)): ?>
                    <p style="color: var(--text-light);">No recent orders found.</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Delivery</th>
                            <th>Customer</th>
                            <th>Area</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $o): ?>
                        <tr>
                            <td><?php echo (int)$o['orderID']; ?></td>
                            <td><?php echo htmlspecialchars($o['orderdate']); ?></td>
                            <td><?php echo htmlspecialchars($o['deliverydate']); ?></td>
                            <td><?php echo htmlspecialchars($o['username']); ?></td>
                            <td><?php echo htmlspecialchars($o['areaname'] ?? ''); ?></td>
                            <td>£<?php echo number_format((float)$o['total'], 2); ?></td>
                            <td><?php echo renderStatusBadge((int)$o['status']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <div class="admin-section">
                <h3>Today's Production List</h3>
                <?php if (empty($todayItems)): ?>
                    <p style="color: var(--text-light);">No items scheduled for today.</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todayItems as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['itemname']); ?></td>
                            <td><?php echo (int)$row['qty']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <script src="main.js"></script>
</body>
</html>


