<?php
require_once '../config/database.php';

// Check if user is admin 
// if (!is_admin()) {
//     header('Location: ../login.php');
//     exit;
// }

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $inquiry_id = (int)$_POST['inquiry_id'];
    $new_status = $_POST['status'];
    
    $allowed_statuses = ['pending', 'read', 'responded', 'archived'];
    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $pdo->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $inquiry_id]);
        $success_message = "Status updated successfully!";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $inquiry_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM inquiries WHERE id = ?");
    $stmt->execute([$inquiry_id]);
    header('Location: inquiries.php');
    exit;
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$sql = "SELECT * FROM inquiries";
if ($filter !== 'all') {
    $sql .= " WHERE status = :status";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
if ($filter !== 'all') {
    $stmt->execute(['status' => $filter]);
} else {
    $stmt->execute();
}
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get counts for badges
$counts = [
    'all' => $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn(),
    'pending' => $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'pending'")->fetchColumn(),
    'read' => $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'read'")->fetchColumn(),
    'responded' => $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'responded'")->fetchColumn(),
    'archived' => $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'archived'")->fetchColumn(),
];

$page_title = 'Inquiries Management';
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inquiries | Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>       
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .filters {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 10px 20px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .filter-btn:hover {
            border-color: #3498db;
            color: #3498db;
        }
        
        .filter-btn.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        
        .badge {
            background: #e74c3c;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .inquiries-grid {
            display: grid;
            gap: 20px;
        }
        
        .inquiry-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .inquiry-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        
        .inquiry-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .inquiry-info h3 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .inquiry-info .email {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .inquiry-info .date {
            color: #95a5a6;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-read {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-responded {
            background: #d4edda;
            color: #155724;
        }
        
        .status-archived {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .inquiry-message {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            color: #495057;
            line-height: 1.6;
        }
        
        .inquiry-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        select.btn {
            padding: 8px 12px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
        }
        
        .empty-state i {
            font-size: 64px;
            color: #bdc3c7;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #7f8c8d;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-envelope"></i> Customer Inquiries</h1>
            <p>Manage and respond to customer messages</p>
            
            <div class="filters">
                <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                    All <span class="badge"><?php echo $counts['all']; ?></span>
                </a>
                <a href="?filter=pending" class="filter-btn <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                    Pending <span class="badge"><?php echo $counts['pending']; ?></span>
                </a>
                <a href="?filter=read" class="filter-btn <?php echo $filter === 'read' ? 'active' : ''; ?>">
                    Read <span class="badge"><?php echo $counts['read']; ?></span>
                </a>
                <a href="?filter=responded" class="filter-btn <?php echo $filter === 'responded' ? 'active' : ''; ?>">
                    Responded <span class="badge"><?php echo $counts['responded']; ?></span>
                </a>
                <a href="?filter=archived" class="filter-btn <?php echo $filter === 'archived' ? 'active' : ''; ?>">
                    Archived <span class="badge"><?php echo $counts['archived']; ?></span>
                </a>
            </div>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <div class="inquiries-grid">
            <?php if (empty($inquiries)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No inquiries found</h3>
                    <p>There are no inquiries matching your filter criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($inquiries as $inquiry): ?>
                    <div class="inquiry-card">
                        <div class="inquiry-header">
                            <div class="inquiry-info">
                                <h3><?php echo htmlspecialchars($inquiry['full_name']); ?></h3>
                                <div class="email">
                                    <i class="fas fa-envelope"></i> 
                                    <?php echo htmlspecialchars($inquiry['email']); ?>
                                </div>
                                <div class="date">
                                    <i class="fas fa-clock"></i> 
                                    <?php echo date('F j, Y, g:i a', strtotime($inquiry['created_at'])); ?>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo $inquiry['status']; ?>">
                                <?php echo htmlspecialchars($inquiry['status']); ?>
                            </span>
                        </div>
                        
                        <div class="inquiry-message">
                            <?php echo nl2br(htmlspecialchars($inquiry['message'])); ?>
                        </div>
                        
                        <div class="inquiry-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                <select name="status" class="btn btn-primary" onchange="this.form.submit()">
                                    <option value="">Change Status</option>
                                    <option value="pending" <?php echo $inquiry['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="read" <?php echo $inquiry['status'] === 'read' ? 'selected' : ''; ?>>Read</option>
                                    <option value="responded" <?php echo $inquiry['status'] === 'responded' ? 'selected' : ''; ?>>Responded</option>
                                    <option value="archived" <?php echo $inquiry['status'] === 'archived' ? 'selected' : ''; ?>>Archived</option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                            
                            <a href="mailto:<?php echo htmlspecialchars($inquiry['email']); ?>?subject=Re: Your Inquiry" class="btn btn-primary">
                                <i class="fas fa-reply"></i> Reply via Email
                            </a>
                            
                            <a href="?delete=<?php echo $inquiry['id']; ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Are you sure you want to delete this inquiry?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>