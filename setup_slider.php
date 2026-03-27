<?php
// setup_slider.php - Execute the slider table setup

require_once 'config.php';

$sql = file_get_contents('setup_slider_table.sql');

if ($conn->multi_query($sql)) {
    echo "SQL executed successfully\n";

    // Check if table was created
    $result = $conn->query("SHOW TABLES LIKE 'slider_images'");
    if ($result && $result->num_rows > 0) {
        echo "slider_images table created successfully\n";

        // Check row count
        $count = $conn->query("SELECT COUNT(*) as count FROM slider_images");
        $row = $count->fetch_assoc();
        echo "Rows inserted: " . $row['count'] . "\n";
    } else {
        echo "Table creation failed\n";
    }
} else {
    echo "SQL execution failed: " . $conn->error . "\n";
}

$conn->close();
?>