<?php
session_start();
include 'db_connect.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$showOtpModal = false;
$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $full_name = htmlspecialchars($_POST['full_name'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $otp = rand(100000, 999999);

    // Generate unique username
    do {
        $username = 'user' . rand(1000, 9999);
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();
    } while ($count > 0);

    // Insert user
    $stmt_users = $conn->prepare("INSERT INTO users (username, email, password, role, otp, verified) VALUES (?, ?, ?, 'member', ?, 0)");
    $stmt_users->bind_param("sssi", $username, $email, $password, $otp);

    if ($stmt_users->execute()) {
        $user_id = $stmt_users->insert_id;
        $stmt_users->close();

        // Insert member details
        $stmt_members = $conn->prepare("INSERT INTO members (user_id, full_name, email, password, role, joined_date) VALUES (?, ?, ?, ?, 'member', CURDATE())");
        $stmt_members->bind_param("isss", $user_id, $full_name, $email, $password);
        $stmt_members->execute();
        $stmt_members->close();

        // Send OTP email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sinabiananrey@gmail.com';
            $mail->Password = 'rard mpnw rozl pqbr'; // App-specific password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('sinabiananrey@gmail.com', 'Sports Club Admin');
            $mail->addAddress($email);
            $mail->Subject = 'Your OTP Code';
            $mail->Body = "Hello $full_name,\n\nYour OTP is: $otp";

            if ($mail->send()) {
                $_SESSION['registered_email'] = $email;
                $showOtpModal = true;
            } else {
                $errorMessage = "Email could not be sent.";
            }
        } catch (Exception $e) {
            $errorMessage = "Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $errorMessage = "Registration failed: " . $stmt_users->error;
    }
}

// OTP Verification
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_otp'])) {
    $entered_otp = trim($_POST['otp']);
    $email = $_SESSION['registered_email'] ?? '';

    $stmt = $conn->prepare("SELECT otp FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($stored_otp);
    $stmt->fetch();
    $stmt->close();

    if ($entered_otp === $stored_otp) {
        $stmt = $conn->prepare("UPDATE users SET verified = 1 WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['registered_email']);
        header("Location: login.php?otp_success=true");
        exit();
    } else {
        $errorMessage = "Incorrect OTP. Try again.";
        $showOtpModal = true;
    }
}
?>
<!-- Replace your HTML <head> and <body> with this updated version: -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #22336e, #556bce);
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      overflow: hidden;
    }

    .signup-container {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      padding: 40px;
      border-radius: 16px;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      animation: fadeIn 0.5s ease-in-out;
      color: #fff;
    }

    .signup-container h2 {
      text-align: center;
      margin-bottom: 24px;
      font-weight: 600;
      font-size: 28px;
    }

    .signup-container label {
      font-size: 14px;
      margin-bottom: 6px;
      display: block;
    }

    .signup-container input {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border: none;
      border-radius: 8px;
      background: rgba(255, 255, 255, 0.2);
      color: #fff;
      font-size: 14px;
    }

    .signup-container input::placeholder {
      color: #ccc;
    }

    .signup-container button {
      width: 100%;
      padding: 12px;
      background: #00bfa6;
      border: none;
      border-radius: 8px;
      color: white;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    .signup-container button:hover {
      background: #00a992;
    }

    .login-link {
      text-align: center;
      margin-top: 16px;
      font-size: 14px;
    }

    .login-link a {
      color: #fff;
      text-decoration: underline;
    }

    .error {
      background: rgba(255, 100, 100, 0.8);
      padding: 12px;
      border-radius: 6px;
      margin-bottom: 15px;
      font-size: 14px;
      text-align: center;
    }

    .modal-overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100vw; height: 100vh;
      background: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
      z-index: 10;
    }

    .modal {
      background: #fff;
      padding: 30px;
      border-radius: 16px;
      width: 90%;
      max-width: 400px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
      animation: slideDown 0.4s ease;
    }

    .modal h3 {
      margin-bottom: 20px;
      font-size: 22px;
      color: #22336e;
      text-align: center;
    }

    .modal input {
      width: 100%;
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 14px;
    }

    .modal button {
      width: 100%;
      padding: 12px;
      background: #22336e;
      color: white;
      border: none;
      border-radius: 8px;
      font-weight: bold;
      cursor: pointer;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-40px); }
      to { opacity: 1; transform: translateY(0); }
    }

  </style>
</head>
<body>

<div class="signup-container">
  <h2>Register</h2>

  <?php if (!empty($errorMessage)): ?>
    <div class="error"><?php echo $errorMessage; ?></div>
  <?php endif; ?>

  <form method="POST">
    <input type="hidden" name="register" value="1">

    <label for="full_name">Full Name</label>
    <input type="text" name="full_name" placeholder="Your full name" required>

    <label for="email">Email</label>
    <input type="email" name="email" placeholder="example@email.com" required>

    <label for="password">Password</label>
    <input type="password" name="password" placeholder="••••••••" required>

    <button type="submit">Sign Up</button>
  </form>

  <div class="login-link">
    Already have an account? <a href="login.php">Login here</a>
  </div>
</div>

<!-- OTP Modal -->
<div class="modal-overlay" id="otpModal">
  <div class="modal">
    <h3>Verify Your Email</h3>
    <form method="POST">
      <input type="text" name="otp" placeholder="Enter OTP" maxlength="6" required>
      <button type="submit" name="verify_otp">Verify</button>
    </form>
  </div>
</div>

<?php if ($showOtpModal): ?>
<script>
  document.getElementById('otpModal').style.display = 'flex';
</script>
<?php endif; ?>

</body>
</html>
