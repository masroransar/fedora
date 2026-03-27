<?php
// api-v2.php - Simpler API using query parameters
// Usage: api-v2.php?action=channels, api-v2.php?action=movies, etc.

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

try {
    require_once 'config.php';
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];
    
    // Route requests
    switch ($action) {
        case 'test':
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'API is working']);
            break;
            
        case 'channels':
            if ($method == 'GET') {
                get_channels_v2();
            }
            break;
            
        case 'movies':
            if ($method == 'GET') {
                get_movies_v2();
            }
            break;
            
        case 'series':
            if ($method == 'GET') {
                get_series_v2();
            }
            break;
            
        case 'categories':
            if ($method == 'GET') {
                get_categories_v2();
            }
            break;
            
        case 'slider':
            if ($method == 'GET') {
                get_sliders_v2();
            }
            break;
            
        case 'social':
            if ($method == 'GET') {
                get_social_v2();
            }
            break;
            
        case 'sidebar':
            if ($method == 'GET') {
                get_sidebar_v2();
            }
            break;
            
        case 'login':
            if ($method == 'POST') {
                handle_login_v2();
            }
            break;
            
        case 'register':
            if ($method == 'POST') {
                handle_register_v2();
            }
            break;
            
        case 'update_profile':
            if ($method == 'POST') {
                update_user_profile_v2();
            }
            break;
            
        case 'change_password':
            if ($method == 'POST') {
                change_user_password_v2();
            }
            break;
            
        case 'upload_profile_picture':
            if ($method == 'POST') {
                upload_profile_picture_v2();
            }
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Unknown action: ' . $action]);
            break;
    }
    
    if (isset($conn)) {
        $conn->close();
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

// ============= FUNCTIONS =============

function get_channels_v2() {
    global $conn;
    $result = $conn->query("SELECT id, name, url, image_url, created_at FROM channels ORDER BY created_at DESC");
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $channels = [];
    while ($row = $result->fetch_assoc()) {
        $channels[] = $row;
    }
    
    http_response_code(200);
    echo json_encode(['success' => true, 'channels' => $channels]);
}

function get_movies_v2() {
    global $conn;
    $result = $conn->query("SELECT id, title, description, video_url, thumbnail_url, category_id, rating FROM movies WHERE is_active = 1 ORDER BY created_at DESC");
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $movies = [];
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
    
    http_response_code(200);
    echo json_encode(['success' => true, 'movies' => $movies]);
}

function get_series_v2() {
    global $conn;
    $result = $conn->query("SELECT id, title, description, thumbnail_url, category_id, total_episodes FROM series WHERE is_active = 1 ORDER BY created_at DESC");
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $series = [];
    while ($row = $result->fetch_assoc()) {
        $series[] = $row;
    }
    
    http_response_code(200);
    echo json_encode(['success' => true, 'series' => $series]);
}

function get_categories_v2() {
    global $conn;
    $result = $conn->query("SELECT id, name, description, icon_class FROM categories WHERE is_active = 1 ORDER BY name ASC");
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    http_response_code(200);
    echo json_encode(['success' => true, 'categories' => $categories]);
}

function get_sliders_v2() {
    global $conn;
    $result = $conn->query("SELECT id, title, description, image_url, link_url FROM slider_images WHERE is_active = 1 ORDER BY created_at DESC");
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $sliders = [];
    while ($row = $result->fetch_assoc()) {
        $sliders[] = $row;
    }
    
    http_response_code(200);
    echo json_encode(['success' => true, 'sliders' => $sliders]);
}

function get_social_v2() {
    global $conn;
    $result = $conn->query("SELECT id, platform, url, icon_class FROM social_links WHERE is_active = 1 ORDER BY sort_order ASC");
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $links = [];
    while ($row = $result->fetch_assoc()) {
        $links[] = $row;
    }
    
    http_response_code(200);
    echo json_encode(['success' => true, 'social' => $links]);
}

function get_sidebar_v2() {
    global $conn;
    $result = $conn->query("SELECT id, title, link, icon_class FROM sidebar_items WHERE is_active = 1 ORDER BY sort_order ASC");
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    http_response_code(200);
    echo json_encode(['success' => true, 'sidebar' => $items]);
}

function handle_login_v2() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Username and password required']);
        return;
    }
    
    $username = $input['username'];
    $password = $input['password'];
    
    $stmt = $conn->prepare("SELECT id, username, password, role, phone, name, profile_picture FROM users WHERE username = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
        return;
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
        $stmt->close();
        return;
    }
    
    $user = $result->fetch_assoc();
    
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
        $stmt->close();
        return;
    }
    
    $stmt->close();
    
    // Generate token
    $token = bin2hex(random_bytes(32));
    
    // Save token to database
    $update_stmt = $conn->prepare("UPDATE users SET token = ? WHERE id = ?");
    $update_stmt->bind_param("si", $token, $user['id']);
    $update_stmt->execute();
    $update_stmt->close();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'user_id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'phone' => $user['phone'],
        'name' => $user['name'],
        'profile_picture' => $user['profile_picture'],
        'token' => $token
    ]);
}


function handle_register_v2() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['username']) || !isset($input['password']) || !isset($input['phone'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    $phone = trim($input['phone']);
    
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
        return;
    }
    
    if (!is_valid_iraqi_phone($phone)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid Iraqi phone number']);
        return;
    }
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR phone = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
        return;
    }
    
    $stmt->bind_param("ss", $username, $phone);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Username or phone already exists']);
        $stmt->close();
        return;
    }
    $stmt->close();
    
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, phone, password, role) VALUES (?, ?, ?, 'user')");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database error']);
        return;
    }
    
    $stmt->bind_param("sss", $username, $phone, $hashed);
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Registration failed']);
    }
    $stmt->close();
}

// Update user profile (name only)
function update_user_profile_v2() {
    global $conn;
    
    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $user_id = $data['user_id'] ?? null;
    $token = $data['token'] ?? null;
    $name = $data['name'] ?? null;
    
    if (!$user_id || !$token || !$name) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    // Verify user exists
    $stmt = $conn->prepare("SELECT id, token FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        $stmt->close();
        return;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify token
    if ($user['token'] !== $token) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid token']);
        return;
    }
    
    // Update profile
    $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
    $stmt->bind_param("si", $name, $user_id);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Update failed']);
    }
    $stmt->close();
}

// Change user password
function change_user_password_v2() {
    global $conn;
    
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    $user_id = $data['user_id'] ?? null;
    $token = $data['token'] ?? null;
    $current_password = $data['current_password'] ?? null;
    $new_password = $data['new_password'] ?? null;
    
    if (!$user_id || !$token || !$current_password || !$new_password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    if (strlen($new_password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters']);
        return;
    }
    
    // Verify user exists
    $stmt = $conn->prepare("SELECT id, password, token FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        $stmt->close();
        return;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify token
    if ($user['token'] !== $token) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid token']);
        return;
    }
    
    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid password']);
        return;
    }
    
    // Update password
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed, $user_id);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Password change failed']);
    }
    $stmt->close();
}

// Upload profile picture
function upload_profile_picture_v2() {
    global $conn;
    
    if (!isset($_FILES['picture']) || !isset($_POST['user_id']) || !isset($_POST['token'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        return;
    }
    
    $user_id = $_POST['user_id'];
    $token = $_POST['token'];
    $file = $_FILES['picture'];
    
    // Verify user exists
    $stmt = $conn->prepare("SELECT id, token FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'User not found']);
        $stmt->close();
        return;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify token
    if ($user['token'] !== $token) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid token']);
        return;
    }
    
    // Validate file
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $filename = $file['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: jpg, jpeg, png, gif']);
        return;
    }
    
    if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'File too large. Max size: 5MB']);
        return;
    }
    
    // Create upload directory
    $upload_dir = dirname(__FILE__) . '/uploads/profiles/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Generate unique filename
    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
    $upload_path = $upload_dir . $new_filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'File upload failed']);
        return;
    }
    
    // Save path to database
    $profile_picture_url = 'http://192.168.56.1/fedora/backend/uploads/profiles/' . $new_filename;
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->bind_param("si", $profile_picture_url, $user_id);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Profile picture uploaded', 'profile_picture' => $profile_picture_url]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
    }
    $stmt->close();
}
?>
