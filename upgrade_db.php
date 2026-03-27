<?php
require_once 'config.php';

echo "╔════════════════════════════════════════════════╗\n";
echo "║   DATABASE UPGRADES                            ║\n";
echo "╚════════════════════════════════════════════════╝\n\n";

// Add image_url to channels if not exists
echo "✓ Adding image_url to channels table...\n";
$result = $conn->query("ALTER TABLE channels ADD COLUMN image_url VARCHAR(500) DEFAULT NULL AFTER url");
if ($result || strpos($conn->error, "Duplicate column") !== false) {
    echo "  ✓ Column added (or already exists)\n\n";
} else {
    echo "  Error: " . $conn->error . "\n\n";
}

// Add link_url to slider_images if not exists
echo "✓ Adding link_url to slider_images table...\n";
$result = $conn->query("ALTER TABLE slider_images ADD COLUMN link_url VARCHAR(500) DEFAULT NULL AFTER image_url");
if ($result || strpos($conn->error, "Duplicate column") !== false) {
    echo "  ✓ Column added (or already exists)\n\n";
} else {
    echo "  Error: " . $conn->error . "\n\n";
}

// Verify changes
echo "✓ Channels table now has:\n";
$result = $conn->query("DESCRIBE channels");
while ($col = $result->fetch_assoc()) {
    echo "  - " . $col['Field'] . "\n";
}

echo "\n✓ Slider Images table now has:\n";
$result = $conn->query("DESCRIBE slider_images");
while ($col = $result->fetch_assoc()) {
    echo "  - " . $col['Field'] . "\n";
}

echo "\nDatabase upgrade complete!\n";
$conn->close();
?>
