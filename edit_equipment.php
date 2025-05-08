<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get equipment details
$equipment_id = $_GET['id'];
$query = "SELECT * FROM equipment WHERE equipment_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $equipment_id);
$stmt->execute();
$result = $stmt->get_result();
$equipment = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $status = $_POST['status'];

    $update_query = "UPDATE equipment SET name = ?, status = ? WHERE equipment_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $name, $status, $equipment_id);

    if ($stmt->execute()) {
        header("Location: manage_equipment.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Equipment</title>
</head>
<body>
    <h2>Edit Equipment</h2>
    <form method="POST">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo $equipment['name']; ?>" required><br>
        <label>Status:</label>
        <select name="status">
            <option value="Available" <?php if ($equipment['status'] == "Available") echo "selected"; ?>>Available</option>
            <option value="Borrowed" <?php if ($equipment['status'] == "Borrowed") echo "selected"; ?>>Borrowed</option>
            <option value="Maintenance" <?php if ($equipment['status'] == "Maintenance") echo "selected"; ?>>Under Maintenance</option>
        </select><br>
        <button type="submit">Update Equipment</button>
    </form>
    <br>
    <a href="manage_equipment.php">Back to Manage Equipment</a>
</body>
</html>