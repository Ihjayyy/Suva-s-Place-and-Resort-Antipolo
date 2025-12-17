<?php
require_once '../login&admin/config/database.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    // Validation
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no errors, insert into database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO inquiries (full_name, email, message, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
            $stmt->execute([$full_name, $email, $message]);
            
            $success_message = "Thank you for contacting us! We'll get back to you soon.";
            
            // Clear form data
            $full_name = $email = $message = '';
        } catch (PDOException $e) {
            $errors[] = "Error submitting inquiry. Please try again later.";
            // Log error for debugging: error_log($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | Suva's Place and Resort Antipolo</title>
    <link rel="stylesheet" href="../public/assets/css/navbar.css">
    <link rel="stylesheet" href="../public/assets/css/shared.css">
    <link rel="stylesheet" href="../public/assets/css/contacts_page.css">
    <link rel="stylesheet" href="../public/assets/css/user_menu.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../public/assets/images/suva's_place_logo.ico">
    <script defer src="../public/assets/js/navbar.js"></script>
    <script defer src="../public/assets/js/user_menu.js"></script>
    <script defer src="../public/assets/js/script.js"></script>
    
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }
    </style>
</head>
<body>

<!------------------------ NAVIGATION BAR ------------------------->

<header class="hero">
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="../public/assets/images/suva's_logo_white.png">
            </div>

            <ul class="nav-links" id="nav-links">
                <li><a href="index.php">Home</a></li>
                <li><a href="about_page.php">About us</a></li>
                <li><a href="gallery_page.php">Gallery</a></li>
                <li><a href="contacts_page.php">Contacts</a></li>
                
                <?php if(is_logged_in()): ?>
                    <li class="user-menu-container">
                        <a href="#" class="user-button" id="userMenuBtn">
                            <i class="fas fa-user-circle"></i> 
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        
                        <div class="user-dropdown" id="userDropdown">
                            <div class="dropdown-header">
                                <i class="fas fa-user-circle"></i>
                                <div>
                                    <p class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></p>
                                    <p class="user-email"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="user_settings.php" class="dropdown-item">
                                <i class="fas fa-cog"></i> User Settings
                            </a>
                            <a href="transaction_history.php" class="dropdown-item">
                                <i class="fas fa-history"></i> Transaction History
                            </a>
                            <a href="help_support.php" class="dropdown-item">
                                <i class="fas fa-question-circle"></i> Help & Support
                            </a>
                            <a href="terms.php" class="dropdown-item">
                                <i class="fas fa-file-contract"></i> Terms and Conditions
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="../login&admin/logout.php" class="dropdown-item logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="../login&admin/login.php" class="login-button"><i class="fas fa-user"></i> Login</a></li>
                <?php endif; ?>
            </ul>

            <div class="burger" id="burger">
                  <i class="fas fa-bars" id="open-icon"></i>
                  <i class="fas fa-arrow-left" id="close-icon"></i>
            </div>
        </div>
    </nav>

    <div class="hero-content">
        <h1>Contacts</h1>
        <p>Get in touch with Suva’s Place Resort for inquiries, reservations, <br> or special requests.</p>
    </div>
</header>

<!---------------- CONTACT SECTION ---------------->

<section class="contact-section reveal">

    <!-- TOP: CONTACT FORM -->
    <div class="contact-form-wrapper">
        <span class="contact-subtitle">
            <i class="fa-regular fa-comment-dots"></i> GET IN TOUCH
        </span>
        <h2>Leave Us Your Info</h2>
        <p class="contact-description">
            Feel free to reach out to us for inquiries, reservations, or concerns.
        </p>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="contact-form-grid">
            <div class="form-row">
                <input type="text" name="full_name" placeholder="Full Name"
                       value="<?php echo htmlspecialchars($full_name ?? ''); ?>" required>

                <input type="email" name="email" placeholder="Email"
                       value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>

            <textarea name="message" placeholder="Comment" required>
<?php echo htmlspecialchars($message ?? ''); ?>
            </textarea>

            <button type="submit" name="submit_inquiry" class="btn-submit">
                SEND YOUR MESSAGE
            </button>
        </form>
    </div>

    <!-- BOTTOM: MAP + INFO -->
    <div class="contact-bottom reveal">
        <div class="map-container">
            <iframe
                src="https://maps.google.com/maps?q=antipolo&t=&z=13&ie=UTF8&iwloc=&output=embed"
                loading="lazy">
            </iframe>
        </div>

        <div class="contact-info-box">
            <span class="info-subtitle">INFORMATION</span>
            <h3>Connect With Us</h3>

            <div class="info-item">
                <i class="fa-brands fa-facebook-f"></i>
                @suvasplaceresortantipolo
            </div>

            <div class="info-item">
                <i class="fa-solid fa-phone"></i>
                0976 023 3563
            </div>

            <div class="info-item">
                <i class="fa-solid fa-envelope"></i>
                suvasplaceresortantipolo@gmail.com
            </div>
        </div>
    </div>

</section>


<!----------------------------- FOOTER SECTION -------------------------------->
<footer class="footer">
    <div class="footer-content">
        <div class="footer-logo">
            <img src="../public/assets/images/suva's_logo_white.png">
        </div>

        <nav class="footer-nav">
            <a href="index.php">Home</a>
            <a href="about_page.php">About us</a>
            <a href="gallery_page.php">Gallery</a>
            <a href="login_page.php">Login</a>
            <a href="">Book Now</a>
            <a href="contacts_page.php">Contact us</a>
        </nav>

        <div class="footer-socials">
            <a href=""><i class="fab fa-facebook-f"></i></a>
            <a href=""><i class="fab fa-tiktok"></i></a>
            <a href=""><i class="fab fa-instagram"></i></a>
        </div>
    </div>

    <div class="footer-bottom">
        <hr />
        <p>©2025 Suva's Place Resort Antipolo. All rights reserved.</p>
    </div>
</footer>

</body>
</html>