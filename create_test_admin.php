<?php
// Create new admin user for testing
require_once 'config.php';

echo "Creating Test Admin User...\n\n";

// Delete old admin if exists
$conn->query("DELETE FROM users WHERE username = 'testadmin'");

$username = 'testadmin';
$password = 'admin123456';  // 12 characters
$phone = '07700000000';
$role = 'admin';

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Insert
$stmt = $conn->prepare("INSERT INTO users (username, password, phone, role) VALUES (?, ?, ?, ?)");
if (!$stmt) {
    echo "✗ Error: " . $conn->error . "\n";
} else {
    $stmt->bind_param("ssss", $username, $hashed, $phone, $role);
    if ($stmt->execute()) {
        echo "✓ Admin user created successfully!\n\n";
        echo "Login Credentials:\n";
        echo "  Username: " . $username . "\n";
        echo "  Password: " . $password . "\n";
        echo "  Phone: " . $phone . "\n";
        echo "  Role: " . $role . "\n";
        echo "\n";
        
        // Verify it works
        echo "Verification:\n";
        $verify_stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $verify_stmt->bind_param("s", $username);
        $verify_stmt->execute();
        $result = $verify_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                echo "  ✓ Password verification: SUCCESS\n";
            } else {
                echo "  ✗ Password verification: FAILED\n";
            }
        }
        $verify_stmt->close();
    } else {
        echo "✗ Database error: " . $stmt->error . "\n";
    }
    $stmt->close();
}

echo "\n";

// Show all users
echo "All Users in Database:\n";
$result = $conn->query("SELECT id, username, phone, role FROM users ORDER BY id DESC");
while ($row = $result->fetch_assoc()) {
    echo "  - " . $row['username'] . " (Phone: " . $row['phone'] . ", Role: " . $row['role'] . ")\n";
}

$conn->close();
?>
