<?php
session_start();
include 'db_connect.php';

if (!$conn) {
    die("❌ Database connection failed: " . mysqli_connect_error());
}

// ✅ Pre-fill login fields using cookies
$email_cookie = isset($_COOKIE['email']) ? $_COOKIE['email'] : '';
$password_cookie = isset($_COOKIE['password']) ? $_COOKIE['password'] : '';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ reCAPTCHA validation
    $recaptcha_secret = "6LeH0UkrAAAAAMBwc2u42e0SJOOzew_9wzpHLU-b"; // your secret key
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $verify_url = "https://www.google.com/recaptcha/api/siteverify";
    $response = file_get_contents($verify_url . "?secret=" . $recaptcha_secret . "&response=" . $recaptcha_response);
    $responseKeys = json_decode($response, true);

    if (!$responseKeys["success"]) {
        $error = "⚠️ Please complete the CAPTCHA.";
    } else {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $remember = isset($_POST['remember']);
        $ip_address = $_SERVER['REMOTE_ADDR'];

        $max_attempts = 5;
        $lockout_time = 15; // minutes

        // 🔁 Check failed attempts
        $stmt = $conn->prepare("SELECT COUNT(*) AS fail_count FROM login_attempts 
                                WHERE email = ? AND ip_address = ? 
                                AND success = 0 AND attempt_time > (NOW() - INTERVAL ? MINUTE)");
        $stmt->bind_param("ssi", $email, $ip_address, $lockout_time);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $failed_attempts = $result['fail_count'];
        $stmt->close();

        if ($failed_attempts >= $max_attempts) {
            $error = "🚫 Too many failed attempts. Please try again in $lockout_time minutes.";
        } else {
            $query = "SELECT user_id, username, email, password, role, verified FROM users WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user) {
                if ($user['verified'] == 0) {
                    $error = "❌ Account not verified. Please check your email.";
                } elseif (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 1)");
                    $stmt->bind_param("ss", $email, $ip_address);
                    $stmt->execute();
                    $stmt->close();

                    if ($_SESSION['role'] === 'member') {
                        $ip = $_SERVER['REMOTE_ADDR'];
                        $user_agent = $_SERVER['HTTP_USER_AGENT'];
                        $username = $_SESSION['username'];

                        $log_query = "INSERT INTO login_logs (user_id, username, ip_address, user_agent) VALUES (?, ?, ?, ?)";
                        $stmt = $conn->prepare($log_query);
                        $stmt->bind_param("isss", $_SESSION['user_id'], $username, $ip, $user_agent);
                        $stmt->execute();
                        $stmt->close();
                    }

                    if ($remember) {
                        setcookie("email", $email, time() + (86400 * 30), "/");
                        setcookie("password", $password, time() + (86400 * 30), "/");
                    } else {
                        setcookie("email", "", time() - 3600, "/");
                        setcookie("password", "", time() - 3600, "/");
                    }

                    header("Location: " . ($user['role'] === 'admin' ? "admin_panel.php" : "member_dashboard.php"));
                    exit();
                } else {
                    $error = "❌ Invalid password.";
                    $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
                    $stmt->bind_param("ss", $email, $ip_address);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                $error = "❌ User not found.";
                $stmt = $conn->prepare("INSERT INTO login_attempts (email, ip_address, success) VALUES (?, ?, 0)");
                $stmt->bind_param("ss", $email, $ip_address);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Sports Club</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ✅ Include reCAPTCHA script -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f3f3;
        }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }
        .login-box {
            display: flex;
            flex-direction: row;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            border-radius: 16px;
            overflow: hidden;
            width: 900px;
            max-width: 100%;
            background-color: #fff;
            transition: box-shadow 0.3s ease;
        }
        .login-box:hover {
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.15);
        }
        .login-left {
            background: linear-gradient(135deg, #1d3ede, #4facfe);
            color: white;
            padding: 50px 40px;
            width: 50%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-left h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        .login-left p {
            font-size: 18px;
            line-height: 1.5;
        }
        .login-right {
            padding: 50px 40px;
            width: 50%;
        }
        .login-right h3 {
            font-size: 28px;
            margin-bottom: 25px;
            color: #22336e;
        }
        .form-control {
            margin-bottom: 20px;
        }
        .form-control input {
            width: 100%;
            padding: 14px 18px;
            border-radius: 30px;
            border: 1px solid #ccc;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-control input:focus {
            border-color: #4facfe;
            outline: none;
        }
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .form-options label {
            display: flex;
            align-items: center;
        }
        .form-options input[type="checkbox"] {
            margin-right: 6px;
        }
        .form-options a {
            text-decoration: none;
            color: #4facfe;
        }
        .form-options a:hover {
            text-decoration: underline;
        }
        .form-button button {
            width: 100%;
            padding: 14px;
            background: #22336e;
            color: white;
            font-weight: bold;
            font-size: 16px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .form-button button:hover {
            background: #162969;
        }
        .extra-options {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .extra-options a {
            color: #4facfe;
            text-decoration: none;
        }
        .extra-options a:hover {
            text-decoration: underline;
        }
        .error {
            color: red;
            background: #ffe0e0;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }
        .success {
            color: green;
            background: #e0ffe6;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }
        @media (max-width: 768px) {
            .login-box {
                flex-direction: column;
            }
            .login-left,
            .login-right {
                width: 100%;
                padding: 40px 20px;
            }
            .login-left {
                text-align: center;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-box">
        <div class="login-left">
            <h2>Sports Club</h2>
            <p>Your Digital Locker Room<br>for Easy Equipment Access!</p>
        </div>
        <div class="login-right">
            <h3>Welcome Back!</h3>

            <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
            <?php if (isset($_GET['registration_success'])) echo "<div class='success'>✅ Registration successful! Please log in.</div>"; ?>

            <form method="POST">
                <div class="form-control">
                    <input type="email" name="email" placeholder="Email" required value="<?php echo htmlspecialchars($email_cookie); ?>">
                </div>
                <div class="form-control">
                    <input type="password" name="password" placeholder="Password" required value="<?php echo htmlspecialchars($password_cookie); ?>">
                </div>
                <div class="form-options">
                    <label>
                        <input type="checkbox" name="remember" <?php echo isset($_COOKIE['email']) ? 'checked' : ''; ?>> Remember Me
                    </label>
                </div>

                <!-- ✅ reCAPTCHA widget -->
                <div class="g-recaptcha" data-sitekey="6LeH0UkrAAAAAPgzlvU1i8KI64l5OT9CQjZw_NGD"></div>
                <br>

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
