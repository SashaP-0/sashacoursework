<?php
session_start();
require_once 'database.php';
require_once 'functions.php';

$error_message = '';
$success_message = '';

// Handle Registration
if (isset($_POST['action']) && $_POST['action'] === 'register') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $forename = trim($_POST['forename'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $addressline = trim($_POST['addressline'] ?? '');
    $postcode = trim($_POST['postcode'] ?? '');
    $phonenumber = trim($_POST['phonenumber'] ?? '');

    if ($username === '' || $email === '' || $password === '' || $confirm_password === '' ||
        $forename === '' || $surname === '' || $addressline === '' || $postcode === '' || $phonenumber === '') {
        $error_message = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } else {
        try {
            $pdo = getDB();

            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM tblusers WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $error_message = 'Username or email already exists.';
            } else {
                // Check if postcode is in delivery area
                $stmt = $pdo->prepare("SELECT deliveryarea FROM tblpostcodes WHERE postcode = ?");
                $stmt->execute([$postcode]);
                $delivery_area = $stmt->fetchColumn();

                if (!$delivery_area) {
                    $error_message = 'Sorry, we don\'t deliver to this postcode yet.';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $pdo->prepare("INSERT INTO tblusers (username, email, password, forename, surname, addressline, postcode, deliveryarea, phonenumber, role, dob, created_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, '2000-01-01', NOW())");

                    if ($stmt->execute([$username, $email, $hashed_password, $forename, $surname, $addressline, $postcode, $delivery_area, $phonenumber])) {
                        $success_message = 'Registration successful! You can now login.';
                        $_POST = array();
                    } else {
                        $error_message = 'Registration failed. Please try again.';
                    }
                }
            }
        } catch (PDOException $e) {
            $error_message = 'Registration error. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - The Village Grocers</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Caveat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .auth-container { min-height: 100vh; background: var(--accent-color); padding: 2rem 0; position: relative; }
        .auth-form { background-color: var(--white); border-radius: var(--border-radius); padding: 3rem; max-width: 600px; margin: 0 auto; box-shadow: var(--shadow); border: 2px solid var(--border-color); }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: var(--text-dark); font-weight: 500; font-family: var(--handwriting-font); }
        .form-group input { width: 100%; padding: 1rem; border: 2px solid var(--border-color); border-radius: var(--border-radius); font-size: 1rem; background: var(--accent-color); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .auth-message { padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.25rem; text-align: center; font-family: var(--handwriting-font); }
        .auth-message.error { background-color: #FFE6E6; color: #CC0000; border: 2px solid #FFB3B3; }
        .auth-message.success { background-color: #E6FFE6; color: #006600; border: 2px solid #B3FFB3; }
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } .auth-form { margin: 0 1rem; padding: 2rem; } }
    </style>
    </head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <div class="logo-illustration">🌾</div>
                    <div><h1>The Village Grocers</h1><p>Fresh • Local • Sustainable</p></div>
                </div>
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link">Home</a></li>
                    <li><a href="products.php" class="nav-link">Products</a></li>
                    <li><a href="about.php" class="nav-link">About</a></li>
                    <li><a href="delivery.php" class="nav-link">Delivery</a></li>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                </ul>
                <div class="nav-cart"><a href="cart.php" class="cart-icon"><span class="cart-count">0</span>🛒</a></div>
                <div class="hamburger"><span></span><span></span><span></span></div>
            </div>
        </nav>
    </header>

    <div class="auth-container">
        <div class="container">
            <h2 style="text-align:center; margin-bottom: 1rem;">Create an Account</h2>
            <?php if ($error_message): ?><div class="auth-message error"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>
            <?php if ($success_message): ?><div class="auth-message success"><?php echo htmlspecialchars($success_message); ?> <a href="login.php">Login</a></div><?php endif; ?>
            <div class="auth-form">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="register">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-forename">Forename</label>
                            <input type="text" id="reg-forename" name="forename" required value="<?php echo isset($_POST['forename']) ? htmlspecialchars($_POST['forename']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="reg-surname">Surname</label>
                            <input type="text" id="reg-surname" name="surname" required value="<?php echo isset($_POST['surname']) ? htmlspecialchars($_POST['surname']) : ''; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg-username">Username</label>
                        <input type="text" id="reg-username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="reg-email">Email Address</label>
                        <input type="email" id="reg-email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-password">Password</label>
                            <input type="password" id="reg-password" name="password" required minlength="8" placeholder="Minimum 8 characters">
                        </div>
                        <div class="form-group">
                            <label for="reg-confirm-password">Confirm Password</label>
                            <input type="password" id="reg-confirm-password" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reg-address">Address Line</label>
                        <input type="text" id="reg-address" name="addressline" required value="<?php echo isset($_POST['addressline']) ? htmlspecialchars($_POST['addressline']) : ''; ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reg-postcode">Postcode</label>
                            <input type="text" id="reg-postcode" name="postcode" required value="<?php echo isset($_POST['postcode']) ? htmlspecialchars($_POST['postcode']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="reg-phone">Phone Number</label>
                            <input type="tel" id="reg-phone" name="phonenumber" required value="<?php echo isset($_POST['phonenumber']) ? htmlspecialchars($_POST['phonenumber']) : ''; ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Create Account</button>
                </form>
                <div class="auth-footer" style="text-align:center; margin-top: 1rem;">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.querySelector('.hamburger')?.addEventListener('click', function() {
            document.querySelector('.nav-menu')?.classList.toggle('active');
        });
    </script>
</body>
</html>


