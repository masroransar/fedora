<?php
require_once 'config.php';
$result = $conn->query('SELECT username, role FROM users');
while($row = $result->fetch_assoc()) {
    echo $row['username'] . ' - ' . $row['role'] . PHP_EOL;
}
?>