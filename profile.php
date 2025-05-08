<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "Error loading profile.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
</head>
<body>
    <h2>User Profile</h2>
    <p><strong>Username:</strong> <?php echo $user['username']; ?></p>
    <p><strong>Role:</strong> <?php echo ucfirst($user['role']); ?></p>
    <a href="<?php echo ($_SESSION['role'] == 'admin') ? 'admin_panel.php' : 'member_home.php'; ?>">Back to Dashboard</a>
    <a href="logout.php">Logout</a>
</body>
</html>