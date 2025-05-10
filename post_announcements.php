<?php 
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_announcement_id'])) {
    $delete_id = $_POST['delete_announcement_id'];
    $delete_query = "DELETE FROM announcements WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    
    if ($stmt->execute()) {
        $message = "<div class='message success'>✅ Announcement deleted successfully!</div>";
    } else {
        $message = "<div class='message error'>❌ Error deleting announcement. Try again.</div>";
    }
}

$announcements = [];
$sql = "SELECT id, title, content, created_at FROM announcements ORDER BY created_at DESC LIMIT 10";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Announcement | Sports Club</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #eef1f5;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: #003366;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
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

        .container {
            margin-left: 270px;
            padding: 30px;
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #003366;
            margin-bottom: 20px;
        }

        .message {
            text-align: center;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            width: 60%;
            margin-left: auto;
            margin-right: auto;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-wrapper {
            max-width: 700px;
            margin: 40px auto;
        }

        .form-wrapper table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .form-wrapper th, .form-wrapper td {
            padding: 14px 16px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .form-wrapper th {
            background: #003366;
            color: white;
        }

        .form-wrapper input, .form-wrapper textarea {
            width: 100%;
            padding: 8px;
            font-size: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .submit-btn {
            background-color:  #003366;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
            width: 50%;
            margin: 20px auto 0;
            display: block;
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background-color: #218838;
        }

        table.announcement-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-top: 30px;
        }

        table.announcement-table th, table.announcement-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        table.announcement-table th {
            background: #003366;
            color: white;
            font-weight: 600;
        }

        table.announcement-table tr:nth-child(even) {
            background: #f9f9f9;
        }

        .toggle-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            background: #003366;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 18px;
            z-index: 1000;
            display: none;
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 15px;
            }

            .sidebar {
                display: none;
                position: relative;
                width: 100%;
                height: auto;
            }

            .toggle-btn {
                display: block;
            }
        }
    </style>
</head>
<body>

<!-- Toggle Button -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <h2><a href="admin_panel.php" style="color:white; text-decoration:none;">Admin Panel</a></h2>
    <a href="manage_members.php">Manage Members</a>
    <a href="manage_equipment.php">Manage Equipment</a>
    <a href="post_announcements.php">Post Announcements</a>
    <a href="view_reports.php">View Reports</a>
    <a href="settings.php">System Settings</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="container">
    <h2>Post an Announcement</h2>

    <?php if (isset($message)) echo $message; ?>

    <div class="form-wrapper">
        <form method="POST">
            <table>
                <tr>
                    <th colspan="2">New Announcement</th>
                </tr>
                <tr>
                    <td><label for="title">Title:</label></td>
                    <td><input type="text" name="title" id="title" required></td>
                </tr>
                <tr>
                    <td><label for="content">Content:</label></td>
                    <td><textarea name="content" id="content" rows="5" required></textarea></td>
                </tr>
            </table>
            <button type="submit" class="submit-btn">Post Announcement</button>
        </form>
    </div>

    <h2>Recent Announcements</h2>
    <?php if (count($announcements) > 0): ?>
        <table class="announcement-table">
    <tr>
        <th>Title</th>
        <th>Content</th>
        <th>Date Posted</th>
        <th>Action</th> <!-- ✅ Added column for delete button -->
    </tr>
    <?php foreach ($announcements as $a): ?>
        <tr>
            <td><?php echo htmlspecialchars($a['title']); ?></td>
            <td><?php echo nl2br(htmlspecialchars($a['content'])); ?></td>
            <td><?php echo date("F j, Y g:i A", strtotime($a['created_at'])); ?></td>
            <td>
                <form method="POST" onsubmit="return confirmDelete();">
    <input type="hidden" name="delete_announcement_id" value="<?php echo $a['id']; ?>">
    <button type="submit" class="delete-btn">Delete</button>
</form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
    <?php else: ?>
        <p style="text-align:center;">No announcements found.</p>
    <?php endif; ?>
</div>

<script>
    function toggleSidebar() {
        var sidebar = document.getElementById("sidebar");
        sidebar.style.display = (sidebar.style.display === "block") ? "none" : "block";
    }
</script>
<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this announcement? This action cannot be undone.");
}
</script>

</body>
</html>
