<?php
// config/database.php
session_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'suvas_db');

// Create MySQLi connection 
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Create PDO connection 
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("PDO Connection failed: " . $e->getMessage());
}

// =====================================================
// HELPER FUNCTIONS
// =====================================================

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

/**
 * Check if user is client
 */
function is_client() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'client';
}

/**
 * Redirect to another page
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Log user activity to database (FIXED - Compatible with current database structure)
 * 
 * @param int $user_id User ID performing the action
 * @param string $action Action type
 * @param string $details Detailed description of the action
 * @return bool Success status
 */
function log_activity($user_id, $action, $details = '') {
    global $conn;
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    // Check if activity_logs table has 'description' or 'details' column
    $check_column = $conn->query("SHOW COLUMNS FROM activity_logs LIKE 'description'");
    $has_description = $check_column && $check_column->num_rows > 0;
    
    $check_details = $conn->query("SHOW COLUMNS FROM activity_logs LIKE 'details'");
    $has_details = $check_details && $check_details->num_rows > 0;
    
    // Check if user_agent column exists
    $check_user_agent = $conn->query("SHOW COLUMNS FROM activity_logs LIKE 'user_agent'");
    $has_user_agent = $check_user_agent && $check_user_agent->num_rows > 0;
    
    try {
        if ($has_description && $has_user_agent) {
            // New structure: description + user_agent
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            if ($stmt) {
                $stmt->bind_param("issss", $user_id, $action, $details, $ip_address, $user_agent);
                $result = $stmt->execute();
                $stmt->close();
                return $result;
            }
        } elseif ($has_description) {
            // Has description but no user_agent
            $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())");
            if ($stmt) {
                $stmt->bind_param("isss", $user_id, $action, $details, $ip_address);
                $result = $stmt->execute();
                $stmt->close();
                return $result;
            }
        } elseif ($has_details) {
            // Old structure: details column
            $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("isss", $user_id, $action, $details, $ip_address);
                $result = $stmt->execute();
                $stmt->close();
                return $result;
            }
        } else {
            // Fallback: basic structure without details/description
            $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, ip_address) VALUES (?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("iss", $user_id, $action, $ip_address);
                $result = $stmt->execute();
                $stmt->close();
                return $result;
            }
        }
    } catch (Exception $e) {
        error_log("Log activity error: " . $e->getMessage());
        return false;
    }
    
    return false;
}

/**
 * Get system setting value
 */
function get_setting($key, $default = '') {
    global $conn;
    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return $default;
}

// =====================================================
// ADDITIONAL HELPER FUNCTIONS FOR USER MANAGEMENT
// =====================================================

/**
 * Generate a unique booking ID
 */
function generate_booking_id() {
    return 'BK' . date('Ymd') . strtoupper(substr(uniqid(), -6));
}

/**
 * Format currency (Philippine Peso)
 */
function format_currency($amount) {
    return '₱' . number_format($amount, 2);
}

/**
 * Get status badge class for HTML elements
 */
function get_status_badge_class($status) {
    $classes = [
        'confirmed' => 'badge-success',
        'pending' => 'badge-warning',
        'cancelled' => 'badge-danger',
        'completed' => 'badge-info',
        'active' => 'badge-success',
        'inactive' => 'badge-danger'
    ];
    
    return $classes[$status] ?? 'badge-secondary';
}

/**
 * Calculate days between two dates
 */
function days_between($date1, $date2) {
    $datetime1 = new DateTime($date1);
    $datetime2 = new DateTime($date2);
    $interval = $datetime1->diff($datetime2);
    return $interval->days;
}

/**
 * Check if date is available for booking
 */
function is_date_available($date, $shift, $exclude_booking_id = null) {
    global $conn;
    
    // Check if shift column exists
    $check_shift = $conn->query("SHOW COLUMNS FROM reservations LIKE 'shift'");
    $has_shift = $check_shift && $check_shift->num_rows > 0;
    
    if ($has_shift) {
        $query = "
            SELECT COUNT(*) as count 
            FROM reservations 
            WHERE booking_date = ? 
            AND shift = ? 
            AND status IN ('pending', 'confirmed')
        ";
        
        if ($exclude_booking_id) {
            $query .= " AND booking_id != ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $date, $shift, $exclude_booking_id);
        } else {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $date, $shift);
        }
    } else {
        // Fallback without shift column
        $query = "
            SELECT COUNT(*) as count 
            FROM reservations 
            WHERE booking_date = ? 
            AND status IN ('pending', 'confirmed')
        ";
        
        if ($exclude_booking_id) {
            $query .= " AND id != ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $date, $exclude_booking_id);
        } else {
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $date);
        }
    }
    
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $result['count'] == 0;
}

/**
 * Get user permissions based on role
 */
function has_permission($role, $module, $permission) {
    $permissions = [
        'admin' => [
            'dashboard' => ['view'],
            'reservations' => ['view', 'create', 'edit', 'delete', 'approve'],
            'customers' => ['view', 'create', 'edit', 'delete'],
            'services' => ['view', 'create', 'edit', 'delete'],
            'notifications' => ['view', 'send'],
            'reviews' => ['view', 'respond', 'delete'],
            'gallery' => ['view', 'upload', 'delete'],
            'inquiries' => ['view', 'respond', 'delete'],
            'users' => ['view', 'create', 'edit', 'delete'],
            'settings' => ['view', 'edit'],
            'reports' => ['view', 'export'],
            'help' => ['view']
        ],
        'staff' => [
            'dashboard' => ['view'],
            'reservations' => ['view', 'edit'],
            'customers' => ['view'],
            'services' => ['view'],
            'notifications' => ['view'],
            'reviews' => ['view', 'respond'],
            'gallery' => ['view'],
            'inquiries' => ['view', 'respond'],
            'users' => [],
            'settings' => [],
            'reports' => ['view'],
            'help' => ['view']
        ]
    ];
    
    return isset($permissions[$role][$module]) && in_array($permission, $permissions[$role][$module]);
}

/**
 * Upload file securely
 */
function upload_file($file, $destination, $allowed_types = ['image/jpeg', 'image/png', 'image/gif'], $max_size = 5242880) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload error occurred'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File size exceeds maximum allowed (' . ($max_size / 1024 / 1024) . 'MB)'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }
    
    // Create destination directory if it doesn't exist
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $filepath = $destination . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'filepath' => $filepath];
    }
    
    return ['success' => false, 'message' => 'Failed to move uploaded file'];
}

/**
 * Delete file securely
 */
function delete_file($filepath) {
    if (file_exists($filepath) && is_file($filepath)) {
        return unlink($filepath);
    }
    return false;
}

/**
 * Generate random password
 */
function generate_random_password($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    $password = '';
    $chars_length = strlen($chars);
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, $chars_length - 1)];
    }
    return $password;
}

/**
 * Send email notification
 * Configure your email settings here
 */
function send_email_notification($to, $subject, $message, $from_name = 'Suva\'s Place And Resort') {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $from_name <noreply@suvasplace.com>" . "\r\n";
    $headers .= "Reply-To: info@suvasplace.com" . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // For production, consider using PHPMailer or a service like SendGrid
    return mail($to, $subject, $message, $headers);
}

/**
 * Format date for display
 */
function format_date($date, $format = 'M d, Y') {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

/**
 * Format datetime for display
 */
function format_datetime($datetime, $format = 'M d, Y h:i A') {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    return date($format, strtotime($datetime));
}

/**
 * Get user's full name by ID
 */
function get_user_name($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['full_name'];
    }
    return 'Unknown User';
}

/**
 * Get time ago format
 */
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    if ($difference < 60) {
        return 'just now';
    } elseif ($difference < 3600) {
        $mins = floor($difference / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $timestamp);
    }
}

/**
 * Validate email format
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Philippine format)
 */
function is_valid_phone($phone) {
    // Remove any non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if it's a valid Philippine mobile number (09XXXXXXXXX or 639XXXXXXXXX)
    if (preg_match('/^(09|639)\d{9}$/', $phone)) {
        return true;
    }
    
    // Check if it's a valid Philippine landline (8 digits)
    if (preg_match('/^\d{8}$/', $phone)) {
        return true;
    }
    
    return false;
}

/**
 * Clean phone number to standard format
 */
function clean_phone_number($phone) {
    // Remove any non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Convert to standard format (09XXXXXXXXX)
    if (substr($phone, 0, 3) === '639') {
        $phone = '0' . substr($phone, 2);
    }
    
    return $phone;
}

/**
 * Check if user has accessed module recently (for activity tracking)
 */
function track_module_access($module_name) {
    if (!is_logged_in()) {
        return false;
    }
    
    log_activity(
        $_SESSION['user_id'],
        'module_accessed',
        "Accessed module: $module_name"
    );
    
    return true;
}

/**
 * Get current user's role
 */
function get_current_user_role() {
    return $_SESSION['role'] ?? $_SESSION['user_type'] ?? 'guest';
}

/**
 * Flash message functions for better UX
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type, // success, error, warning, info
        'message' => $message
    ];
}

function get_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

function has_flash_message() {
    return isset($_SESSION['flash_message']);
}

?>