<?php
// upgrade_users_table.php - Add profile columns to users table
require_once 'config.php';

echo "Starting database upgrade for user profiles...\n\n";

try {
    // Check if 'name' column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'name'");
    if ($result->num_rows == 0) {
        echo "Adding 'name' column to users table...\n";
        $conn->query("ALTER TABLE users ADD COLUMN name VARCHAR(255) AFTER username");
        echo "✓ 'name' column added\n\n";
    } else {
        echo "✓ 'name' column already exists\n\n";
    }
    
    // Check if 'profile_picture' column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    if ($result->num_rows == 0) {
        echo "Adding 'profile_picture' column to users table...\n";
        $conn->query("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(500) AFTER name");
        echo "✓ 'profile_picture' column added\n\n";
    } else {
        echo "✓ 'profile_picture' column already exists\n\n";
    }
    
    // Check if 'token' column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'token'");
    if ($result->num_rows == 0) {
        echo "Adding 'token' column to users table...\n";
        $conn->query("ALTER TABLE users ADD COLUMN token VARCHAR(255) AFTER profile_picture");
        echo "✓ 'token' column added\n\n";
    } else {
        echo "✓ 'token' column already exists\n\n";
    }
    
    // Show final schema
    echo "Current users table schema:\n";
    echo "================================\n";
    $result = $conn->query("DESCRIBE users");
    while ($row = $result->fetch_assoc()) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "\n✓ Database upgrade completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage();
}

$conn->close();
?>
