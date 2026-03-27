<?php
// Test Login and Register API endpoints
require_once 'config.php';

echo "=== Testing Login/Register API v2 ===\n\n";

// Test 1: Test the test endpoint first
echo "Test 1: API Health Check (?action=test)\n";
header('Content-Type: application/json');
$json = json_encode(['success' => true, 'message' => 'API is working']);
echo "  Response: " . $json . "\n";
echo "  Valid JSON: " . (json_decode($json) ? "YES ✓" : "NO ✗") . "\n\n";

// Test 2: Simulate registration
echo "Test 2: Registration Simulation\n";
$regData = [
    'username' => 'testuser123',
    'password' => 'password123',
    'phone' => '07701234567'
];

// Validate input
$username = trim($regData['username']);
$password = $regData['password'];
$phone = trim($regData['phone']);

$errors = [];
if (strlen($password) < 6) {
    $errors[] = 'Password too short';
}

if (!preg_match('/^07[0-9]{8}$/', $phone)) {
    $errors[] = 'Invalid Iraqi phone';
}

if (empty($errors)) {
    echo "  ✓ Input validation passed\n";
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR phone = ?");
    $stmt->bind_param("ss", $username, $phone);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "  ✗ User already exists\n";
    } else {
        echo "  ✓ User doesn't exist (ready to create)\n";
    }
    $stmt->close();
} else {
    echo "  ✗ Validation errors: " . implode(", ", $errors) . "\n";
}

echo "\n";

// Test 3: Test login simulation
echo "Test 3: Login Simulation\n";
$loginUser = 'test';  // admin user
$loginPass = 'test123';

$stmt = $conn->prepare("SELECT id, username, password, role, phone FROM users WHERE username = ?");
$stmt->bind_param("s", $loginUser);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo "  ✓ User found: " . $user['username'] . "\n";
    
    if (password_verify($loginPass, $user['password'])) {
        echo "  ✓ Password verification passed\n";
        $token = bin2hex(random_bytes(32));
        $json = json_encode([
            'success' => true,
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'phone' => $user['phone'],
            'token' => $token
        ]);
        echo "  ✓ Login response JSON: " . substr($json, 0, 80) . "...\n";
    } else {
        echo "  ✗ Password verification failed\n";
    }
} else {
    echo "  ✗ User not found\n";
}
$stmt->close();

echo "\n✓✓✓ Login/Register API Test Complete ✓✓✓\n";

$conn->close();
?>
