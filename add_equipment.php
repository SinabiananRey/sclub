<?php
session_start();
include 'db_connect.php';

// Ensure only admin users can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Process Equipment Addition
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];

    $query = "INSERT INTO equipment (name, stock, status) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sis", $name, $stock, $status);

    if ($stmt->execute()) {
        $_SESSION['confirmation_message'] = "Equipment added successfully!";
        header("Location: manage_equipment.php");
        exit();
    } else {
        $_SESSION['confirmation_message'] = "Error adding equipment. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Equipment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #f9fafb; color: #333; }
        .container { max-width: 600px; margin: 50px auto; padding: 20px; background: #fff; border-radius: 8px; }
        h2 { text-align: center; color: #1e3a8a; }
        form { margin-top: 20px; }
        input, select, button { width: 100%; padding: 10px; margin-top: 10px; border-radius: 6px; border: 1px solid #ddd; }
        button { background: #3b82f6; color: white; font-weight: bold; cursor: pointer; }
        button:hover { background: #2563eb; }
        .message { text-align: center; margin: 20px 0; font-weight: 500; color: green; }
    </style>
</head>
<body>

<div class="container">
    <h2>Add New Equipment</h2>

    <?php if (isset($_SESSION['confirmation_message'])): ?>
        <div class="message"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Equipment Name:</label>
        <input type="text" name="name" required>

        <label>Stock Quantity:</label>
        <input type="number" name="stock" required>

        <label>Status:</label>
        <select name="status">
            <option value="Available">Available</option>
            <option value="Borrowed">Borrowed</option>
            <option value="Maintenance">Under Maintenance</option>
        </select>

        <button type="submit">Add Equipment</button>
    </form>
</div>

</body>
</html>