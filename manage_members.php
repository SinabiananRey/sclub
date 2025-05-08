<?php
session_start();
include 'db_connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $verification_code = md5(uniqid(rand(), true));

    $query = "INSERT INTO users (username, email, password, role, verification_code, verified) VALUES (?, ?, ?, 'member', ?, 0)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $username, $email, $password, $verification_code);

    if ($stmt->execute()) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sinabiananrey@gmail.com';
            $mail->Password = 'rftw ogej ipih lzqz';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('your-email@example.com', 'Sports Club Admin');
            $mail->addAddress($email);
            $mail->Subject = 'Verify Your Sports Club Account';
            $mail->Body = "Hello $username,\n\nClick the link below to verify your account:\n\nhttp://yourwebsite.com/verify.php?code=$verification_code\n\nThank you!";

            $mail->send();
            $message = "<p class='success'>Member added successfully! Verification email sent.</p>";
        } catch (Exception $e) {
            $message = "<p class='error'>Email error: {$mail->ErrorInfo}</p>";
        }
    } else {
        $message = "<p class='error'>Error adding member: " . $stmt->error . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Members | Sports Club</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .toggle-btn {
            display: none;
            position: absolute;
            top: 15px;
            left: 15px;
            background-color: #003366;
            color: white;
            border: none;
            font-size: 24px;
            cursor: pointer;
            z-index: 1000;
            padding: 8px 12px;
            border-radius: 5px;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #003366;
            color: white;
            padding: 20px;
            transition: left 0.3s ease;
        }

        .sidebar h2 {
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .sidebar a:hover {
            background: #0055aa;
        }

        .main-content {
            flex-grow: 1;
            padding: 20px;
            background: #f4f4f4;
            overflow-y: auto;
        }

        h2 {
            margin-bottom: 20px;
        }

        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 500px;
        }

        form label {
            display: block;
            margin-top: 10px;
        }

        form input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
        }

        form button {
            margin-top: 15px;
            padding: 10px 20px;
            background: #003366;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                position: absolute;
                top: 0;
                left: -250px;
                height: 100%;
                z-index: 999;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                padding-top: 60px;
            }

            .toggle-btn {
                display: block;
            }
        }
    </style>
</head>
<body>

<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="container">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h2>Admin Panel</h2>
        <a href="manage_members.php">Manage Members</a>
        <a href="manage_equipment.php">Manage Equipment</a>
        <a href="post_announcements.php">Manage Announcements</a>
        <a href="view_reports.php">View Reports</a>
        <a href="settings.php">System Settings</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Add New Member</h2>

        <?php if (isset($message)) echo $message; ?>

        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" required>
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Add Member</button>
        </form>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }
</script>

</body>
</html>
