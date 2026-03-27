<?php
require_once 'config.php';

echo "╔════════════════════════════════════════════════╗\n";
echo "║   DATABASE SCHEMA CHECK                        ║\n";
echo "╚════════════════════════════════════════════════╝\n\n";

// Check slider_images table
echo "📸 Slider Images Table:\n";
$result = $conn->query("DESCRIBE slider_images");
if ($result) {
    while ($col = $result->fetch_assoc()) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    $count = $conn->query("SELECT COUNT(*) as c FROM slider_images")->fetch_assoc()['c'];
    echo "  Records: $count\n\n";
} else {
    echo "  ✗ Table not found\n\n";
}

// Check channels table
echo "📺 Channels Table:\n";
$result = $conn->query("DESCRIBE channels");
if ($result) {
    while ($col = $result->fetch_assoc()) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    $count = $conn->query("SELECT COUNT(*) as c FROM channels")->fetch_assoc()['c'];
    echo "  Records: $count\n\n";
} else {
    echo "  ✗ Table not found\n\n";
}

// Check categories table
echo "📂 Categories Table:\n";
$result = $conn->query("DESCRIBE categories");
if ($result) {
    while ($col = $result->fetch_assoc()) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    $count = $conn->query("SELECT COUNT(*) as c FROM categories")->fetch_assoc()['c'];
    echo "  Records: $count\n\n";
} else {
    echo "  ✗ Table not found\n\n";
}

// Check data in slider_images
echo "Slider Images Data:\n";
$result = $conn->query("SELECT * FROM slider_images LIMIT 3");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "  - " . json_encode($row) . "\n";
    }
} else {
    echo "  No data\n";
}
echo "\n";

// Check data in channels
echo "Channels Data:\n";
$result = $conn->query("SELECT * FROM channels LIMIT 3");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "  - " . json_encode($row) . "\n";
    }
} else {
    echo "  No data\n";
}
echo "\n";

// Check data in categories
echo "Categories Data:\n";
$result = $conn->query("SELECT * FROM categories LIMIT 3");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "  - " . json_encode($row) . "\n";
    }
} else {
    echo "  No data\n";
}

$conn->close();
?>
