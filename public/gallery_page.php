<?php
require_once '../login&admin/config/database.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery | Suva's Place and Resort Antipolo</title>
    <link rel="stylesheet" href="../public/assets/css/navbar.css">
    <link rel="stylesheet" href="../public/assets/css/shared.css">
    <link rel="stylesheet" href="../public/assets/css/gallery_page.css">
    <link rel="stylesheet" href="../public/assets/css/user_menu.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../public/assets/images/suva's_place_logo.ico">
    <script defer src="../public/assets/js/navbar.js"></script>
    <script defer src="../public/assets/js/user_menu.js"></script>
    <script defer src="../public/assets/js/gallery_page.js"></script>
    <script defer src="../public/assets/js/script.js"></script>
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
        <h1>Gallery</h1>
        <p>Explore moments and spaces that make every stay unforgettable.</p>
    </div>
</header>


<section class="highlights reveal">
  <div class="highlight-carousel">
    <button class="carousel-btn prev">&#10094;</button>

    <div class="highlight-track">
      <div class="highlight-card"><img src="../public/assets/images/img1.jpg"/></div>
      <div class="highlight-card"><img src="../public/assets/images/img2.jpg"/></div>
      <div class="highlight-card"><img src="../public/assets/images/img3.jpg"/></div>
    </div>

    <button class="carousel-btn next">&#10095;</button>
  </div>

  <h2 class="section-title">Highlights</h2>
  <p class="section-desc">
    Discover the features that make every stay at Suva's Place Resort relaxing, comfortable, and truly memorable.
  </p>

</section>



<section class="facilities reveal">
  <h2 class="section-title">Facilities</h2>

  <div class="facilities-slider">
    <div class="facilities-track">
      <img src="../public/assets/images/img4.jpg"/>
      <img src="../public/assets/images/img5.jpg"/>
      <img src="../public/assets/images/img6.jpg"/>
      <img src="../public/assets/images/img7.jpg"/>
      <img src="../public/assets/images/img8.jpg"/>
      <img src="../public/assets/images/img9.jpg"/>
    </div>
  </div>

  <div class="facilities-controls">
    <button id="facPrev">&#10094;</button>
    <div class="dots"></div>
    <button id="facNext">&#10095;</button>
  </div>
</section>









<!----------------------------- FOOTER SECTION -------------------------------->

<footer class="footer">
    <div class="footer-content">
        <div class="footer-logo">
            <img src="../public/assets/images/suva's_logo_white.png">
        </div>

        <nav class="footer-nav">
            <a href="../client_side/index.php">Home</a>
            <a href="../client_side/about_page.php">About us</a>
            <a href="../client_side/gallery_page.php">Gallery</a>
            <a href="../client_side/login_page.php">Login</a>
            <a href="../client_side/booknow_page.php">Book Now</a>
            <a href="../client_side/contact_page.php">Contact us</a>
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