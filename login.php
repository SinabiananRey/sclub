<?php
session_start();
include 'db_connect.php';

// ✅ Debugging: Check if database connection works
if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ✅ Fetch user data from `users` table
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

    if (!$result) {
        die("❌ get_result() Failed: " . $conn->error);
    }

    $user = $result->fetch_assoc();
    $stmt->close(); // ✅ Close after execution

    if ($user) {
        echo "Entered Password: " . $_POST['password'];
        echo "<br>Stored Hashed Password: " . $user['password'];

        if ($user['verified'] == 0) {
            $error = "Account not verified. Please check your email.";
        } elseif (password_verify($_POST['password'], $user['password'])) {
            echo "<br>✅ Password matches!";

            // ✅ Store session data
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // ✅ Redirect based on user role
            if ($user['role'] === 'admin') {
                header("Location: admin_panel.php");
            } else {
                header("Location: member_dashboard.php");
            }
            exit();
        } else {
            echo "<br>❌ Invalid password!";
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
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if (isset($_GET['registration_success'])) echo "<p style='color: green;'>Registration successful! Please log in.</p>"; ?>

        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" required>
            <label>Password:</label>
            <input type="password" name="password" required>
            <button type="submit">Login</button>
        </form>

        <div class="extra-options">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>