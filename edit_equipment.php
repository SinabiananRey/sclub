<?php
session_start();
include 'db_connect.php';

// Ensure only admin users can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch equipment details for editing
$equipment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($equipment_id === 0) {
    $_SESSION['error_message'] = "❌ Invalid equipment ID.";
    header("Location: manage_equipment.php");
    exit();
}

$query = "SELECT * FROM equipment WHERE equipment_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $equipment_id);
$stmt->execute();
$result = $stmt->get_result();
$equipment = $result->fetch_assoc();

if (!$equipment) {
    $_SESSION['error_message'] = "❌ Equipment not found.";
    header("Location: manage_equipment.php");
    exit();
}

// Handle form submission for updating equipment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $status = $_POST['status'];

    // Input validation
    if (empty($name)) {
        $_SESSION['error_message'] = "❌ Equipment name is required.";
    } elseif (!in_array($status, ['Available', 'Borrowed', 'Maintenance'])) {
        $_SESSION['error_message'] = "❌ Invalid status.";
    } else {
        // Update equipment details in the database
        $update_query = "UPDATE equipment SET name = ?, status = ? WHERE equipment_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssi", $name, $status, $equipment_id);

        if ($stmt->execute()) {
            $_SESSION['confirmation_message'] = "✅ Equipment updated successfully!";
            header("Location: manage_equipment.php");
            exit();
        } else {
            $_SESSION['error_message'] = "❌ Failed to update equipment.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Equipment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #003366;
        }
        .message {
            text-align: center;
            font-weight: 600;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
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
        label {
            font-size: 14px;
            color: #555;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            background-color: #003366;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #0055aa;
        }
        a {
            display: inline-block;
            text-align: center;
            text-decoration: none;
            background-color: #ccc;
            padding: 8px 16px;
            border-radius: 5px;
            margin-top: 20px;
            color: black;
        }
        a:hover {
            background-color: #999;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Equipment</h2>

    <!-- Display success or error message -->
    <?php if (isset($_SESSION['confirmation_message'])): ?>
        <div class="message success"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="message error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <form method="POST" action="edit_equipment.php?id=<?php echo $equipment['equipment_id']; ?>">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($equipment['name']); ?>" required>

        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="Available" <?php echo $equipment['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
            <option value="Borrowed" <?php echo $equipment['status'] === 'Borrowed' ? 'selected' : ''; ?>>Borrowed</option>
            <option value="Maintenance" <?php echo $equipment['status'] === 'Maintenance' ? 'selected' : ''; ?>>Under Maintenance</option>
        </select>

        <button type="submit">Update Equipment</button>
    </form>

    <a href="manage_equipment.php">Back to Manage Equipment</a>
</div>

</body>
</html>
