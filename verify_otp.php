<?php
session_start();
include 'db_connect.php';

$email = $_GET['email'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_otp = trim($_POST['otp']);
    $email = $_SESSION['email'] ?? $_POST['email']; // fallback for direct POST

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

        $_SESSION['email'] = null; // clear
        header("Location: login.php?otp_success=true");
        exit();
    } else {
        $error = "❌ Incorrect OTP. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <style>
        body {
            background: rgba(0, 0, 0, 0.6);
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .modal {
            background: #fff;
            padding: 30px 40px;
            border-radius: 10px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .modal h2 {
            text-align: center;
            color: #22336e;
            margin-bottom: 20px;
        }

        .modal input {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .modal button {
            width: 100%;
            padding: 12px;
            background-color: #22336e;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        .modal button:hover {
            background-color: #1a2957;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="modal">
        <h2>Email Verification</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
            <label for="otp">Enter OTP sent to your email</label>
            <input type="text" name="otp" id="otp" maxlength="6" required>
            <button type="submit">Verify OTP</button>
        </form>
    </div>

</body>
</html>
