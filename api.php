<?php
// api.php - Complete API for Flutter app
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$action = $request[0] ?? '';

// Routes
switch ($action) {
    // Authentication
    case 'login':
        if ($method == 'POST') {
            handle_login();
        }
        break;
    
    case 'register':
        if ($method == 'POST') {
            handle_register();
        }
        break;
    
    // Get data endpoints
    case 'slider':
        if ($method == 'GET') {
            get_sliders();
        }
        break;
    
    case 'channels':
        if ($method == 'GET') {
            get_channels();
        }
        break;
    
    case 'movies':
        if ($method == 'GET') {
            get_movies();
        }
        break;
    
    case 'series':
        if ($method == 'GET') {
            get_series();
        }
        break;
    
    case 'categories':
        if ($method == 'GET') {
            get_categories();
        }
        break;
    
    case 'social':
        if ($method == 'GET') {
            get_social();
        }
        break;
    
    case 'sidebar':
        if ($method == 'GET') {
            get_sidebar();
        }
        break;
    
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}

// ============= AUTHENTICATION =============

function handle_login() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Username and password required']);
        return;
    }
    
    $username = $input['username'];
    $password = $input['password'];
    
    $stmt = $conn->prepare("SELECT id, username, password, role, phone FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid username or password']);
        $stmt->close();
        return;
    }
    
    $user = $result->fetch_assoc();
    
    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid username or password']);
        $stmt->close();
        return;
    }
    
    $stmt->close();
    
    // Generate token (simple example - use JWT in production)
    $token = bin2hex(random_bytes(32));
    
    echo json_encode([
        'success' => true,
        'user_id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'phone' => $user['phone'],
        'token' => $token
    ]);
}

function handle_register() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['username']) || !isset($input['password']) || !isset($input['phone'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    $phone = trim($input['phone']);
    
    // Validate
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 6 characters']);
        return;
    }
    
    if (!is_valid_iraqi_phone($phone)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid Iraqi phone number']);
        return;
    }
    
    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR phone = ?");
    $stmt->bind_param("ss", $username, $phone);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Username or phone already exists']);
        $stmt->close();
        return;
    }
    $stmt->close();
    
    // Insert user
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, phone, password, role) VALUES (?, ?, ?, 'user')");
    $stmt->bind_param("sss", $username, $phone, $hashed);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Registration failed']);
    }
    $stmt->close();
}

// ============= GET ENDPOINTS =============

function get_sliders() {
    global $conn;
    
    $result = $conn->query("SELECT id, title, description, image_url, link_url, is_active FROM slider_images WHERE is_active = 1 ORDER BY created_at DESC");
    
    $sliders = [];
    while ($row = $result->fetch_assoc()) {
        $sliders[] = $row;
    }
    
    echo json_encode(['success' => true, 'sliders' => $sliders]);
}

function get_channels() {
    global $conn;
    
    $result = $conn->query("SELECT id, name, url, created_at FROM channels ORDER BY created_at DESC");
    
    $channels = [];
    while ($row = $result->fetch_assoc()) {
        $channels[] = $row;
    }
    
    echo json_encode(['success' => true, 'channels' => $channels]);
}

function get_movies() {
    global $conn;
    
    $result = $conn->query("SELECT id, title, description, video_url, thumbnail_url, category_id, rating, is_active FROM movies WHERE is_active = 1 ORDER BY created_at DESC");
    
    $movies = [];
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
    
    echo json_encode(['success' => true, 'movies' => $movies]);
}

function get_series() {
    global $conn;
    
    $result = $conn->query("SELECT id, title, description, thumbnail_url, category_id, total_episodes, is_active FROM series WHERE is_active = 1 ORDER BY created_at DESC");
    
    $series = [];
    while ($row = $result->fetch_assoc()) {
        $series[] = $row;
    }
    
    echo json_encode(['success' => true, 'series' => $series]);
}

function get_categories() {
    global $conn;
    
    $result = $conn->query("SELECT id, name, description, icon_class FROM categories WHERE is_active = 1 ORDER BY name ASC");
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    
    echo json_encode(['success' => true, 'categories' => $categories]);
}

function get_social() {
    global $conn;
    
    $result = $conn->query("SELECT id, platform, url, icon_class FROM social_links WHERE is_active = 1 ORDER BY sort_order ASC");
    
    $links = [];
    while ($row = $result->fetch_assoc()) {
        $links[] = $row;
    }
    
    echo json_encode(['success' => true, 'social' => $links]);
}

function get_sidebar() {
    global $conn;
    
    $result = $conn->query("SELECT id, title, link, icon_class FROM sidebar_items WHERE is_active = 1 ORDER BY sort_order ASC");
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    echo json_encode(['success' => true, 'sidebar' => $items]);
}

// Helper function
function is_valid_iraqi_phone($phone) {
    return preg_match('/^07[0-9]{8}$/', $phone) === 1;
}

if (isset($conn)) {
    $conn->close();
}
?>


