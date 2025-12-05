<?php
// generate_hash.php
// Run this file once to generate a new password hash
// Then DELETE this file for security!

$password = 'admin123'; // Change this to your desired password

$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h2>Password Hash Generator</h2>";
echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
echo "<p><strong>Hash:</strong> " . htmlspecialchars($hash) . "</p>";

echo "<hr>";
echo "<h3>Run this SQL query in phpMyAdmin:</h3>";
echo "<textarea style='width:100%; height:100px; font-family:monospace;'>";
echo "UPDATE users SET password = '$hash' WHERE username = 'admin';";
echo "</textarea>";

echo "<hr>";
echo "<p style='color:red;'><strong>⚠️ DELETE THIS FILE AFTER USE!</strong></p>";

// Uncomment the lines below to automatically update the database
/*
require_once 'config/database.php';
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $hash);
if ($stmt->execute()) {
    echo "<p style='color:green;'>✅ Password updated successfully!</p>";
} else {
    echo "<p style='color:red;'>❌ Failed to update password</p>";
}
*/
?>