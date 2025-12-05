<?php
require_once '../login&admin/config/database.php';

// Fetch reviews from database
$reviews_query = "SELECT r.*, u.username, u.full_name 
                  FROM reviews r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.status = 'approved' 
                  ORDER BY r.created_at DESC";
$reviews_result = $conn->query($reviews_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suva's Place and Resort Antipolo</title>

    <link rel="stylesheet" href="../public/assets/css/navbar.css">
    <link rel="stylesheet" href="../public/assets/css/landing_page.css">
    <link rel="stylesheet" href="../public/assets/css/shared.css">
    <link rel="stylesheet" href="../public/assets/css/user_menu.css">

    <script defer src="../public/assets/js/navbar.js"></script>
    <script defer src="../public/assets/js/user_menu.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../public/assets/images/suva's_place_logo.ico">
</head>

<body>

<!-------------------- HERO SECTION ---------------------->

<header class="hero">
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

    <div class="hero-content">
        <h1>Have fun <br><span>under the sun!</span></h1>
        <p>Vacation destination in the heart of Antipolo City.</p>
        <div class="hero-buttons">
            <a href="booking_page.php" class="btn-outline">Book Now</a>
        </div>
    </div>
</header>

<!------------------- PACKAGES SECTION ---------------------->

<section class="packages">
  <div class="packages-header">
    <p>OUR PACKAGES</p>
    <h2>PACKAGES WE OFFER</h2>
  </div>

  <div class="packages-container">
    <div class="package-card">
      <img src="../public/assets/images/packages_day&night.png" alt="Day & Night Rates">
      <div class="package-overlay">
        <h3>DAY & NIGHT RATES</h3>
      </div>
    </div>

    <div class="package-card">
      <img src="../public/assets/images/packages_packageA.png" alt="Package A">
      <div class="package-overlay">
        <h3>PACKAGE A</h3>
      </div>
    </div>

    <div class="package-card">
      <img src="../public/assets/images/packages_packageB.png" alt="Package B">
      <div class="package-overlay">
        <h3>PACKAGE B</h3>
      </div>
    </div>
  </div>
</section>


<!--------------------- REVIEWS SECTION ------------------------>

<section class="reviews">
  <div class="reviews-header">
    <h2>WHAT OUR CLIENTS SAY</h2>
  </div>

  <div class="review-slider">
    <button class="nav-btn prev" id="prevBtn">&#10094;</button>

    <div class="review-cards-container" id="reviewContainer">
      <?php if($reviews_result->num_rows > 0): ?>
        <?php while($review = $reviews_result->fetch_assoc()): ?>
          <div class="review-card">
            <div class="stars">
              <?php 
              for($i = 0; $i < 5; $i++) {
                echo $i < $review['rating'] ? '★' : '☆';
              }
              ?>
            </div>
            <p class="review-text">
              "<?php echo htmlspecialchars($review['review_text']); ?>"
            </p>
            <div class="reviewer">
              <img src="../public/assets/images/default-avatar.png" alt="Reviewer">
              <span><?php echo htmlspecialchars($review['full_name'] ?: $review['username']); ?></span>
            </div>
            <p class="review-date"><?php echo date('F j, Y', strtotime($review['created_at'])); ?></p>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="review-card">
          <div class="stars">★★★★★</div>
          <p class="review-text">
            "Be the first to share your experience at Suva's Resort!"
          </p>
          <div class="reviewer">
            <img src="../public/assets/images/github_profile.jpg" alt="Reviewer">
            <span>Guest</span>
          </div>
        </div>
      <?php endif; ?>
    </div>
    <button class="nav-btn next" id="nextBtn">&#10095;</button>
  </div>
  <?php if(is_logged_in()): ?>
  <div class="add-review-container">
    <button class="btn-add-review" id="addReviewBtn">
      <i class="fas fa-plus"></i> Add Your Review
    </button>
  </div>

  <!-- Add Review Modal -->
  <div class="review-modal" id="reviewModal">
    <div class="modal-content">
      <span class="close-modal">&times;</span>
      <h3>Share Your Experience</h3>
      <form action="submit_review.php" method="POST" id="reviewForm">
        <div class="rating-input">
          <label>Your Rating:</label>
          <div class="star-rating">
            <input type="radio" name="rating" value="5" id="star5" required>
            <label for="star5">★</label>
            <input type="radio" name="rating" value="4" id="star4">
            <label for="star4">★</label>
            <input type="radio" name="rating" value="3" id="star3">
            <label for="star3">★</label>
            <input type="radio" name="rating" value="2" id="star2">
            <label for="star2">★</label>
            <input type="radio" name="rating" value="1" id="star1">
            <label for="star1">★</label>
          </div>
        </div>
        <div class="form-group">
          <label for="review_text">Your Review:</label>
          <textarea name="review_text" id="review_text" rows="4" required 
                    placeholder="Share your experience at Suva's Resort..."></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Submit Review</button>
      </form>
    </div>
  </div>
  <?php endif; ?>
</section>


<!-------------------- FOOTER SECTION ----------------------->

<footer class="footer">
    <div class="footer-content">
        <div class="footer-logo">
            <img src="../public/assets/images/suva's_logo_white.png">
        </div>

        <nav class="footer-nav">
            <a href="landing_page.php">Home</a>
            <a href="about_page.php">About us</a>
            <a href="gallery_page.php">Gallery</a>
            <a href="../login&admin/login.php">Login</a>
            <a href="booking_page.php">Book Now</a>
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

<script src="../public/assets/js/review_slider.js"></script>

</body>
</html>