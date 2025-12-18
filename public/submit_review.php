<?php
require_once '../login&admin/config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error'] = 'You must be logged in to submit a review';
    redirect('landing_page.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $rating = intval($_POST['rating']);
    $review_text = sanitize_input($_POST['review_text']);
    
    // Validate input
    if ($rating < 1 || $rating > 5) {
        $_SESSION['error'] = 'Invalid rating value';
        redirect('landing_page.php');
        exit();
    }
    
    if (strlen($review_text) < 10) {
        $_SESSION['error'] = 'Review must be at least 10 characters long';
        redirect('landing_page.php');
        exit();
    }
    
    
    // Insert review (default status is 'pending' for admin approval)
    $stmt = $conn->prepare("INSERT INTO reviews (user_id, rating, review_text, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("iis", $user_id, $rating, $review_text);
    
    if ($stmt->execute()) {
        // Log activity
        log_activity($user_id, 'Review Submitted', 'User submitted a review with rating ' . $rating);
        
        $_SESSION['success'] = 'Thank you for your review! It will be published after admin approval.';
    } else {
        $_SESSION['error'] = 'Failed to submit review. Please try again.';
    }
    
    $stmt->close();
    redirect('index.php');
} else {
    redirect('index.php');
}
?>