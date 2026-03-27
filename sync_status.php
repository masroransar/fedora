<?php
// sync_status.php - Check sync status between Web Admin, API, and Flutter

header('Content-Type: application/json');
require_once 'config.php';

$status = [
    'timestamp' => date('Y-m-d H:i:s'),
    'system_status' => 'Checking...',
    'components' => [],
    'issues' => []
];

try {
    // 1. Check database connection
    if ($conn->connect_error) {
        $status['components']['database'] = 'ERROR: ' . $conn->connect_error;
        $status['issues'][] = 'Database connection failed';
    } else {
        $status['components']['database'] = 'OK';
    }

    // 2. Check slider_images table
    $result = $conn->query("SHOW TABLES LIKE 'slider_images'");
    if (!$result || $result->num_rows == 0) {
        $status['components']['slider_images_table'] = 'MISSING';
        $status['issues'][] = 'Table slider_images does not exist. Create it from setup_slider_table.sql';
    } else {
        // Count images
        $count = $conn->query("SELECT COUNT(*) as total FROM slider_images");
        $row = $count->fetch_assoc();
        $status['components']['slider_images_table'] = 'OK (Total: ' . $row['total'] . ' images)';
    }

    // 3. Check users table
    $result = $conn->query("SHOW TABLES LIKE 'users'");
    if (!$result || $result->num_rows == 0) {
        $status['components']['users_table'] = 'MISSING';
        $status['issues'][] = 'Table users does not exist';
    } else {
        $count = $conn->query("SELECT COUNT(*) as total FROM users");
        $row = $count->fetch_assoc();
        $status['components']['users_table'] = 'OK (Total: ' . $row['total'] . ' users)';
    }

    // 4. Check API file
    if (file_exists('api.php')) {
        $status['components']['api.php'] = 'OK';
    } else {
        $status['components']['api.php'] = 'MISSING';
        $status['issues'][] = 'api.php not found';
    }

    // 5. Check slider_functions.php file
    if (file_exists('slider_functions.php')) {
        $status['components']['slider_functions.php'] = 'OK';
    } else {
        $status['components']['slider_functions.php'] = 'MISSING';
        $status['issues'][] = 'slider_functions.php not found';
    }

    // 6. Test get_slider_api
    if (file_exists('api.php') && file_exists('slider_functions.php')) {
        ob_start();
        include 'slider_functions.php';
        $slider_count = 0;
        try {
            $result = $conn->query("SELECT COUNT(*) as total FROM slider_images WHERE is_active = 1");
            if ($result) {
                $row = $result->fetch_assoc();
                $slider_count = $row['total'];
            }
        } catch (Exception $e) {
            $status['issues'][] = 'Error querying sliders: ' . $e->getMessage();
        }
        ob_end_clean();
        $status['components']['active_sliders'] = 'OK (' . $slider_count . ' active)';
    }

    // Final status
    if (count($status['issues']) == 0) {
        $status['system_status'] = 'ALL SYSTEMS OPERATIONAL ✅';
    } else {
        $status['system_status'] = 'ISSUES DETECTED ⚠️';
    }

    http_response_code(200);
    echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'system_status' => 'ERROR',
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}

?>
