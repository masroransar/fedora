<?php
// Direct API test - simulate Flutter request
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "=== API Direct Test ===\n\n";

require_once 'config.php';

echo "1. Testing Channels Endpoint:\n";
echo "   Query: SELECT id, name, url, created_at FROM channels ORDER BY created_at DESC\n";

$result = $conn->query("SELECT id, name, url, created_at FROM channels ORDER BY created_at DESC");
if (!$result) {
    echo "   ERROR: " . $conn->error . "\n\n";
} else {
    $channels = [];
    while ($row = $result->fetch_assoc()) {
        $channels[] = $row;
    }
    
    $json = json_encode(['success' => true, 'channels' => $channels]);
    echo "   Found " . count($channels) . " channels\n";
    echo "   JSON length: " . strlen($json) . " bytes\n";
    echo "   First char code: " . ord($json[0]) . " (should be 123 for {)\n";
    echo "   Valid JSON: " . (json_decode($json) ? "YES" : "NO") . "\n";
    echo "   First 500 chars:\n";
    echo "   " . substr($json, 0, 500) . "\n\n";
}

echo "2. Testing Movies Endpoint:\n";
$result = $conn->query("SELECT id, title, description, video_url, thumbnail_url, category_id, rating FROM movies WHERE is_active = 1 ORDER BY created_at DESC");
if (!$result) {
    echo "   ERROR: " . $conn->error . "\n\n";
} else {
    $movies = [];
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
    
    $json = json_encode(['success' => true, 'movies' => $movies]);
    echo "   Found " . count($movies) . " movies\n";
    echo "   JSON length: " . strlen($json) . " bytes\n";
    echo "   First char code: " . ord($json[0]) . " (should be 123 for {)\n";
    echo "   Valid JSON: " . (json_decode($json) ? "YES" : "NO") . "\n";
    echo "   First 500 chars:\n";
    echo "   " . substr($json, 0, 500) . "\n\n";
}

echo "3. Checking for errors or warnings:\n";
$errors = error_get_last();
if ($errors) {
    echo "   ERROR: " . print_r($errors, true) . "\n";
} else {
    echo "   No errors detected ✓\n";
}

$conn->close();
?>
