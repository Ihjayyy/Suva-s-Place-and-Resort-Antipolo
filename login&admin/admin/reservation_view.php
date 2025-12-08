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

// Handle delete request
if (isset($_GET['delete']) && $_GET['delete'] == 1) {
    // Disable foreign key checks temporarily to avoid constraint issues
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
    $stmt->bind_param("i", $reservation_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Reservation deleted successfully!';
        if (function_exists('log_activity')) {
            log_activity($_SESSION['user_id'], 'Delete Reservation', "Deleted reservation #$reservation_id");
        }
        $stmt->close();
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        redirect('reservations.php');
    } else {
        $_SESSION['error_message'] = 'Failed to delete reservation: ' . $stmt->error;
        $stmt->close();
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    }
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

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <?php 
            echo htmlspecialchars($_SESSION['error_message']); 
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php 
            echo htmlspecialchars($_SESSION['success_message']); 
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

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
                    <span class="label">Check-in:</span>
                    <span class="value"><?php echo date('F d, Y h:i A', strtotime($reservation['check_in'])); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Check-out:</span>
                    <span class="value"><?php echo date('F d, Y h:i A', strtotime($reservation['check_out'])); ?></span>
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
                    <span class="label">Email:</span>
                    <span class="value"><?php echo htmlspecialchars($reservation['email']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="label">Contact Number:</span>
                    <span class="value"><?php echo htmlspecialchars($reservation['phone']); ?></span>
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

            <!-- Selected Services -->
            <div class="detail-section">
                <h2>Selected Services</h2>
                <?php 
                $selectedItems = !empty($reservation['selected_items']) ? 
                    json_decode($reservation['selected_items'], true) : [];
                
                if (!empty($selectedItems)):
                    foreach ($selectedItems as $item):
                ?>
                    <div class="service-item">
                        <div class="service-header">
                            <i class="fas fa-<?php echo strpos($item['name'], 'Casa') !== false || strpos($item['name'], 'Cuarto') !== false ? 'bed' : 'home'; ?>"></i>
                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                        </div>
                        <div class="service-details">
                            <span class="service-option"><?php echo htmlspecialchars($item['option']); ?></span>
                            <span class="service-price">₱<?php echo number_format($item['price'], 2); ?></span>
                        </div>
                    </div>
                <?php 
                    endforeach;
                else:
                ?>
                    <p><em>No services selected</em></p>
                <?php endif; ?>
            </div>

            <!-- Payment Information -->
            <div class="detail-section">
                <h2>Payment Information</h2>
                <div class="detail-item">
                    <span class="label">Payment Method:</span>
                    <span class="value">
                        <?php 
                        $paymentMethods = [
                            'onsite' => 'Pay at Resort',
                            'online' => 'Online Payment'
                        ];
                        echo $paymentMethods[$reservation['payment_method']] ?? 'Not specified';
                        ?>
                    </span>
                </div>
                <div class="detail-item">
                    <span class="label">Total Amount:</span>
                    <span class="value">
                        <strong style="color: #FFE100; font-size: 1.2rem;">
                            ₱<?php echo number_format($reservation['total_amount'] ?? 0, 2); ?>
                        </strong>
                    </span>
                </div>
                <?php if (!empty($reservation['proof_of_payment'])): ?>
                    <div class="detail-item" style="flex-direction: column; align-items: flex-start;">
                        <span class="label">Proof of Payment:</span>
                        <div style="margin-top: 10px; width: 100%;">
                            <?php 
                            // Check if the proof_of_payment already contains the full path
                            $proofValue = $reservation['proof_of_payment'];
                            
                            // If it already contains 'uploads/', use it as-is, otherwise build the path
                            if (strpos($proofValue, 'uploads/') === 0) {
                                // Already has full path from root
                                $possiblePaths = [
                                    '../' . $proofValue,
                                    '../../' . $proofValue,
                                    $proofValue
                                ];
                            } else {
                                // Just the filename, try multiple possible paths
                                $possiblePaths = [
                                    '../uploads/proof_of_payment/' . $proofValue,
                                    '../../uploads/proof_of_payment/' . $proofValue,
                                    '../public/uploads/proof_of_payment/' . $proofValue,
                                    'uploads/proof_of_payment/' . $proofValue,
                                    '../uploads/payment_proofs/' . $proofValue,
                                    '../../uploads/payment_proofs/' . $proofValue,
                                    '../public/uploads/payment_proofs/' . $proofValue,
                                    'uploads/payment_proofs/' . $proofValue
                                ];
                            }
                            
                            $proof_path = null;
                            foreach ($possiblePaths as $path) {
                                if (file_exists($path)) {
                                    $proof_path = $path;
                                    break;
                                }
                            }
                            
                            $file_ext = strtolower(pathinfo($proofValue, PATHINFO_EXTENSION));
                            
                            if ($proof_path && file_exists($proof_path)):
                                if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])): 
                            ?>
                                <a href="<?php echo $proof_path; ?>" target="_blank" class="proof-link">
                                    <img src="<?php echo $proof_path; ?>" 
                                         alt="Proof of Payment" 
                                         class="proof-image"
                                         style="max-width: 300px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); cursor: pointer;">
                                </a>
                                <div style="margin-top: 8px;">
                                    <a href="<?php echo $proof_path; ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-external-link-alt"></i> View Full Size
                                    </a>
                                    <a href="<?php echo $proof_path; ?>" 
                                       download 
                                       class="btn btn-sm btn-secondary">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            <?php 
                                elseif ($file_ext === 'pdf'): 
                            ?>
                                <div class="pdf-proof" style="text-align: center; padding: 20px; background: #f9fafb; border-radius: 8px;">
                                    <i class="fas fa-file-pdf" style="font-size: 3rem; color: #e74c3c;"></i>
                                    <p style="margin: 10px 0;"><?php echo htmlspecialchars($reservation['proof_of_payment']); ?></p>
                                    <a href="<?php echo $proof_path; ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View PDF
                                    </a>
                                    <a href="<?php echo $proof_path; ?>" 
                                       download 
                                       class="btn btn-sm btn-secondary">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                </div>
                            <?php 
                                endif;
                            else: 
                            ?>
                                <div style="padding: 15px; background: #fee2e2; border-radius: 8px; color: #991b1b;">
                                    <p style="margin: 0;"><i class="fas fa-exclamation-triangle"></i> File not found: <?php echo htmlspecialchars($proofValue); ?></p>
                                    <p style="margin: 5px 0 0; font-size: 0.9rem;">Looking for file in multiple locations...</p>
                                    <details style="margin-top: 10px; font-size: 0.85rem;">
                                        <summary style="cursor: pointer; opacity: 0.8;">Show searched paths</summary>
                                        <ul style="margin: 5px 0; padding-left: 20px;">
                                            <?php foreach ($possiblePaths as $path): ?>
                                                <li><?php echo htmlspecialchars($path); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </details>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="detail-item">
                        <span class="label">Proof of Payment:</span>
                        <span class="value"><em>No proof uploaded (Pay at Resort)</em></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Special Requests -->
            <?php if (!empty($reservation['additional_requests']) || !empty($reservation['notes'])): ?>
            <div class="detail-section" style="grid-column: 1 / -1;">
                <h2>Additional Information</h2>
                <?php if (!empty($reservation['additional_requests'])): ?>
                <div class="detail-item">
                    <span class="label">Special Requests:</span>
                    <span class="value"><?php echo htmlspecialchars($reservation['additional_requests']); ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($reservation['notes'])): ?>
                <div class="detail-item">
                    <span class="label">Notes:</span>
                    <span class="value"><?php echo nl2br(htmlspecialchars($reservation['notes'])); ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div class="detail-actions">
            <a href="reservation_edit.php?id=<?php echo $reservation['id']; ?>" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Reservation
            </a>
            <button onclick="confirmDelete(<?php echo $reservation['id']; ?>)" class="btn btn-danger">
                <i class="fas fa-trash"></i> Delete Reservation
            </button>
        </div>
    </div>
</div>

<script>
function confirmDelete(id) {
    if (confirm('Are you sure you want to delete this reservation? This action cannot be undone.')) {
        window.location.href = 'reservation_view.php?id=' + id + '&delete=1';
    }
}
</script>

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

.service-item {
    background: white;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 10px;
    border-left: 3px solid #FFE100;
}

.service-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
}

.service-header i {
    color: #FFE100;
    font-size: 1.2rem;
}

.service-header strong {
    font-size: 1.1rem;
    color: #3C2A21;
}

.service-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.service-option {
    color: #6b7280;
    font-size: 0.9rem;
}

.service-price {
    font-weight: 700;
    color: #FFE100;
    font-size: 1.1rem;
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

.proof-image {
    transition: transform 0.3s ease;
}

.proof-image:hover {
    transform: scale(1.05);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.875rem;
    margin-right: 8px;
}

.proof-link {
    display: inline-block;
}

.alert {
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 500;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 2px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 2px solid #ef4444;
}

.alert i {
    font-size: 1.2rem;
}
</style>

<?php include 'includes/footer.php'; ?>