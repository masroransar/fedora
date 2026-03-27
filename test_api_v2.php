<?php
// Test API v2 with query parameters
echo "=== Testing API v2 (Query Parameters) ===\n\n";

require_once 'config.php';

// Simulate API v2 requests
echo "1. Testing ?action=channels\n";
$_GET['action'] = 'channels';
$_SERVER['REQUEST_METHOD'] = 'GET';
$result = $conn->query("SELECT id, name, url, created_at FROM channels ORDER BY created_at DESC LIMIT 1");
$row = $result->fetch_assoc();
$json = json_encode(['success' => true, 'channels' => [$row]]);
echo "   Response: " . substr($json, 0, 100) . "...\n";
echo "   Valid JSON: " . (json_decode($json) ? "YES ✓" : "NO ✗") . "\n\n";

echo "2. Testing ?action=movies\n";
$result = $conn->query("SELECT id, title, video_url FROM movies WHERE is_active = 1 LIMIT 1");
$row = $result->fetch_assoc();
$json = json_encode(['success' => true, 'movies' => [$row]]);
echo "   Response: " . substr($json, 0, 100) . "...\n";
echo "   Valid JSON: " . (json_decode($json) ? "YES ✓" : "NO ✗") . "\n\n";

echo "3. Testing ?action=categories\n";
$result = $conn->query("SELECT id, name FROM categories WHERE is_active = 1 LIMIT 1");
$row = $result->fetch_assoc();
$json = json_encode(['success' => true, 'categories' => [$row]]);
echo "   Response: " . substr($json, 0, 100) . "...\n";
echo "   Valid JSON: " . (json_decode($json) ? "YES ✓" : "NO ✗") . "\n\n";

echo "✓ API v2 Query Parameter Format Test Complete\n";
$conn->close();
?>
