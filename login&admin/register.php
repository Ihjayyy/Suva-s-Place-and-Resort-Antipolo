<?php
// register.php
require_once 'config/database.php';

// REDIRECT USER IF ALREADY LOGGED IN
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/dashboard.php');
    } else {
        redirect('../public/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get and sanitize input
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $terms = isset($_POST['terms']);
    
    // Validation
    $errors = [];
    
    if (empty($username) || empty($email) || empty($phone) || empty($password)) {
        $errors[] = 'All fields are required';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (!$terms) {
        $errors[] = 'You must agree to the Terms and Conditions';
    }

    
    // CHECK IF THE USERNAME IS ALREADY EXISTS
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = 'Username already exists';
        }
        $stmt->close();
    }
    
    // CHECK IF EMAIL IS ALREADY EXISTS
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = 'Email already registered';
        }
        $stmt->close();
    }
    
    // If no errors, create the user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Use username as full_name since the field was removed
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, user_type, status, created_at) VALUES (?, ?, ?, ?, ?, 'customer', 'active', NOW())");
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $username, $phone);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // Log activity
            log_activity($user_id, 'User Registration', 'New user registered successfully');
            
            $_SESSION['success'] = 'Registration successful! Please login with your credentials.';
            redirect('login.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
        $stmt->close();
    }
    
    // Store errors in session and redirect back
    if (!empty($errors)) {
        $_SESSION['register_error'] = implode('<br>', $errors);
        redirect('login.php');
    }
}

// If accessed directly without POST, redirect to login
redirect('login.php');
?>