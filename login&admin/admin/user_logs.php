<?php
// admin/user_logs.php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$records_per_page = 50;
$offset = ($page - 1) * $records_per_page;

// Filters
$user_filter = isset($_GET['user']) ? intval($_GET['user']) : 0;
$action_filter = isset($_GET['action']) ? $_GET['action'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';

// Build query
$where_conditions = [];
$params = [];
$types = '';

if ($user_filter > 0) {
    $where_conditions[] = "al.user_id = ?";
    $params[] = $user_filter;
    $types .= 'i';
}

if (!empty($action_filter)) {
    $where_conditions[] = "al.action = ?";
    $params[] = $action_filter;
    $types .= 's';
}

if (!empty($date_from)) {
    $where_conditions[] = "DATE(al.created_at) >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $where_conditions[] = "DATE(al.created_at) <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get total records
$count_query = "SELECT COUNT(*) as count FROM activity_logs al $where_clause";
$stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['count'];
$total_pages = ceil($total_records / $records_per_page);

// Get activity logs
$logs_query = "
    SELECT 
        al.*,
        u.full_name,
        u.email,
        u.role
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    $where_clause
    ORDER BY al.created_at DESC
    LIMIT ? OFFSET ?
";

$params[] = $records_per_page;
$params[] = $offset;
$types .= 'ii';

$stmt = $conn->prepare($logs_query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$logs = $stmt->get_result();

// Get all users for filter
$users = $conn->query("SELECT id, full_name FROM users ORDER BY full_name");

// Get statistics
$today_activities = $conn->query("SELECT COUNT(*) as count FROM activity_logs WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
$week_activities = $conn->query("SELECT COUNT(*) as count FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'];
$total_activities = $conn->query("SELECT COUNT(*) as count FROM activity_logs")->fetch_assoc()['count'];

$page_title = 'Activity Logs';
include 'includes/header.php';
?>

<div class="content-container">
    <div class="page-header">
        <div>
            <h1>Activity Logs & Audit Trail</h1>
            <p class="page-subtitle">Monitor all user activities and system changes</p>
        </div>
        <a href="users.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Users
        </a>
    </div>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>Today's Activities</h3>
                <p class="stat-value"><?php echo $today_activities; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fa-solid fa-calendar-week"></i>
            </div>
            <div class="stat-content">
                <h3>This Week</h3>
                <p class="stat-value"><?php echo $week_activities; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fa-solid fa-database"></i>
            </div>
            <div class="stat-content">
                <h3>Total Activities</h3>
                <p class="stat-value"><?php echo number_format($total_activities); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Filters -->
    <div class="filters-card">
        <h3><i class="fa-solid fa-filter"></i> Filters</h3>
        <form method="GET" id="filterForm">
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="user">User</label>
                    <select name="user" id="user">
                        <option value="">All Users</option>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <option value="<?php echo $user['id']; ?>" 
                                    <?php echo ($user_filter == $user['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['full_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="action">Action Type</label>
                    <select name="action" id="action">
                        <option value="">All Actions</option>
                        <option value="login" <?php echo ($action_filter === 'login') ? 'selected' : ''; ?>>Login</option>
                        <option value="logout" <?php echo ($action_filter === 'logout') ? 'selected' : ''; ?>>Logout</option>
                        <option value="user_created" <?php echo ($action_filter === 'user_created') ? 'selected' : ''; ?>>User Created</option>
                        <option value="user_updated" <?php echo ($action_filter === 'user_updated') ? 'selected' : ''; ?>>User Updated</option>
                        <option value="user_deleted" <?php echo ($action_filter === 'user_deleted') ? 'selected' : ''; ?>>User Deleted</option>
                        <option value="user_status_changed" <?php echo ($action_filter === 'user_status_changed') ? 'selected' : ''; ?>>Status Changed</option>
                        <option value="reservation_created" <?php echo ($action_filter === 'reservation_created') ? 'selected' : ''; ?>>Reservation Created</option>
                        <option value="reservation_updated" <?php echo ($action_filter === 'reservation_updated') ? 'selected' : ''; ?>>Reservation Updated</option>
                        <option value="settings_updated" <?php echo ($action_filter === 'settings_updated') ? 'selected' : ''; ?>>Settings Updated</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="date_from">Date From</label>
                    <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                </div>
                
                <div class="filter-group">
                    <label for="date_to">Date To</label>
                    <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Apply Filters
                </button>
                <a href="user_logs.php" class="btn btn-secondary">
                    <i class="fa-solid fa-rotate-right"></i> Reset
                </a>
            </div>
        </form>
    </div>
    
    <!-- Activity Logs Table -->
    <div class="data-table">
        <div class="table-header">
            <h2>Activity Logs</h2>
            <div class="table-info">
                Showing <?php echo ($offset + 1); ?> to <?php echo min($offset + $records_per_page, $total_records); ?> of <?php echo number_format($total_records); ?> entries
            </div>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs && $logs->num_rows > 0): ?>
                        <?php while ($log = $logs->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $log['id']; ?></strong></td>
                                <td>
                                    <div class="user-info">
                                        <?php if ($log['full_name']): ?>
                                            <div class="user-avatar-small">
                                                <?php echo strtoupper(substr($log['full_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="user-name"><?php echo htmlspecialchars($log['full_name']); ?></div>
                                                <small><?php echo htmlspecialchars($log['email']); ?></small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Deleted User</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="action-badge action-<?php echo $log['action']; ?>">
                                        <?php
                                        $action_icons = [
                                            'login' => 'right-to-bracket',
                                            'logout' => 'right-from-bracket',
                                            'user_created' => 'user-plus',
                                            'user_updated' => 'user-pen',
                                            'user_deleted' => 'user-minus',
                                            'user_status_changed' => 'toggle-on',
                                            'reservation_created' => 'calendar-plus',
                                            'reservation_updated' => 'calendar-check',
                                            'settings_updated' => 'gear'
                                        ];
                                        $icon = $action_icons[$log['action']] ?? 'circle-info';
                                        ?>
                                        <i class="fa-solid fa-<?php echo $icon; ?>"></i>
                                        <?php echo ucwords(str_replace('_', ' ', $log['action'])); ?>
                                    </span>
                                </td>
                                <td class="description-cell">
                                    <?php echo htmlspecialchars($log['description']); ?>
                                </td>
                                <td>
                                    <code class="ip-address"><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></code>
                                </td>
                                <td>
                                    <div class="timestamp">
                                        <div><?php echo date('M d, Y', strtotime($log['created_at'])); ?></div>
                                        <small><?php echo date('h:i:s A', strtotime($log['created_at'])); ?></small>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px;">
                                <i class="fa-solid fa-inbox" style="font-size: 48px; color: #d1d5db; margin-bottom: 10px;"></i>
                                <p style="color: #6b7280;">No activity logs found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo ($page - 1) . (isset($_GET['user']) ? '&user=' . $_GET['user'] : '') . (isset($_GET['action']) ? '&action=' . $_GET['action'] : '') . (isset($_GET['date_from']) ? '&date_from=' . $_GET['date_from'] : '') . (isset($_GET['date_to']) ? '&date_to=' . $_GET['date_to'] : ''); ?>" class="btn btn-sm">
                        <i class="fa-solid fa-chevron-left"></i> Previous
                    </a>
                <?php endif; ?>
                
                <div class="page-numbers">
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <a href="?page=<?php echo $i . (isset($_GET['user']) ? '&user=' . $_GET['user'] : '') . (isset($_GET['action']) ? '&action=' . $_GET['action'] : '') . (isset($_GET['date_from']) ? '&date_from=' . $_GET['date_from'] : '') . (isset($_GET['date_to']) ? '&date_to=' . $_GET['date_to'] : ''); ?>" 
                           class="page-number <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo ($page + 1) . (isset($_GET['user']) ? '&user=' . $_GET['user'] : '') . (isset($_GET['action']) ? '&action=' . $_GET['action'] : '') . (isset($_GET['date_from']) ? '&date_from=' . $_GET['date_from'] : '') . (isset($_GET['date_to']) ? '&date_to=' . $_GET['date_to'] : ''); ?>" class="btn btn-sm">
                        Next <i class="fa-solid fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.content-container {
    padding: 30px;
    max-width: 1600px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 30px;
}

.page-header h1 {
    margin: 0 0 5px 0;
    color: #111827;
}

.page-subtitle {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
    font-size: 24px;
    color: white;
}

.stat-icon.blue { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.stat-icon.green { background: linear-gradient(135deg, #10b981, #059669); }
.stat-icon.orange { background: linear-gradient(135deg, #f59e0b, #d97706); }

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #6b7280;
}

.stat-value {
    margin: 0;
    font-size: 28px;
    font-weight: bold;
    color: #111827;
}

.filters-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 25px;
    margin-bottom: 30px;
}

.filters-card h3 {
    margin: 0 0 20px 0;
    color: #111827;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.filters-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 15px;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    color: #374151;
    font-weight: 500;
    font-size: 13px;
}

.filter-group select,
.filter-group input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.filter-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.data-table {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.table-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-header h2 {
    margin: 0;
    font-size: 18px;
    color: #111827;
}

.table-info {
    color: #6b7280;
    font-size: 14px;
}

.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: #f9fafb;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #374151;
    font-size: 13px;
    white-space: nowrap;
}

td {
    padding: 15px 12px;
    border-bottom: 1px solid #e5e7eb;
}

tr:last-child td {
    border-bottom: none;
}

tr:hover {
    background: #f9fafb;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-avatar-small {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
}

.user-name {
    font-weight: 500;
    color: #111827;
}

.action-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.action-login { background: #dbeafe; color: #1e40af; }
.action-logout { background: #e5e7eb; color: #374151; }
.action-user_created { background: #d1fae5; color: #065f46; }
.action-user_updated { background: #fed7aa; color: #92400e; }
.action-user_deleted { background: #fee2e2; color: #991b1b; }
.action-user_status_changed { background: #e0e7ff; color: #4338ca; }
.action-reservation_created { background: #d1fae5; color: #065f46; }
.action-reservation_updated { background: #dbeafe; color: #1e40af; }
.action-settings_updated { background: #e0e7ff; color: #4338ca; }

.description-cell {
    max-width: 400px;
    color: #6b7280;
    font-size: 13px;
}

.ip-address {
    background: #f3f4f6;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #374151;
    font-family: 'Courier New', monospace;
}

.timestamp {
    font-size: 13px;
}

.timestamp small {
    color: #9ca3af;
    font-size: 11px;
}

.text-muted {
    color: #9ca3af;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    padding: 20px;
    border-top: 1px solid #e5e7eb;
}

.page-numbers {
    display: flex;
    gap: 5px;
}

.page-number {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    text-decoration: none;
    color: #374151;
    font-size: 14px;
    transition: all 0.2s;
}

.page-number:hover {
    background: #f9fafb;
}

.page-number.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
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
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 13px;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .filters-grid {
        grid-template-columns: 1fr;
    }
    
    .table-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<?php include 'includes/footer.php'; ?>