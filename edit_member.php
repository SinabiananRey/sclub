<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get member details
$member_id = $_GET['id'];
$query = "SELECT members.member_id, users.username, members.full_name, members.email 
          FROM members
          JOIN users ON members.user_id = users.user_id
          WHERE members.member_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $username = $_POST['username'];

    $update_query = "UPDATE members 
                     JOIN users ON members.user_id = users.user_id
                     SET users.username = ?, members.full_name = ?, members.email = ? 
                     WHERE members.member_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $username, $full_name, $email, $member_id);

    if ($stmt->execute()) {
        header("Location: manage_members.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Member</title>
</head>
<body>
    <h2>Edit Member</h2>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo $member['username']; ?>" required><br>
        <label>Name:</label>
        <input type="text" name="full_name" value="<?php echo $member['full_name']; ?>" required><br>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo $member['email']; ?>" required><br>
        <button type="submit">Update Member</button>
    </form>
    <br>
    <a href="manage_members.php">Back to Manage Members</a>
</body>
</html>