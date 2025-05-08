<?php
session_start();
include 'db_connect.php';

// Ensure only admin users can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch current settings
$settings_query = "SELECT * FROM settings LIMIT 1";
$settings_result = $conn->query($settings_query);
$settings = $settings_result->fetch_assoc();

// Handle updates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $club_name = $_POST['club_name'];
    $admin_email = $_POST['admin_email'];
    $borrowing_limit = $_POST['borrowing_limit'];
    $admin_password = $_POST['admin_password'];

    // Fetch admin's stored password
    $user_query = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($user_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($admin_password, $user['password'])) {
        $update_query = "UPDATE settings SET club_name = ?, admin_email = ?, borrowing_limit = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssi", $club_name, $admin_email, $borrowing_limit);
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Settings updated successfully!</p>";
        }
    } else {
        echo "<p style='color: red;'>Incorrect password. Settings update failed.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings | Admin Panel</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
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

        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .form-container form {
            display: flex;
            flex-direction: column;
        }

        .form-container label {
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-container input {
            margin-bottom: 15px;
            padding: 8px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .form-container button {
            padding: 10px 20px;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #45a049;
        }

        .form-container .success {
            color: green;
            margin-bottom: 15px;
        }

        .form-container .error {
            color: red;
            margin-bottom: 15px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            font-size: 16px;
            color: #333;
        }

        .back-link a:hover {
            text-decoration: underline;
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
        <a href="post_announcements.php">Post Announcements</a>
        <a href="view_reports.php">View Reports</a>
        <a href="settings.php">System Settings</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>System Settings</h2>

        <div class="form-container">
            <form method="POST">
                <label>Club Name:</label>
                <input type="text" name="club_name" value="<?php echo $settings['club_name']; ?>" required><br>

                <label>Admin Email:</label>
                <input type="email" name="admin_email" value="<?php echo $settings['admin_email']; ?>" required><br>

                <label>Borrowing Limit:</label>
                <input type="number" name="borrowing_limit" value="<?php echo $settings['borrowing_limit']; ?>" required><br>

                <label>Enter Admin Password:</label>
                <input type="password" name="admin_password" required><br>

                <button type="submit">Update Settings</button>
            </form>

            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (password_verify($admin_password, $user['password'])) {
                    echo "<p class='success'>Settings updated successfully!</p>";
                } else {
                    echo "<p class='error'>Incorrect password. Settings update failed.</p>";
                }
            }
            ?>
        </div>

        <br>
        <div class="back-link">
            <a href="admin_panel.php">Back to Admin Panel</a>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }
</script>

</body>
</html>
