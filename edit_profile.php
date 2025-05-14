<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// ✅ Auto-update overdue status before fetching history
$update_status_query = "UPDATE borrow_transactions  
                        SET status = 'overdue'  
                        WHERE return_date < NOW() AND (status IS NULL OR status = '')";
$conn->query($update_status_query);

// ✅ Fetch user info
$query = "SELECT username, email FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ✅ Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'], $_POST['email'], $_POST['password'], $_POST['confirm_password'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_query = "UPDATE users SET username = ?, email = ?, password = ? WHERE user_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssi", $username, $email, $hashed_password, $member_id);

        if ($stmt->execute()) {
            $_SESSION['confirmation_message'] = "Profile updated successfully!";
            header("Location: edit_profile.php");
            exit();
        } else {
            $error = "Error updating profile.";
        }
    }
}

// ✅ Handle return action
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['return_transaction_id'])) {
    $return_id = $_POST['return_transaction_id'];
    $return_query = "UPDATE borrow_transactions SET returned_date = NOW(), status = 'returned' WHERE transaction_id = ?";
    $stmt = $conn->prepare($return_query);
    $stmt->bind_param("i", $return_id);

    if ($stmt->execute()) {
        $_SESSION['confirmation_message'] = "✅ Equipment returned successfully!";
        header("Location: edit_profile.php");
        exit();
    } else {
        $error = "❌ Error updating return status.";
    }
}

// ✅ Borrowing history - include transaction_id for the return button
$history_query = "SELECT b.transaction_id, b.borrow_date, b.return_date, b.returned_date, e.name,  
                         CASE  
                             WHEN b.status = 'borrowed' AND b.return_date < NOW() THEN 'overdue'  
                             ELSE b.status  
                         END AS status  
                  FROM borrow_transactions b  
                  JOIN equipment e ON b.equipment_id = e.equipment_id  
                  WHERE b.member_id = ? ORDER BY b.borrow_date DESC";

$stmt = $conn->prepare($history_query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$history_result = $stmt->get_result();

// ✅ Send overdue email notifications
function sendOverdueNotification($member_email, $equipment_name, $return_date) {
    $subject = "Overdue Equipment Notice";
    $message = "Hello,\n\nYou have overdue equipment: $equipment_name.\nIt was due on $return_date.\nPlease return it as soon as possible.";
    $headers = "From: admin@sportsclub.com";

    mail($member_email, $subject, $message, $headers);
}

$overdue_query = "SELECT u.email, e.name, b.return_date  
                  FROM borrow_transactions b  
                  JOIN users u ON b.member_id = u.user_id  
                  JOIN equipment e ON b.equipment_id = e.equipment_id  
                  WHERE b.return_date < NOW() AND b.status = 'overdue'";

$result = $conn->query($overdue_query);
while ($row = $result->fetch_assoc()) {
    sendOverdueNotification($row['email'], $row['name'], $row['return_date']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile & History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f9fafb; margin: 0; padding: 0; color: #333; }
        header { background: #1e3a8a; color: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; margin-left: 1rem; text-decoration: none; font-weight: 500; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 800px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; }
        h2 { color: #1e3a8a; text-align: center; }
        label { display: block; margin-top: 15px; font-weight: 600; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #ccc; border-radius: 4px; }
        button, .toggle-btn { margin-top: 20px; padding: 10px 16px; background-color: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 500; }
        button:hover, .toggle-btn:hover { background-color: #2563eb; }
        .message { background-color: #e0f2fe; color: #0369a1; padding: 12px; border-radius: 6px; margin-top: 20px; text-align: center; }
        .error { color: red; font-weight: 500; margin-top: 10px; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f3f4f6; }
    </style>
</head>
<body>

<header>
    <div><strong>Sports Club</strong></div>
    <nav>
        <a href="member_dashboard.php">Home</a>
        <a href="view_equipment.php">Equipment</a>
        <a href="edit_profile.php">Settings</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Edit Profile</h2>
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    <?php if (isset($_SESSION['confirmation_message'])): ?>
        <div class="message"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <label>New Password:</label>
        <input type="password" name="password" required>
        <label>Confirm New Password:</label>
        <input type="password" name="confirm_password" required>
        <button type="submit">Update Profile</button>
    </form>

    <h2>Borrowing History</h2>
    <table>
        <tr><th>Equipment</th><th>Status</th><th>Borrowed Date</th><th>Return Date</th><th>Returned Date</th><th>Action</th></tr>
        <?php while ($row = $history_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><?php echo htmlspecialchars($row['borrow_date']); ?></td>
                <td><?php echo htmlspecialchars($row['return_date']); ?></td>
                <td><?php echo $row['returned_date'] ? htmlspecialchars($row['returned_date']) : 'Not Returned'; ?></td>
                <td>
                    <?php if ($row['status'] == 'borrowed' || $row['status'] == 'overdue') { ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="return_transaction_id" value="<?php echo $row['transaction_id']; ?>">
                            <button type="submit">Mark as Returned</button>
                        </form>
                    <?php } else { echo "✔ Returned"; } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

</body>
</html>
