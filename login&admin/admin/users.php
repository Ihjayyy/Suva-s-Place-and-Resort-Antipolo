<?php
// admin/users.php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'toggle_status':
                $user_id = intval($_POST['user_id']);
                $current_status = $_POST['current_status'];
                $new_status = ($current_status === 'active') ? 'inactive' : 'active';
                
                $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
                $stmt->bind_param("si", $new_status, $user_id);
                
                if ($stmt->execute()) {
                    // Log activity
                    log_activity($_SESSION['user_id'], 'user_status_changed', "Changed user ID $user_id status to $new_status");
                    $_SESSION['success'] = "User status updated successfully";
                } else {
                    $_SESSION['error'] = "Failed to update user status";
                }
                break;
                
            case 'delete_user':
                $user_id = intval($_POST['user_id']);
                
                // Prevent deleting own account
                if ($user_id == $_SESSION['user_id']) {
                    $_SESSION['error'] = "You cannot delete your own account";
                } else {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->bind_param("i", $user_id);
                    
                    if ($stmt->execute()) {
                        log_activity($_SESSION['user_id'], 'user_deleted', "Deleted user ID $user_id");
                        $_SESSION['success'] = "User deleted successfully";
                    } else {
                        $_SESSION['error'] = "Failed to delete user";
                    }
                }
                break;
        }
        
        header("Location: users.php");
        exit();
    }
}

// Check if 'role' column exists, fallback to 'user_type'
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
$has_role_column = $check_column->num_rows > 0;

// Build query based on available columns
if ($has_role_column) {
    $role_field = 'u.role';
} else {
    $role_field = 'u.user_type as role';
}

// Get all users with their roles
$users_query = "
    SELECT 
        u.id,
        u.full_name,
        u.email,
        $role_field,
        u.status,
        u.created_at,
        u.last_login,
        COUNT(DISTINCT al.id) as activity_count
    FROM users u
    LEFT JOIN activity_logs al ON u.id = al.user_id
    WHERE u.user_type = 'admin'
    GROUP BY u.id
    ORDER BY u.created_at DESC
";
$users = $conn->query($users_query);

// Get statistics - use appropriate field
if ($has_role_column) {
    $total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin'")->fetch_assoc()['count'];
    $active_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin' AND status = 'active'")->fetch_assoc()['count'];
    $admin_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
    $inactive_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin' AND status = 'inactive'")->fetch_assoc()['count'];
} else {
    $total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin'")->fetch_assoc()['count'];
    $active_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin' AND status = 'active'")->fetch_assoc()['count'];
    $admin_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin'")->fetch_assoc()['count'];
    $inactive_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE user_type = 'admin' AND status = 'inactive'")->fetch_assoc()['count'];
}

$page_title = 'User Management';
include 'includes/header.php';
?>

<div class="content-container">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fa-solid fa-check-circle"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-exclamation-circle"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <div class="page-header">
        <h1>User Management</h1>
        <div class="header-actions">
            <a href="user_add.php" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Add New Admin
            </a>
            <a href="user_roles.php" class="btn btn-secondary">
                <i class="fa-solid fa-shield-halved"></i> Manage Roles
            </a>
            <a href="user_logs.php" class="btn btn-secondary">
                <i class="fa-solid fa-clock-rotate-left"></i> Activity Logs
            </a>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Total Admin Users</h3>
                <p class="stat-value"><?php echo $total_users; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div class="stat-content">
                <h3>Active Users</h3>
                <p class="stat-value"><?php echo $active_users; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <div class="stat-content">
                <h3>Administrators</h3>
                <p class="stat-value"><?php echo $admin_users; ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon red">
                <i class="fa-solid fa-user-slash"></i>
            </div>
            <div class="stat-content">
                <h3>Inactive Users</h3>
                <p class="stat-value"><?php echo $inactive_users; ?></p>
            </div>
        </div>
    </div>
    
    <!-- Users Table -->
    <div class="data-table">
        <div class="table-header">
            <h2>All Admin Users</h2>
            <div class="table-filters">
                <input type="text" id="searchInput" placeholder="Search users..." class="search-input">
                <select id="roleFilter" class="filter-select">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                </select>
                <select id="statusFilter" class="filter-select">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>
        
        <div class="table-responsive">
            <table id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Activities</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users && $users->num_rows > 0): ?>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr data-role="<?php echo $user['role']; ?>" data-status="<?php echo $user['status']; ?>">
                                <td><strong>#<?php echo $user['id']; ?></strong></td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar-small">
                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <i class="fa-solid <?php echo ($user['role'] === 'admin') ? 'fa-user-shield' : 'fa-user'; ?>"></i>
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="activity-count"><?php echo $user['activity_count']; ?> actions</span>
                                </td>
                                <td>
                                    <?php if ($user['last_login']): ?>
                                        <small><?php echo date('M d, Y h:i A', strtotime($user['last_login'])); ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">Never</small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?php echo date('M d, Y', strtotime($user['created_at'])); ?></small></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn-icon btn-edit" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $user['status']; ?>">
                                                <button type="submit" class="btn-icon <?php echo ($user['status'] === 'active') ? 'btn-warning' : 'btn-success'; ?>" 
                                                        title="<?php echo ($user['status'] === 'active') ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fa-solid <?php echo ($user['status'] === 'active') ? 'fa-ban' : 'fa-check'; ?>"></i>
                                                </button>
                                            </form>
                                            
                                            <button onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>')" 
                                                    class="btn-icon btn-delete" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 40px;">
                                <i class="fa-solid fa-users" style="font-size: 48px; color: #d1d5db; margin-bottom: 10px;"></i>
                                <p style="color: #6b7280;">No users found</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <h3><i class="fa-solid fa-triangle-exclamation"></i> Confirm Delete</h3>
        <p>Are you sure you want to delete <strong id="deleteUserName"></strong>?</p>
        <p class="warning-text">This action cannot be undone.</p>
        <form id="deleteForm" method="POST">
            <input type="hidden" name="action" value="delete_user">
            <input type="hidden" name="user_id" id="deleteUserId">
            <div class="modal-actions">
                <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete User</button>
            </div>
        </form>
    </div>
</div>

<style>
.content-container {
    padding: 30px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.page-header h1 {
    margin: 0;
    color: #111827;
}

.header-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border-left: 4px solid #10b981;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
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
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
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
.stat-icon.red { background: linear-gradient(135deg, #ef4444, #dc2626); }

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 14px;
    color: #6b7280;
    font-weight: 500;
}

.stat-value {
    margin: 0;
    font-size: 28px;
    font-weight: bold;
    color: #111827;
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
    flex-wrap: wrap;
    gap: 15px;
}

.table-header h2 {
    margin: 0;
    color: #111827;
    font-size: 18px;
}

.table-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.search-input, .filter-select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.search-input {
    min-width: 200px;
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
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

td {
    padding: 15px 12px;
    border-bottom: 1px solid #e5e7eb;
    color: #111827;
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
}

.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.role-admin {
    background: #dbeafe;
    color: #1e40af;
}

.role-staff {
    background: #e0e7ff;
    color: #4338ca;
}

.status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.activity-count {
    color: #6b7280;
    font-size: 13px;
}

.text-muted {
    color: #9ca3af;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn, .btn-icon {
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

.btn-icon {
    padding: 8px;
    width: 35px;
    height: 35px;
    justify-content: center;
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

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-edit {
    background: #3b82f6;
    color: white;
}

.btn-edit:hover {
    background: #2563eb;
}

.btn-warning {
    background: #f59e0b;
    color: white;
}

.btn-warning:hover {
    background: #d97706;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
}

.btn-delete {
    background: #ef4444;
    color: white;
}

.btn-delete:hover {
    background: #dc2626;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.2s;
}

.modal-content {
    background: white;
    margin: 10% auto;
    padding: 30px;
    border-radius: 12px;
    max-width: 500px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    animation: slideDown 0.3s;
}

.modal-content h3 {
    margin: 0 0 15px 0;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-content h3 i {
    color: #ef4444;
}

.warning-text {
    color: #ef4444;
    font-size: 14px;
    margin-top: 10px;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 25px;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .table-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .table-filters {
        width: 100%;
    }
    
    .search-input {
        width: 100%;
    }
}
</style>

<script>
// Search and Filter Functionality
document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('roleFilter').addEventListener('change', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const roleFilter = document.getElementById('roleFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    const table = document.getElementById('usersTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const name = row.cells[1].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const role = row.getAttribute('data-role');
        const status = row.getAttribute('data-status');
        
        const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
        const matchesRole = !roleFilter || role === roleFilter;
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesRole && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

// Delete User Modal
function deleteUser(userId, userName) {
    document.getElementById('deleteUserId').value = userId;
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteModal').style.display = 'block';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('deleteModal');
    if (event.target === modal) {
        closeDeleteModal();
    }
}
</script>

<?php include 'includes/footer.php'; ?>