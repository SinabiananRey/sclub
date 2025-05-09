<?php
session_start();
include 'db_connect.php';

if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT user_id, username, email, password, role, verified FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        die("❌ Query Preparation Failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        die("❌ Query Execution Failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        if ($user['verified'] == 0) {
            $error = "Account not verified. Please check your email.";
        } elseif (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header("Location: " . ($user['role'] === 'admin' ? "admin_panel.php" : "member_dashboard.php"));
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Sports Club</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f3f3;
        }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-box {
            display: flex;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-radius: 10px;
            overflow: hidden;
            width: 800px;
            max-width: 100%;
        }
        .login-left {
            background: linear-gradient(to right, #1d3ede, #4794ff);
            color: white;
            padding: 40px;
            width: 50%;
        }
        .login-left h2 {
            margin-top: 0;
            font-size: 32px;
        }
        .login-left p {
            font-size: 16px;
        }
        .login-right {
            background: white;
            padding: 40px;
            width: 50%;
        }
        .login-right h3 {
            margin-top: 0;
            font-size: 24px;
        }
        .form-control {
            width: 100%;
            margin: 10px 0;
        }
        .form-control input {
            width: 100%;
            padding: 12px;
            border-radius: 25px;
            border: 1px solid #ccc;
            outline: none;
        }
        .form-control input:focus {
            border-color: #4794ff;
        }
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0 20px;
        }
        .form-options input {
            margin-right: 5px;
        }
        .form-options a {
            text-decoration: none;
            color: #7b26d0;
            font-size: 14px;
        }
        .form-button button {
            width: 100%;
            padding: 12px;
            background-color: #1f327b;
            color: white;
            font-weight: bold;
            border: none;
            border-radius: 25px;
            cursor: pointer;
        }
        .form-button button:hover {
            background-color: #162969;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .extra-options {
            text-align: center;
            margin-top: 10px;
        }
        .extra-options a {
            color: #4794ff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-left">
                <h2>Sports Club</h2>
                <p>A Sports Club Management System in Northern Bukidnon State College</p>
            </div>
            <div class="login-right">
                <h3>Login</h3>
                <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
                <?php if (isset($_GET['registration_success'])) echo "<p style='color: green;'>Registration successful! Please log in.</p>"; ?>
                
                <form method="POST">
                    <div class="form-control">
                        <input type="email" name="email" placeholder="Username" required>
                    </div>
                    <div class="form-control">
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="form-options">
                        <label><input type="checkbox" name="remember"> Remember</label>
                    </div>
                    <div class="form-button">
                        <button type="submit">Login</button>
                    </div>
                </form>
                <div class="extra-options">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
