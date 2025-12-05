<?php
// admin/reservation_view.php
require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$reservation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($reservation_id <= 0) {
    redirect('reservations.php');
}

// Fetch reservation details
$stmt = $conn->prepare("SELECT * FROM reservations WHERE id = ?");
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$result = $stmt->get_result();
$reservation = $result->fetch_assoc();

if (!$reservation) {
    redirect('reservations.php');
}

$page_title = 'View Reservation';
include 'includes/header.php';
?>

<div class="content-container">
    <div style="margin-bottom: 30px;">
        <a href="reservations.php" class="btn btn-secondary">← Back to Reservations</a>
    </div>

    <div class="reservation-details">
        <div class="detail-header">
            <h1>Reservation Details</h1>
            <span class="badge <?php 
                echo $reservation['status'] === 'confirmed' ? 'badge-success' : 
                    ($reservation['status'] === 'pending' ? 'badge-warning' : 
                    ($reservation['status'] === 'cancelled' ? 'badge-danger' : 'badge-info')); 
            ?>">
                <?php echo strtoupper($reservation['status']); ?>
            </span>
        </div>

        <div class="detail-grid">
            <!-- Booking Information -->
            <div class="detail-section">
                <h2>Booking Information</h2>
                <div class="detail-item">
                    <span class="label">Booking ID:</span>
                    <span class="value"><strong><?php echo htmlspecialchars($reservation['booking_id']); ?></strong></span>
                </div>
                <div class="detail-item">
                    <span class="label">Booking Date:</span>
                    <span class="value"><?php echo date('F d, Y', strtotime($reservation['booking_date'])); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Shift:</span>
                    <span class="value">
                        <?php 
                        $shifts = [
                            'day' => 'Day Swimming (8:00 AM - 4:30 PM)',
                            'night' => 'Night Tour (8:00 PM - 4:30 AM)',
                            'whole_day' => 'Overnight Stay (2:00 PM - 11:00 AM)'
                        ];
                        echo $shifts[$reservation['shift']] ?? $reservation['shift'];
                        ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="label">Created:</span>
                    <span class="value"><?php echo date('F d, Y h:i A', strtotime($reservation['created_at'])); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Last Updated:</span>
                    <span class="value"><?php echo date('F d, Y h:i A', strtotime($reservation['updated_at'])); ?></span>
                </div>
            </div>

            <!-- Guest Information -->
            <div class="detail-section">
                <h2>Guest Information</h2>
                <div class="detail-item">
                    <span class="label">Full Name:</span>
                    <span class="value"><?php echo htmlspecialchars($reservation['full_name']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Contact Number:</span>
                    <span class="value"><?php echo htmlspecialchars($reservation['contact_number']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Address:</span>
                    <span class="value"><?php echo nl2br(htmlspecialchars($reservation['address'])); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Number of Adults:</span>
                    <span class="value"><?php echo $reservation['num_adults']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Number of Kids:</span>
                    <span class="value"><?php echo $reservation['num_kids']; ?></span>
                </div>
            </div>

            <!-- Services -->
            <div class="detail-section">
                <h2>Services & Amenities</h2>
                <div class="detail-item">
                    <span class="label">Room:</span>
                    <span class="value">
                        <?php 
                        if (empty($reservation['room'])) {
                            echo '<em>No Room</em>';
                        } else {
                            $rooms = [
                                'standard' => 'Standard Room',
                                'deluxe' => 'Deluxe Room',
                                'family' => 'Family Room',
                                'suite' => 'Suite'
                            ];
                            echo $rooms[$reservation['room']] ?? $reservation['room'];
                        }
                        ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="label">Swimming Type:</span>
                    <span class="value">
                        <?php 
                        if (empty($reservation['swimming_type'])) {
                            echo '<em>No Swimming</em>';
                        } else {
                            $pools = [
                                'adult_pool' => 'Adult Pool',
                                'kids_pool' => 'Kids Pool',
                                'infinity_pool' => 'Infinity Pool'
                            ];
                            echo $pools[$reservation['swimming_type']] ?? $reservation['swimming_type'];
                        }
                        ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="label">Cottage:</span>
                    <span class="value">
                        <?php 
                        if (empty($reservation['cottage'])) {
                            echo '<em>No Cottage</em>';
                        } else {
                            $cottages = [
                                'small' => 'Umbrella Cottage (4-5 pax)',
                                'medium' => 'Family Cottage (10-15 pax)',
                                'large' => 'Barkada Cottage (20-30 pax)',
                                'pavilion' => 'Silong (30-40 pax)'
                            ];
                            echo $cottages[$reservation['cottage']] ?? $reservation['cottage'];
                        }
                        ?>
                    </span>
                </div>
            </div>

            <!-- Special Requests -->
            <?php if (!empty($reservation['others'])): ?>
            <div class="detail-section" style="grid-column: 1 / -1;">
                <h2>Special Requests</h2>
                <div class="detail-item">
                    <span class="value"><?php echo nl2br(htmlspecialchars($reservation['others'])); ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="detail-actions">
            <a href="reservation_edit.php?id=<?php echo $reservation['id']; ?>" class="btn btn-primary">Edit Reservation</a>
            <a href="?delete=1&id=<?php echo $reservation['id']; ?>" 
               class="btn btn-danger" 
               onclick="return confirm('Are you sure you want to delete this reservation?')">Delete Reservation</a>
        </div>
    </div>
</div>

<style>
.reservation-details {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
}

.detail-header h1 {
    margin: 0;
    font-size: 28px;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.detail-section {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
}

.detail-section h2 {
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 10px;
}

.detail-item {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px solid #e5e7eb;
}

.detail-item:last-child {
    border-bottom: none;
}

.detail-item .label {
    font-weight: 600;
    color: #6b7280;
    min-width: 150px;
}

.detail-item .value {
    flex: 1;
    color: #111827;
}

.detail-actions {
    display: flex;
    gap: 15px;
    padding-top: 20px;
    border-top: 2px solid #e5e7eb;
}

.badge {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
}

.badge-success { background: #10b981; color: white; }
.badge-warning { background: #f59e0b; color: white; }
.badge-danger { background: #ef4444; color: white; }
.badge-info { background: #3b82f6; color: white; }
</style>

<?php include 'includes/footer.php'; ?>