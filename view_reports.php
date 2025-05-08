<?php
session_start();
include 'db_connect.php';

// Ensure only admin users can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch report data
$member_count_query = "SELECT COUNT(*) AS total_members FROM members";
$member_count_result = $conn->query($member_count_query);
$member_data = $member_count_result->fetch_assoc();

$equipment_count_query = "SELECT COUNT(*) AS total_equipment FROM equipment";
$equipment_count_result = $conn->query($equipment_count_query);
$equipment_data = $equipment_count_result->fetch_assoc();

$borrowed_items_query = "SELECT COUNT(*) AS borrowed_count FROM borrow_transactions WHERE status = 'borrowed'";
$borrowed_items_result = $conn->query($borrowed_items_query);
$borrowed_data = $borrowed_items_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Reports | Admin Panel</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }

        .toggle-btn {
            display: none;
            position: absolute;
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

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: #003366;
            color: white;
            padding: 20px;
            transition: left 0.3s ease;
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

        .main-content {
            flex-grow: 1;
            padding: 20px;
            background: #f4f4f4;
            overflow-y: auto;
        }

        h2 {
            margin-bottom: 20px;
        }

        .summary-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .summary-container ul {
            list-style-type: none;
            padding: 0;
        }

        .summary-container li {
            margin: 10px 0;
        }

        canvas {
            max-width: 100%;
            margin-top: 20px;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            font-size: 16px;
            color: #333;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .download-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #003366;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .download-btn:hover {
            background-color: #45a049;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                position: absolute;
                top: 0;
                left: -250px;
                height: 100%;
                z-index: 999;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                padding-top: 60px;
            }

            .toggle-btn {
                display: block;
            }
        }
    </style>
</head>
<body>

<button class="toggle-btn" onclick="toggleSidebar()">☰</button>

<div class="container">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <h2>Admin Panel</h2>
        <a href="manage_members.php">Manage Members</a>
        <a href="manage_equipment.php">Manage Equipment</a>
        <a href="post_announcements.php">Post Announcements</a>
        <a href="view_reports.php">View Reports</a>
        <a href="settings.php">System Settings</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Club Reports</h2>

        <div class="summary-container">
            <h3>Summary Overview</h3>
            <ul>
                <li><strong>Total Members:</strong> <?php echo $member_data['total_members']; ?></li>
                <li><strong>Total Equipment:</strong> <?php echo $equipment_data['total_equipment']; ?></li>
                <li><strong>Currently Borrowed Equipment:</strong> <?php echo $borrowed_data['borrowed_count']; ?></li>
            </ul>
        </div>

        <h3>Graphical Overview</h3>
        <canvas id="membersChart"></canvas>
        <canvas id="equipmentChart"></canvas>
        <canvas id="borrowedChart"></canvas>

        <script>
            // Members Chart
            const membersCtx = document.getElementById('membersChart').getContext('2d');
            new Chart(membersCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Total Members'],
                    datasets: [{
                        data: [<?php echo json_encode($member_data['total_members']); ?>],
                        backgroundColor: ['#4CAF50']
                    }]
                }
            });

            // Equipment Chart
            const equipmentCtx = document.getElementById('equipmentChart').getContext('2d');
            new Chart(equipmentCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Total Equipment'],
                    datasets: [{
                        data: [<?php echo json_encode($equipment_data['total_equipment']); ?>],
                        backgroundColor: ['#FF9800']
                    }]
                }
            });

            // Borrowed Equipment Chart
            const borrowedCtx = document.getElementById('borrowedChart').getContext('2d');
            new Chart(borrowedCtx, {
                type: 'bar',
                data: {
                    labels: ['Borrowed Equipment'],
                    datasets: [{
                        data: [<?php echo json_encode($borrowed_data['borrowed_count']); ?>],
                        backgroundColor: ['#2196F3']
                    }]
                }
            });
        </script>

        <!-- Download Reports Button -->
        <a href="generate_pdf.php" class="download-btn" target="_blank">Download Report as PDF</a>

        <br>
        <div class="back-link">
            <a href="admin_panel.php">Back to Admin Panel</a>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }
</script>

</body>
</html>
