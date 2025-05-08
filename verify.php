<?php
include 'db_connect.php';
session_start();

if (isset($_GET['code'])) {
    $verification_code = $_GET['code'];
    $update_query = "UPDATE users SET verified = 1 WHERE verification_code = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("s", $verification_code);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Your account has been verified! Please log in.";
        header("Location: login.php");
        exit();
    } else {
        echo "Invalid verification code!";
    }
}
?>