<?php
// login.php
require_once 'config/database.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('login.php');
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, full_name, user_type, status FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($user['status'] !== 'active') {
                $error = 'Your account has been deactivated. Please contact support.';
            } elseif (password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                
                // Update last login
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                
                // Log activity
                log_activity($user['id'], 'User Login', 'User logged in successfully');
                
                // Redirect based on user type
                if ($user['user_type'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('../public/index.php');
                }
                exit;
            } else {
                $_SESSION['login_error'] = 'Invalid username or password';
                header("Location: login.php");
                exit;
            }
        } else {
            $_SESSION['login_error'] = 'Invalid username or password';
            header("Location: login.php");
            exit;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Suva's Place and Resort Antipolo</title>

    <link rel="stylesheet" href="../public/assets/css/navbar.css">
    <link rel="stylesheet" href="../public/assets/css/shared.css">
    <link rel="stylesheet" href="../public/assets/css/login.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../public/assets/images/suva's_place_logo.ico">
    <script defer src="../public/assets/js/navbar.js"></script>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="../public/assets/images/suva's_logo.png">
            </div>

            <ul class="nav-links" id="nav-links">
                <li><a href="../public/index.php">Home</a></li>
                <li><a href="../public/about_page.php">About us</a></li>
                <li><a href="../public/gallery_page.php">Gallery</a></li>
                <li><a href="../public/contacts_page.php">Contacts</a></li>
                <li><a href="login.php" class="login-button"><i class="fas fa-user"></i> Login</a></li>
            </ul>

            <div class="burger" id="burger">
                  <i class="fas fa-bars" id="open-icon"></i>
                  <i class="fas fa-arrow-left" id="close-icon"></i>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="forms-container">
            <div class="form-wrapper">

                <!--------------------LOGIN FORM----------------------->
                
                <form action="login.php" method="POST" class="form-content login-form" id="loginForm">
                    <h2 class="form-title">Welcome Back</h2>
                    <p class="form-subtitle">Sign in to continue</p>
                    
                    <?php if(isset($_SESSION['login_error'])): ?>
                        <div class="alert alert-error">
                            <?php 
                                echo $_SESSION['login_error']; 
                                unset($_SESSION['login_error']);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php 
                                echo $_SESSION['success']; 
                                unset($_SESSION['success']);
                            ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="input-group">
                        <label for="login_email">Email Address</label>
                        <input type="email" id="login_email" name="email" required placeholder="Enter your email">
                    </div>
                    
                    <div class="input-group">
                        <label for="login_password">Password</label>
                        <input type="password" id="login_password" name="password" required placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Sign In</button>
                    
                    <p class="switch-form">
                        Don't have an account? 
                        <a href="#" onclick="switchToRegister(); return false;">Register Now</a>
                    </p>
                </form>
                
                <!-----------------------REGISTRATION FORM----------------------->

                <form action="register.php" method="POST" class="form-content register-form" id="registerForm" style="display: none;">
                    <h2 class="form-title">Join Suva's Resort</h2>
                    <p class="form-subtitle">Create your account</p>
                    
                    <?php if(isset($_SESSION['register_error'])): ?>
                        <div class="alert alert-error">
                            <?php 
                                echo $_SESSION['register_error']; 
                                unset($_SESSION['register_error']);
                            ?>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                switchToRegister();
                            });
                        </script>
                    <?php endif; ?>

                    <div class="input-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required placeholder="Enter a username">
                    </div>
                    
                    <div class="input-group">
                        <label for="register_email">Email Address</label>
                        <input type="email" id="register_email" name="email" required placeholder="Enter an email">
                    </div>
                    
                    <div class="input-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required placeholder="Enter your number">
                    </div>
                    
                    <div class="input-group">
                        <label for="register_password">Password</label>
                        <input type="password" id="register_password" name="password" required minlength="6" placeholder="Enter your password">
                    </div>
                    
                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Confirm your password">
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">
                            I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create Account</button>
                    
                    <p class="switch-form">
                        Already have an account? 
                        <a href="#" onclick="switchToLogin(); return false;">Sign In</a>
                    </p>
                </form>
            </div>
        </div>
        
        <div class="image-container">
            <div class="overlay"></div>
            <div class="image-content">
                <h1>Suva's Resort and Place Antipolo</h1>
                <p>Have fun under the sun!</p>
            </div>
        </div>
    </div> 
    
    <script>
        function switchToRegister() {
            document.getElementById('loginForm').style.display = 'none';
            document.getElementById('registerForm').style.display = 'block';
        }
        
        function switchToLogin() {
            document.getElementById('registerForm').style.display = 'none';
            document.getElementById('loginForm').style.display = 'block';
        }
        
        // Password confirmation validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('register_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
            }
        });
    </script>
</body>
</html>