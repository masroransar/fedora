<?php
// index.php - Main page
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Main Page</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .user-info { background: #f0f0f0; padding: 10px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="user-info">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <p>Role: <?php echo htmlspecialchars($role); ?></p>
        <a href="logout.php">Logout</a>
        <?php if ($role == 'admin'): ?>
            <a href="admin.php">Admin Panel</a>
        <?php endif; ?>
    </div>

    <h3>Main Content</h3>
    <p>This is the main page for users.</p>
</body>
</html>