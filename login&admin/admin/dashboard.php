<?php
// admin/dashboard.php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Get dashboard statistics
$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('monday this week'));
$month_start = date('Y-m-01');

// Total reservations today
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE DATE(created_at) = ?");
$stmt->bind_param("s", $today);
$stmt->execute();
$today_bookings = $stmt->get_result()->fetch_assoc()['count'];

// Total reservations this week
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE DATE(created_at) >= ?");
$stmt->bind_param("s", $week_start);
$stmt->execute();
$week_bookings = $stmt->get_result()->fetch_assoc()['count'];

// Total reservations this month
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM reservations WHERE DATE(created_at) >= ?");
$stmt->bind_param("s", $month_start);
$stmt->execute();
$month_bookings = $stmt->get_result()->fetch_assoc()['count'];

// Pending bookings
$pending_bookings = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'")->fetch_assoc()['count'];

// Upcoming bookings (future booking dates, confirmed status)
$upcoming_bookings = $conn->query("
    SELECT 
        r.id,
        r.booking_id,
        r.full_name,
        r.booking_date,
        r.shift,
        r.status
    FROM reservations r
    WHERE r.booking_date >= CURDATE() 
    AND r.status = 'confirmed'
    ORDER BY r.booking_date ASC
    LIMIT 10
");

// Recent bookings (last 10 bookings created)
$recent_bookings = $conn->query("
    SELECT 
        r.id,
        r.booking_id,
        r.full_name,
        r.booking_date,
        r.shift,
        r.status,
        r.created_at
    FROM reservations r
    ORDER BY r.created_at DESC
    LIMIT 10
");

$page_title = 'Dashboard';
include 'includes/header.php';
?>

<div class="content-container">
    <h1 style="margin-bottom: 30px;">Dashboard Overview</h1>
    
    <!-- Statistics Cards -->
    <div class="dashboard-cards">
        <div class="card">
            <div class="card-icon blue"><i class="fa-solid fa-calendar-check"></i></div>
            <h3>Today's Bookings</h3>
            <div class="value"><?php echo $today_bookings; ?></div>
        </div>
        
        <div class="card">
            <div class="card-icon green"><i class="fa-solid fa-calendar-week"></i></div>
            <h3>This Week</h3>
            <div class="value"><?php echo $week_bookings; ?></div>
        </div>
        
        <div class="card">
            <div class="card-icon orange"><i class="fa-solid fa-calendar-days"></i></div>
            <h3>This Month</h3>
            <div class="value"><?php echo $month_bookings; ?></div>
        </div>
        
        <div class="card">
            <div class="card-icon red"><i class="fa-solid fa-hourglass-half"></i></div>
            <h3>Pending</h3>
            <div class="value"><?php echo $pending_bookings; ?></div>
        </div>
    </div>
    
    <!-- Upcoming Bookings -->
    <div class="data-table" style="margin-top: 30px; margin-bottom: 30px;">
        <h2 style="margin-bottom: 15px;">Upcoming Bookings (Confirmed)</h2>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Booking Date</th>
                    <th>Shift</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($upcoming_bookings && $upcoming_bookings->num_rows > 0): ?>
                    <?php while ($booking = $upcoming_bookings->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($booking['booking_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                            <td>
                                <?php 
                                $shifts = [
                                    'day' => 'Day Swimming',
                                    'night' => 'Night Tour',
                                    'whole_day' => 'Overnight'
                                ];
                                echo $shifts[$booking['shift']] ?? $booking['shift'];
                                ?>
                            </td>
                            <td><span class="badge badge-success"><?php echo ucfirst($booking['status']); ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="reservation_view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">No upcoming confirmed bookings</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Recent Activity -->
    <div class="data-table">
        <h2 style="margin-bottom: 15px;">Recent Bookings</h2>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Customer</th>
                    <th>Booking Date</th>
                    <th>Created</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($recent_bookings && $recent_bookings->num_rows > 0): ?>
                    <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($booking['booking_id']); ?></strong></td>
                            <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                            <td><small><?php echo date('M d, Y h:i A', strtotime($booking['created_at'])); ?></small></td>
                            <td>
                                <?php
                                $status_class = '';
                                switch ($booking['status']) {
                                    case 'confirmed': $status_class = 'badge-success'; break;
                                    case 'pending': $status_class = 'badge-warning'; break;
                                    case 'cancelled': $status_class = 'badge-danger'; break;
                                    case 'completed': $status_class = 'badge-info'; break;
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($booking['status']); ?></span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="reservation_view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px; color: #6b7280;">No bookings found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.card-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    font-size: 24px;
    color: white;
}



.card h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #6b7280;
    font-weight: 500;
}

.card .value {
    font-size: 36px;
    font-weight: bold;
    color: #111827;
}

.data-table {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.data-table h2 {
    margin-top: 0;
    color: #111827;
    font-size: 20px;
}

.data-table table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.data-table th {
    background: #f9fafb;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    color: #111827;
}

.data-table tr:last-child td {
    border-bottom: none;
}

.data-table tr:hover {
    background: #f9fafb;
}

.badge {
    padding: 5px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.badge-success { background: #d1fae5; color: #065f46; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-info { background: #dbeafe; color: #1e40af; }

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn {
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    display: inline-block;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

@media (max-width: 768px) {
    .dashboard-cards {
        grid-template-columns: 1fr;
    }
    
    .data-table {
        overflow-x: auto;
    }
}
</style>

<?php include 'includes/footer.php'; ?>