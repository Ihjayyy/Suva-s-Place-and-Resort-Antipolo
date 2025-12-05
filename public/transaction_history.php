<?php
require_once '../login&admin/config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('../login&admin/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's bookings/transactions
$query = "SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$bookings = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History | Suva's Place</title>
    <link rel="stylesheet" href="../public/assets/css/navbar.css">
    <link rel="stylesheet" href="../public/assets/css/shared.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../public/assets/images/suva's_place_logo.ico">
    <style>
        .container {
            max-width: 1000px;
            margin: 100px auto 50px;
            padding: 30px;
        }
        .page-header {
            margin-bottom: 30px;
        }
        .page-header h2 {
            color: #333;
        }
        .transaction-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .booking-id {
            font-weight: 600;
            color: #333;
        }
        .status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        .status.pending { background: #fff3cd; color: #856404; }
        .status.confirmed { background: #d4edda; color: #155724; }
        .status.cancelled { background: #f8d7da; color: #721c24; }
        .transaction-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .detail-item {
            display: flex;
            gap: 10px;
        }
        .detail-item i {
            color: #4CAF50;
            margin-top: 3px;
        }
        .detail-label {
            font-weight: 500;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .no-transactions {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="../public/assets/images/suva's_logo.png">
            </div>
            <ul class="nav-links">
                <li><a href="landing_page.php">Home</a></li>
                <li><a href="about_page.php">About us</a></li>
                <li><a href="gallery_page.php">Gallery</a></li>
                <li><a href="contacts_page.php">Contacts</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-history"></i> Transaction History</h2>
            <p>View all your bookings and transactions</p>
        </div>

        <?php if($bookings->num_rows > 0): ?>
            <?php while($booking = $bookings->fetch_assoc()): ?>
                <div class="transaction-card">
                    <div class="transaction-header">
                        <span class="booking-id">Booking #<?php echo $booking['id']; ?></span>
                        <span class="status <?php echo strtolower($booking['status']); ?>">
                            <?php echo ucfirst($booking['status']); ?>
                        </span>
                    </div>
                    <div class="transaction-details">
                        <div class="detail-item">
                            <i class="fas fa-calendar"></i>
                            <div>
                                <div class="detail-label">Check-in Date:</div>
                                <div class="detail-value"><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-check"></i>
                            <div>
                                <div class="detail-label">Check-out Date:</div>
                                <div class="detail-value"><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <div class="detail-label">Guests:</div>
                                <div class="detail-value"><?php echo $booking['guests']; ?> person(s)</div>
                            </div>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <div>
                                <div class="detail-label">Total Amount:</div>
                                <div class="detail-value">₱<?php echo number_format($booking['total_amount'], 2); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-transactions">
                <i class="fas fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>
                <h3>No Transactions Yet</h3>
                <p>You haven't made any bookings yet.</p>
                <a href="booking_page.php" style="display: inline-block; margin-top: 20px; padding: 12px 30px; background: #4CAF50; color: white; text-decoration: none; border-radius: 8px;">Make a Booking</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>