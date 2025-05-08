<?php
session_start();
include 'db_connect.php';
require 'vendor/autoload.php'; // ✅ Load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = htmlspecialchars($_POST['full_name'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    // ✅ Ensure unique username
    do {
        $username = 'user' . rand(1000, 9999);
        $query_check_username = "SELECT COUNT(*) AS count FROM users WHERE username = ?";
        $stmt_check = $conn->prepare($query_check_username);
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();
    } while ($count > 0);

    // ✅ Insert user into `users` table
    $query_users = "INSERT INTO users (username, email, password, role, verified) 
                    VALUES (?, ?, ?, 'member', 1)";
    $stmt_users = $conn->prepare($query_users);
    $stmt_users->bind_param("sss", $username, $email, $password);
    
    if ($stmt_users->execute()) {
        $user_id = $stmt_users->insert_id;
        $stmt_users->close();

        // ✅ Insert user into `members` table
        $query_members = "INSERT INTO members (user_id, full_name, email, password, role, joined_date) 
                          VALUES (?, ?, ?, ?, 'member', CURDATE())";
        $stmt_members = $conn->prepare($query_members);
        $stmt_members->bind_param("isss", $user_id, $full_name, $email, $password);
        $stmt_members->execute();
        $stmt_members->close();

        // ✅ Send confirmation email using PHPMailer
        $mail = new PHPMailer(true);
        try {
            // ✅ Secure SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sinabiananrey@gmail.com'; // ✅ Gmail address
            $mail->Password = 'gklswbprilhazyip';         // ✅ Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->SMTPDebug = 0; // Disable debugging in production
            
            // ✅ Email setup
            $mail->setFrom('your-email@example.com', 'Sports Club Admin');
            $mail->addAddress($email);
            $mail->Subject = 'Welcome to the Sports Club!';
            $mail->Body = "Hello $full_name,\n\nWelcome to the Sports Club! Your membership has been successfully registered.\n\nEnjoy your journey with us!\n\nBest regards,\nSports Club Admin";

            // ✅ Send email
            $mail->send();
            $_SESSION['confirmation_message'] = "Registration successful! A confirmation email has been sent.";
            header("Location: login.php?registration_success=true");
            exit();
        } catch (Exception $e) {
            echo "<p class='error'>Email could not be sent: {$mail->ErrorInfo}</p>";
        }
    } else {
        echo "<p class='error'>Error adding member: " . $stmt_users->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Sports Club</title>
</head>
<body>
    <div class="container">
        <h2>Member Registration</h2>

        <?php if (isset($_SESSION['confirmation_message'])): ?>
            <div class="message"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Full Name:</label>
            <input type="text" name="full_name" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>