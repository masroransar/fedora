<?php
// register.php - User registration with OTP verification
session_start();
require_once 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);

    // Validation
    if (empty($username) || empty($password) || empty($confirm_password) || empty($phone)) {
        $message = 'All fields are required.';
    } elseif (strlen($password) < 6 || strlen($password) > 128) {
        $message = 'Password must be between 6 and 128 characters.';
    } elseif ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
    } elseif (!is_valid_iraqi_phone($phone)) {
        $message = 'Please enter a valid Iraqi phone number (07xxxxxxxx).';
    } else {
        // Check for duplicate username or phone
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR phone = ?");
        $stmt->bind_param("ss", $username, $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $message = 'Username or phone number already exists.';
        } else {
            // Generate OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

            // Store OTP
            $stmt = $conn->prepare("INSERT INTO otp_codes (phone, code, expires_at) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $phone, $otp, $expires_at);
            $stmt->execute();

            // Send OTP
            send_otp_sms($phone, $otp);

            // Store user data in session
            $_SESSION['temp_user'] = [
                'username' => $username,
                'password' => hash_password($password),
                'phone' => $phone
            ];

            // Redirect to OTP verification
            header('Location: verify_otp.php');
            exit();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 300px; }
        input { display: block; margin: 10px 0; padding: 8px; width: 100%; }
        button { padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
        .message { color: red; }
    </style>
</head>
<body>
    <h2>Register</h2>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password (6-128 chars)" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <input type="text" name="phone" placeholder="Iraqi Phone (07xxxxxxxx)" required>
        <button type="submit">Register</button>
    </form>
    <p class="message"><?php echo $message; ?></p>
    <a href="login.php">Already have an account? Login</a>
</body>
</html>