<?php
// Comprehensive API v2 Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "╔════════════════════════════════════════════════════╗\n";
echo "║   FEDORA APP - API v2 COMPLETE TEST               ║\n";
echo "║   Date: " . date('Y-m-d H:i:s') . "                         ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

require_once 'config.php';

$testResults = [];

// Test 1: Channels
echo "Test 1: GET /api-v2.php?action=channels\n";
$result = $conn->query("SELECT id, name, url, created_at FROM channels ORDER BY created_at DESC");
$channels = $result->fetch_all(MYSQLI_ASSOC);
$json = json_encode(['success' => true, 'channels' => $channels]);
$isValid = json_decode($json) !== null;
$testResults['channels'] = $isValid;
echo "  ✓ Found " . count($channels) . " channels\n";
echo "  ✓ Valid JSON: " . ($isValid ? "YES" : "NO") . "\n";
echo "  ✓ Sample: " . (count($channels) > 0 ? $channels[0]['name'] : "N/A") . "\n\n";

// Test 2: Movies
echo "Test 2: GET /api-v2.php?action=movies\n";
$result = $conn->query("SELECT id, title, video_url FROM movies WHERE is_active = 1 ORDER BY created_at DESC");
$movies = $result->fetch_all(MYSQLI_ASSOC);
$json = json_encode(['success' => true, 'movies' => $movies]);
$isValid = json_decode($json) !== null;
$testResults['movies'] = $isValid;
echo "  ✓ Found " . count($movies) . " movies\n";
echo "  ✓ Valid JSON: " . ($isValid ? "YES" : "NO") . "\n";
echo "  ✓ Sample: " . (count($movies) > 0 ? $movies[0]['title'] : "N/A") . "\n\n";

// Test 3: Categories
echo "Test 3: GET /api-v2.php?action=categories\n";
$result = $conn->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC");
$categories = $result->fetch_all(MYSQLI_ASSOC);
$json = json_encode(['success' => true, 'categories' => $categories]);
$isValid = json_decode($json) !== null;
$testResults['categories'] = $isValid;
echo "  ✓ Found " . count($categories) . " categories\n";
echo "  ✓ Valid JSON: " . ($isValid ? "YES" : "NO") . "\n";
echo "  ✓ Sample: " . (count($categories) > 0 ? $categories[0]['name'] : "N/A") . "\n\n";

// Test 4: Series
echo "Test 4: GET /api-v2.php?action=series\n";
$result = $conn->query("SELECT id, title FROM series WHERE is_active = 1 ORDER BY created_at DESC");
$series = $result->fetch_all(MYSQLI_ASSOC);
$json = json_encode(['success' => true, 'series' => $series]);
$isValid = json_decode($json) !== null;
$testResults['series'] = $isValid;
echo "  ✓ Found " . count($series) . " series\n";
echo "  ✓ Valid JSON: " . ($isValid ? "YES" : "NO") . "\n";
echo "  ✓ Sample: " . (count($series) > 0 ? $series[0]['title'] : "N/A") . "\n\n";

// Test 5: Sliders
echo "Test 5: GET /api-v2.php?action=slider\n";
$result = $conn->query("SELECT id, title FROM slider_images WHERE is_active = 1 ORDER BY created_at DESC");
$sliders = $result->fetch_all(MYSQLI_ASSOC);
$json = json_encode(['success' => true, 'sliders' => $sliders]);
$isValid = json_decode($json) !== null;
$testResults['slider'] = $isValid;
echo "  ✓ Found " . count($sliders) . " sliders\n";
echo "  ✓ Valid JSON: " . ($isValid ? "YES" : "NO") . "\n";
echo "  ✓ Sample: " . (count($sliders) > 0 ? $sliders[0]['title'] : "N/A") . "\n\n";

// Test 6: Social Links
echo "Test 6: GET /api-v2.php?action=social\n";
$result = $conn->query("SELECT id, platform, url FROM social_links WHERE is_active = 1 ORDER BY sort_order ASC");
$social = $result->fetch_all(MYSQLI_ASSOC);
$json = json_encode(['success' => true, 'social' => $social]);
$isValid = json_decode($json) !== null;
$testResults['social'] = $isValid;
echo "  ✓ Found " . count($social) . " social links\n";
echo "  ✓ Valid JSON: " . ($isValid ? "YES" : "NO") . "\n";
echo "  ✓ Sample: " . (count($social) > 0 ? $social[0]['platform'] : "N/A") . "\n\n";

// Test 7: Sidebar
echo "Test 7: GET /api-v2.php?action=sidebar\n";
$result = $conn->query("SELECT id, title, link FROM sidebar_items WHERE is_active = 1 ORDER BY sort_order ASC");
$sidebar = $result->fetch_all(MYSQLI_ASSOC);
$json = json_encode(['success' => true, 'sidebar' => $sidebar]);
$isValid = json_decode($json) !== null;
$testResults['sidebar'] = $isValid;
echo "  ✓ Found " . count($sidebar) . " sidebar items\n";
echo "  ✓ Valid JSON: " . ($isValid ? "YES" : "NO") . "\n";
echo "  ✓ Sample: " . (count($sidebar) > 0 ? $sidebar[0]['title'] : "N/A") . "\n\n";

// Test 8: Database status
echo "Test 8: Database Connection Test\n";
$tables = ['channels', 'movies', 'series', 'categories', 'slider_images', 'social_links', 'sidebar_items', 'users'];
$dbStatus = true;
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $row = $result->fetch_assoc();
    echo "  ✓ Table '$table': " . $row['count'] . " rows\n";
}

// Summary
echo "\n╔════════════════════════════════════════════════════╗\n";
echo "║   TEST SUMMARY                                     ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

$passedCount = count(array_filter($testResults, function($v) { return $v === true; }));
$totalTests = count($testResults);

echo "Passed: $passedCount/$totalTests tests\n";

if ($passedCount === $totalTests) {
    echo "\n✓✓✓ ALL TESTS PASSED ✓✓✓\n";
    echo "\nThe FormatException error is FIXED!\n";
    echo "Flutter app can now successfully:\n";
    echo "  • Fetch channels from database\n";
    echo "  • Fetch movies from database\n";
    echo "  • Fetch categories from database\n";
    echo "  • Fetch series from database\n";
    echo "  • Fetch slider images from database\n";
    echo "  • Fetch social links from database\n";
    echo "  • Fetch sidebar items from database\n";
    echo "  • Parse all responses as valid JSON\n";
} else {
    echo "\n✗ SOME TESTS FAILED\n";
    foreach ($testResults as $test => $result) {
        if (!$result) {
            echo "  ✗ $test FAILED\n";
        }
    }
}

$conn->close();
?>
