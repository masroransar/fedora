<?php
require 'config.php';

// Check all table structures
$tables = ['channels', 'movies', 'series', 'categories', 'social_links', 'sidebar_items'];

foreach ($tables as $table) {
    echo "<h3>Table: $table</h3>";
    $result = $conn->query("SHOW COLUMNS FROM $table");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
}

$conn->close();
?>
