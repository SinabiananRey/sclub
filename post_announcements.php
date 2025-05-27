<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle posting or editing announcements
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['title'], $_POST['content'], $_POST['action'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $admin_id = $_SESSION['user_id'];
    $image_path = "";

    $target_dir = __DIR__ . "/uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    if (!empty($_FILES['announcement_image']['name'])) {
        $image_name = basename($_FILES['announcement_image']['name']);
        $target_file = $target_dir . $image_name;

        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (in_array($file_type, ['jpg', 'jpeg', 'png'])) {
            if (move_uploaded_file($_FILES['announcement_image']['tmp_name'], $target_file)) {
                $image_path = "uploads/" . $image_name;
            } else {
                $message = "<p class='message error'>❌ Image upload failed.</p>";
            }
        } else {
            $message = "<p class='message error'>❌ Invalid file type. Only JPG & PNG allowed.</p>";
        }
    }

    if ($_POST['action'] === 'add') {
        $query = "INSERT INTO announcements (admin_id, title, content, image_path, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("isss", $admin_id, $title, $content, $image_path);
        $stmt->execute();
        $message = "<p class='message success'>✅ Announcement posted successfully!</p>";
    } elseif ($_POST['action'] === 'edit' && isset($_POST['announcement_id'])) {
        $announcement_id = $_POST['announcement_id'];
        $query = "UPDATE announcements SET title = ?, content = ?, image_path = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $title, $content, $image_path, $announcement_id);
        $stmt->execute();
        $message = "<p class='message success'>✅ Announcement updated successfully!</p>";
    }
}

// Handle deleting announcements
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_announcement_id'])) {
    $delete_id = $_POST['delete_announcement_id'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $message = "<p class='message success'>✅ Announcement deleted successfully!</p>";
}

// Fetch announcements
$announcements = [];
$result = $conn->query("SELECT id, title, content, image_path, created_at FROM announcements ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $announcements[] = $row;
}

// Prepare for edit
$edit_id = isset($_GET['edit_announcement_id']) ? intval($_GET['edit_announcement_id']) : null;
$edit_title = '';
$edit_content = '';
$edit_image = '';

if ($edit_id) {
    $stmt = $conn->prepare("SELECT title, content, image_path FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $stmt->bind_result($edit_title, $edit_content, $edit_image);
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

        form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-top: 15px;
        }

        input[type="text"], textarea, input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-top: 5px;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0055aa;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #003366;
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        img {
            max-width: 150px;
            border-radius: 8px;
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 15px;
            }

            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><a href="admin_panel.php">Admin Panel</a></h2>
    <a href="manage_members.php">Manage Members</a>
    <a href="manage_equipment.php">Manage Equipment</a>
    <a href="post_announcements.php">Post Announcements</a>
    <a href="view_reports.php">View Reports</a>
    <a href="settings.php">System Settings</a>
</div>

<div class="container">
    <h2>Manage Announcements</h2>

    <?php if ($message) echo $message; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="announcement_id" value="<?= $edit_id; ?>">
        <label>Title:</label>
        <input type="text" name="title" required value="<?= htmlspecialchars($edit_title); ?>">
        <label>Content:</label>
        <textarea name="content" rows="5" required><?= htmlspecialchars($edit_content); ?></textarea>
        <label>Upload Image:</label>
        <input type="file" name="announcement_image" accept="image/*">
        <button type="submit" name="action" value="<?= $edit_id ? 'edit' : 'add'; ?>">
            <?= $edit_id ? 'Update Announcement' : 'Post Announcement'; ?>
        </button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Title</th>
                <th>Content</th>
                <th>Image</th>
                <th>Date Posted</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($announcements as $a): ?>
            <tr>
                <td><?= htmlspecialchars($a['title']); ?></td>
                <td><?= nl2br(htmlspecialchars($a['content'])); ?></td>
                <td>
                    <?php if (!empty($a['image_path'])): ?>
                        <img src="<?= $a['image_path']; ?>" alt="Announcement Image">
                    <?php endif; ?>
                </td>
                <td><?= date("F j, Y g:i A", strtotime($a['created_at'])); ?></td>
                <td>
                    <form method="POST" onsubmit="return confirmDelete();">
                        <input type="hidden" name="delete_announcement_id" value="<?= $a['id']; ?>">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                    <a href="?edit_announcement_id=<?= $a['id']; ?>">Edit</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this announcement?");
}
</script>

</body>
</html>
