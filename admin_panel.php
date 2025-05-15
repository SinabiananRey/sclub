<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$member_query = "SELECT COUNT(*) AS total_members FROM members WHERE role = 'member'";
$member_result = $conn->query($member_query);
$member_data = $member_result->fetch_assoc();

$equipment_query = "SELECT COUNT(*) AS total_equipment FROM equipment";
$equipment_result = $conn->query($equipment_query);
$equipment_data = $equipment_result->fetch_assoc();

$borrow_query = "SELECT COUNT(*) AS borrowed_count FROM borrow_transactions WHERE status = 'borrowed'";
$borrow_result = $conn->query($borrow_query);
$borrow_data = $borrow_result->fetch_assoc();

$monthly_borrow_query = "SELECT MONTH(borrow_date) AS month, COUNT(*) AS borrow_count FROM borrow_transactions 
    WHERE status = 'borrowed' GROUP BY MONTH(borrow_date) ORDER BY MONTH(borrow_date)";
$monthly_borrow_result = $conn->query($monthly_borrow_query);
$months = [];
$monthly_counts = [];

while ($row = $monthly_borrow_result->fetch_assoc()) {
    $months[] = date('F', mktime(0, 0, 0, $row['month'], 1));
    $monthly_counts[] = $row['borrow_count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel | Sports Club</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #eef1f5;
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

        .sidebar h2 {
            margin-bottom: 20px;
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
            color: #003366;
            text-align: center;
        }

        .stats {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }

        .stat-box {
            flex: 1 1 200px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .stat-box h3 {
            color: #333;
        }

        .charts-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 40px;
        }

        .chart-card {
            flex: 1 1 45%;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .chart-card h4 {
            text-align: center;
            margin-bottom: 20px;
        }

        canvas {
            height: 300px !important;
            width: 100% !important;
        }

        .toggle-btn {
            display: none;
        }

        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 20px;
            }

            .sidebar {
                display: none;
            }

            .toggle-btn {
                display: block;
                position: fixed;
                top: 15px;
                left: 15px;
                background-color: #003366;
                color: white;
                border: none;
                font-size: 24px;
                cursor: pointer;
                z-index: 1000;
                padding: 8px 12px;
                border-radius: 5px;
            }
        }
    </style>
</head>
<body>

<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="sidebar" id="sidebar">
    <h2><a href="admin_panel.php" style="color:white; text-decoration:none;">Admin Panel</a></h2>
    <a href="manage_members.php">Manage Members</a>
    <a href="manage_equipment.php">Manage Equipment</a>
    <a href="post_announcements.php">Post Announcements</a>
    <a href="view_reports.php">View Reports</a>
    <a href="settings.php">System Settings</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
    <h2>Welcome, Admin!</h2>

    <div class="stats">
        <div class="stat-box">
            <h3>Total Members</h3>
            <p><?php echo $member_data['total_members']; ?></p>
        </div>
        <div class="stat-box">
            <h3>Total Equipment</h3>
            <p><?php echo $equipment_data['total_equipment']; ?></p>
        </div>
        <div class="stat-box">
            <h3>Borrowed Equipment</h3>
            <p><?php echo $borrow_data['borrowed_count']; ?></p>
        </div>
    </div>

    <div class="charts-container">
        <div class="chart-card">
            <h4>Member Count</h4>
            <canvas id="membersChart"></canvas>
        </div>
        <div class="chart-card">
            <h4>Equipment Count</h4>
            <canvas id="equipmentChart"></canvas>
        </div>
        <div class="chart-card">
            <h4>Borrowed Equipment</h4>
            <canvas id="borrowedChart"></canvas>
        </div>
        <div class="chart-card">
            <h4>Monthly Borrowing Trends</h4>
            <canvas id="monthlyBorrowChart"></canvas>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        var sidebar = document.getElementById("sidebar");
        sidebar.style.display = sidebar.style.display === "block" ? "none" : "block";
    }

    new Chart(document.getElementById('membersChart'), {
        type: 'doughnut',
        data: {
            labels: ['Total Members'],
            datasets: [{
                data: [<?php echo $member_data['total_members']; ?>],
                backgroundColor: ['#4CAF50']
            }]
        }
    });

    new Chart(document.getElementById('equipmentChart'), {
        type: 'doughnut',
        data: {
            labels: ['Total Equipment'],
            datasets: [{
                data: [<?php echo $equipment_data['total_equipment']; ?>],
                backgroundColor: ['#FF9800']
            }]
        }
    });

    new Chart(document.getElementById('borrowedChart'), {
        type: 'bar',
        indexAxis: 'y',
        data: {
            labels: ['Borrowed Equipment'],
            datasets: [{
                data: [<?php echo $borrow_data['borrowed_count']; ?>],
                backgroundColor: ['#2196F3']
            }]
        }
    });

    new Chart(document.getElementById('monthlyBorrowChart'), {
        type: 'bar',
        indexAxis: 'y',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Monthly Borrowing',
                data: <?php echo json_encode($monthly_counts); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        }
    });
</script>

</body>
</html>
