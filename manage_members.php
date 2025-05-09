<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Handle member deletion (Ensures removal from both `members` and `users`)
if (isset($_POST['delete_member_id'])) {
    $delete_id = intval($_POST['delete_member_id']);

    // ✅ Check if member has borrow transactions
    $check_borrow_query = "SELECT COUNT(*) FROM borrow_transactions WHERE member_id = ?";
    $stmt_check = $conn->prepare($check_borrow_query);
    $stmt_check->bind_param("i", $delete_id);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        $message = "<p class='error'>Cannot delete member. They have active borrow transactions!</p>";
    } else {
        // ✅ First, delete from `members`
        $delete_members_query = "DELETE FROM members WHERE user_id = ?";
        $stmt_delete_members = $conn->prepare($delete_members_query);
        $stmt_delete_members->bind_param("i", $delete_id);
        $stmt_delete_members->execute();
        $stmt_delete_members->close();

        // ✅ Then, delete from `users` (Ensures login removal)
        $delete_users_query = "DELETE FROM users WHERE user_id = ?";
        $stmt_delete_users = $conn->prepare($delete_users_query);
        $stmt_delete_users->bind_param("i", $delete_id);
        $stmt_delete_users->execute();
        $stmt_delete_users->close();

        $message = "<p class='success'>Member deleted successfully!</p>";
    }
}

// ✅ Fetch all members
$query = "SELECT user_id, full_name, email, role, joined_date FROM members";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Members | Sports Club</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: #003366;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
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

        .container {
            max-width: 800px;
            margin: auto;
            padding-left: 270px; /* Push content right */
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
        }

        .message {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #003366;
            color: white;
        }

        .delete-btn {
            background: red;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
        }

        .delete-btn:hover {
            background: darkred;
        }
    </style>
</head>
<body>

<!-- ✅ Sidebar -->
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="manage_members.php">Manage Members</a>
    <a href="manage_equipment.php">Manage Equipment</a>
    <a href="post_announcements.php">Manage Announcements</a>
    <a href="view_reports.php">View Reports</a>
    <a href="settings.php">System Settings</a>
    <a href="logout.php">Logout</a>
</div>

<!-- ✅ Main Content -->
<div class="container">
    <h2>Manage Members</h2>
    
    <?php if (isset($message)) echo "<div class='message'>$message</div>"; ?>

    <table>
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined Date</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['full_name']); ?></td>
                <td><?= htmlspecialchars($row['email']); ?></td>
                <td><?= htmlspecialchars($row['role']); ?></td>
                <td><?= htmlspecialchars($row['joined_date']); ?></td>
                <td>
                    <form method="POST" onsubmit="return confirmDelete()">
                        <input type="hidden" name="delete_member_id" value="<?= $row['user_id']; ?>">
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this member? This action cannot be undone.");
}
</script>

</body>
</html>