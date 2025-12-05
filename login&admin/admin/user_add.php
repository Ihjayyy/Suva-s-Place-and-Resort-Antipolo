<?php
// admin/user_add.php
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate inputs
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    
    // Validation
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email already exists";
        }
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (!in_array($role, ['admin', 'staff'])) {
        $errors[] = "Invalid role selected";
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = "Invalid status selected";
    }
    
    // If no errors, create user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate username from email
        $username = substr($email, 0, strpos($email, '@'));
        $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);
        
        // Check if username exists, if so, append number
        $base_username = $username;
        $counter = 1;
        while (true) {
            $check_username = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $check_username->bind_param("s", $username);
            $check_username->execute();
            if ($check_username->get_result()->num_rows == 0) {
                break;
            }
            $username = $base_username . $counter;
            $counter++;
        }
        
        // IMPORTANT: Set user_type to 'admin' for admin panel users
        // This ensures they show up in the user management system
        $user_type = 'admin'; // Always 'admin' for admin panel users
        
        // Check if 'role' column exists in the table
        $check_role_column = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
        $has_role_column = $check_role_column && $check_role_column->num_rows > 0;
        
        if ($has_role_column) {
            // New table structure with 'role' column
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, user_type, role, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssssss", $username, $email, $hashed_password, $full_name, $user_type, $role, $status);
        } else {
            // Old table structure without 'role' column
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, user_type, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssssss", $username, $email, $hashed_password, $full_name, $user_type, $status);
        }
        
        if ($stmt->execute()) {
            $new_user_id = $stmt->insert_id;
            
            // Log activity
            log_activity($_SESSION['user_id'], 'user_created', "Created new user: $full_name (ID: $new_user_id, Role: $role)");
            
            $_SESSION['success'] = "User created successfully! Username: $username";
            header("Location: users.php");
            exit();
        } else {
            $errors[] = "Failed to create user. Please try again. Error: " . $conn->error;
        }
    }
}

$page_title = 'Add New Admin';
include 'includes/header.php';
?>

<div class="content-container">
    <div class="page-header">
        <div>
            <h1>Add New Admin User</h1>
            <p class="page-subtitle">Create a new administrator or staff account</p>
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
        <form method="POST" id="addUserForm">
            <div class="form-grid">
                <div class="form-section">
                    <h3><i class="fa-solid fa-user"></i> Personal Information</h3>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" id="full_name" name="full_name" 
                               value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" 
                               required>
                        <small>Enter the full name of the user</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                               required>
                        <small>This will be used for login (username will be auto-generated)</small>
                    </div>
                    
                    <div class="info-box">
                        <i class="fa-solid fa-info-circle"></i>
                        <strong>Note:</strong> Username will be automatically generated from the email address.
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fa-solid fa-lock"></i> Security</h3>
                    
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <div class="password-input">
                            <input type="password" id="password" name="password" required>
                            <button type="button" onclick="togglePassword('password')" class="toggle-password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                        <small>Minimum 6 characters (recommended: 8+ with mix of letters, numbers & symbols)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <div class="password-input">
                            <input type="password" id="confirm_password" name="confirm_password" required>
                            <button type="button" onclick="togglePassword('confirm_password')" class="toggle-password">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <small>Re-enter password to confirm</small>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fa-solid fa-shield-halved"></i> Role & Permissions</h3>
                    
                    <div class="form-group">
                        <label for="role">User Role <span class="required">*</span></label>
                        <select id="role" name="role" required>
                            <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>
                                Administrator
                            </option>
                            <option value="staff" <?php echo (isset($_POST['role']) && $_POST['role'] === 'staff') ? 'selected' : ''; ?>>
                                Staff
                            </option>
                        </select>
                        <small>Administrators have full access to all features</small>
                    </div>
                    
                    <div class="role-info" id="roleInfo">
                        <div class="role-description" id="adminDescription">
                            <h4>Administrator Permissions:</h4>
                            <ul>
                                <li><i class="fa-solid fa-check"></i> Manage all reservations</li>
                                <li><i class="fa-solid fa-check"></i> Manage customers and users</li>
                                <li><i class="fa-solid fa-check"></i> Access system settings</li>
                                <li><i class="fa-solid fa-check"></i> View reports and analytics</li>
                                <li><i class="fa-solid fa-check"></i> Manage services and gallery</li>
                            </ul>
                        </div>
                        
                        <div class="role-description" id="staffDescription" style="display: none;">
                            <h4>Staff Permissions:</h4>
                            <ul>
                                <li><i class="fa-solid fa-check"></i> View reservations</li>
                                <li><i class="fa-solid fa-check"></i> Update reservation status</li>
                                <li><i class="fa-solid fa-check"></i> View customer information</li>
                                <li><i class="fa-solid fa-times"></i> Cannot manage users</li>
                                <li><i class="fa-solid fa-times"></i> Cannot access system settings</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fa-solid fa-toggle-on"></i> Account Status</h3>
                    
                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select id="status" name="status" required>
                            <option value="active" <?php echo (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : 'selected'; ?>>
                                Active
                            </option>
                            <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : ''; ?>>
                                Inactive
                            </option>
                        </select>
                        <small>Inactive users cannot login to the system</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <a href="users.php" class="btn btn-secondary">
                    <i class="fa-solid fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-user-plus"></i> Create User
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
    margin: 0 0 20px 0;
    color: #111827;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e5e7eb;
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

.password-strength {
    margin-top: 8px;
    height: 4px;
    background: #e5e7eb;
    border-radius: 2px;
    overflow: hidden;
}

.password-strength.weak {
    background: linear-gradient(to right, #ef4444 33%, #e5e7eb 33%);
}

.password-strength.medium {
    background: linear-gradient(to right, #f59e0b 66%, #e5e7eb 66%);
}

.password-strength.strong {
    background: #10b981;
}

.info-box {
    background: #dbeafe;
    border: 1px solid #93c5fd;
    border-radius: 6px;
    padding: 12px;
    margin-top: 15px;
    font-size: 13px;
    color: #1e40af;
    display: flex;
    align-items: flex-start;
    gap: 8px;
}

.info-box i {
    margin-top: 2px;
}

.role-info {
    margin-top: 15px;
}

.role-description {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 15px;
}

.role-description h4 {
    margin: 0 0 10px 0;
    color: #111827;
    font-size: 14px;
}

.role-description ul {
    margin: 0;
    padding: 0;
    list-style: none;
}

.role-description li {
    padding: 5px 0;
    color: #374151;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.role-description li i.fa-check {
    color: #10b981;
}

.role-description li i.fa-times {
    color: #ef4444;
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
    
    .form-container {
        padding: 20px;
    }
    
    .form-section {
        padding: 15px;
    }
}
</style>

<script>
// Toggle password visibility
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

// Show/hide role descriptions
document.getElementById('role').addEventListener('change', function() {
    const adminDesc = document.getElementById('adminDescription');
    const staffDesc = document.getElementById('staffDescription');
    
    if (this.value === 'admin') {
        adminDesc.style.display = 'block';
        staffDesc.style.display = 'none';
    } else {
        adminDesc.style.display = 'none';
        staffDesc.style.display = 'block';
    }
});

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('passwordStrength');
    const strength = calculatePasswordStrength(password);
    
    strengthDiv.className = 'password-strength';
    if (password.length > 0) {
        if (strength <= 2) {
            strengthDiv.classList.add('weak');
        } else if (strength <= 3) {
            strengthDiv.classList.add('medium');
        } else {
            strengthDiv.classList.add('strong');
        }
    }
});

function calculatePasswordStrength(password) {
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    return strength;
}

// Form validation
document.getElementById('addUserForm').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
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
});
</script>

<?php include 'includes/footer.php'; ?>