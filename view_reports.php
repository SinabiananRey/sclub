<?php
session_start();
include 'db_connect.php';

// ✅ Ensure only admin users can access reports
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Fetch member borrowing details including return status
$member_query = "SELECT m.full_name, m.email, COUNT(b.transaction_id) AS total_borrowed, 
                 GROUP_CONCAT(CONCAT(e.name, ' (', IF(b.status = 'returned', 'Returned', 'Not Returned'), ')') SEPARATOR ', ') AS borrowed_items
                 FROM members m
                 LEFT JOIN borrow_transactions b ON m.user_id = b.member_id
                 LEFT JOIN equipment e ON b.equipment_id = e.equipment_id
                 GROUP BY m.user_id
                 ORDER BY total_borrowed DESC";
$member_result = $conn->query($member_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Reports | Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; background: #f9fafb; color: #333; }
        .container { max-width: 1000px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; }
        h2 { text-align: center; color: #1e3a8a; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f3f4f6; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { font-size: 16px; color: #1e3a8a; }
        .back-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="container">
    <h2>Member Borrowing Overview</h2>

    <div class="summary-container">
        <h3>Borrowing Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Total Items Borrowed</th>
                    <th>Borrowed Equipment (Return Status)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $member_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_borrowed']); ?></td>
                        <td><?php echo $row['borrowed_items'] ? htmlspecialchars($row['borrowed_items']) : 'No items borrowed'; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="back-link">
        <a href="admin_panel.php">Back to Admin Panel</a>
    </div>
</div>

</body>
</html>