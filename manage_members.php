<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Handle member deletion only if no borrow transactions exist
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_member_id'])) {
    $delete_id = intval($_POST['delete_member_id']);

    // ✅ Check if the member has borrowing transactions
   $check_borrow_query = "SELECT COUNT(*) FROM borrow_transactions WHERE member_id = ? AND status = 'borrowed'";
$stmt_check = $conn->prepare($check_borrow_query);
$stmt_check->bind_param("i", $delete_id);
$stmt_check->execute();
$stmt_check->bind_result($count);
$stmt_check->fetch();
$stmt_check->close();

if ($count > 0) {
    $message = "<p class='message error'>❌ Cannot delete member. They still have unreturned equipment!</p>";
} else {
    // ✅ Proceed with member deletion
    $delete_members_query = "DELETE FROM members WHERE user_id = ?";
    $stmt_delete_members = $conn->prepare($delete_members_query);
    $stmt_delete_members->bind_param("i", $delete_id);
    $stmt_delete_members->execute();
    $stmt_delete_members->close();

    $delete_users_query = "DELETE FROM users WHERE user_id = ?";
    $stmt_delete_users = $conn->prepare($delete_users_query);
    $stmt_delete_users->bind_param("i", $delete_id);
    $stmt_delete_users->execute();
    $stmt_delete_users->close();

    $message = "<p class='message success'>✅ Member deleted successfully!</p>";
}
}

// ✅ Fetch members list
$query = "SELECT user_id, full_name, email, role, joined_date FROM members";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Members | Sports Club</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #eef1f5; display: flex; }
        .sidebar {
            width: 250px; background: #003366; color: white;
            padding: 20px; height: 100vh; position: fixed; left: 0; top: 0;
        }
        .sidebar a {
            display: block; color: white; text-decoration: none;
            padding: 10px; border-radius: 5px; margin-bottom: 10px;
        }
        .sidebar a:hover { background: #0055aa; }
        .container { margin-left: 270px; padding: 30px; width: 100%; }
        h2 { text-align: center; color: #003366; margin-bottom: 20px; }
        .message {
            text-align: center; font-weight: 600;
            padding: 12px 20px; border-radius: 6px;
            margin-bottom: 20px; width: 60%;
            margin-left: auto; margin-right: auto;
        }
        .message.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table {
            width: 100%; border-collapse: collapse;
            background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: center;
        }
        th { background: #003366; color: white; font-weight: 600; }
        tr:nth-child(even) { background: #f9f9f9; }
        .delete-btn {
            background: #dc3545; color: white; border: none;
            padding: 8px 14px; border-radius: 5px; cursor: pointer;
            transition: background 0.3s;
        }
        .delete-btn:hover { background: #c82333; }
        .delete-btn:disabled { background: #ccc; color: #666; cursor: not-allowed; }
    </style>
</head>
<body>

<!-- ✅ Sidebar -->
<div class="sidebar">
    <h2><a href="admin_panel.php" style="color:white;">Admin Panel</a></h2>
    <a href="manage_members.php">Manage Members</a>
    <a href="manage_equipment.php">Manage Equipment</a>
    <a href="post_announcements.php">Post Announcements</a>
    <a href="view_reports.php">View Reports</a>
    <a href="settings.php">System Settings</a>
</div>

<!-- ✅ Main Content -->
<div class="container">
    <h2>Manage Members</h2>

    <?php if (isset($message)) echo $message; ?>

    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
                // ✅ Check if the member has borrowing transactions
                $check_trans_query = "SELECT COUNT(*) FROM borrow_transactions WHERE member_id = ?";
                $stmt_trans = $conn->prepare($check_trans_query);
                $stmt_trans->bind_param("i", $row['user_id']);
                $stmt_trans->execute();
                $stmt_trans->bind_result($trans_count);
                $stmt_trans->fetch();
                $stmt_trans->close();
                $allow_deletion = ($trans_count == 0);
            ?>
            <tr>
                <td><?= htmlspecialchars($row['full_name']); ?></td>
                <td><?= htmlspecialchars($row['email']); ?></td>
                <td><?= htmlspecialchars($row['role']); ?></td>
                <td><?= htmlspecialchars($row['joined_date']); ?></td>
                <td>
                    <form method="POST" onsubmit="return confirmDelete()">
                        <input type="hidden" name="delete_member_id" value="<?= $row['user_id']; ?>">
                        <button type="submit" class="delete-btn" <?= !$allow_deletion ? 'disabled title="Cannot delete, has transactions"' : ''; ?>>Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
function confirmDelete() {
    return confirm("Are you sure you want to delete this member? This action cannot be undone.");
}
</script>

</body>
</html>