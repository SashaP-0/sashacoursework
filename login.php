<?php
session_start();
require_once 'database.php';
require_once 'functions.php';

$error_message = '';

// Handle Login
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = 'Please fill in all fields.';
    } else {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT * FROM tblusers WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['userID'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ((int)$user['role'] === 1) {
                    header('Location: admin.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $error_message = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error_message = 'Login error. Please try again.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - The Village Grocers</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Caveat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .auth-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--accent-color) 0%, #E6D7C3 100%);
            padding: 2rem 0;
            position: relative;
        }
        
        .auth-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain3" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23e7d5c7" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="%23e7d5c7" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain3)"/></svg>');
            pointer-events: none;
        }
        
        .auth-tabs {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 2;
        }
        
        .auth-tab {
            padding: 1rem 2rem;
            background-color: var(--white);
            color: var(--text-dark);
            text-decoration: none;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            margin: 0 0.5rem;
            transition: var(--transition);
            font-family: var(--handwriting-font);
            font-size: 1.1rem;
            border: 2px solid transparent;
            border-bottom: none;
        }
        
        .auth-tab.active {
            background-color: var(--primary-color);
            color: var(--white);
            border-color: var(--primary-color);
        }
        
        .auth-tab:hover:not(.active) {
            background-color: var(--accent-color);
            border-color: var(--border-color);
        }
        
        .auth-form {
            background-color: var(--white);
            border-radius: var(--border-radius);
            padding: 3rem;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: var(--shadow);
            position: relative;
            z-index: 2;
            border: 2px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 500;
            font-family: var(--handwriting-font);
        }
        
        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
            background-color: var(--accent-color);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(25, 132, 56, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .auth-submit {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            margin-top: 1rem;
        }
        
        .auth-message {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            text-align: center;
            font-family: var(--handwriting-font);
        }
        
        .auth-message.error {
            background-color: #FFE6E6;
            color: #CC0000;
            border: 2px solid #FFB3B3;
        }
        
        .auth-message.success {
            background-color: #E6FFE6;
            color: #006600;
            border: 2px solid #B3FFB3;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 2rem;
            color: var(--text-light);
            font-family: var(--handwriting-font);
        }
        
        .auth-footer a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .form-section {
            display: none;
        }
        
        .form-section.active {
            display: block;
        }
        
        @media (max-width: 768px) {
            .auth-form {
                margin: 0 1rem;
                padding: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .auth-tabs {
                flex-direction: column;
                align-items: center;
            }
            
            .auth-tab {
                margin: 0.25rem 0;
                border-radius: var(--border-radius);
                border: 2px solid transparent;
            }
        }
    </style>
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
                    <li><a href="products.php" class="nav-link">Products</a></li>
                    <li><a href="about.php" class="nav-link">About</a></li>
                    <li><a href="delivery.php" class="nav-link">Delivery</a></li>
                    <li><a href="login.php" class="nav-link active">Login</a></li>
                </ul>
                <div class="nav-cart">
                    <a href="cart.php" class="cart-icon">
                        <span class="cart-count">0</span>
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

    <!-- Auth Container -->
    <div class="auth-container">
        <div class="container">
            <div class="auth-tabs" style="justify-content:center;">
                <a href="register.php" class="auth-tab">Register</a>
            </div>

            <!-- Messages -->
            <?php if ($error_message): ?>
                <div class="auth-message error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            
            

            <!-- Login Form -->
            <div class="auth-form" id="loginForm">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="form-group">
                        <label for="login-email">Email Address</label>
                        <input type="email" id="login-email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary auth-submit">Login</button>
                </form>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.querySelector('.hamburger').addEventListener('click', function() {
            document.querySelector('.nav-menu').classList.toggle('active');
        });
    </script>
</body>
</html>
