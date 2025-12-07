<?php
require_once '../login&admin/config/database.php';

// Fetch packages from database
$packages = [];
$packages_query = "SELECT * FROM services WHERE type = 'package' AND is_available = 1 ORDER BY price ASC";

if ($packages_result) {
    while ($pkg = $packages_result->fetch_assoc()) {
        $packages[] = $pkg;
    }
}

// If no packages in database, use defaults
if (empty($packages)) {
    $packages = [
        [
            'id' => 0,
            'name' => 'Day & Night Rates',
            'description' => 'Access to pool facilities during day or night shift',
            'price' => 150.00,
            'max_pax' => 1,
            'category' => 'public',
            'amenities' => 'Pool access, Shower facilities, Changing rooms',
            'inclusions' => 'Entrance fee, Basic facilities',
            'image_url' => '../public/assets/images/packages_day&night.png'
        ],
        [
            'id' => 0,
            'name' => 'Package A',
            'description' => 'Complete day swimming package with cottage',
            'price' => 800.00,
            'max_pax' => 10,
            'category' => 'public',
            'amenities' => 'Pool access, Small cottage, Grill area',
            'inclusions' => 'Entrance for 10 pax, Small cottage rental',
            'image_url' => '../public/assets/images/packages_packageA.png'
        ],
        [
            'id' => 0,
            'name' => 'Package B',
            'description' => 'Premium overnight package with large cottage',
            'price' => 1500.00,
            'max_pax' => 20,
            'category' => 'public',
            'amenities' => 'Pool access, Large cottage, Grill area, Karaoke',
            'inclusions' => 'Entrance for 20 pax, Large cottage rental, Complimentary snacks',
            'image_url' => '../public/assets/images/packages_packageB.png'
        ]
    ];
}

// Fetch reviews
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

    <style>
        /* Package Modal Styles */
        .package-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s;
        }

        .package-modal.active {
            display: flex;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: slideUp 0.4s;
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            color: #666;
            cursor: pointer;
            z-index: 10;
            background: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }

        .modal-close:hover {
            background: #f44336;
            color: white;
            transform: rotate(90deg);
        }

        .modal-header {
            background: linear-gradient(135deg, #2c5f2d 0%, #5a9e5c 100%);
            color: white;
            padding: 40px 30px 30px;
            border-radius: 20px 20px 0 0;
        }

        .modal-header h2 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .modal-header .price {
            font-size: 2.5rem;
            font-weight: bold;
            margin-top: 15px;
        }

        .modal-header .price small {
            font-size: 1rem;
            opacity: 0.9;
        }

        .modal-body {
            padding: 30px;
        }

        .package-section {
            margin-bottom: 25px;
        }

        .package-section h3 {
            color: #2c5f2d;
            font-size: 1.3rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .package-section p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .package-features {
            list-style: none;
            padding: 0;
        }

        .package-features li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .package-features li:last-child {
            border-bottom: none;
        }

        .package-features i {
            color: #2c5f2d;
            font-size: 1.2rem;
        }

        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .amenity-item {
            background: #f0f8f0;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            font-size: 0.9rem;
            color: #2c5f2d;
        }

        .modal-footer {
            padding: 20px 30px 30px;
            display: flex;
            gap: 15px;
        }

        .modal-btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .modal-btn-primary {
            background: linear-gradient(135deg, #2c5f2d 0%, #5a9e5c 100%);
            color: white;
        }

        .modal-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44, 95, 45, 0.3);
        }

        .modal-btn-secondary {
            background: #f5f5f5;
            color: #666;
        }

        .modal-btn-secondary:hover {
            background: #e0e0e0;
        }

        .package-card {
            cursor: pointer;
            transition: transform 0.3s;
        }

        .package-card:hover {
            transform: scale(1.05);
        }
    </style>
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
    <?php foreach ($packages as $index => $package): ?>
    <div class="package-card" onclick="openPackageModal(<?php echo $index; ?>)">
      <img src="<?php echo htmlspecialchars($package['image_url'] ?? '../public/assets/images/packages_day&night.png'); ?>" 
           alt="<?php echo htmlspecialchars($package['name']); ?>">
      <div class="package-overlay">
        <h3><?php echo strtoupper(htmlspecialchars($package['name'])); ?></h3>
        <p style="margin-top: 10px; font-size: 1.5rem; font-weight: bold;">
          ₱<?php echo number_format($package['price'], 2); ?>
        </p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- Package Modals - ADD THIS BEFORE YOUR REVIEWS SECTION -->
<?php foreach ($packages as $index => $package): ?>
<div class="package-modal" id="packageModal<?php echo $index; ?>">
    <div class="modal-content">
        <span class="modal-close" onclick="closePackageModal(<?php echo $index; ?>)">&times;</span>
        
        <div class="modal-header">
            <h2><?php echo htmlspecialchars($package['name']); ?></h2>
            <p><?php echo htmlspecialchars($package['description']); ?></p>
            <div class="price">
                ₱<?php echo number_format($package['price'], 2); ?>
                <small>/<?php echo $package['max_pax']; ?> pax</small>
            </div>
        </div>

        <div class="modal-body">
            <div class="package-section">
                <h3><i class="fas fa-info-circle"></i> Package Details</h3>
                <p><?php echo htmlspecialchars($package['description']); ?></p>
                <ul class="package-features">
                    <li><i class="fas fa-users"></i> <strong>Capacity:</strong> Up to <?php echo $package['max_pax']; ?> persons</li>
                    <li><i class="fas fa-tag"></i> <strong>Price:</strong> ₱<?php echo number_format($package['price'], 2); ?></li>
                    <li><i class="fas fa-layer-group"></i> <strong>Category:</strong> <?php echo ucfirst($package['category']); ?></li>
                </ul>
            </div>

            <?php if (!empty($package['amenities'])): ?>
            <div class="package-section">
                <h3><i class="fas fa-check-circle"></i> Amenities</h3>
                <div class="amenities-grid">
                    <?php 
                    $amenities = explode(',', $package['amenities']);
                    foreach ($amenities as $amenity): ?>
                        <div class="amenity-item"><?php echo trim($amenity); ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($package['inclusions'])): ?>
            <div class="package-section">
                <h3><i class="fas fa-gift"></i> Inclusions</h3>
                <ul class="package-features">
                    <?php 
                    $inclusions = explode(',', $package['inclusions']);
                    foreach ($inclusions as $inclusion): ?>
                        <li><i class="fas fa-check"></i> <?php echo trim($inclusion); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>

        <div class="modal-footer">
            <?php if (is_logged_in()): ?>
                <button class="modal-btn modal-btn-primary" onclick="bookPackage(<?php echo $package['id']; ?>)">
                    <i class="fas fa-calendar-check"></i> Book Now
                </button>
            <?php else: ?>
                <button class="modal-btn modal-btn-primary" onclick="window.location.href='../login&admin/login.php?redirect=booking_page.php'">
                    <i class="fas fa-sign-in-alt"></i> Login to Book
                </button>
            <?php endif; ?>
            <button class="modal-btn modal-btn-secondary" onclick="closePackageModal(<?php echo $index; ?>)">
                Close
            </button>
        </div>
    </div>
</div>
<?php endforeach; ?>

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

<script>
    const packages = <?php echo json_encode($packages); ?>;

    function openPackageModal(index) {
        const modal = document.getElementById('packageModal' + index);
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closePackageModal(index) {
        const modal = document.getElementById('packageModal' + index);
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    function bookPackage(packageId) {
        window.location.href = 'booking_page.php?package_id=' + packageId;
    }

    // Close modal when clicking outside
    document.querySelectorAll('.package-modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
        });
    });

    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.package-modal.active').forEach(modal => {
                modal.classList.remove('active');
                document.body.style.overflow = 'auto';
            });
        }
    });
</script>

</body>
</html>