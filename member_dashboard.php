<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: login.php");
    exit();
}

// ✅ Fetch recent announcements
$announcement_query = "SELECT id, title, content, image_path, created_at FROM announcements ORDER BY created_at DESC LIMIT 5";
$announcement_result = $conn->query($announcement_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Inter', sans-serif; background: #f5f7fa; color: #333; }
        header { background: #1e3a8a; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
        header div { font-weight: 600; font-size: 1.2rem; }
        nav a { color: white; margin-left: 1.2rem; text-decoration: none; font-weight: 500; transition: opacity 0.2s ease; }
        nav a:hover { opacity: 0.8; }
        .hero { background: linear-gradient(to right, #1e3a8a, #3b82f6); color: white; text-align: center; padding: 70px 20px; }
        .hero h1 { font-size: 2.5rem; margin-bottom: 10px; }
        .hero p { font-size: 1.1rem; margin-bottom: 25px; }
        .hero a { background: white; color: #1e3a8a; padding: 12px 24px; border-radius: 6px; font-weight: 600; text-decoration: none; transition: background 0.2s ease, color 0.2s ease; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .hero a:hover { background: #e0e7ff; color: #1d4ed8; }
        .container { max-width: 900px; margin: 50px auto; padding: 0 20px; }
        .container h3 { font-size: 1.5rem; margin-bottom: 20px; color: #1e3a8a; text-align: center; }
        .announcement-list { display: grid; gap: 15px; padding: 0; list-style: none; }
        .announcement-list li { background: white; border-left: 6px solid #3b82f6; padding: 16px 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .announcement-list li:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .announcement-list li strong { font-size: 1.05rem; display: block; margin-bottom: 6px; color: #1e40af; }
        .announcement-list li span { display: block; margin-top: 10px; font-size: 0.85rem; color: #6b7280; }
        .announcement-image {
            max-width: 100%;
            width: 100%;
            height: auto;
            border-radius: 8px;
            margin-top: 10px;
            display: block;
        }
        .announcement-img-wrapper {
            margin-top: 10px;
            text-align: center;
        }
        .footer { text-align: center; font-size: 0.9rem; color: #666; margin: 80px 0 30px; }
        .footer a { color: #1e3a8a; text-decoration: none; font-weight: 500; }
        .footer a:hover { text-decoration: underline; }
        .social-icons { margin-top: 10px; }
        .social-icons a { margin: 0 10px; color: #1e3a8a; font-size: 1.2rem; transition: color 0.2s ease; }
        .social-icons a:hover { color: #2563eb; }
        @media (max-width: 600px) {
            .hero h1 { font-size: 2rem; }
            nav a { margin-left: 0.8rem; font-size: 0.9rem; }
            .container { margin: 30px auto; }
        }
    </style>
</head>
<body>

<header>
    <div>Sports Club</div>
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
    <a href="view_equipment.php">Borrow Equipment</a>
</div>

<div class="container">
    <h3>Latest Announcements</h3>
    <ul class="announcement-list">
        <?php while ($row = $announcement_result->fetch_assoc()) { ?>
            <li>
                <strong><?php echo htmlspecialchars($row['title']); ?></strong>
                <?php echo nl2br(htmlspecialchars($row['content'])); ?>

                <?php if (!empty($row['image_path'])) { ?>
                    <div class="announcement-img-wrapper">
                        <img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="announcement-image" alt="Announcement Image">
                    </div>
                <?php } ?>

                <span>Posted on <?php echo date('F j, Y', strtotime($row['created_at'])); ?></span>
            </li>
        <?php } ?>
    </ul>
</div>

<div class="footer">
    <p>Need help?</p>
    <div class="social-icons">
        <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook-f"></i></a>
        <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://x.com" target="_blank"><i class="fab fa-x-twitter"></i></a>
    </div>
</div>

</body>
</html>
