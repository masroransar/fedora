<?php
// verify_otp.php - OTP verification
session_start();
require_once 'config.php';

$message = '';

if (!isset($_SESSION['temp_user'])) {
    header('Location: register.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = trim($_POST['otp']);
    $phone = $_SESSION['temp_user']['phone'];

    // Validate OTP
    $stmt = $conn->prepare("SELECT id FROM otp_codes WHERE phone = ? AND code = ? AND expires_at > NOW() AND used = 0");
    $stmt->bind_param("ss", $phone, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Mark OTP as used
        $stmt = $conn->prepare("UPDATE otp_codes SET used = 1 WHERE phone = ? AND code = ?");
        $stmt->bind_param("ss", $phone, $otp);
        $stmt->execute();

        // Insert user
        $user = $_SESSION['temp_user'];
        $stmt = $conn->prepare("INSERT INTO users (username, password, phone) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user['username'], $user['password'], $user['phone']);
        $stmt->execute();

        // Clear session
        unset($_SESSION['temp_user']);

        $message = 'Registration successful! <a href="login.php">Login now</a>';
    } else {
        $message = 'Invalid or expired OTP.';
    }
    $stmt->close();
}

// Resend OTP
if (isset($_POST['resend'])) {
    $phone = $_SESSION['temp_user']['phone'];
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    $stmt = $conn->prepare("INSERT INTO otp_codes (phone, code, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $phone, $otp, $expires_at);
    $stmt->execute();

    send_otp_sms($phone, $otp);
    $message = 'OTP resent.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 300px; }
        input { display: block; margin: 10px 0; padding: 8px; width: 100%; }
        button { padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; margin-right: 10px; }
        .message { color: green; }
    </style>
</head>
<body>
    <h2>Verify Your Phone</h2>
    <p>Enter the 6-digit code sent to <?php echo $_SESSION['temp_user']['phone']; ?></p>
    <form method="post">
        <input type="text" name="otp" placeholder="Enter OTP" maxlength="6" required>
        <button type="submit">Verify</button>
        <button type="submit" name="resend">Resend OTP</button>
    </form>
    <p class="message"><?php echo $message; ?></p>
</body>
</html>