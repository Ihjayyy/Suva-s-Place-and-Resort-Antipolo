<?php
// logout.php
require_once 'config/database.php';

if (is_logged_in()) {
    log_activity($_SESSION['user_id'], 'User Logout', 'User logged out');
}

// Destroy session
session_destroy();

// Redirect to login
redirect('login.php');
?>