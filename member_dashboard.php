<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit();
}

// Handle terms acceptance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_terms'])) {
    $update_query = "UPDATE users SET terms_accepted = 1 WHERE user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    header("Location: member_dashboard.php");
    exit();
}

// Handle incident report submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_incident'])) {
    $title = htmlspecialchars($_POST['incident_title']);
    $description = htmlspecialchars($_POST['incident_description']);
    $user_id = $_SESSION['user_id'];

    if ($title && $description) {
        $stmt = $conn->prepare("INSERT INTO incident_reports (user_id, title, description, reported_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iss", $user_id, $title, $description);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Incident reported successfully!');</script>";
        } else {
            echo "<script>alert('❌ Error reporting the incident.');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('⚠️ All fields are required.');</script>";
    }
}

// Check if user has accepted terms
$show_terms_popup = false;
$query = "SELECT terms_accepted FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($terms_accepted);
$stmt->fetch();
$stmt->close();

if (!$terms_accepted) {
    $show_terms_popup = true;
}

// Fetch latest announcements
$announcements = [];
$query = "SELECT id, title, content, image_path, created_at FROM announcements ORDER BY created_at DESC LIMIT 5";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Member Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        header {
            background: #1e3a8a;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        header div {
            font-weight: 600;
            font-size: 1.2rem;
        }

        nav a {
            color: white;
            margin-left: 1.2rem;
            text-decoration: none;
            font-weight: 500;
        }

        .hero {
            background: linear-gradient(to right, #1e3a8a, #3b82f6);
            color: white;
            text-align: center;
            padding: 70px 20px;
        }

        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 0 20px;
        }

        .announcement-list {
            display: grid;
            gap: 20px;
            padding: 0;
            list-style: none;
        }

        .announcement-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            border-left: 6px solid #3b82f6;
        }

        .announcement-img-wrapper img {
            width: 100%;
            max-height: 240px;
            object-fit: cover;
        }

        .announcement-content {
            padding: 20px;
        }

        .footer {
            text-align: center;
            font-size: 0.9rem;
            color: #666;
            margin: 80px 0 30px;
        }

        .social-icons a {
            margin: 0 10px;
            color: #1e3a8a;
            font-size: 1.2rem;
        }

        /* Terms Modal */
        .modal, .incident-modal {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            display: flex; justify-content: center; align-items: center;
            z-index: 9999;
        }

        .modal-content, .incident-content {
            background: white;
            padding: 20px 30px;
            border-radius: 8px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .modal-content button, .incident-content button {
            background-color: #1e3a8a;
            color: white;
            padding: 10px 20px;
            margin-top: 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .incident-report-trigger {
            background: #d97706;
            color: white;
            padding: 10px 20px;
            margin: 30px auto;
            border: none;
            border-radius: 5px;
            display: block;
            cursor: pointer;
        }

        .incident-content h3 {
            margin-bottom: 10px;
            color: #d97706;
            text-align: center;
        }

        .incident-content input,
        .incident-content textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            margin: 8px 0;
            border-radius: 5px;
        }

        .incident-content button:hover {
            background: #b45309;
        }

        @media (max-width: 600px) {
            .hero h1 { font-size: 2rem; }
            nav a { margin-left: 0.8rem; font-size: 0.9rem; }
            .container { margin: 30px auto; }
        }
    </style>
</head>
<body>

<header>
    <div>🏋️ <strong>Sports Club</strong></div>
    <nav>
        <a href="member_dashboard.php">Home</a>
        <a href="view_equipment.php">Equipment</a>
        <a href="edit_profile.php">Settings</a>
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
        <?php if (count($announcements) > 0): ?>
            <?php foreach ($announcements as $a): ?>
                <li class="announcement-card">
                    <?php if (!empty($a['image_path'])): ?>
                        <div class="announcement-img-wrapper">
                            <img src="<?= htmlspecialchars($a['image_path']) ?>" alt="Announcement Image">
                        </div>
                    <?php endif; ?>
                    <div class="announcement-content">
                        <div class="announcement-title"><?= htmlspecialchars($a['title']) ?></div>
                        <div class="announcement-text"><?= nl2br(htmlspecialchars($a['content'])) ?></div>
                        <div class="announcement-date">📅 <?= date('F j, Y', strtotime($a['created_at'])) ?></div>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; color: #555;">No announcements at the moment.</p>
        <?php endif; ?>
    </ul>

    <!-- Report Incident Button -->
    <button class="incident-report-trigger" onclick="document.getElementById('incidentModal').style.display='flex'">⚠ Report an Incident</button>
</div>

<div class="footer">
    <p>Need help?</p>
    <div class="social-icons">
        <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook-f"></i></a>
        <a href="https://instagram.com" target="_blank"><i class="fab fa-instagram"></i></a>
        <a href="https://x.com" target="_blank"><i class="fab fa-x-twitter"></i></a>
    </div>
</div>

<!-- Incident Report Modal -->
<div id="incidentModal" class="incident-modal" style="display:none;">
    <div class="incident-content">
        <h3>⚠ Report an Incident</h3>
        <form method="POST">
            <input type="text" name="incident_title" placeholder="Incident Title" required>
            <textarea name="incident_description" placeholder="Describe the issue..." required></textarea>
            <button type="submit" name="report_incident">Submit Report</button>
            <button type="button" onclick="document.getElementById('incidentModal').style.display='none'" style="background:#ccc; color:#333; margin-left:10px;">Cancel</button>
        </form>
    </div>
</div>

<!-- Terms Modal -->
<?php if ($show_terms_popup): ?>
<div id="termsModal" class="modal">
    <div class="modal-content">
        <h2>📜 Terms & Conditions</h2>
        <p>By using this system, you agree to follow club rules on equipment use and respectful conduct. Your personal data is protected under <strong>Republic Act No. 10173</strong> (Data Privacy Act of 2012).</p>
        <form method="POST">
            <button type="submit" name="accept_terms">Accept & Continue</button>
        </form>
    </div>
</div>
<?php endif; ?>

</body>
</html>
