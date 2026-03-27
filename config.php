<?php
// config.php - Database configuration and helper functions

// Database settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Change as needed
define('DB_PASS', ''); // Change as needed
define('DB_NAME', 'fedora_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8");

// Function to send OTP SMS (currently logs to file, replace with actual SMS gateway)
function send_otp_sms($phone, $otp) {
    // TODO: Integrate with Iraqi SMS gateway (Asiacell, Zain, Korek)
    // For now, log to file
    $log = "OTP sent to $phone: $otp at " . date('Y-m-d H:i:s') . "\n";
    file_put_contents('otp_log.txt', $log, FILE_APPEND);
    return true;
}

// Function to validate Iraqi phone number
function is_valid_iraqi_phone($phone) {
    // Iraqi phone numbers: start with 07, followed by 9 digits (total 11 digits)
    return preg_match('/^07[0-9]{9}$/', $phone);
}

// Function to hash password
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to verify password
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}
?>