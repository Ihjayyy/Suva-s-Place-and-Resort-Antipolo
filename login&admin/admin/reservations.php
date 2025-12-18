<?php
// admin/reservations.php
require_once '../config/database.php';
require '../config/email_function.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $reservation_id = (int)$_POST['reservation_id'];
    $new_status = sanitize_input($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE reservations SET status = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("si", $new_status, $reservation_id);
    
    if ($stmt->execute()) {
        // Fetch user info
        $userStmt = $conn->prepare("SELECT full_name, email FROM reservations WHERE id = ?");
        $userStmt->bind_param("i", $reservation_id);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        
        if ($userRow = $userResult->fetch_assoc()) {
            $fullName = $userRow['full_name'];
            $email = $userRow['email'];

            // Send status update email
            sendStatusUpdateEmail($email, $fullName, $reservation_id, $new_status);
        }

        $userStmt->close();

        $success = 'Reservation status updated successfully';
        if (function_exists('log_activity')) {
            log_activity($_SESSION['user_id'], 'Update Reservation Status', "Reservation #$reservation_id status changed to $new_status");
        }
    }

    $stmt->close();
}


// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $reservation_id = (int)$_GET['id'];
    
    try {
        // Disable foreign key checks temporarily
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Delete reservation directly (no room_inventory table exists)
        $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
        $stmt->bind_param("i", $reservation_id);
        
        if ($stmt->execute()) {
            $success = 'Reservation deleted successfully';
            if (function_exists('log_activity')) {
                log_activity($_SESSION['user_id'], 'Delete Reservation', "Deleted reservation #$reservation_id");
            }
        } else {
            $error = 'Failed to delete reservation: ' . $stmt->error;
        }
        
        $stmt->close();
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
    } catch (Exception $e) {
        // Re-enable foreign key checks even if error occurs
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        $error = 'Failed to delete reservation: ' . $e->getMessage();
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$date_filter = isset($_GET['date']) ? sanitize_input($_GET['date']) : '';

// Build query - Updated to match actual database schema
$query = "SELECT r.* 
          FROM reservations r 
          WHERE 1=1";

if ($status_filter !== 'all') {
    $query .= " AND r.status = '" . $conn->real_escape_string($status_filter) . "'";
}

if (!empty($search)) {
    $query .= " AND (r.full_name LIKE '%" . $conn->real_escape_string($search) . "%' 
                OR r.booking_id LIKE '%" . $conn->real_escape_string($search) . "%' 
                OR r.contact_number LIKE '%" . $conn->real_escape_string($search) . "%' 
                OR r.email LIKE '%" . $conn->real_escape_string($search) . "%')";
}

if (!empty($date_filter)) {
    $query .= " AND DATE(r.check_in) = '" . $conn->real_escape_string($date_filter) . "'";
}

$query .= " ORDER BY r.created_at DESC";

$reservations = $conn->query($query);

// Get statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
    SUM(total_amount) as total_revenue
    FROM reservations";
$stats = $conn->query($stats_query)->fetch_assoc();

$page_title = 'Reservations';
include 'includes/header.php';
?>

<div class="content-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>Reservation Management</h1>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #3b82f6;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['total']); ?></h3>
                <p>Total Bookings</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #f59e0b;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['pending']); ?></h3>
                <p>Pending</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #10b981;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo number_format($stats['confirmed']); ?></h3>
                <p>Confirmed</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #8b5cf6;">
                <i class="fas fa-peso-sign"></i>
            </div>
            <div class="stat-info">
                <h3>₱<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                <p>Total Revenue</p>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filter-card">
        <form method="GET" action="">
            <div class="filter-grid">
                <div class="form-group">
                    <label for="search">
                        <i class="fas fa-search"></i> Search
                    </label>
                    <input type="text" id="search" name="search" 
                           placeholder="Name, Booking ID, Phone, Email..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="form-group">
                    <label for="status">
                        <i class="fas fa-filter"></i> Status
                    </label>
                    <select id="status" name="status">
                        <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                        <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date">
                        <i class="fas fa-calendar"></i> Check-in Date
                    </label>
                    <input type="date" id="date" name="date" 
                           value="<?php echo htmlspecialchars($date_filter); ?>">
                </div>
                
                <div class="form-group" style="display: flex; align-items: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        Apply Filters
                    </button>
                    <a href="reservations.php" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Reservations Table -->
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Check-in / Check-out</th>
                    <th>Guests</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reservations && $reservations->num_rows > 0): ?>
                    <?php while ($reservation = $reservations->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($reservation['booking_id']); ?></strong>
                                <br><small style="color: #999;">
                                    <i class="far fa-clock"></i>
                                    <?php echo date('M d, Y h:i A', strtotime($reservation['created_at'])); ?>
                                </small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($reservation['full_name']); ?></strong>
                                <br><small style="color: #666;">
                                    <i class="far fa-envelope"></i>
                                    <?php echo htmlspecialchars($reservation['email']); ?>
                                </small>
                            </td>
                            <td>
                                <i class="fas fa-phone"></i>
                                <?php echo htmlspecialchars($reservation['phone']); ?>
                            </td>
                            <td>
                                <strong>Check-in:</strong><br>
                                <?php echo date('M d, Y h:i A', strtotime($reservation['check_in'])); ?>
                                <br><br>
                                <strong>Check-out:</strong><br>
                                <?php echo date('M d, Y h:i A', strtotime($reservation['check_out'])); ?>
                            </td>
                            <td>
                                <i class="fas fa-users"></i>
                                <?php echo $reservation['num_adults']; ?> adults
                                <?php if ($reservation['num_kids'] > 0): ?>
                                    <br><small><?php echo $reservation['num_kids']; ?> kids</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: #FFE100; font-size: 1.1rem;">
                                    ₱<?php echo number_format($reservation['total_amount'], 2); ?>
                                </strong>
                            </td>
                            <td>
                                <form method="POST" action="" style="margin: 0;">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                    <select name="status" onchange="this.form.submit()" class="status-badge <?php 
                                        echo $reservation['status'] === 'confirmed' ? 'status-success' : 
                                            ($reservation['status'] === 'pending' ? 'status-warning' : 
                                            ($reservation['status'] === 'cancelled' ? 'status-danger' : 'status-info')); 
                                    ?>">
                                        <option value="pending" <?php echo $reservation['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $reservation['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo $reservation['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="completed" <?php echo $reservation['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="reservation_view.php?id=<?php echo $reservation['id']; ?>" 
                                       class="btn btn-sm btn-primary" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="reservation_edit.php?id=<?php echo $reservation['id']; ?>" 
                                       class="btn btn-sm btn-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=1&id=<?php echo $reservation['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this reservation? This action cannot be undone.')"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 40px; color: #999;">
                            <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                            No reservations found
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-info h3 {
    margin: 0;
    font-size: 2rem;
    color: #111827;
}

.stat-info p {
    margin: 5px 0 0;
    color: #6b7280;
    font-size: 0.95rem;
}

.filter-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.filter-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 20px;
    align-items: end;
}

@media (max-width: 1024px) {
    .filter-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 768px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
}

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-sm {
    padding: 8px 12px;
    font-size: 0.9rem;
}

.status-badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.status-success { background: #10b981; color: white; }
.status-warning { background: #f59e0b; color: white; }
.status-danger { background: #ef4444; color: white; }
.status-info { background: #3b82f6; color: white; }

.status-badge:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}

.alert {
    padding: 15px 20px;
    margin-bottom: 25px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
}

.data-table {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    overflow: hidden;
}

.data-table table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead {
    background: #f9fafb;
}

.data-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
}

.data-table td {
    padding: 15px;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: top;
}

.data-table tbody tr:hover {
    background: #f9fafb;
}
</style>

<?php include 'includes/footer.php'; ?>