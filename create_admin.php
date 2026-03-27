<?php
// Create default admin user
require 'config.php';

echo "<h2>Create Admin User</h2>";

// Admin credentials
$username = "admin";
$password = "admin123";
$email = "admin@fedora.local";
$phone = "07701234567"; // Iraqi phone format

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin already exists
$check = $conn->prepare("SELECT id FROM users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo "<p style='color: orange;'>⚠ Admin user already exists!</p>";
} else {
    // Insert admin user
    $stmt = $conn->prepare("INSERT INTO users (username, phone, password, role) VALUES (?, ?, ?, 'admin')");
    $stmt->bind_param("sss", $username, $phone, $hashed_password);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✓ Admin user created successfully!</p>";
        echo "<p><strong>Login Credentials:</strong></p>";
        echo "<ul>";
        echo "<li><strong>Username:</strong> admin</li>";
        echo "<li><strong>Password:</strong> admin123</li>";
        echo "</ul>";
        echo "<p style='color: red;'><strong>Note:</strong> Change your password after first login!</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

echo "<hr>";
echo "<p><a href='login.php'>Go to Login</a> | <a href='admin.php'>Go to Admin Panel</a></p>";

$conn->close();
?>
