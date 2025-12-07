<?php
require_once '../login&admin/config/database.php';

// Redirect to login if not logged in
if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    redirect('../login&admin/login.php');
}

// Fetch all available services
$services = [];
$servicesQuery = "SELECT * FROM services WHERE availability = 'available' ORDER BY category, price";
$servicesResult = $conn->query($servicesQuery);

while ($row = $servicesResult->fetch_assoc()) {
    $services[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book now | Suva's Place and Resort Antipolo</title>

    <link rel="stylesheet" href="../public/assets/css/navbar.css">
    <link rel="stylesheet" href="../public/assets/css/booking_page.css">
    <link rel="stylesheet" href="../public/assets/css/shared.css">
    <link rel="stylesheet" href="../public/assets/css/user_menu.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../public/assets/images/suva's_place_logo.ico">

    <script defer src="../public/assets/js/navbar.js"></script>
    <script defer src="../public/assets/js/booking_page.js"></script>
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

                <?php if (is_logged_in()): ?>
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
                                    <p class="user-email"><?php echo htmlspecialchars($_SESSION['email'] ?? $_SESSION['username']); ?></p>
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
                            <div class="dropdown-divider"></div>
                            <a href="../login&admin/logout.php" class="dropdown-item logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="burger" id="burger">
                <i class="fas fa-bars" id="open-icon"></i>
                <i class="fas fa-arrow-left" id="close-icon"></i>
            </div>
        </div>
    </nav>

    <!------------------------- BOOK NOW HERO SECTION --------------------->
    <section class="hero">
        <div class="hero-content">
            <h1>Book now</h1>
        </div>
    </section>

    <div class="page-wrapper">
        <header class="header">
            <h1 class="resort-title">Suva's Place and Resort Antipolo</h1>
            <p class="resort-subtitle">Have fun under the sun!</p>
            <p>Book your perfect getaway with our exclusive accommodations</p>
        </header>

        <div class="booking-container">
            <!-- Accommodation Selection Section -->
            <section class="accommodation-section">
                <h2 class="section-title">Choose Your Accommodation</h2>
                <p class="section-subtitle">Select from our exquisite cottages and rooms</p>

                <!-- Cottages Gallery -->
                <div class="accommodation-category">
                    <h3 class="category-title"><i class="fas fa-home"></i> Beach Cottages</h3>
                    <div class="gallery-container">
                        <!-- Umbrella Cottage -->
                        <div class="gallery-card" onclick="openModal('umbrella')">
                            <div class="gallery-image">
                                <img src="../public/assets/images/umbrella-cottage.png" alt="Umbrella Cottage" onerror="this.src='../public/assets/images/placeholder.jpg'">
                                <div class="gallery-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <p>View Details</p>
                                </div>
                            </div>
                            <div class="gallery-info">
                                <h4>Umbrella Cottage</h4>
                                <p class="gallery-capacity"><i class="fas fa-users"></i> 4-5 pax</p>
                                <p class="gallery-price">Starting at ₱600</p>
                            </div>
                        </div>

                        <!-- Family Cottage -->
                        <div class="gallery-card" onclick="openModal('family')">
                            <div class="gallery-image">
                                <img src="../public/assets/images/family-cottage.png" alt="Family Cottage" onerror="this.src='../public/assets/images/placeholder.jpg'">
                                <div class="gallery-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <p>View Details</p>
                                </div>
                            </div>
                            <div class="gallery-info">
                                <h4>Family Cottage</h4>
                                <p class="gallery-capacity"><i class="fas fa-users"></i> 10-15 pax</p>
                                <p class="gallery-price">Starting at ₱900</p>
                            </div>
                        </div>

                        <!-- Barkada Cottage -->
                        <div class="gallery-card" onclick="openModal('barkada')">
                            <div class="gallery-image">
                                <img src="../public/assets/images/barkada-cottage.png" alt="Barkada Cottage" onerror="this.src='../public/assets/images/placeholder.jpg'">
                                <div class="gallery-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <p>View Details</p>
                                </div>
                            </div>
                            <div class="gallery-info">
                                <h4>Barkada Cottage</h4>
                                <p class="gallery-capacity"><i class="fas fa-users"></i> 20-30 pax</p>
                                <p class="gallery-price">Starting at ₱1,300</p>
                            </div>
                        </div>

                        <!-- Silong -->
                        <div class="gallery-card" onclick="openModal('silong')">
                            <div class="gallery-image">
                                <img src="../public/assets/images/silong.png" alt="Silong" onerror="this.src='../public/assets/images/placeholder.jpg'">
                                <div class="gallery-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <p>View Details</p>
                                </div>
                            </div>
                            <div class="gallery-info">
                                <h4>Silong</h4>
                                <p class="gallery-capacity"><i class="fas fa-users"></i> 30-40 pax</p>
                                <p class="gallery-price">₱2,000 / day</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rooms Gallery -->
                <div class="accommodation-category">
                    <h3 class="category-title"><i class="fas fa-bed"></i> Premium Rooms</h3>
                    <div class="gallery-container">
                        <!-- Casa Ernesto -->
                        <div class="gallery-card" onclick="openModal('ernesto')">
                            <div class="gallery-image">
                                <img src="../public/assets/images/casa-ernesto.png" alt="Casa Ernesto" onerror="this.src='../public/assets/images/placeholder.jpg'">
                                <div class="gallery-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <p>View Details</p>
                                </div>
                            </div>
                            <div class="gallery-info">
                                <h4>Casa Ernesto</h4>
                                <p class="gallery-capacity"><i class="fas fa-users"></i> Up to 18 guests</p>
                                <p class="gallery-price">Starting at ₱6,500</p>
                            </div>
                        </div>

                        <!-- Casa Ma. Elena -->
                        <div class="gallery-card" onclick="openModal('elena')">
                            <div class="gallery-image">
                                <img src="../public/assets/images/casa-elena.png" alt="Casa Ma. Elena" onerror="this.src='../public/assets/images/placeholder.jpg'">
                                <div class="gallery-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <p>View Details</p>
                                </div>
                            </div>
                            <div class="gallery-info">
                                <h4>Casa Ma. Elena</h4>
                                <p class="gallery-capacity"><i class="fas fa-users"></i> Up to 8 guests</p>
                                <p class="gallery-price">Starting at ₱4,000</p>
                            </div>
                        </div>

                        <!-- Casa Edmundo -->
                        <div class="gallery-card" onclick="openModal('edmundo')">
                            <div class="gallery-image">
                                <img src="../public/assets/images/casa-edmundo.png" alt="Casa Edmundo" onerror="this.src='../public/assets/images/placeholder.jpg'">
                                <div class="gallery-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <p>View Details</p>
                                </div>
                            </div>
                            <div class="gallery-info">
                                <h4>Casa Edmundo</h4>
                                <p class="gallery-capacity"><i class="fas fa-users"></i> Up to 5 guests</p>
                                <p class="gallery-price">Starting at ₱3,000</p>
                            </div>
                        </div>

                        <!-- Standard Cuarto -->
                        <div class="gallery-card" onclick="openModal('standard')">
                            <div class="gallery-image">
                                <img src="../public/assets/images/standard-cuarto.png" alt="Standard Cuarto" onerror="this.src='../public/assets/images/placeholder.jpg'">
                                <div class="gallery-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <p>View Details</p>
                                </div>
                            </div>
                            <div class="gallery-info">
                                <h4>Standard Cuarto</h4>
                                <p class="gallery-capacity"><i class="fas fa-clock"></i> Hourly rates</p>
                                <p class="gallery-price">Starting at ₱500</p>
                            </div>
                        </div>

                        <!-- Deluxe Cuarto -->
                        <div class="gallery-card" onclick="openModal('deluxe')">
                            <div class="gallery-image">
                                <img src="../public/assets/images/deluxe-cuarto.png" alt="Deluxe Cuarto" onerror="this.src='../public/assets/images/placeholder.jpg'">
                                <div class="gallery-overlay">
                                    <i class="fas fa-search-plus"></i>
                                    <p>View Details</p>
                                </div>
                            </div>
                            <div class="gallery-info">
                                <h4>Deluxe Cuarto</h4>
                                <p class="gallery-capacity"><i class="fas fa-clock"></i> Hourly rates</p>
                                <p class="gallery-price">Starting at ₱700</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Booking Form Section -->
            <section class="form-section">
                <h2 class="section-title">Booking Details</h2>
                <p class="section-subtitle">Complete your reservation</p>

                <!-- Selected Items Display -->
                <div class="selected-items" id="selectedItems">
                    <h3 class="selected-title">Your Selection</h3>
                    <p class="selection-instruction">
                        <i class="fas fa-info-circle"></i> 
                        Select <strong>one cottage</strong> (required) and optionally <strong>one room</strong>
                    </p>
                    <div class="selected-list" id="selectedList">
                        <p class="no-selection">No accommodation selected yet. Please select at least one cottage.</p>
                    </div>
                </div>

                <!-- Booking Form -->
                <form class="booking-form" id="bookingForm" enctype="multipart/form-data">
                    <!-- Guest Information -->
                    <div class="form-section-group">
                        <h3 class="form-section-title"><i class="fas fa-user"></i> Guest Information</h3>

                        <div class="form-group">
                            <label for="fullName" class="required">Full Name</label>
                            <input type="text" id="fullName" name="fullName" required
                                placeholder="Enter your full name"
                                value="<?php echo htmlspecialchars($_SESSION['full_name'] ?? ''); ?>">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email" class="required">Email Address</label>
                                <input type="email" id="email" name="email" required
                                    placeholder="Enter your email address"
                                    value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="contactNumber" class="required">Contact Number</label>
                                <input type="tel" id="contactNumber" name="contactNumber" required
                                    placeholder="Enter your phone number"
                                    value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="address" class="required">Address</label>
                            <textarea id="address" name="address" required rows="3"
                                placeholder="Enter your complete address"><?php echo htmlspecialchars($_SESSION['address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- Booking Details -->
                    <div class="form-section-group">
                        <h3 class="form-section-title"><i class="fas fa-calendar"></i> Booking Details</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="checkIn" class="required">Check-in Date & Time</label>
                                <input type="datetime-local" id="checkIn" name="checkIn" required
                                    min="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                            <div class="form-group">
                                <label for="checkOut" class="required">Check-out Date & Time</label>
                                <input type="datetime-local" id="checkOut" name="checkOut" required
                                    min="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="guestCount" class="required">Number of Adults</label>
                                <input type="number" id="guestCount" name="guestCount" min="1" max="50" required value="1">
                            </div>
                            <div class="form-group">
                                <label for="numKids">Number of Kids</label>
                                <input type="number" id="numKids" name="numKids" min="0" max="50" value="0">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="additionalRequests">Special Requests</label>
                            <select id="additionalRequests" name="additionalRequests">
                                <option value="">None</option>
                                <option value="extra-bed">Extra Bed</option>
                                <option value="early-checkin">Early Check-in</option>
                                <option value="late-checkout">Late Check-out</option>
                                <option value="airport-transfer">Airport Transfer</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="notes">Additional Notes</label>
                            <textarea id="notes" name="notes" rows="3"
                                placeholder="Any special requirements or notes..."></textarea>
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="form-section-group">
                        <h3 class="form-section-title"><i class="fas fa-credit-card"></i> Payment Details</h3>

                        <div class="payment-options">
                            <div class="payment-option active" data-method="onsite">
                                <i class="fas fa-money-bill-wave"></i>
                                <div>
                                    <label>Pay at Resort</label>
                                    <p>Pay when you arrive</p>
                                </div>
                            </div>
                            <div class="payment-option" data-method="online">
                                <i class="fas fa-mobile-alt"></i>
                                <div>
                                    <label>Online Payment</label>
                                    <p>Pay now with proof</p>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" id="paymentMethod" name="paymentMethod" value="onsite">

                        <div class="file-upload" id="proofUpload" style="display: none;">
                            <div class="form-group">
                                <label for="proofOfPayment">Proof of Payment</label>
                                <div class="file-input-wrapper">
                                    <input type="file" id="proofOfPayment" name="proofOfPayment" accept="image/*,.pdf">
                                    <div class="file-input-label">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click to upload proof of payment (Image or PDF)</p>
                                        <p class="file-info" id="fileInfo">No file selected</p>
                                    </div>
                                </div>
                            </div>
                            <div class="file-preview" id="filePreview"></div>
                        </div>
                    </div>

                    <!-- Booking Policy -->
                    <div class="booking-policy">
                        <h3><i class="fas fa-info-circle"></i> Booking Policy</h3>
                        <ol>
                            <li>Reschedule must be made 7 days before the date of reservation.</li>
                            <li>In an event of "No Show" Initial Deposit is no longer refundable.</li>
                            <li>Initial Deposit is non-refundable unless accidents and god acts occurs.</li>
                            <li>Once it settles, payments are Non-refundable.</li>
                        </ol>
                    </div>

                    <!-- Total Display -->
                    <div class="total-display">
                        <div class="total-label">Total Amount</div>
                        <div class="total-amount" id="totalAmount">₱0</div>
                    </div>

                    <!-- Terms & Submit -->
                    <div class="form-group terms-group">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">I agree to the terms and conditions, cancellation policy, and booking policy stated above</label>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="fas fa-calendar-check"></i> Confirm Booking
                    </button>
                </form>
            </section>
        </div>

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
    </div>

    <!-- Modals for each accommodation -->
    <?php 
    $modals_file = __DIR__ . '/accommodation_modals.php';
    if (file_exists($modals_file)) {
        include $modals_file;
    } else {
        echo "<!-- Warning: accommodation_modals.php not found at: " . $modals_file . " -->";
    }
    ?>

</body>

</html>