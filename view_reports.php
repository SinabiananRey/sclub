<?php
session_start();
include 'db_connect.php';

date_default_timezone_set('Asia/Manila');

// ✅ Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Handle return confirmation
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

// ✅ Fetch borrowing records
$member_query = "
    SELECT 
        m.user_id, 
        m.full_name, 
        m.email, 
        b.transaction_id, 
        e.name AS equipment_name, 
        b.status, 
        b.return_date 
    FROM members m
    JOIN borrow_transactions b ON m.user_id = b.member_id
    JOIN equipment e ON b.equipment_id = e.equipment_id
    ORDER BY m.full_name ASC, b.status DESC
";

$member_result = $conn->query($member_query);

// Group records by member
$grouped_records = [];
$borrowed_count = 0;

while ($row = $member_result->fetch_assoc()) {
    $user_id = $row['user_id'];
    if (!isset($grouped_records[$user_id])) {
        $grouped_records[$user_id] = [
            'full_name' => $row['full_name'],
            'email' => $row['email'],
            'borrowings' => []
        ];
    }

    if (strtolower(trim($row['status'])) === 'borrowed') {
        $borrowed_count++;
    }

    $grouped_records[$user_id]['borrowings'][] = $row;
}
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
        h2 {
            text-align: center; color: #003366; margin-bottom: 10px;
            cursor: pointer;
        }
        .message {
            text-align: center; font-weight: 600;
            padding: 12px 20px; border-radius: 6px;
            margin-bottom: 20px; background: #fff3cd; color: #856404;
        }
        .alert {
            text-align: center; font-weight: bold;
            color: #d97706; margin-bottom: 20px;
        }
        .member-box {
            background: white;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .member-box h3 {
            margin: 0 0 5px;
            color: #003366;
        }
        .member-box small {
            color: #666;
        }
        table {
            width: 100%; border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px 12px; border-bottom: 1px solid #ddd; text-align: center;
        }
        th { background: #f0f4f8; font-weight: bold; }
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
            font-size: 0.85em; font-weight: 600;
        }
        .borrowed { background: #facc15; color: #000; }
        .returned { background: #22c55e; color: white; }
        .due-today {
            color: #eab308;
            font-weight: bold;
            margin-left: 5px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2><a href="admin_panel.php" style="color:white;">Admin Panel</a></h2>
    <a href="manage_members.php">Manage Members</a>
    <a href="manage_equipment.php">Manage Equipment</a>
    <a href="post_announcements.php">Post Announcements</a>
    <a href="view_reports.php">View Reports</a>
    <a href="settings.php">System Settings</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="container">
    <h2 onclick="toggleTable()">Member Borrowing Overview</h2>

    <?php if ($borrowed_count > 0): ?>
        <div class="alert">⚠️ There are <?= $borrowed_count ?> borrowed equipment(s) to confirm return!</div>
    <?php endif; ?>

    <?php if (isset($_SESSION['confirmation_message'])): ?>
        <div class="message"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
    <?php endif; ?>

    <div id="borrowingTable" style="display: none;">
        <?php foreach ($grouped_records as $member): ?>
            <div class="member-box">
                <h3><?= htmlspecialchars($member['full_name']) ?></h3>
                <small><?= htmlspecialchars($member['email']) ?></small>
                <table>
                    <thead>
                        <tr>
                            <th>Equipment</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($member['borrowings'] as $borrow): 
                            $status = strtolower(trim($borrow['status']));
                            $due_date = new DateTime($borrow['return_date']);
                            $today = new DateTime();

                            $enable_button = ($status === 'borrowed');
                            $is_due_today = ($status === 'borrowed' && $today->format('Y-m-d') === $due_date->format('Y-m-d'));
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($borrow['equipment_name']) ?></td>
                            <td>
                                <span class="badge <?= $status === 'borrowed' ? 'borrowed' : 'returned' ?>">
                                    <?= ucfirst($status) ?>
                                </span>
                            </td>
                            <td>
                                <?= $due_date->format("F j, Y") ?>
                                <?php if ($is_due_today): ?>
                                    <span class="due-today">(Due Today)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="approve_return_id" value="<?= $borrow['transaction_id'] ?>">
                                    <button type="submit" <?= !$enable_button ? 'disabled title="Only borrowed items can be returned"' : '' ?>>
                                        Confirm
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function toggleTable() {
        const table = document.getElementById('borrowingTable');
        table.style.display = table.style.display === 'none' ? 'block' : 'none';
    }
</script>

</body>
</html>
