<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - Admin Panel' : 'Admin Panel'; ?></title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/shared.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <!--------- SIDE BAR--------- -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="" alt="">
                <h2>Suva's Place And Resort Antipolo</h2>
                <p><?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
            </div>
            
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'class="active"' : ''; ?>>
                        <i class="fa-solid fa-house"></i> Dashboard
                    </a>
                </li>
                
                <li>
                    <a href="reservations.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'reservations.php') ? 'class="active"' : ''; ?>>
                        <i class="fa-solid fa-calendar-days"></i> Reservations
                    </a>
                </li>
                
                <li>
                    <a href="customers.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'customers.php') ? 'class="active"' : ''; ?>>
                        <i class="fa-solid fa-users"></i> Customers
                    </a>
                </li>
                
                <li>
                    <a href="services.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'services.php') ? 'class="active"' : ''; ?>>
                        <i class="fa-solid fa-door-closed"></i> Services Management
                    </a>
                </li>
                
                <li>
                    <a href="notifications.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'notifications.php') ? 'class="active"' : ''; ?>>
                        <i class="fa-solid fa-bell"></i> Notifications
                    </a>
                </li>
                
                <li>
                    <a href="reviews.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'reviews.php') ? 'class="active"' : ''; ?>>
                        <i class="fa-solid fa-star"></i> Reviews & Feedback
                    </a>
                </li>
                
                <li>
                    <a href="gallery.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'gallery.php') ? 'class="active"' : ''; ?>>
                        <i class="fa-solid fa-image"></i> Gallery Management
                    </a>
                </li>
                
                <li>
                    <a href="inquiries.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'inquiries.php') ? 'class="active"' : ''; ?>>
                        <i class="fa-solid fa-inbox "></i> Inquiries
                    </a>
                </li>
                
                <li>
                    <a href="users.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'users.php') ? 'class="active"' : ''; ?>>
                        <i class="fa-solid fa-id-card"></i> User Management
                    </a>
                </li>
                
                <li>
                    <a href="settings.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'settings.php') ? 'class="active"' : ''; ?>>
                        <i class="fa-solid fa-gear"></i> System Settings
                    </a>
                </li>
                
                <li>
                    <a href="reports.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'class="active"' : ''; ?>>
                        <i class="fa-solid fa-chart-line"></i> Reports & Analytics
                    </a>
                </li>
                
                <li>
                    <a href="help.php" <?php echo (basename($_SERVER['PHP_SELF']) == 'help.php') ? 'class="active"' : ''; ?>>
                        <i class="fa-solid fa-circle-question"></i> Help & Support
                    </a>
                </li>
                
                <li>
                    <a href="../logout.php">
                        <i class="fa-solid fa-right-from-bracket"></i> Logout
                    </a>
                </li>
            </ul>
        </aside>
        
        <!------------------- MAIN CONTENT -------------------->
        <main class="main-content">
            <!------------------- TOP NAVIGATION -------------------->
            <nav class="top-nav">
                <h1><?php echo isset($page_title) ? $page_title : 'Admin Panel'; ?></h1>
                
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    <a href="../logout.php" class="btn-logout">Logout</a>
                </div>
            </nav>