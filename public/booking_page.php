<?php

// Include database configuration
require_once '../login&admin/config/database.php';

// Initialize variables to store form data and errors
$errors = [];
$success = false;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input data
    $fullName = sanitize_input($_POST['full_name'] ?? '');
    $contactNumber = sanitize_input($_POST['contact_number'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $bookingDate = sanitize_input($_POST['booking_date'] ?? '');
    $numAdults = intval($_POST['num_adults'] ?? 0);
    $numKids = intval($_POST['num_kids'] ?? 0);
    $room = sanitize_input($_POST['room'] ?? '');
    $swimmingType = sanitize_input($_POST['swimming_type'] ?? '');
    $cottage = sanitize_input($_POST['cottage'] ?? '');
    $others = sanitize_input($_POST['others'] ?? '');
    $shift = sanitize_input($_POST['shift'] ?? '');

    // Validation
    if (empty($fullName)) $errors[] = "Full Name is required";
    if (empty($contactNumber)) $errors[] = "Contact Number is required";
    if (empty($address)) $errors[] = "Address is required";
    if (empty($bookingDate)) $errors[] = "Booking Date is required";
    if ($numAdults < 1) $errors[] = "At least 1 adult is required";
    if (empty($shift)) $errors[] = "Shift selection is required";
    
    // Validate booking date (must be at least 7 days in advance)
    $bookingTimestamp = strtotime($bookingDate);
    $currentTimestamp = strtotime(date('Y-m-d'));
    $daysDifference = ($bookingTimestamp - $currentTimestamp) / (60 * 60 * 24);
    
    if ($daysDifference < 7) {
        $errors[] = "Booking must be made at least 7 days in advance";
    }

    // If no errors, process the booking
    if (empty($errors)) {
        // Generate a unique booking ID
        $bookingId = 'BK-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        // Prepare SQL statement to insert booking into database
        $stmt = $conn->prepare("INSERT INTO reservations (booking_id, full_name, contact_number, address, booking_date, num_adults, num_kids, room, swimming_type, cottage, shift, others, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        
        $stmt->bind_param("sssssiisssss", 
            $bookingId, 
            $fullName, 
            $contactNumber, 
            $address, 
            $bookingDate, 
            $numAdults, 
            $numKids, 
            $room, 
            $swimmingType, 
            $cottage, 
            $shift, 
            $others
        );

        if ($stmt->execute()) {
            // Store booking info in session for confirmation display
            $_SESSION['last_booking'] = [
                'booking_id' => $bookingId,
                'full_name' => $fullName,
                'contact_number' => $contactNumber,
                'booking_date' => $bookingDate,
                'num_adults' => $numAdults,
                'num_kids' => $numKids,
                'shift' => $shift
            ];

            $success = true;
        } else {
            $errors[] = "Failed to submit booking. Please try again. Error: " . $stmt->error;
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
    <title>Booking | Suva's Place and Resort Antipolo</title>
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="../public/assets/css/navbar.css">
    <link rel="stylesheet" href="../public/assets/css/booking_page.css">
    <link rel="stylesheet" href="../public/assets/css/shared.css">
    <link rel="stylesheet" href="../public/assets/css/user_menu.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../public/assets/images/suva's_place_logo.ico">

    <script defer src="../public/assets/js/navbar.js"></script>
    <script defer src="../public/assets/js/user_menu.js"></script>
</head>

<body>
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

    <section class="hero">
        <div class="hero-content">
            <h1>Book now</h1>
        </div>
    </section>
    <div class="overlay"></div>

    <div class="container">

        <header class="header">
            <h1>Suva's Place and Resort Antipolo</h1>
            <p class="tagline">Have fun under the sun!</p>
        </header>

        <?php if ($success): ?>
            <div class="success-message">
                <h2>Booking Confirmed!</h2>
                <p>Thank you, <strong><?php echo htmlspecialchars($_SESSION['last_booking']['full_name']); ?></strong>!</p>
                <p>Your Booking ID: <strong><?php echo htmlspecialchars($_SESSION['last_booking']['booking_id']); ?></strong></p>
                <p>Please save this ID for your records. A confirmation will be sent to your contact number.</p>
                <p><em>Your booking status is currently "Pending" and will be reviewed by our admin.</em></p>
                <button onclick="window.location.href='booking_page.php'" class="btn-primary">Make Another Booking</button>
            </div>
        <?php else: ?>

            <?php if (!empty($errors)): ?>
                <!------------------- ERROR DISPLAY SECTION ------------------->
                <div class="error-message">
                    <h3>Please fix the following errors:</h3>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!--------------------- BOOKING FORM ------------------------>
            <form method="POST" action="" class="booking-form">

                <!-------------------------------- GUEST INFO -------------------------------->
                <section class="form-section">
                    <h2 class="section-title">Guest Information</h2>

                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name"
                            value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>"
                            placeholder="Enter your full name" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number <span class="required">*</span></label>
                        <input type="tel" id="contact_number" name="contact_number"
                            value="<?php echo htmlspecialchars($_POST['contact_number'] ?? ''); ?>"
                            placeholder="e.g., +63 912 345 6789" required>
                    </div>

                    <div class="form-group">
                        <label for="address">Address <span class="required">*</span></label>
                        <textarea id="address" name="address" rows="3"
                            placeholder="Enter your complete address" required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                </section>

                <!--------------------------- BOOKING DETAILS SECTION -------------------------->
                <section class="form-section">
                    <h2 class="section-title">Booking Details</h2>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="booking_date">Booking Date <span class="required">*</span></label>
                            <input type="date" id="booking_date" name="booking_date"
                                value="<?php echo htmlspecialchars($_POST['booking_date'] ?? ''); ?>"
                                min="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="shift">Shift <span class="required">*</span></label>
                            <select id="shift" name="shift" required>
                                <option value="">Select Shift</option>
                                <option value="day" <?php echo ($_POST['shift'] ?? '') === 'day' ? 'selected' : ''; ?>>Day Swimming (8:00 AM - 4:30 PM)</option>
                                <option value="night" <?php echo ($_POST['shift'] ?? '') === 'night' ? 'selected' : ''; ?>>Night Tour (8:00 PM - 4:30 AM)</option>
                                <option value="whole_day" <?php echo ($_POST['shift'] ?? '') === 'whole_day' ? 'selected' : ''; ?>>Overnight Stay (2:00 PM - 11:00 AM)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="num_adults">No. of Adults <span class="required">*</span></label>
                            <input type="number" id="num_adults" name="num_adults"
                                value="<?php echo htmlspecialchars($_POST['num_adults'] ?? '1'); ?>"
                                min="1" max="50" required>
                        </div>

                        <div class="form-group">
                            <label for="num_kids">No. of Kids</label>
                            <input type="number" id="num_kids" name="num_kids"
                                value="<?php echo htmlspecialchars($_POST['num_kids'] ?? '0'); ?>"
                                min="0" max="50">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="room">Room</label>
                            <select id="room" name="room">
                                <option value="">No Room</option>
                                <option value="standard" <?php echo ($_POST['room'] ?? '') === 'standard' ? 'selected' : ''; ?>>Standard Room</option>
                                <option value="deluxe" <?php echo ($_POST['room'] ?? '') === 'deluxe' ? 'selected' : ''; ?>>Deluxe Room</option>
                                <option value="family" <?php echo ($_POST['room'] ?? '') === 'family' ? 'selected' : ''; ?>>Family Room</option>
                                <option value="suite" <?php echo ($_POST['room'] ?? '') === 'suite' ? 'selected' : ''; ?>>Suite</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="swimming_type">Swimming Type</label>
                            <select id="swimming_type" name="swimming_type">
                                <option value="">No Swimming</option>
                                <option value="adult_pool" <?php echo ($_POST['swimming_type'] ?? '') === 'adult_pool' ? 'selected' : ''; ?>>Adult Pool</option>
                                <option value="kids_pool" <?php echo ($_POST['swimming_type'] ?? '') === 'kids_pool' ? 'selected' : ''; ?>>Kids Pool</option>
                                <option value="infinity_pool" <?php echo ($_POST['swimming_type'] ?? '') === 'infinity_pool' ? 'selected' : ''; ?>>Infinity Pool</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cottage">Cottage</label>
                        <select id="cottage" name="cottage">
                            <option value="">No Cottage</option>
                            <option value="small" <?php echo ($_POST['cottage'] ?? '') === 'small' ? 'selected' : ''; ?>>Umbrella Cottage (4-5 pax)</option>
                            <option value="medium" <?php echo ($_POST['cottage'] ?? '') === 'medium' ? 'selected' : ''; ?>>Family Cottage (10-15 pax)</option>
                            <option value="large" <?php echo ($_POST['cottage'] ?? '') === 'large' ? 'selected' : ''; ?>>Barkada Cottage (20-30 pax)</option>
                            <option value="pavilion" <?php echo ($_POST['cottage'] ?? '') === 'pavilion' ? 'selected' : ''; ?>>Silong (30-40 pax)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="others">Others (Special Requests)</label>
                        <textarea id="others" name="others" rows="3"
                            placeholder="Any special requests, dietary requirements, or additional services?"><?php echo htmlspecialchars($_POST['others'] ?? ''); ?></textarea>
                    </div>
                </section>

                <!----------------------------POLICY SECTION---------------------------->
                <section class="policy-section">
                    <h2 class="section-title">Booking Policy</h2>
                    <div class="policy-content">
                        <ol>
                            <li>Must be made 7 days before the date of reservation.</li>
                            <li>In an event of "No Show", Initial Deposit is no longer refundable.</li>
                            <li>Non-refundable unless accidents and god acts occur.</li>
                            <li>Once settled, payments are Non-refundable.</li>
                        </ol>
                        <div class="policy-agreement">
                            <label class="checkbox-label">
                                <input type="checkbox" name="agree_policy" required>
                                <span>I have read and agree to the booking policy <span class="required">*</span></span>
                            </label>
                        </div>
                    </div>
                </section>

                <!-------------------SUBMIT BUTTON-------------------->
                <div class="form-actions">
                    <button type="submit" class="booking-btn-primary">Submit Booking</button>
                    <button type="reset" class="booking-btn-secondary">Clear Form</button>
                </div>
            </form>

        <?php endif; ?>
    </div>
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