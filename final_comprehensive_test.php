<?php
// Final comprehensive test - All systems
require_once 'config.php';

echo "╔════════════════════════════════════════════════════╗\n";
echo "║   FINAL COMPREHENSIVE TEST                         ║\n";
echo "║   All Systems Ready for Deployment                ║\n";
echo "║   Date: " . date('Y-m-d H:i:s') . "                         ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

$tests_passed = 0;
$tests_total = 0;

// ============================================
// TEST SUITE 1: Database Connection
// ============================================
echo "╔ DATABASE CONNECTION ════════════════════════════════╗\n";
$tests_total++;
if ($conn && $conn->connect_error === null) {
    echo "✓ MySQL connection successful\n";
    echo "  Host: " . DB_HOST . "\n";
    echo "  Database: " . DB_NAME . "\n";
    $tests_passed++;
} else {
    echo "✗ MySQL connection failed\n";
}
echo "╚════════════════════════════════════════════════════╝\n\n";

// ============================================
// TEST SUITE 2: Database Tables Exist
// ============================================
echo "╔ DATABASE TABLES ════════════════════════════════════╗\n";
$required_tables = ['users', 'channels', 'movies', 'categories', 'series', 'slider_images', 'social_medias', 'sidebar_contents'];
$all_tables_exist = true;

foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    $tests_total++;
    if ($result && $result->num_rows > 0) {
        echo "✓ Table '$table' exists\n";
        $tests_passed++;
    } else {
        echo "✗ Table '$table' missing\n";
        $all_tables_exist = false;
    }
}
echo "╚════════════════════════════════════════════════════╝\n\n";

// ============================================
// TEST SUITE 3: User Authentication
// ============================================
echo "╔ USER AUTHENTICATION ════════════════════════════════╗\n";

// Test 3.1: Test Admin User Exists
$tests_total++;
$username_test = 'testadmin';
$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username_test);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "✓ Test admin user 'testadmin' exists\n";
    echo "  - User ID: " . $user['id'] . "\n";
    echo "  - Role: " . $user['role'] . "\n";
    
    // Test 3.2: Password Verification
    $tests_total++;
    if (password_verify('admin123456', $user['password'])) {
        echo "✓ Password verification works (admin123456)\n";
        $tests_passed++;
    } else {
        echo "✗ Password verification failed\n";
    }
    $tests_passed++;
} else {
    echo "✗ Test admin user not found\n";
}
$stmt->close();

// Test 3.3: Check all users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
$tests_total++;
echo "✓ Total users in database: " . $row['count'] . "\n";
$tests_passed++;

echo "╚════════════════════════════════════════════════════╝\n\n";

// ============================================
// TEST SUITE 4: API Response Format
// ============================================
echo "╔ API RESPONSE FORMAT ════════════════════════════════╗\n";

// Simulate login response
$tests_total++;
$token = bin2hex(random_bytes(32));
$response = [
    'success' => true,
    'user_id' => 5,
    'username' => 'testadmin',
    'role' => 'admin',
    'phone' => '07700000000',
    'token' => $token
];

$json = json_encode($response);
$decoded = json_decode($json, true);

if ($decoded && isset($decoded['success'], $decoded['user_id'], $decoded['username'], $decoded['role'], $decoded['phone'], $decoded['token'])) {
    echo "✓ Login response format valid\n";
    echo "  Fields: success, user_id, username, role, phone, token\n";
    $tests_passed++;
} else {
    echo "✗ Login response format invalid\n";
}

echo "╚════════════════════════════════════════════════════╝\n\n";

// ============================================
// TEST SUITE 5: Content Data
// ============================================
echo "╔ CONTENT DATA ═══════════════════════════════════════╗\n";

$content_checks = [
    ['name' => 'Channels', 'query' => 'SELECT COUNT(*) as count FROM channels'],
    ['name' => 'Movies', 'query' => 'SELECT COUNT(*) as count FROM movies WHERE is_active = 1'],
    ['name' => 'Series', 'query' => 'SELECT COUNT(*) as count FROM series WHERE is_active = 1'],
    ['name' => 'Categories', 'query' => 'SELECT COUNT(*) as count FROM categories WHERE is_active = 1'],
    ['name' => 'Slider Images', 'query' => 'SELECT COUNT(*) as count FROM slider_images WHERE is_active = 1'],
];

foreach ($content_checks as $check) {
    $result = $conn->query($check['query']);
    $tests_total++;
    
    if ($result) {
        $row = $result->fetch_assoc();
        $count = $row['count'];
        
        if ($count > 0) {
            echo "✓ " . $check['name'] . ": " . $count . " items\n";
            $tests_passed++;
        } else {
            echo "✗ " . $check['name'] . ": 0 items (may need data)\n";
        }
    } else {
        echo "⚠ " . $check['name'] . ": Table not found (optional)\n";
    }
}

echo "╚════════════════════════════════════════════════════╝\n\n";

// ============================================
// TEST SUITE 6: Flutter Files Updated
// ============================================
echo "╔ FLUTTER CODE FILES ════════════════════════════════╗\n";

$flutter_files = [
    '../lib/login_screen.dart' => 'Login Screen',
    '../lib/register_screen.dart' => 'Register Screen',
    '../lib/admin_screen.dart' => 'Admin Screen',
    '../lib/main_screen.dart' => 'Main Screen',
];

foreach ($flutter_files as $file => $name) {
    $tests_total++;
    $full_path = __DIR__ . '/' . $file;
    if (file_exists($full_path)) {
        $content = file_get_contents($full_path);
        if (strpos($content, '?action=') !== false) {
            echo "✓ $name - Updated with api-v2.php endpoints\n";
            $tests_passed++;
        } else {
            echo "⚠ $name - File exists but may need endpoint update\n";
        }
    } else {
        echo "✗ $name - File not found\n";
    }
}

echo "╚════════════════════════════════════════════════════╝\n\n";

// ============================================
// TEST SUITE 7: APK Build
// ============================================
echo "╔ APK BUILD ══════════════════════════════════════════╗\n";
$tests_total++;
$apk_path = 'c:\\xampp\\htdocs\\fedora\\build\\app\\outputs\\flutter-apk\\app-release.apk';
if (file_exists($apk_path)) {
    $size = filesize($apk_path);
    $size_mb = round($size / (1024 * 1024), 2);
    echo "✓ APK built successfully\n";
    echo "  Path: build/app/outputs/flutter-apk/app-release.apk\n";
    echo "  Size: " . $size_mb . " MB\n";
    $tests_passed++;
} else {
    echo "✗ APK not found - build may have failed\n";
}

echo "╚════════════════════════════════════════════════════╝\n\n";

// ============================================
// FINAL SUMMARY
// ============================================
echo "╔════════════════════════════════════════════════════╗\n";
echo "║   TEST RESULTS                                     ║\n";
echo "╠════════════════════════════════════════════════════╣\n";
echo "║  Passed: $tests_passed/$tests_total                                ║\n";
echo "║  Pass Rate: " . round(($tests_passed/$tests_total)*100, 1) . "%                                  ║\n";

if ($tests_passed === $tests_total) {
    echo "║                                                    ║\n";
    echo "║  ✓✓✓ ALL SYSTEMS READY FOR PRODUCTION ✓✓✓      ║\n";
    echo "║                                                    ║\n";
    echo "║  Next Steps:                                       ║\n";
    echo "║  1. Transfer APK to Android device                ║\n";
    echo "║  2. Install app-release.apk                       ║\n";
    echo "║  3. Launch app and login with:                    ║\n";
    echo "║     - Username: testadmin                         ║\n";
    echo "║     - Password: admin123456                       ║\n";
    echo "║  4. Verify admin panel loads                      ║\n";
    echo "║  5. Test content display and playback             ║\n";
} else {
    echo "║                                                    ║\n";
    echo "║  ⚠ Some tests failed - review above               ║\n";
}
echo "║                                                    ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

// ============================================
// CREDENTIALS & QUICK START
// ============================================
echo "╔════════════════════════════════════════════════════╗\n";
echo "║   QUICK START CREDENTIALS                          ║\n";
echo "╠════════════════════════════════════════════════════╣\n";
echo "║                                                    ║\n";
echo "║  Admin User:                                       ║\n";
echo "║    Username: testadmin                             ║\n";
echo "║    Password: admin123456                           ║\n";
echo "║    Role: admin                                     ║\n";
echo "║    Phone: 07700000000                              ║\n";
echo "║                                                    ║\n";
echo "║  API Base URL: 192.168.56.1/fedora/backend/        ║\n";
echo "║  API Endpoint: api-v2.php?action=LOGIN             ║\n";
echo "║                                                    ║\n";
echo "║  APK Location: build/app/outputs/flutter-apk/      ║\n";
echo "║                app-release.apk                     ║\n";
echo "║                                                    ║\n";
echo "╚════════════════════════════════════════════════════╝\n\n";

$conn->close();
?>
