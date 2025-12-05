<?php
// admin/reviews.php - ENHANCED VERSION WITH SEARCH
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Handle review actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['review_id'])) {
        $review_id = intval($_POST['review_id']);
        $action = $_POST['action'];
        
        if ($action === 'approve') {
            $stmt = $conn->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $review_id);
            if ($stmt->execute()) {
                log_activity($_SESSION['user_id'], 'Review Approved', "Approved review ID: $review_id");
                $_SESSION['success'] = 'Review approved successfully!';
            }
        } elseif ($action === 'reject') {
            $stmt = $conn->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
            $stmt->bind_param("i", $review_id);
            if ($stmt->execute()) {
                log_activity($_SESSION['user_id'], 'Review Rejected', "Rejected review ID: $review_id");
                $_SESSION['success'] = 'Review rejected successfully!';
            }
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->bind_param("i", $review_id);
            if ($stmt->execute()) {
                log_activity($_SESSION['user_id'], 'Review Deleted', "Deleted review ID: $review_id");
                $_SESSION['success'] = 'Review deleted successfully!';
            }
        }
        
        redirect('reviews.php');
    }
}

// Get filter and search parameters
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$rating_filter = isset($_GET['rating']) ? intval($_GET['rating']) : 0;

// Build WHERE clause
$where_conditions = [];
$params = [];
$param_types = '';

// Status filter
switch ($filter) {
    case 'pending':
        $where_conditions[] = "r.status = 'pending'";
        break;
    case 'approved':
        $where_conditions[] = "r.status = 'approved'";
        break;
    case 'rejected':
        $where_conditions[] = "r.status = 'rejected'";
        break;
}

// Search filter
if (!empty($search)) {
    $search_param = "%$search%";
    $where_conditions[] = "(u.username LIKE ? OR u.full_name LIKE ? OR u.email LIKE ? OR r.review_text LIKE ?)";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $param_types .= 'ssss';
}

// Rating filter
if ($rating_filter > 0 && $rating_filter <= 5) {
    $where_conditions[] = "r.rating = ?";
    $params[] = $rating_filter;
    $param_types .= 'i';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get reviews statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
    AVG(rating) as avg_rating
FROM reviews";
$stats = $conn->query($stats_query)->fetch_assoc();

// Get filtered reviews with user information
$reviews_query = "SELECT 
    r.id,
    r.rating,
    r.review_text,
    r.status,
    r.created_at,
    u.username,
    u.full_name,
    u.email
FROM reviews r
JOIN users u ON r.user_id = u.id
$where_clause
ORDER BY 
    CASE WHEN r.status = 'pending' THEN 1 ELSE 2 END,
    r.created_at DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($reviews_query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $reviews_result = $stmt->get_result();
} else {
    $reviews_result = $conn->query($reviews_query);
}

$page_title = 'Review Management';
include 'includes/header.php';
?>

<div class="content-container">
    <div class="page-header">
        <h1><i class="fas fa-comments"></i> Review Management</h1>
        <p>Manage and moderate customer reviews</p>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fas fa-comments"></i>
            </div>
            <div class="stat-content">
                <h3>Total Reviews</h3>
                <div class="stat-value"><?php echo $stats['total']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon orange">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3>Pending</h3>
                <div class="stat-value"><?php echo $stats['pending']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fas fa-check"></i>
            </div>
            <div class="stat-content">
                <h3>Approved</h3>
                <div class="stat-value"><?php echo $stats['approved']; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-content">
                <h3>Average Rating</h3>
                <div class="stat-value"><?php echo number_format($stats['avg_rating'], 1); ?></div>
                <div class="star-display">
                    <?php 
                    $avg = round($stats['avg_rating']);
                    for($i = 0; $i < 5; $i++) {
                        echo $i < $avg ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="search-filter-section">
        <form method="GET" action="reviews.php" class="search-form">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" 
                       name="search" 
                       placeholder="Search by user name, email, or review content..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i> Search
                </button>
            </div>
            
            <div class="filter-controls">
                <select name="filter" class="filter-select" onchange="this.form.submit()">
                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                    <option value="pending" <?php echo $filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="approved" <?php echo $filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="rejected" <?php echo $filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
                
                <select name="rating" class="filter-select" onchange="this.form.submit()">
                    <option value="0" <?php echo $rating_filter === 0 ? 'selected' : ''; ?>>All Ratings</option>
                    <option value="5" <?php echo $rating_filter === 5 ? 'selected' : ''; ?>>★★★★★ (5)</option>
                    <option value="4" <?php echo $rating_filter === 4 ? 'selected' : ''; ?>>★★★★☆ (4)</option>
                    <option value="3" <?php echo $rating_filter === 3 ? 'selected' : ''; ?>>★★★☆☆ (3)</option>
                    <option value="2" <?php echo $rating_filter === 2 ? 'selected' : ''; ?>>★★☆☆☆ (2)</option>
                    <option value="1" <?php echo $rating_filter === 1 ? 'selected' : ''; ?>>★☆☆☆☆ (1)</option>
                </select>

                <?php if(!empty($search) || $filter !== 'all' || $rating_filter > 0): ?>
                    <a href="reviews.php" class="btn-clear-filters">
                        <i class="fas fa-times"></i> Clear Filters
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Results Count -->
    <?php if(!empty($search) || $filter !== 'all' || $rating_filter > 0): ?>
    <div class="results-info">
        <i class="fas fa-info-circle"></i>
        Showing <?php echo $reviews_result->num_rows; ?> result(s)
        <?php if(!empty($search)): ?>
            for "<?php echo htmlspecialchars($search); ?>"
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Reviews List -->
    <div class="reviews-container">
        <?php if($reviews_result && $reviews_result->num_rows > 0): ?>
            <?php while($review = $reviews_result->fetch_assoc()): ?>
                <div class="review-card <?php echo $review['status']; ?>">
                    <div class="review-header">
                        <div class="reviewer-info">
                            <div class="reviewer-avatar">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <div class="reviewer-details">
                                <h4><?php echo htmlspecialchars($review['full_name'] ?: $review['username']); ?></h4>
                                <p class="reviewer-email">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($review['email']); ?>
                                </p>
                                <p class="review-date">
                                    <i class="far fa-calendar"></i>
                                    <?php echo date('F j, Y \a\t g:i A', strtotime($review['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                        <div class="review-status-badge">
                            <?php
                            $badge_class = '';
                            $badge_icon = '';
                            switch($review['status']) {
                                case 'pending':
                                    $badge_class = 'badge-warning';
                                    $badge_icon = 'fa-clock';
                                    break;
                                case 'approved':
                                    $badge_class = 'badge-success';
                                    $badge_icon = 'fa-check-circle';
                                    break;
                                case 'rejected':
                                    $badge_class = 'badge-danger';
                                    $badge_icon = 'fa-times-circle';
                                    break;
                            }
                            ?>
                            <span class="status-badge <?php echo $badge_class; ?>">
                                <i class="fas <?php echo $badge_icon; ?>"></i>
                                <?php echo ucfirst($review['status']); ?>
                            </span>
                        </div>
                    </div>

                    <div class="review-rating">
                        <?php 
                        for($i = 0; $i < 5; $i++) {
                            if($i < $review['rating']) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                        <span class="rating-text">(<?php echo $review['rating']; ?>/5)</span>
                    </div>

                    <div class="review-content">
                        <p><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                    </div>

                    <div class="review-actions">
                        <?php if($review['status'] === 'pending'): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Approve this review?');">
                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Reject this review?');">
                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </form>
                        <?php elseif($review['status'] === 'approved'): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Reject this review? It will be hidden from the public.');">
                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </form>
                        <?php elseif($review['status'] === 'rejected'): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Approve this review?');">
                                <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                <input type="hidden" name="action" value="approve">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this review permanently?');">
                            <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>No Reviews Found</h3>
                <p>
                    <?php if(!empty($search)): ?>
                        No reviews match your search criteria. Try different keywords.
                    <?php else: ?>
                        There are no reviews matching your current filter.
                    <?php endif; ?>
                </p>
                <?php if(!empty($search) || $filter !== 'all' || $rating_filter > 0): ?>
                    <a href="reviews.php" class="btn btn-primary" style="margin-top: 20px;">
                        <i class="fas fa-arrow-left"></i> View All Reviews
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Add these additional styles to your existing CSS */
.content-container {
    padding: 30px;
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 30px;
}

.page-header h1 {
    color: #111827;
    font-size: 28px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.page-header p {
    color: #6b7280;
    font-size: 14px;
}

.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    flex-shrink: 0;
}

.stat-content h3 {
    font-size: 14px;
    color: #6b7280;
    margin: 0 0 8px 0;
    font-weight: 500;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #111827;
    line-height: 1;
}

.star-display {
    margin-top: 4px;
    color: #fbbf24;
    font-size: 14px;
}

.filter-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 24px;
    overflow-x: auto;
    padding-bottom: 4px;
}

.tab {
    padding: 12px 20px;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    text-decoration: none;
    color: #6b7280;
    font-weight: 500;
    transition: all 0.2s;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tab:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

.tab.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.reviews-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.review-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border-left: 4px solid transparent;
    transition: all 0.2s;
}

.review-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 16px;
}

.reviewer-info {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.reviewer-avatar {
    width: 48px;
    height: 48px;
    background: #f3f4f6;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    font-size: 32px;
    flex-shrink: 0;
}

.reviewer-details h4 {
    margin: 0 0 4px 0;
    color: #111827;
    font-size: 16px;
}

.reviewer-email {
    margin: 0 0 4px 0;
    color: #6b7280;
    font-size: 13px;
}

.review-date {
    margin: 0;
    color: #9ca3af;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.badge-warning {
    background: #fef3c7;
    color: #92400e;
}

.badge-success {
    background: #d1fae5;
    color: #065f46;
}

.badge-danger {
    background: #fee2e2;
    color: #991b1b;
}

.review-rating {
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.review-rating i {
    color: #fbbf24;
    font-size: 18px;
}

.review-rating .far {
    color: #d1d5db;
}

.rating-text {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
}

.review-content {
    margin-bottom: 20px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
}

.review-content p {
    margin: 0;
    color: #374151;
    line-height: 1.6;
    font-size: 14px;
}

.review-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn:hover {
    transform: translateY(-2px);
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #059669;
}

.btn-warning {
    background: #f59e0b;
    color: white;
}

.btn-warning:hover {
    background: #d97706;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
}

.empty-state i {
    font-size: 64px;
    color: #d1d5db;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #374151;
    margin: 0 0 8px 0;
}

.empty-state p {
    color: #6b7280;
    margin: 0;
}

@media (max-width: 768px) {
    .content-container {
        padding: 20px;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .review-header {
        flex-direction: column;
    }
    
    .review-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}

.search-filter-section {
    background: white;
    padding: 24px;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 24px;
}

.search-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.search-bar {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f9fafb;
    padding: 12px 16px;
    border-radius: 10px;
    border: 2px solid #e5e7eb;
    transition: border-color 0.3s;
}

.search-bar:focus-within {
    border-color: #3b82f6;
    background: white;
}

.search-bar i {
    color: #9ca3af;
    font-size: 18px;
}

.search-bar input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 15px;
    color: #111827;
    outline: none;
}

.search-bar input::placeholder {
    color: #9ca3af;
}

.btn-search {
    padding: 10px 24px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
}

.btn-search:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.filter-controls {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-select {
    padding: 10px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    color: #374151;
    background: white;
    cursor: pointer;
    transition: all 0.3s;
    min-width: 160px;
}

.filter-select:hover {
    border-color: #3b82f6;
}

.filter-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.btn-clear-filters {
    padding: 10px 20px;
    background: #f3f4f6;
    color: #6b7280;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.btn-clear-filters:hover {
    background: #e5e7eb;
    color: #374151;
}

.results-info {
    background: #eff6ff;
    padding: 12px 20px;
    border-radius: 8px;
    color: #1e40af;
    font-size: 14px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.results-info i {
    font-size: 16px;
}

@media (max-width: 768px) {
    .search-bar {
        flex-direction: column;
        align-items: stretch;
    }
    
    .btn-search {
        justify-content: center;
    }
    
    .filter-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-select {
        width: 100%;
    }
}
</style>

<?php include 'includes/footer.php'; ?>

