<?php
require_once '../login&admin/config/database.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('../login&admin/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    
    $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ? WHERE id = ?");
    $update_stmt->bind_param("sssi", $full_name, $email, $phone, $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        $_SESSION['success'] = 'Profile updated successfully!';
        log_activity($user_id, 'Profile Update', 'User updated profile information');
        redirect('user_settings.php');
    } else {
        $error = 'Failed to update profile';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Settings | Suva's Place</title>
    <link rel="stylesheet" href="../public/assets/css/navbar.css">
    <link rel="stylesheet" href="../public/assets/css/shared.css">
    <link rel="stylesheet" href="../public/assets/css/user_menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="../public/assets/images/suva's_place_logo.ico">

    <script defer src="../public/assets/js/navbar.js"></script>
    <script defer src="../public/assets/js/user_menu.js"></script>
    <style>
        body{
            background: url("../public/assets/images/userSettings-bg.png") center/cover no-repeat;

        }

        .hero {
            position: relative;
            height: 45vh;
            background: url("../public/assets/images/userSettings-header.png") center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--first-color);
        }

        .hero::before{
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
        }

        .hero-content{
            position: relative;
            z-index: 1;
        }

        .hero h1{
            font-size: 4rem;
            font-weight: 600;
            margin-top: 5rem;
        }
        .settings-container {
            background: var(--first-color);
            color: var(--third-color);
            margin: 50px auto 20px;
            max-width: 700px;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .settings-header {
            margin-bottom: 30px;
        }
        .settings-header h2 {
            margin-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--third-color);
            border-radius: 8px;
            font-size: 14px;
        }
        .btn-save {
            padding: 12px 30px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s ease-in-out;
        }
        .btn-save:hover {
            background: var(--second-color);
            color: var(--third-color);
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">
                <img src="../public/assets/images/suva's_logo_white.png">
            </div>

            <ul class="nav-links" id="nav-links">
                <li><a href="landing_page.php">Home</a></li>
                <li><a href="about_page.php">About us</a></li>
                <li><a href="gallery_page.php">Gallery</a></li>
                <li><a href="contacts_page.php">Contacts</a></li>
                
                <?php if(is_logged_in()): ?>
                    <li class="user-menu-container">
                        <a href="#" class="user-button" id="userMenuBtn">
                            <i class="fas fa-user-circle"></i> 
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                            <i class="fas fa-chevron-down"></i>
                        </a>
                        
                        <!-- User Dropdown Menu -->
                        <div class="user-dropdown" id="userDropdown">
                            <div class="dropdown-header">
                                <i class="fas fa-user-circle"></i>
                                <div>
                                    <p class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></p>
                                    <p class="user-email"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="user_settings.php" class="dropdown-item">
                                <i class="fas fa-cog"></i> User Settings
                            </a>
                            <a href="transaction_history.php" class="dropdown-item">
                                <i class="fas fa-history"></i> Transaction History
                            </a>
                            <a href="help_support.php" class="dropdown-item">
                                <i class="fas fa-question-circle"></i> Help & Support
                            </a>
                            <a href="terms.php" class="dropdown-item">
                                <i class="fas fa-file-contract"></i> Terms and Conditions
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="../login&admin/logout.php" class="dropdown-item logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="../login&admin/login.php" class="login-button"><i class="fas fa-user"></i> Login</a></li>
                <?php endif; ?>
            </ul>

            <div class="burger" id="burger">
                  <i class="fas fa-bars" id="open-icon"></i>
                  <i class="fas fa-arrow-left" id="close-icon"></i>
            </div>
        </div>
    </nav>

  <section class="hero">
    <div class="hero-content">
      <h1>User Settings</h1>
    </div>
  </section>

    <div class="settings-container">
        <div class="settings-header">
            <h2><i class="fas fa-cog"></i> User Settings</h2>
            <p>Manage your account information</p>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
            </div>
            
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
            </div>
            
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>

</body>
</html>