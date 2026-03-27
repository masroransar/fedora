<?php
// check_database.php - Check database status and tables

header('Content-Type: application/json');

require_once 'config.php';

$status = [
    'database_connected' => false,
    'tables' => [],
    'errors' => [],
    'timestamp' => date('Y-m-d H:i:s')
];

try {
    // Check connection
    if ($conn->connect_error) {
        $status['errors'][] = "Connection failed: " . $conn->connect_error;
        http_response_code(500);
        echo json_encode($status);
        exit;
    }
    
    $status['database_connected'] = true;
    
    // Check tables
    $tables = ['users', 'slider_images', 'otp_codes'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            // Get table info
            $info = $conn->query("SELECT COUNT(*) as count FROM $table");
            $row = $info->fetch_assoc();
            $status['tables'][$table] = [
                'exists' => true,
                'rows' => $row['count']
            ];
        } else {
            $status['tables'][$table] = [
                'exists' => false,
                'rows' => 0
            ];
        }
    }
    
    // Check slider images specifically
    if ($status['tables']['slider_images']['exists']) {
        $result = $conn->query("DESC slider_images");
        $columns = [];
        while ($col = $result->fetch_assoc()) {
            $columns[] = $col['Field'];
        }
        $status['tables']['slider_images']['columns'] = $columns;
    }
    
    http_response_code(200);
    echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    $status['errors'][] = $e->getMessage();
    http_response_code(500);
    echo json_encode($status);
}

$conn->close();
?>
