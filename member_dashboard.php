<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: login.php");
    exit();
}

$announcement_query = "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5";
$announcement_result = $conn->query($announcement_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f9fafb;
            color: #333;
        }

        header {
            background: #1e3a8a;
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav a {
            color: #fff;
            margin-left: 1rem;
            text-decoration: none;
            font-weight: 500;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .hero {
            background: #1e3a8a;
            color: #fff;
            text-align: center;
            padding: 60px 20px;
        }

        .hero h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .hero p {
            margin-bottom: 20px;
        }

        .hero a {
            background: #3b82f6;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .container h3 {
            margin-bottom: 20px;
            text-align: center;
            color: #1e3a8a;
        }

        .announcement-list {
            list-style: none;
            padding: 0;
        }

        .announcement-list li {
            background: #e0e7ff;
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 6px;
        }

        .announcement-list li strong {
            display: block;
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .announcement-list li span {
            font-size: 0.85rem;
            color: #555;
        }

        .footer {
            text-align: center;
            font-size: 0.9rem;
            color: #666;
            margin: 60px 0 20px;
        }

        .footer a {
            color: #1e3a8a;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            header, .hero, .container {
                padding: 1rem;
            }

            nav a {
                margin-left: 0.5rem;
                font-size: 0.9rem;
            }

            .hero h1 {
                font-size: 1.5rem;
            }
        }
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

<div class="hero">
    <h1>Welcome to Sports Club</h1>
    <p>Your Hub for Sports & Activities</p>
    <a href="view_equipment.php">Borrow Now</a>
</div>

<div class="container">
    <h3>Announcements</h3>
    <ul class="announcement-list">
        <?php while ($row = $announcement_result->fetch_assoc()) { ?>
            <li>
                <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                <?php echo htmlspecialchars($row['content']); ?>
                <span>(<?php echo htmlspecialchars($row['created_at']); ?>)</span>
            </li>
        <?php } ?>
    </ul>
</div>

<div class="footer">
    <p>Need help? <a href="contact.php">Contact Support</a></p>
</div>

</body>
</html>
