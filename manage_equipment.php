<?php
session_start();
include 'db_connect.php';

// Ensure only admin users can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all equipment
$equipment_query = "SELECT * FROM equipment";
$equipment_result = $conn->query($equipment_query);

// Handle equipment deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM equipment WHERE equipment_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        header("Location: manage_equipment.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Equipment | Admin Panel</title>
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

        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            text-align: left;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }

        th {
            background-color: #003366;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        a {
            color: #007BFF;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .back-link {
            display: block;
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

        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-top: 20px;
        }

        .form-container label {
            display: block;
            margin-top: 10px;
        }

        .form-container input[type="text"], .form-container select {
            padding: 10px;
            width: 100%;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .form-container button {
            margin-top: 15px;
            padding: 10px 20px;
            background: #003366;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #45a049;
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
        <a href="post_announcements.php">Manage Announcements</a>
        <a href="view_reports.php">View Reports</a>
        <a href="settings.php">System Settings</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Manage Equipment</h2>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $equipment_result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['equipment_id']; ?></td>
                    <td><?php echo $row['name']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <a href="edit_equipment.php?id=<?php echo $row['equipment_id']; ?>">Edit</a> |
                        <a href="manage_equipment.php?delete_id=<?php echo $row['equipment_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <div class="form-container">
            <h3>Add New Equipment</h3>
            <form method="POST" action="add_equipment.php">
                <label>Name:</label>
                <input type="text" name="name" required><br>
                <label>Status:</label>
                <select name="status">
                    <option value="Available">Available</option>
                    <option value="Borrowed">Borrowed</option>
                    <option value="Maintenance">Under Maintenance</option>
                </select><br>
                <button type="submit">Add Equipment</button>
            </form>
        </div>

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
