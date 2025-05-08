<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $status = $_POST['status'];
    
    $query = "INSERT INTO equipment (name, status) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $name, $status);

    if ($stmt->execute()) {
        header("Location: manage_equipment.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Equipment</title>
</head>
<body>
    <h2>Add Equipment</h2>
    <form method="POST">
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
</body>
</html>