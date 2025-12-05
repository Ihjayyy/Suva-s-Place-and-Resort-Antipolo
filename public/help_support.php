<?php
require_once '../login&admin/config/database.php';
if (!is_logged_in()) redirect('../login&admin/login.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Help & Support</title>
    <link rel="stylesheet" href="../public/assets/css/shared.css">
</head>
<body>
    <div style="max-width: 800px; margin: 100px auto; padding: 30px;">
        <h2>Help & Support</h2>
        <p>Contact us at: support@suvasplace.com</p>
        <p>Phone: +63 123 456 7890</p>
    </div>
</body>
</html>