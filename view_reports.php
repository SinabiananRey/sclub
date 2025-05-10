<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

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
    <title>Reports | Sports Club</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #eef1f5;
            display: flex;
        }

        .sidebar {
            width: 250px;
            background: #003366;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
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
            margin-left: 270px;
            padding: 30px;
            width: 100%;
        }

        h2 {
            text-align: center;
            color: #003366;
            margin-bottom: 20px;
        }

        .message {
            text-align: center;
            font-weight: 600;
            padding: 12px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            width: 60%;
            margin-left: auto;
            margin-right: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-top: 30px;
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #003366;
            color: white;
            font-weight: 600;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .toggle-btn {
            position: fixed;
            top: 10px;
            left: 10px;
            background: #003366;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 18px;
            z-index: 1000;
            display: none;
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 15px;
            }

            .sidebar {
                display: none;
                position: relative;
                width: 100%;
                height: auto;
            }

            .toggle-btn {
                display: block;
            }
        }
    </style>
</head>
<body>

<!-- Toggle Button -->
<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <h2><a href="admin_panel.php" style="color:white; text-decoration:none;">Admin Panel</a></h2>
    <a href="manage_members.php">Manage Members</a>
    <a href="manage_equipment.php">Manage Equipment</a>
    <a href="post_announcements.php">Post Announcements</a>
    <a href="view_reports.php">View Reports</a>
    <a href="settings.php">System Settings</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="container">
    <h2>Member Borrowing Overview</h2>

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

<script>
    function toggleSidebar() {
        var sidebar = document.getElementById("sidebar");
        sidebar.style.display = (sidebar.style.display === "block") ? "none" : "block";
    }
</script>

</body>
</html>
