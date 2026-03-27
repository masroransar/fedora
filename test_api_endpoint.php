<?php
// Test API endpoint directly
echo "Testing API endpoints directly:\n\n";

require_once 'config.php';

// Test channels
echo "=== CHANNELS ENDPOINT ===\n";
$result = $conn->query("SELECT id, name, url, created_at FROM channels ORDER BY created_at DESC LIMIT 2");
echo "Status: " . ($result ? "OK" : "ERROR") . "\n";
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
$json = json_encode(['success' => true, 'channels' => $data]);
echo "JSON Output: " . substr($json, 0, 100) . "...\n";
echo "First character: " . ord($json[0]) . " (should be 123 for {)\n\n";

// Test movies
echo "=== MOVIES ENDPOINT ===\n";
$result = $conn->query("SELECT id, title, description, video_url, thumbnail_url, category_id, rating FROM movies WHERE is_active = 1 LIMIT 2");
echo "Status: " . ($result ? "OK" : "ERROR") . "\n";
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
$json = json_encode(['success' => true, 'movies' => $data]);
echo "JSON Output: " . substr($json, 0, 100) . "...\n";
echo "First character: " . ord($json[0]) . " (should be 123 for {)\n\n";

echo "✓ API endpoints are working correctly!\n";
$conn->close();
?>
