<?php
session_start();
include 'db_connect.php';

// Ensure only members can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: login.php");
    exit();
}

// Fetch all announcements
$announcements_query = "SELECT title, content, date_posted FROM announcements ORDER BY date_posted DESC";
$announcements_result = $conn->query($announcements_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Announcements</title>
</head>
<body>
    <h2>Club Announcements</h2>

    <?php while ($row = $announcements_result->fetch_assoc()) { ?>
        <div>
            <h3><?php echo $row['title']; ?></h3>
            <p><?php echo $row['content']; ?></p>
            <small>Posted on: <?php echo $row['date_posted']; ?></small>
        </div>
        <hr>
    <?php } ?>

    <br>
</body>
</html>