<?php
session_start();
include 'db_connect.php';

// ✅ Ensure only admins can manage announcements
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// ✅ Handle posting or editing announcements
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'], $_POST['content'], $_POST['action'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $admin_id = $_SESSION['user_id'];

    if ($_POST['action'] === 'add') {
        // Insert new announcement
        $query = "INSERT INTO announcements (admin_id, title, content) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $admin_id, $title, $content);
        $stmt->execute();
        $message = "<p class='success'>✅ Announcement posted successfully!</p>";
    } elseif ($_POST['action'] === 'edit' && isset($_POST['announcement_id'])) {
        // Update existing announcement
        $announcement_id = $_POST['announcement_id'];
        $query = "UPDATE announcements SET title = ?, content = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $title, $content, $announcement_id);
        $stmt->execute();
        $message = "<p class='success'>✅ Announcement updated successfully!</p>";
    }
}

// ✅ Handle deleting an announcement
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_announcement_id'])) {
    $delete_id = $_POST['delete_announcement_id'];
    $delete_query = "DELETE FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $message = "<p class='success'>✅ Announcement deleted successfully!</p>";
}

// ✅ Fetch recent announcements
$announcements = [];
$sql = "SELECT id, title, content, created_at FROM announcements ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}

// ✅ Check if editing is requested
$edit_id = isset($_GET['edit_announcement_id']) ? intval($_GET['edit_announcement_id']) : null;
$edit_title = '';
$edit_content = '';

if ($edit_id) {
    $query = "SELECT title, content FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_title, $edit_content);
    $stmt->fetch();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Announcements | Sports Club</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #003366;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
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
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
            background: #fff;
            min-height: 100vh;
            box-sizing: border-box;
        }

        h2, h3 {
            color: #003366;
            text-align: center;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        .form-container {
            max-width: 700px;
            margin: 0 auto 40px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            color: #003366;
        }

        input[type="text"], textarea {
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            width: 100%;
        }

        button[type="submit"], .delete-btn {
            background-color: #003366;
            color: white;
            padding: 12px;
            border: none;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        button[type="submit"]:hover, .delete-btn:hover {
            background-color: #0055aa;
        }

        .announcement-table {
            width: 70%;
            max-width: 800px;
            border-collapse: collapse;
            margin: 20px auto;
            table-layout: fixed;
        }

        .announcement-table th,
        .announcement-table td {
            border: 1px solid #ccc;
            padding: 16px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        .announcement-table th {
            background-color: #003366;
            color: white;
        }

        .announcement-table td {
            background-color: #f9f9f9;
        }

        .delete-btn {
            background: red;
            margin-top: 8px;
        }

        .delete-btn:hover {
            background: darkred;
        }
    </style>
</head>
<body>

<!-- ✅ Sidebar -->
<div class="sidebar">
    <h2><a href="admin_panel.php" style="color:white;">Admin Panel</a></h2>
    <a href="manage_members.php">Manage Members</a>
    <a href="manage_equipment.php">Manage Equipment</a>
    <a href="post_announcements.php">Post Announcements</a>
    <a href="view_reports.php">View Reports</a>
    <a href="settings.php">System Settings</a>
    <a href="logout.php">Logout</a>
</div>

<!-- ✅ Main Content -->
<div class="main-content">
    <h2>Manage Announcements</h2>

    <div class="message">
        <?php if (!empty($message)) echo $message; ?>
    </div>

    <!-- ✅ Announcement Form -->
    <div class="form-container">
        <form method="POST">
            <input type="hidden" name="announcement_id" value="<?php echo $edit_id; ?>">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" required value="<?php echo htmlspecialchars($edit_title); ?>">

            <label for="content">Content:</label>
            <textarea name="content" id="content" rows="5" required><?php echo htmlspecialchars($edit_content); ?></textarea>

            <button type="submit" name="action" value="<?php echo $edit_id ? 'edit' : 'add'; ?>">
                <?php echo $edit_id ? 'Update Announcement' : 'Post Announcement'; ?>
            </button>
        </form>
    </div>

    <h3>Recent Announcements</h3>
    <table class="announcement-table">
        <tr><th>Title</th><th>Content</th><th>Date Posted</th><th>Action</th></tr>
        <?php foreach ($announcements as $a): ?>
        <tr>
            <td><?php echo htmlspecialchars($a['title']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($a['content'])); ?></td>
            <td><?php echo date("F j, Y g:i A", strtotime($a['created_at'])); ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="delete_announcement_id" value="<?php echo $a['id']; ?>">
                    <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this announcement?');">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

</body>
</html>
