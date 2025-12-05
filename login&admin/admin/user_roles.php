<?php
// admin/user_edit.php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$errors = [];
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "User not found";
    header("Location: users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email exists for other users
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    if (!in_array($role, ['admin', 'staff'])) {
        $errors[] = "Invalid role selected";
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = "Invalid status selected";
    }
    
    // Validate password if provided
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
    }
    
    // If no errors, update user
    if (empty($errors)) {
        if (!empty($password)) {
            // Update with new password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ?, role = ?, status = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $full_name, $email, $hashed_password, $role, $status, $user_id);
        } else {
            // Update without password
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, role = ?, status = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $full_name, $email, $role, $status, $user_id);
        }
        
        if ($stmt->execute()) {
            // Log activity
            log_activity($conn, $_SESSION['user_id'], 'user_updated', "Updated user: $full_name (ID: $user_id)");
            
            $_SESSION['success'] = "User updated successfully";
            header("Location: users.php");
            exit();
        } else {
            $errors[] = "Failed to update user. Please try again.";
        }
    }
}

$page_title = 'Edit User';
include 'includes/header.php';
?>

<div class="content-container">
    <div class="page-header">
        <div>
            <h1>Edit User Account</h1>
            <p class="page-subtitle">Update user information and permissions</p>
        </div>
        <a href="users.php" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Back to Users
        </a>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <i class="fa-solid fa-exclamation-circle"></i>
            <div>
                <strong>Please fix the following errors:</strong>
                <ul style="margin: 5px 0 0 0; padding-left: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="form-container">
        <div class="user-header">
            <div class="user-avatar-large">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <div class="user-meta">
                <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <div class="user-badges">
                    <span class="role-badge role-<?php echo $user['role']; ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                    <span class="status-badge status-<?php echo $user['status']; ?>">
                        <?php echo ucfirst($user['status']); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <form method="POST" id="editUserForm">
            <div class="form-grid">
                <div class="form-section">
                    <h3><i class="fa-solid fa-user"></i> Personal Information</h3>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fa-solid fa-lock"></i> Change Password</h3>
                    <p class="section-note"><i class="fa-solid fa-info-circle"></i> Leave blank to keep current password</p>
                    
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="password-input">
                            <input type="password" id="password" name="password">
                            <button type="button" onclick="togglePassword('password')" class="toggle-password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <small>Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <div class="password-input">
                            <input type="password" id="confirm_password" name="confirm_password">
                            <button type="button" onclick="togglePassword('confirm_password')" class="toggle-password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fa-solid fa-shield-halved"></i> Role & Permissions</h3>
                    
                    <div class="form-group">
                        <label for="role">User Role <span class="required">*</span></label>
                        <select id="role" name="role" required 
                                <?php echo ($user_id == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            <option value="admin" <?php echo ($user['role'] === 'admin') ? 'selected' : ''; ?>>
                                Administrator
                            </option>
                            <option value="staff" <?php echo ($user['role'] === 'staff') ? 'selected' : ''; ?>>
                                Staff
                            </option>
                        </select>
                        <?php if ($user_id == $_SESSION['user_id']): ?>
                            <small class="text-warning">You cannot change your own role</small>
                            <input type="hidden" name="role" value="<?php echo $user['role']; ?>">
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fa-solid fa-toggle-on"></i> Account Status</h3>
                    
                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select id="status" name="status" required
                                <?php echo ($user_id == $_SESSION['user_id']) ? 'disabled' : ''; ?>>
                            <option value="active" <?php echo ($user['status'] === 'active') ? 'selected' : ''; ?>>
                                Active
                            </option>
                            <option value="inactive" <?php echo ($user['status'] === 'inactive') ? 'selected' : ''; ?>>
                                Inactive
                            </option>
                        </select>
                        <?php if ($user_id == $_SESSION['user_id']): ?>
                            <small class="text-warning">You cannot deactivate your own account</small>
                            <input type="hidden" name="status" value="<?php echo $user['status']; ?>">
                        <?php endif; ?>
                    </div>
                    
                    <div class="info-box">
                        <p><strong>Created:</strong> <?php echo date('M d, Y h:i A', strtotime($user['created_at'])); ?></p>
                        <?php if ($user['last_login']): ?>
                            <p><strong>Last Login:</strong> <?php echo date('M d, Y h:i A', strtotime($user['last_login'])); ?></p>
                        <?php else: ?>
                            <p><strong>Last Login:</strong> Never</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="users.php" class="btn btn-secondary">
                    <i class="fa-solid fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Update User
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.content-container {
    padding: 30px;
    max-width: 1400px;
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

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

.form-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    padding: 30px;
}

.user-header {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-radius: 8px;
    margin-bottom: 30px;
    color: white;
}

.user-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.user-meta h2 {
    margin: 0 0 5px 0;
    font-size: 24px;
}

.user-meta p {
    margin: 0 0 10px 0;
    opacity: 0.9;
}

.user-badges {
    display: flex;
    gap: 10px;
}

.role-badge, .status-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.role-admin {
    background: rgba(255, 255, 255, 0.25);
    color: white;
}

.role-staff {
    background: rgba(255, 255, 255, 0.25);
    color: white;
}

.status-active {
    background: rgba(16, 185, 129, 0.2);
    color: #d1fae5;
}

.status-inactive {
    background: rgba(239, 68, 68, 0.2);
    color: #fee2e2;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
}

.form-section {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
}

.form-section h3 {
    margin: 0 0 15px 0;
    color: #111827;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e5e7eb;
}

.section-note {
    background: #fef3c7;
    color: #92400e;
    padding: 10px;
    border-radius: 6px;
    font-size: 13px;
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #374151;
    font-weight: 500;
    font-size: 14px;
}

.required {
    color: #ef4444;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-group small {
    display: block;
    margin-top: 5px;
    color: #6b7280;
    font-size: 12px;
}

.text-warning {
    color: #d97706;
}

.password-input {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 5px;
}

.toggle-password:hover {
    color: #3b82f6;
}

.info-box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 15px;
    margin-top: 15px;
}

.info-box p {
    margin: 5px 0;
    color: #374151;
    font-size: 13px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
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

@media (max-width: 1024px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 15px;
    }
    
    .user-header {
        flex-direction: column;
        text-align: center;
    }
}
</style>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        button.classList.remove('fa-eye');
        button.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        button.classList.remove('fa-eye-slash');
        button.classList.add('fa-eye');
    }
}

// Form validation
document.getElementById('editUserForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password || confirmPassword) {
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long!');
            return false;
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>