<?php
session_start();
include 'db_connect.php';

if (isset($_GET['code'])) {
    $verification_code = $_GET['code'];

    $query = "SELECT user_id FROM users WHERE verification_code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close(); // ✅ Close after execution

    if ($user) {
        $query_update = "UPDATE users SET verified = 1 WHERE verification_code = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("s", $verification_code);
        $stmt_update->execute();
        $stmt_update->close();

        echo "<p style='color: green;'>Your account has been verified! You can now log in.</p>";
    } else {
        echo "<p class='error'>Invalid verification code.</p>";
    }
}
?>