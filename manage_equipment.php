<?php
session_start();
include 'db_connect.php';

// Ensure only admin users can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Handle equipment deletion safely
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $delete_borrow_query = "DELETE FROM borrow_transactions WHERE equipment_id = ?";
    $stmt_borrow = $conn->prepare($delete_borrow_query);
    $stmt_borrow->bind_param("i", $delete_id);
    $stmt_borrow->execute();

    $delete_equipment_query = "DELETE FROM equipment WHERE equipment_id = ?";
    $stmt_equipment = $conn->prepare($delete_equipment_query);
    $stmt_equipment->bind_param("i", $delete_id);
    if ($stmt_equipment->execute()) {
        $_SESSION['confirmation_message'] = "Equipment deleted successfully!";
        header("Location: manage_equipment.php");
        exit();
    }
}

$equipment_query = "SELECT equipment_id, name, status, stock FROM equipment";
$equipment_result = $conn->query($equipment_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Equipment | Sports Club</title>
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

        .form-wrapper input, .form-wrapper select {
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
            transition: background 0.3s;
        }

        .submit-btn:hover {
            background-color: #218838;
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

<!-- Toggle button for small screens -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<!-- Main Content -->
<div class="container">
    <h2>Manage Equipment</h2>

    <?php if (isset($_SESSION['confirmation_message'])): ?>
        <div class="message success"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Status</th>
            <th>Stock</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $equipment_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['equipment_id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['status']; ?></td>
                <td><?php echo $row['stock']; ?></td>
                <td>
                    <a href="edit_equipment.php?id=<?php echo $row['equipment_id']; ?>">Edit</a> |
                    <a href="manage_equipment.php?delete_id=<?php echo $row['equipment_id']; ?>" onclick="return confirm('Are you sure?')">
                        <button class="delete-btn">Delete</button>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <h3 style="text-align:center; margin-top:40px; color:#003366;">Add New Equipment</h3>
    <div class="form-wrapper">
        <form method="POST" action="add_equipment.php">
            <table>
                <tr>
                    <th>Field</th>
                    <th>Input</th>
                </tr>
                <tr>
                    <td>Name:</td>
                    <td><input type="text" name="name" required></td>
                </tr>
                <tr>
                    <td>Stock:</td>
                    <td><input type="number" name="stock" required></td>
                </tr>
                <tr>
                    <td>Status:</td>
                    <td>
                        <select name="status">
                            <option value="Available">Available</option>
                            <option value="Borrowed">Borrowed</option>
                            <option value="Maintenance">Under Maintenance</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align:center;">
                        <button type="submit" class="submit-btn">Add Equipment</button>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

<script>
    function toggleSidebar() {
        var sidebar = document.getElementById("sidebar");
        sidebar.style.display = (sidebar.style.display === "block") ? "none" : "block";
    }
</script>

</body>
</html>
