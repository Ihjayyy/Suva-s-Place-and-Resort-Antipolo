<?php
require_once '../login&admin/config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | Suva's Place and Resort Antipolo</title>

    <link rel="stylesheet" href="../public/assets/css/navbar.css">
    <link rel="stylesheet" href="../public/assets/css/about_page.css">
    <link rel="stylesheet" href="../public/assets/css/shared.css">
    <link rel="stylesheet" href="../public/assets/css/user_menu.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../public/assets/images/suva's_place_logo.ico">

    <script defer src="../public/assets/js/navbar.js"></script>
    <script defer src="../public/assets/js/about_page.js"></script>
    <script defer src="../public/assets/js/user_menu.js"></script>
</head>
<body>

<!------------------------ NAVIGATION BAR ------------------------->

    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="../public/assets/images/suva's_logo_white.png">
            </div>

            <ul class="nav-links" id="nav-links">
                <li><a href="landing_page.php">Home</a></li>
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
                        
                        <!-- User Dropdown Menu -->
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

  <!------------------------- ABOUT US HERO SECTION --------------------->
  <section class="hero">
    <div class="hero-content">
      <h1>About us</h1>
    </div>
  </section>

  <!-------------------------- HISTORY SECTION ----------------------------->
  <section class="history">
    <div class="history-content">
      <img src="../public/assets/images/about-history-img.png" alt="Founder" class="history-img" />
      <div class="history-text">
        <h2>The Prime mover</h2>
        <p>
          Jose Timbol. Suva, born on November 3, 1918 in Makati City, Married to the former Teodora Cawill of Manila with whom
          they were blessed with 11 children. Mr. Suva is a businessman as wekk as an active civic leader and helps other charitable
          organization.
          <br><br>
          He's the former Vice President of Comite De Festejos of Quiapo (Organization that Manage the Annual Festival of the black Nazarene)
          for over a decade and the Vice Commander of the American Legion of the Philippines. He is also the owner of the Filipinas Merantile
          that manufacture rubber products and also the proprietor of Suva's Place Resort Antipolo.
        </p>
        <button class="btn-learn" id="learnBtn">Learn more</button>
      </div>

      <div class="extra-text" id="extraText">
          <h2>Having fun under the sun since 1971</h2>
          <p>
            Suva's Place Resort was established in 1971 by Mr. Jose Timbol Suva. Originally named Pecina Dela Virgen Resort, it was later changed
            to the family's last name. The resort's first amenity was the Main Pool, which is 10ft deep and features 2 slides and 1 diving board.
            Two kiddie pools were added in subsequent years, and in 1995, Semi Private Pool, Casas, and Cuartos were built.
            <br><br>
            The current successor is committed to completing renovations and adding additional amenities such as a bar, restaurant, events place and
            suite rooms by 2030. The resort also plans to expand its hospitality business in the outskirts of Antipolo City. Suva's Place Resort promises
            to provide excellent quality services, the most  comfortable and affordable place to stay.
          </p>
          <button class="btn-learn" id="showLessBtn">Show less</button>
      </div>
    </div>
  </section>

  <!----------------------- MSV SECTION ----------------------------->

  <section class="mv-section">
    <div class="mv-bg"></div>
    <div class="mv-container">
      <div class="mv-card">
        <h3>Mission</h3>
        <ul>
          <li>To be the gateway place that everybody would want to spend their valuable time.</li>
          <li>To provide an excellent customer service.</li>
          <li>To offer an enjoyment that creates memories they will never forget.</li>
          <li>To meet expectations and needs of each customer.</li>
          <li>To provide excellent customer satisfaction.</li>
        </ul>
      </div>

      <div class="mv-card">
        <h3>Vision</h3>
        <p>
          Our vision is to be recognized as the premier destination for joy and unforgettable experience in the
          resort industry. We strive to create a vibrant atmosphere where fun and enjoyment are at the heart of
          every guest interaction. Committed to upholding core values, we prioritize exceptional customer
          service, ensuring each visitor feels valued and appreciated.
        </p>
      </div>

      <div class="mv-card">
        <h3>Core Values</h3>
        <ul>
          <li>Respect</li>
          <li>Values</li>
          <li>Teamwork</li>
          <li>Excellence</li>
        </ul>
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
            <a href="landing_page.php">Home</a>
            <a href="about_page.php">About us</a>
            <a href="gallery_page.php">Gallery</a>
            <a href="login_page.php">Login</a>
            <a href="">Book Now</a>
            <a href="contactS_page.php">Contact us</a>
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