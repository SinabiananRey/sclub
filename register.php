<?php
session_start();
include 'db_connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // ✅ Step 1: Check if username or email already exists
    $check_user_query = "SELECT user_id FROM users WHERE username = ? OR email = ?";
    $stmt_check = $conn->prepare($check_user_query);
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $_SESSION['error_message'] = "Username or email is already taken.";
    } else {
        // ✅ Step 2: Insert user into `users`, default `verified` status is FALSE
        $verification_code = md5(uniqid(rand(), true));
        $register_user_query = "INSERT INTO users (username, email, password, role, verified, verification_code) VALUES (?, ?, ?, 'member', 0, ?)";
        $stmt = $conn->prepare($register_user_query);
        $stmt->bind_param("ssss", $username, $email, $password, $verification_code);

        if ($stmt->execute()) {
            // ✅ Step 3: Get the new user ID
            $user_id = $stmt->insert_id;

            // ✅ Step 4: Automatically register them in `members`
            $register_member_query = "INSERT INTO members (user_id, full_name, email, joined_date) VALUES (?, ?, ?, NOW())";
            $stmt_member = $conn->prepare($register_member_query);
            $stmt_member->bind_param("iss", $user_id, $username, $email);
            $stmt_member->execute();

            // ✅ Step 5: Send Verification Email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // ✅ Use a real SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'your-email@gmail.com'; // ✅ Replace with your Gmail address
                $mail->Password = 'your-app-password'; // ✅ Use an app password for Gmail
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('your-email@gmail.com', 'Sports Club');
                $mail->addAddress($email);
                $mail->Subject = 'Verify Your Email';
                $mail->Body = "Hello $username,\n\nClick the link below to verify your account:\n\nhttp://yourwebsite.com/verify.php?code=$verification_code\n\nThank you!";

                $mail->send();
                $_SESSION['success_message'] = "Registration successful! Please check your email for verification.";
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Email error: {$mail->ErrorInfo}";
            }

            header("Location: register_success.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Sports Club</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f9fafb; color: #333; }
        .container { max-width: 400px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; }
        h2 { text-align: center; color: #1e3a8a; }
        input, button { width: 100%; padding: 12px; margin-top: 10px; border-radius: 6px; border: 1px solid #ddd; }
        button { background: #3b82f6; color: white; font-weight: bold; cursor: pointer; }
        button:hover { background: #2563eb; }
        .message { text-align: center; margin: 20px 0; font-weight: 500; color: #0369a1; }
    </style>
</head>
<body>

<div class="container">
    <h2>Register</h2>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="message"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Sign Up</button>
    </form>
</div>

</body>
</html>