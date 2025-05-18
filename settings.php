<?php
session_start();
include 'db_connect.php';

// ✅ Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Fetch existing settings
$settings_query = "SELECT * FROM settings WHERE id = 1 LIMIT 1";
$settings_result = $conn->query($settings_query);
$settings = $settings_result->fetch_assoc();

$feedback = "";

// ✅ Handle settings update request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $club_name = $_POST['club_name'];
    $admin_email = $_POST['admin_email'];
    $borrowing_limit = $_POST['borrowing_limit'];
    $admin_password = $_POST['admin_password'];

    $user_query = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($admin_password, $user['password'])) {
        $update_query = "UPDATE settings SET club_name = ?, admin_email = ?, borrowing_limit = ? WHERE id = 1";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssi", $club_name, $admin_email, $borrowing_limit);

        if ($stmt->execute()) {
            $feedback = "<div class='message success'>✅ Settings updated successfully!</div>";
        } else {
            $feedback = "<div class='message error'>❌ Error updating settings. Try again.</div>";
        }
    } else {
        $feedback = "<div class='message error'>❌ Incorrect password. Settings update failed.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Settings | Sports Club</title>
    <!-- ✅ Font Awesome for logout icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #eef1f5; display: flex; }
        .sidebar { width: 250px; background: #003366; color: white; padding: 20px; height: 100vh; position: fixed; left: 0; top: 0; }
        .sidebar a { display: block; color: white; text-decoration: none; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        .sidebar a:hover { background: #0055aa; }
        .container { margin-left: 270px; padding: 30px; width: 100%; }
        h2 { text-align: center; color: #003366; margin-bottom: 20px; }
        .form-container { max-width: 700px; background: white; padding: 30px; border-radius: 10px; margin: 0 auto; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        label { font-weight: bold; margin-top: 15px; display: block; }
        input[type="text"], input[type="email"], input[type="number"], input[type="password"] {
            width: 100%; padding: 10px; margin-top: 8px; border-radius: 5px;
            border: 1px solid #ccc; margin-bottom: 20px; font-size: 15px;
        }
        .submit-btn {
            background-color: #003366; color: white; padding: 12px; border: none;
            font-size: 16px; border-radius: 5px; cursor: pointer; width: 100%; margin-top: 10px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .submit-btn:hover {
            background-color: #0055aa;
        }
        .message {
            text-align: center; font-weight: 600; padding: 12px 20px;
            border-radius: 6px; margin-bottom: 20px;
        }
        .message.success {
            background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;
        }
        .logout-btn {
            background-color: #d9534f; color: white; padding: 8px 16px; border: none;
            font-size: 14px; border-radius: 5px; cursor: pointer; width: auto; margin-top: 20px;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .logout-btn:hover {
            background-color: #c9302c;
        }
        .toggle-btn { position: fixed; top: 10px; left: 10px; background: #003366; color: white; border: none; padding: 10px 15px; font-size: 18px; z-index: 1000; display: none; }
        @media (max-width: 768px) {
            .container { margin-left: 0; padding: 15px; }
            .sidebar { display: none; position: relative; width: 100%; height: auto; }
            .toggle-btn { display: block; }
        }
    </style>
</head>
<body>

<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="sidebar" id="sidebar">
    <h2><a href="admin_panel.php" style="color:white;">Admin Panel</a></h2>
    <a href="manage_members.php">Manage Members</a>
    <a href="manage_equipment.php">Manage Equipment</a>
    <a href="post_announcements.php">Post Announcements</a>
    <a href="view_reports.php">View Reports</a>
    <a href="settings.php">System Settings</a>
</div>

<div class="container">
    <h2>System Settings</h2>

    <?php echo $feedback; ?>

    <div class="form-container">
        <form method="POST">
            <label>Club Name:</label>
            <input type="text" name="club_name" value="<?php echo htmlspecialchars($settings['club_name']); ?>" required>

            <label>Admin Email:</label>
            <input type="email" name="admin_email" value="<?php echo htmlspecialchars($settings['admin_email']); ?>" required>

            <label>Borrowing Limit:</label>
            <input type="number" name="borrowing_limit" value="<?php echo htmlspecialchars($settings['borrowing_limit']); ?>" required>

            <label>Enter Admin Password:</label>
            <input type="password" name="admin_password" required>

            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> Update Settings
            </button>
        </form>

        <!-- ✅ Logout button with icon, styled red and small -->
        <button class="logout-btn" onclick="confirmLogout()">
            <i class="fas fa-sign-out-alt"></i> Logout
        </button>
    </div>
</div>

<script>
    function toggleSidebar() {
        var sidebar = document.getElementById("sidebar");
        sidebar.style.display = (sidebar.style.display === "block") ? "none" : "block";
    }

    function confirmLogout() {
        if (confirm("Are you sure you want to logout?")) {
            window.location.href = "logout.php";
        }
    }
</script>

</body>
</html>
