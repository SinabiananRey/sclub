<?php
session_start();
include 'db_connect.php';

// Only admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle POST deletion securely
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);

    // Delete associated borrow records first
    $stmt_borrow = $conn->prepare("DELETE FROM borrow_transactions WHERE equipment_id = ?");
    $stmt_borrow->bind_param("i", $delete_id);
    $stmt_borrow->execute();

    // Optionally delete image file
    $stmt_img = $conn->prepare("SELECT image_path FROM equipment WHERE equipment_id = ?");
    $stmt_img->bind_param("i", $delete_id);
    $stmt_img->execute();
    $img_result = $stmt_img->get_result();
    if ($img_row = $img_result->fetch_assoc()) {
        if (!empty($img_row['image_path']) && file_exists($img_row['image_path'])) {
            unlink($img_row['image_path']);
        }
    }

    // Then delete equipment
    $stmt_equipment = $conn->prepare("DELETE FROM equipment WHERE equipment_id = ?");
    $stmt_equipment->bind_param("i", $delete_id);
    if ($stmt_equipment->execute()) {
        $_SESSION['confirmation_message'] = "✅ Equipment deleted successfully!";
        header("Location: manage_equipment.php");
        exit();
    } else {
        $_SESSION['error_message'] = "❌ Failed to delete equipment.";
    }
}

// Handle new equipment add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_equipment'])) {
    $name = $_POST['name'];
    $stock = intval($_POST['stock']);
    $status = $_POST['status'];
    $target_dir = "uploads/";
    $image_path = "";

    if (!empty($_FILES['equipment_image']['name'])) {
        $image_name = basename($_FILES['equipment_image']['name']);
        $target_file = $target_dir . time() . "_" . $image_name;

        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (in_array($file_type, ['jpg', 'jpeg', 'png'])) {
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }
            if (move_uploaded_file($_FILES['equipment_image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO equipment (name, status, stock, image_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $name, $status, $stock, $image_path);
    if ($stmt->execute()) {
        $_SESSION['confirmation_message'] = "✅ Equipment added successfully.";
    } else {
        $_SESSION['error_message'] = "❌ Failed to add equipment.";
    }
    header("Location: manage_equipment.php");
    exit();
}

// Fetch equipment list
$equipment_result = $conn->query("SELECT equipment_id, name, status, stock, image_path FROM equipment");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Equipment | Sports Club</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #eef1f5; }
        .sidebar {
            width: 250px; background: #003366; color: white; padding: 20px;
            height: 100vh; position: fixed; top: 0; left: 0;
        }
        .sidebar a {
            display: block; color: white; text-decoration: none;
            padding: 10px; margin-bottom: 10px; border-radius: 5px;
        }
        .sidebar a:hover { background: #0055aa; }
        .container {
            margin-left: 270px; padding: 30px;
        }
        h2, h3 { color: #003366; text-align: center; }
        .message, .error {
            width: 60%; margin: 20px auto; text-align: center;
            padding: 12px; border-radius: 6px; font-weight: 600;
        }
        .message { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        table {
            width: 100%; border-collapse: collapse; background: white;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1); border-radius: 10px; overflow: hidden;
        }
        th, td {
            padding: 14px; text-align: center; border-bottom: 1px solid #ddd;
        }
        th { background: #003366; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }

        img {
            max-width: 100px;
            height: auto;
            border-radius: 8px;
        }

        .btn {
            padding: 8px 14px; border: none; border-radius: 5px;
            cursor: pointer; font-weight: 500;
        }
        .edit-btn { background: #007bff; color: white; }
        .edit-btn:hover { background: #0056b3; }

        .delete-btn { background: #dc3545; color: white; }
        .delete-btn:hover { background: #c82333; }

        .submit-btn {
            background-color: #003366; color: white; padding: 10px 16px;
            border: none; border-radius: 5px; width: 50%; margin-top: 10px;
        }

        .submit-btn:hover { background-color: #0055aa; }

        form.delete-form { display: inline; }

        @media (max-width: 768px) {
            .container { margin-left: 0; padding: 15px; }
            .sidebar { display: none; position: relative; width: 100%; height: auto; }
            .toggle-btn { display: block; }
        }

        .toggle-btn {
            position: fixed; top: 10px; left: 10px;
            background: #003366; color: white; padding: 10px 15px;
            border: none; font-size: 18px; z-index: 1000; display: none;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <h2><a href="admin_panel.php" style="color:white;">Admin Panel</a></h2>
    <a href="manage_members.php">Manage Members</a>
    <a href="manage_equipment.php">Manage Equipment</a>
    <a href="post_announcements.php">Post Announcements</a>
    <a href="view_reports.php">View Reports</a>
    <a href="settings.php">System Settings</a>
</div>

<!-- Toggle button -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<!-- Main Content -->
<div class="container">
    <h2>Manage Equipment</h2>

    <?php if (!empty($_SESSION['confirmation_message'])): ?>
        <div class="message"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error_message'])): ?>
        <div class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <table>
        <tr>
            <th>Image</th>
            <th>ID</th>
            <th>Name</th>
            <th>Status</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $equipment_result->fetch_assoc()) { ?>
            <tr>
                <td>
                    <?php if (!empty($row['image_path'])): ?>
                        <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Equipment Image">
                    <?php else: ?>
                        <span>No image</span>
                    <?php endif; ?>
                </td>
                <td><?php echo $row['equipment_id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><?php echo $row['stock']; ?></td>
                <td>
                    <a class="btn edit-btn" href="edit_equipment.php?id=<?php echo $row['equipment_id']; ?>">Edit</a>
                    <form method="POST" action="manage_equipment.php" class="delete-form" onsubmit="return confirm('Are you sure you want to delete this equipment?');">
                        <input type="hidden" name="delete_id" value="<?php echo $row['equipment_id']; ?>">
                        <button class="btn delete-btn" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

    <h3>Add New Equipment</h3>
    <div class="form-wrapper">
        <form method="POST" action="manage_equipment.php" enctype="multipart/form-data">
            <table>
                <tr><td>Name:</td><td><input type="text" name="name" required></td></tr>
                <tr><td>Stock:</td><td><input type="number" name="stock" min="1" required></td></tr>
                <tr>
                    <td>Status:</td>
                    <td>
                        <select name="status" required>
                            <option value="Available">Available</option>
                            <option value="Borrowed">Borrowed</option>
                            <option value="Maintenance">Under Maintenance</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Upload Image:</td>
                    <td><input type="file" name="equipment_image" accept="image/*"></td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:center;">
                        <button type="submit" name="add_equipment" class="submit-btn">Add Equipment</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById("sidebar");
        sidebar.style.display = (sidebar.style.display === "block") ? "none" : "block";
    }
</script>

</body>
</html>
