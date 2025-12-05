<?php
// admin/reservations.php

require_once '../config/database.php';

if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$success = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $reservation_id = (int)$_POST['reservation_id'];
    $new_status = sanitize_input($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $reservation_id);
    
    if ($stmt->execute()) {
        $success = 'Reservation status updated successfully';
        log_activity($_SESSION['user_id'], 'Update Reservation Status', "Reservation #$reservation_id status changed to $new_status");
    } else {
        $error = 'Failed to update reservation status';
    }
}

// Handle delete
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $reservation_id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
    $stmt->bind_param("i", $reservation_id);
    
    if ($stmt->execute()) {
        $success = 'Reservation deleted successfully';
        log_activity($_SESSION['user_id'], 'Delete Reservation', "Deleted reservation #$reservation_id");
    } else {
        $error = 'Failed to delete reservation';
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build query
$query = "SELECT * FROM reservations WHERE 1=1";

if ($status_filter !== 'all') {
    $query .= " AND status = '" . $conn->real_escape_string($status_filter) . "'";
}

if (!empty($search)) {
    $query .= " AND (full_name LIKE '%$search%' OR booking_id LIKE '%$search%' OR contact_number LIKE '%$search%')";
}

$query .= " ORDER BY created_at DESC";

$reservations = $conn->query($query);

$page_title = 'Reservations';
include 'includes/header.php';
?>

<div class="content-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>All Reservations</h1>
        <a href="reservation_add.php" class="btn btn-primary">+ New Booking</a>
    </div>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="data-table" style="margin-bottom: 20px;">
        <form method="GET" action="" style="display: flex; gap: 15px; align-items: end;">
            <div class="form-group" style="margin: 0; flex: 1;">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" placeholder="Search by name, booking ID, or contact..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="form-group" style="margin: 0; min-width: 200px;">
                <label for="status">Filter by Status</label>
                <select id="status" name="status">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="confirmed" <?php echo $status_filter === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Apply Filters</button>
            <a href="reservations.php" class="btn btn-secondary">Reset</a>
        </form>
    </div>
    
    <!-- Reservations Table -->
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer Name</th>
                    <th>Contact</th>
                    <th>Booking Date</th>
                    <th>Shift</th>
                    <th>Guests</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reservations && $reservations->num_rows > 0): ?>
                    <?php while ($reservation = $reservations->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($reservation['booking_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($reservation['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['contact_number']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($reservation['booking_date'])); ?></td>
                            <td>
                                <?php 
                                $shifts = [
                                    'day' => 'Day Swimming',
                                    'night' => 'Night Tour',
                                    'whole_day' => 'Overnight'
                                ];
                                echo $shifts[$reservation['shift']] ?? $reservation['shift'];
                                ?>
                            </td>
                            <td>
                                <small>
                                    Adults: <?php echo $reservation['num_adults']; ?><br>
                                    Kids: <?php echo $reservation['num_kids']; ?>
                                </small>
                            </td>
                            <td>
                                <form method="POST" action="" style="margin: 0;">
                                    <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                    <select name="status" onchange="this.form.submit()" class="badge <?php 
                                        echo $reservation['status'] === 'confirmed' ? 'badge-success' : 
                                            ($reservation['status'] === 'pending' ? 'badge-warning' : 
                                            ($reservation['status'] === 'cancelled' ? 'badge-danger' : 'badge-info')); 
                                    ?>" style="border: none; cursor: pointer;">
                                        <option value="pending" <?php echo $reservation['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="confirmed" <?php echo $reservation['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="cancelled" <?php echo $reservation['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        <option value="completed" <?php echo $reservation['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td><small><?php echo date('M d, Y', strtotime($reservation['created_at'])); ?></small></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="reservation_view.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                    <a href="reservation_edit.php?id=<?php echo $reservation['id']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                    <a href="?delete=1&id=<?php echo $reservation['id']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Are you sure you want to delete this reservation?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align: center;">No reservations found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

.badge {
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.badge-success { background: #10b981; color: white; }
.badge-warning { background: #f59e0b; color: white; }
.badge-danger { background: #ef4444; color: white; }
.badge-info { background: #3b82f6; color: white; }

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 4px;
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
</style>

<?php include 'includes/footer.php'; ?>