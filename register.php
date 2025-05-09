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
            $mail->Password = 'rard mpnw rozl pqbr';         // ✅ Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->SMTPDebug = 2; // ✅ Enable debugging for errors
            $mail->Debugoutput = 'html'; // ✅ Make errors readable
            
            // ✅ Email setup
            $mail->setFrom('sinabiananrey@gmail.com', 'Sports Club Admin');
            $mail->addAddress($email);
            $mail->Subject = 'Welcome to the Sports Club!';
            $mail->Body = "Hello $full_name,\n\nWelcome to the Sports Club! Your membership has been successfully registered.\n\nEnjoy your journey with us!\n\nBest regards,\nSports Club Admin";

            // ✅ Send email and log errors if needed
            if ($mail->send()) {
                $_SESSION['confirmation_message'] = "Registration successful! A confirmation email has been sent.";
                header("Location: login.php?registration_success=true");
                exit();
            } else {
                error_log("❌ Email Error: " . $mail->ErrorInfo); // ✅ Log errors for debugging
                echo "<p class='error'>Email could not be sent: {$mail->ErrorInfo}</p>";
            }
        } catch (Exception $e) {
            error_log("❌ SMTP Error: {$mail->ErrorInfo}"); // ✅ Log SMTP errors
            echo "<p class='error'>SMTP Error: {$mail->ErrorInfo}</p>";
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
    <title>Sign Up - Sports Club</title>
    <style>
        body {
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        .signup-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        .signup-container h2 {
            text-align: center;
            color: #22336e;
            margin-bottom: 25px;
        }

        .signup-container label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .signup-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }

        .signup-container button {
            width: 100%;
            padding: 12px;
            background-color: #22336e;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .signup-container button:hover {
            background-color: #1a2957;
        }

        .signup-container .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .signup-container .login-link a {
            color: #22336e;
            text-decoration: none;
            font-weight: bold;
        }

        .message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="signup-container">
        <h2>Sign Up</h2>

        <?php if (isset($_SESSION['confirmation_message'])): ?>
            <div class="message"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="full_name" required>

            <label>Email</label>
            <input type="email" name="email" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit">Sign Up</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

</body>
</html>