<?php
// slider_functions.php - Slider API functions

/**
 * Get all active slider images
 */
function get_slider_api() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            SELECT id, title, image_url, description, sort_order, is_active, created_at 
            FROM slider_images 
            WHERE is_active = 1 
            ORDER BY sort_order ASC, created_at DESC
        ");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $sliders = [];
        while ($row = $result->fetch_assoc()) {
            $sliders[] = $row;
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Sliders retrieved successfully',
            'sliders' => $sliders,
            'count' => count($sliders)
        ]);
        
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving sliders',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Add new slider image (Admin only)
 */
function add_slider_api() {
    global $conn;
    
    try {
        // Validate admin token
        $token = $_GET['token'] ?? '';
        if (!validate_admin_token($token)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized - Admin access required'
            ]);
            return;
        }
        
        // Get JSON data
        $data = json_decode(file_get_contents('php://input'), true);
        
        $title = trim($data['title'] ?? '');
        $image_url = trim($data['image_url'] ?? '');
        $description = trim($data['description'] ?? '');
        $sort_order = intval($data['sort_order'] ?? 0);
        
        // Validate required fields
        if (empty($title) || empty($image_url)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Title and image_url are required'
            ]);
            return;
        }
        
        // Insert slider
        $stmt = $conn->prepare("
            INSERT INTO slider_images (title, image_url, description, sort_order, is_active)
            VALUES (?, ?, ?, ?, 1)
        ");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("sssi", $title, $image_url, $description, $sort_order);
        
        if ($stmt->execute()) {
            $slider_id = $stmt->insert_id;
            
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => 'Slider added successfully',
                'slider' => [
                    'id' => $slider_id,
                    'title' => $title,
                    'image_url' => $image_url,
                    'description' => $description,
                    'sort_order' => $sort_order,
                    'is_active' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error adding slider',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Delete slider image by ID (Admin only)
 */
function delete_slider_api() {
    global $conn;
    
    try {
        // Validate admin token
        $token = $_GET['token'] ?? '';
        if (!validate_admin_token($token)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized - Admin access required'
            ]);
            return;
        }
        
        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid slider ID'
            ]);
            return;
        }
        
        // Check if slider exists
        $stmt = $conn->prepare("SELECT id FROM slider_images WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows == 0) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Slider not found'
            ]);
            $stmt->close();
            return;
        }
        
        $stmt->close();
        
        // Delete slider
        $stmt = $conn->prepare("DELETE FROM slider_images WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Slider deleted successfully',
                'deleted_id' => $id
            ]);
        } else {
            throw new Exception("Delete failed: " . $stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting slider',
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Validate admin token
 */
function validate_admin_token($token) {
    if (empty($token)) {
        return false;
    }
    
    try {
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);
        
        if (count($parts) !== 3) {
            return false;
        }
        
        list($user_id, $username, $role) = $parts;
        
        // Verify role is admin
        return $role === 'admin';
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Get user details from token
 */
function get_user_from_token($token) {
    if (empty($token)) {
        return null;
    }
    
    try {
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);
        
        if (count($parts) !== 3) {
            return null;
        }
        
        list($user_id, $username, $role) = $parts;
        
        return [
            'user_id' => $user_id,
            'username' => $username,
            'role' => $role
        ];
    } catch (Exception $e) {
        return null;
    }
}

// --- Content API helpers for movies, series, categories, social, sidebar, channels ---

function get_json_from_table($table, $where = '', $params = []) {
    global $conn;
    $sql = "SELECT * FROM $table" . ($where ? " WHERE $where" : "");
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'message' => "Prepare failed: $conn->error"];
    }

    if (!empty($params)) {
        $stmt->bind_param(...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return ['success' => true, 'data' => $data];
}

function get_channels_api() {
    $result = get_json_from_table('channels', 'is_active = 1');
    if (!$result['success']) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch channels', 'error' => $result['message']]);
        return;
    }
    echo json_encode(['success' => true, 'channels' => $result['data']]);
}

function get_movies_api() {
    $result = get_json_from_table('movies', 'is_active = 1');
    if (!$result['success']) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch movies', 'error' => $result['message']]);
        return;
    }
    echo json_encode(['success' => true, 'movies' => $result['data']]);
}

function get_series_api() {
    $result = get_json_from_table('series', 'is_active = 1');
    if (!$result['success']) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch series', 'error' => $result['message']]);
        return;
    }
    echo json_encode(['success' => true, 'series' => $result['data']]);
}

function get_categories_api() {
    $result = get_json_from_table('categories');
    if (!$result['success']) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch categories', 'error' => $result['message']]);
        return;
    }
    echo json_encode(['success' => true, 'categories' => $result['data']]);
}

function get_social_api() {
    $result = get_json_from_table('social_links', 'is_active = 1');
    if (!$result['success']) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch social links', 'error' => $result['message']]);
        return;
    }
    echo json_encode(['success' => true, 'social' => $result['data']]);
}

function get_sidebar_api() {
    $result = get_json_from_table('sidebar_items', 'is_active = 1');
    if (!$result['success']) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch sidebar items', 'error' => $result['message']]);
        return;
    }
    echo json_encode(['success' => true, 'sidebar' => $result['data']]);
}

function admin_post_item($table, $fields) {
    global $conn;

    $token = $_GET['token'] ?? '';
    if (!validate_admin_token($token)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized - admin required']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if ($data === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid JSON body']);
        return;
    }

    $columns = [];
    $values = [];
    $types = '';
    $params = [];

    foreach ($fields as $field => $type) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => "$field is required"]);
            return;
        }
        $columns[] = $field;
        $values[] = '?';
        $types .= $type;
        $params[] = trim($data[$field]);
    }

    $sql = 'INSERT INTO ' . $table . ' (' . implode(',', $columns) . ') VALUES (' . implode(',', $values) . ')';
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Prepare failed', 'error' => $conn->error]);
        return;
    }

    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => ucfirst($table) . ' created', 'id' => $stmt->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Insert failed', 'error' => $stmt->error]);
    }
    $stmt->close();
}

function admin_delete_item($table) {
    global $conn;
    $token = $_GET['token'] ?? '';
    if (!validate_admin_token($token)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized - admin required']);
        return;
    }

    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid id']);
        return;
    }

    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Prepare failed', 'error' => $conn->error]);
        return;
    }

    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Delete failed', 'error' => $stmt->error]);
    }
    $stmt->close();
}

function add_channel_api() {
    admin_post_item('channels', ['name' => 's', 'url' => 's']);
}

function delete_channel_api() {
    admin_delete_item('channels');
}

function add_movie_api() {
    admin_post_item('movies', ['title' => 's', 'description' => 's', 'video_url' => 's', 'thumbnail_url' => 's', 'category_id' => 'i']);
}

function delete_movie_api() {
    admin_delete_item('movies');
}

function add_series_api() {
    admin_post_item('series', ['title' => 's', 'description' => 's', 'video_url' => 's', 'thumbnail_url' => 's', 'category_id' => 'i']);
}

function delete_series_api() {
    admin_delete_item('series');
}

function add_category_api() {
    admin_post_item('categories', ['name' => 's', 'slug' => 's', 'description' => 's']);
}

function delete_category_api() {
    admin_delete_item('categories');
}

function add_social_api() {
    admin_post_item('social_links', ['name' => 's', 'icon' => 's', 'url' => 's']);
}

function delete_social_api() {
    admin_delete_item('social_links');
}

function add_sidebar_api() {
    admin_post_item('sidebar_items', ['label' => 's', 'icon' => 's', 'route' => 's']);
}

function delete_sidebar_api() {
    admin_delete_item('sidebar_items');
}

?>
