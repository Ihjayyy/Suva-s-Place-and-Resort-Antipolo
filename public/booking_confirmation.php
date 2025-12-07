<?php
require_once '../login&admin/config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('../login&admin/login.php');
}

// Get booking ID from URL
$bookingId = isset($_GET['id']) ? sanitize_input($_GET['id']) : '';

if (empty($bookingId)) {
    redirect('booking_page.php');
}

// Fetch booking details
$stmt = $conn->prepare("SELECT r.*, u.username FROM reservations r 
                        LEFT JOIN users u ON r.user_id = u.id 
                        WHERE r.booking_id = ? AND r.user_id = ?");
$stmt->bind_param("si", $bookingId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    redirect('booking_page.php');
}

// Fetch booking items
$itemsStmt = $conn->prepare("SELECT * FROM reservation_items WHERE reservation_id = ?");
$itemsStmt->bind_param("i", $booking['id']);
$itemsStmt->execute();
$items = $itemsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation | Suva's Place and Resort</title>
    
    <link rel="stylesheet" href="../public/assets/css/navbar.css">
    <link rel="stylesheet" href="../public/assets/css/shared.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../public/assets/images/suva's_place_logo.ico">
    
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .confirmation-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        
        .success-icon {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .success-icon i {
            font-size: 5rem;
            color: #10b981;
            animation: scaleIn 0.5s ease;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .confirmation-title {
            text-align: center;
            color: #111827;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .confirmation-subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 40px;
        }
        
        .booking-id-box {
            background: #f0fdf4;
            border: 2px dashed #10b981;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .booking-id-label {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .booking-id-value {
            color: #2c5f2d;
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 2px;
        }
        
        .detail-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            color: #374151;
            font-size: 1.2rem;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .detail-label {
            color: #6b7280;
            font-weight: 600;
        }
        
        .detail-value {
            color: #111827;
            text-align: right;
        }
        
        .items-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .items-list li {
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .item-name {
            font-weight: 600;
            color: #111827;
        }
        
        .item-option {
            color: #6b7280;
            font-size: 0.9rem;
            display: block;
            margin-top: 5px;
        }
        
        .item-price {
            color: #2c5f2d;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .total-box {
            background: #2c5f2d;
            color: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        
        .total-label {
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .total-amount {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .policy-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        
        .policy-box h4 {
            color: #856404;
            margin-top: 0;
            margin-bottom: 15px;
        }
        
        .policy-box ol {
            color: #856404;
            padding-left: 20px;
            margin: 0;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: #2c5f2d;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1e4620;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 95, 45, 0.3);
            color: white;
        }
        
        .btn-secondary {
            background: white;
            color: #2c5f2d;
            border: 2px solid #2c5f2d;
        }
        
        .btn-secondary:hover {
            background: #f0fdf4;
        }
        
        @media print {
            .action-buttons, nav, footer {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .confirmation-container {
                padding: 10px;
            }
            
            .confirmation-card {
                padding: 20px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            <img src="../public/assets/images/suva's_logo_white.png">
        </div>
    </div>
</nav>

<div class="confirmation-container">
    <div class="confirmation-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="confirmation-title">Booking Confirmed!</h1>
        <p class="confirmation-subtitle">Your reservation has been successfully processed</p>
        
        <div class="booking-id-box">
            <div class="booking-id-label">Your Booking ID</div>
            <div class="booking-id-value"><?php echo htmlspecialchars($booking['booking_id']); ?></div>
        </div>
        
        <!-- Guest Information -->
        <div class="detail-section">
            <h3 class="section-title">
                <i class="fas fa-user"></i>
                Guest Information
            </h3>
            <div class="detail-row">
                <span class="detail-label">Full Name:</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['full_name']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Contact Number:</span>
                <span class="detail-value"><?php echo htmlspecialchars($booking['phone']); ?></span>
            </div>
        </div>
        
        <!-- Booking Details -->
        <div class="detail-section">
            <h3 class="section-title">
                <i class="fas fa-calendar-alt"></i>
                Booking Details
            </h3>
            <div class="detail-row">
                <span class="detail-label">Check-in:</span>
                <span class="detail-value"><?php echo date('F d, Y h:i A', strtotime($booking['check_in'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Check-out:</span>
                <span class="detail-value"><?php echo date('F d, Y h:i A', strtotime($booking['check_out'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Guests:</span>
                <span class="detail-value">
                    <?php echo $booking['num_adults']; ?> Adult(s)
                    <?php if ($booking['num_kids'] > 0): ?>
                        , <?php echo $booking['num_kids']; ?> Kid(s)
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="detail-value">
                    <strong style="color: #f59e0b;"><?php echo strtoupper($booking['status']); ?></strong>
                </span>
            </div>
        </div>
        
        <!-- Selected Services -->
        <div class="detail-section">
            <h3 class="section-title">
                <i class="fas fa-list"></i>
                Selected Accommodations
            </h3>
            <ul class="items-list">
                <?php while ($item = $items->fetch_assoc()): ?>
                <li>
                    <div>
                        <div class="item-name"><?php echo htmlspecialchars($item['service_name']); ?></div>
                        <span class="item-option"><?php echo htmlspecialchars($item['service_option']); ?></span>
                    </div>
                    <div class="item-price">₱<?php echo number_format($item['price'], 2); ?></div>
                </li>
                <?php endwhile; ?>
            </ul>
            
            <div class="total-box">
                <div class="total-label">Total Amount</div>
                <div class="total-amount">₱<?php echo number_format($booking['total_amount'], 2); ?></div>
            </div>
        </div>
        
        <!-- Booking Policy -->
        <div class="policy-box">
            <h4><i class="fas fa-info-circle"></i> Important Reminders</h4>
            <ol>
                <li>Please present this booking ID upon arrival</li>
                <li>Reschedule must be made 7 days before the date of reservation</li>
                <li>In an event of "No Show" Initial Deposit is no longer refundable</li>
                <li>A confirmation email has been sent to <?php echo htmlspecialchars($booking['email']); ?></li>
            </ol>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Print Confirmation
            </button>
            <a href="landing_page.php" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-logo">
            <img src="../public/assets/images/suva's_logo_white.png">
        </div>
        <p style="text-align: center; color: #6b7280; margin-top: 20px;">
            Thank you for choosing Suva's Place and Resort!
        </p>
    </div>
    <div class="footer-bottom">
        <hr />
        <p>©2025 Suva's Place Resort Antipolo. All rights reserved.</p>
    </div>
</footer>

</body>
</html>