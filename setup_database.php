<?php
// Setup database tables for Fedora app
require 'config.php';

echo "<h2>Database Setup - Fedora App</h2>";

// SQL statements to create tables
// NOTE: categories must be created FIRST (before tables that reference it)
$sql_statements = [
    // Categories table (MUST be first - referenced by channels/movies/series)
    "CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL UNIQUE,
        slug VARCHAR(255) NOT NULL UNIQUE,
        description TEXT,
        icon_class VARCHAR(100),
        sort_order INT DEFAULT 0,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Users table
    "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(100) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone VARCHAR(20) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Slider images table
    "CREATE TABLE IF NOT EXISTS slider_images (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        image_url VARCHAR(500),
        link_url VARCHAR(500),
        sort_order INT DEFAULT 0,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Channels table
    "CREATE TABLE IF NOT EXISTS channels (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        category_id INT,
        stream_url VARCHAR(500),
        thumbnail_url VARCHAR(500),
        logo_url VARCHAR(500),
        is_live TINYINT DEFAULT 0,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Movies table
    "CREATE TABLE IF NOT EXISTS movies (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        category_id INT,
        video_url VARCHAR(500),
        thumbnail_url VARCHAR(500),
        duration INT,
        release_date DATE,
        rating DECIMAL(3, 1),
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Series table
    "CREATE TABLE IF NOT EXISTS series (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        category_id INT,
        thumbnail_url VARCHAR(500),
        total_episodes INT,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Social links table
    "CREATE TABLE IF NOT EXISTS social_links (
        id INT PRIMARY KEY AUTO_INCREMENT,
        platform VARCHAR(100) NOT NULL,
        url VARCHAR(500) NOT NULL,
        icon_class VARCHAR(100),
        sort_order INT DEFAULT 0,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Sidebar items table
    "CREATE TABLE IF NOT EXISTS sidebar_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        link VARCHAR(500),
        icon_class VARCHAR(100),
        sort_order INT DEFAULT 0,
        is_active TINYINT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // OTP codes table (for phone verification)
    "CREATE TABLE IF NOT EXISTS otp_codes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        phone VARCHAR(20) NOT NULL,
        code VARCHAR(6) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME,
        used TINYINT DEFAULT 0,
        INDEX idx_phone (phone),
        INDEX idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

// Execute all SQL statements
$success_count = 0;
$error_count = 0;

foreach ($sql_statements as $sql) {
    if ($conn->query($sql) === TRUE) {
        $success_count++;
        echo "<p style='color: green;'>✓ Table created/verified successfully</p>";
    } else {
        $error_count++;
        echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
    }
}

// Insert default data if tables are empty
if ($success_count > 0) {
    // Check if categories table is empty
    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    $row = $result->fetch_assoc();
    
    if ($row['count'] == 0) {
        echo "<hr><h3>Inserting default categories...</h3>";
        $default_categories = [
            "أفلام عربية",
            "مسلسلات",
            "أفلام أجنبية",
            "وثائقي",
            "رياضة",
            "أطفال"
        ];
        
        foreach ($default_categories as $cat) {
            $stmt = $conn->prepare("INSERT INTO categories (name, is_active) VALUES (?, 1)");
            $stmt->bind_param("s", $cat);
            if ($stmt->execute()) {
                echo "<p>✓ Added category: $cat</p>";
            }
            $stmt->close();
        }
    }
}

echo "<hr>";
echo "<h3>Setup Complete!</h3>";
echo "<p>✓ Tables created: $success_count</p>";
if ($error_count > 0) {
    echo "<p>✗ Errors: $error_count</p>";
}
echo "<p><a href='admin.php'>Go to Admin Panel</a></p>";

$conn->close();
?>
