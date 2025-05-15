<?php
session_start();
include 'db_connect.php';

// ✅ Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ✅ Handle return approval requests
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_return_id'])) {
    $approve_id = $_POST['approve_return_id'];
    $approve_query = "UPDATE borrow_transactions SET returned_date = NOW(), status = 'returned' WHERE transaction_id = ?";
    $stmt = $conn->prepare($approve_query);
    $stmt->bind_param("i", $approve_id);

    if ($stmt->execute()) {
        $_SESSION['confirmation_message'] = "✅ Equipment marked as returned successfully!";
        header("Location: view_reports.php");
        exit();
    } else {
        $_SESSION['confirmation_message'] = "❌ Error updating return status.";
    }
}

// ✅ Fetch member borrowing records
$member_query = "SELECT m.user_id, m.full_name, m.email, b.transaction_id, e.name AS equipment_name, b.status 
                 FROM members m
                 JOIN borrow_transactions b ON m.user_id = b.member_id
                 JOIN equipment e ON b.equipment_id = e.equipment_id
                 ORDER BY b.status DESC, m.full_name ASC";
$member_result = $conn->query($member_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports | Sports Club</title>
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
            margin-bottom: 20px; background: #fff3cd; color: #856404;
        }
        table {
            width: 100%; border-collapse: collapse;
            background: white; border-radius: 10px;
            overflow: hidden; box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        th, td {
            padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: center;
        }
        th { background: #003366; color: white; font-weight: 600; }
        tr:nth-child(even) { background: #f9f9f9; }
        button {
            padding: 6px 10px; background-color: #3b82f6;
            color: white; border: none; border-radius: 4px;
            cursor: pointer; font-weight: 500; font-size: 0.9em;
            transition: 0.2s ease;
        }
        button:hover { background-color: #2563eb; }
        button:disabled {
            background-color: #ccc; color: #666; cursor: not-allowed;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 600;
        }
        .borrowed { background: #facc15; color: #000; }
        .returned { background: #22c55e; color: white; }
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
    <a href="logout.php">Logout</a>
</div>

<!-- ✅ Main Content -->
<div class="container">
    <h2>Member Borrowing Overview</h2>

    <?php if (isset($_SESSION['confirmation_message'])): ?>
        <div class="message"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr><th>Full Name</th><th>Email</th><th>Equipment Borrowed</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody>
            <?php while ($row = $member_result->fetch_assoc()) {
                $status = strtolower(trim($row['status']));
                $is_borrowed = ($status === 'borrowed');
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['equipment_name']); ?></td>
                    <td>
                        <span class="badge <?php echo $is_borrowed ? 'borrowed' : 'returned'; ?>">
                            <?php echo ucfirst($status); ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" style="display: flex; justify-content: center; align-items: center; margin: 0;">
                            <input type="hidden" name="approve_return_id" value="<?php echo $row['transaction_id']; ?>">
                            <button type="submit" <?php echo !$is_borrowed ? 'disabled title="Already returned"' : ''; ?>>
                                Confirm
                            </button>
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
