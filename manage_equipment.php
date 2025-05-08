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
<html>
<head>
    <title>Manage Equipment | Admin Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2, h3 {
            color: #003366;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #003366;
            color: #fff;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .btn-delete {
            background-color: #ff4d4d;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-delete:hover {
            background-color: #cc0000;
        }

        form {
            margin-top: 30px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button[type="submit"] {
            margin-top: 15px;
            background-color: #003366;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        button[type="submit"]:hover {
            background-color: #0055a5;
        }

        .message {
            margin: 10px 0;
            color: green;
            font-weight: bold;
            text-align: center;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #003366;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Manage Equipment</h2>

    <?php if (isset($_SESSION['confirmation_message'])): ?>
        <div class="message"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
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
                        <button class="btn-delete">Delete</button>
                    </a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <h3>Add New Equipment</h3>
    <form method="POST" action="add_equipment.php">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Stock:</label>
        <input type="number" name="stock" required>

        <label>Status:</label>
        <select name="status">
            <option value="Available">Available</option>
            <option value="Borrowed">Borrowed</option>
            <option value="Maintenance">Under Maintenance</option>
        </select>

        <button type="submit">Add Equipment</button>
    </form>

    <a class="back-link" href="admin_panel.php">← Back to Admin Panel</a>
</div>

</body>
</html>
