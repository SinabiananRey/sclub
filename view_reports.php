<?php
session_start();
include 'db_connect.php';
date_default_timezone_set('Asia/Manila');

// PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

// ✅ Only allow admins
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// ✅ Insert login log once per session
if (!isset($_SESSION['login_logged'])) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    $log_query = "INSERT INTO login_logs (user_id, ip_address, user_agent) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($log_query);
    $stmt->bind_param("iss", $_SESSION['user_id'], $ip, $user_agent);
    $stmt->execute();
    $stmt->close();

    $_SESSION['login_logged'] = true;
}

// ✅ Confirm return of equipment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_transaction_id'])) {
    $tx_id = intval($_POST['confirm_transaction_id']);
    $stmt = $conn->prepare("UPDATE borrow_transactions SET status = 'returned' WHERE transaction_id = ?");
    $stmt->bind_param("i", $tx_id);
    $stmt->execute();
    $stmt->close();
    header("Location: view_reports.php");
    exit();
}

// ✅ Admin Responds to Incident Report + Email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_incident'])) {
    $incident_id = $_POST['incident_id'];
    $admin_response = htmlspecialchars($_POST['admin_response']);
    $status = $_POST['status'];

    // ✅ Get user's email and incident title
    $query = "SELECT u.email, i.title FROM incident_reports i 
              JOIN users u ON i.user_id = u.user_id 
              WHERE i.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $incident_id);
    $stmt->execute();
    $stmt->bind_result($member_email, $incident_title);
    $stmt->fetch();
    $stmt->close();

    // ✅ Update incident report
    $stmt = $conn->prepare("UPDATE incident_reports SET admin_response = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $admin_response, $status, $incident_id);

    if ($stmt->execute()) {
        $stmt->close();

        // ✅ Send Email to Member
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sinabiananrey@gmail.com';
            $mail->Password = 'rard mpnw rozl pqbr';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('sinabiananrey@gmail.com', 'Sports Club Admin');
            $mail->addAddress($member_email);
            $mail->Subject = 'Incident Report Response';
            $mail->Body = "Hello,\n\nYour reported incident titled '$incident_title' has been reviewed.\n\n"
                        . "Admin Response:\n$admin_response\n\n"
                        . "Status: $status\n\n"
                        . "Best regards,\nSports Club Admin";

            $mail->send();
            echo "<script>alert('✅ Incident updated & email sent!'); window.location.href='view_reports.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('⚠️ Incident updated, but email failed: " . $mail->ErrorInfo . "'); window.location.href='view_reports.php';</script>";
        }
    } else {
        echo "<script>alert('❌ Error updating incident.'); window.history.back();</script>";
    }
}

// ✅ Fetch borrowing records
$member_query = "
    SELECT 
        m.user_id, m.full_name, m.email, 
        b.transaction_id, e.name AS equipment_name, 
        b.status, b.return_date 
    FROM members m
    JOIN borrow_transactions b ON m.user_id = b.member_id
    JOIN equipment e ON b.equipment_id = e.equipment_id
    ORDER BY m.full_name ASC, b.status DESC
";
$member_result = $conn->query($member_query);

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

// ✅ Fetch login logs
$log_query = "
    SELECT m.full_name, l.login_time 
    FROM login_logs l 
    JOIN members m ON l.user_id = m.user_id 
    ORDER BY l.login_time DESC LIMIT 10
";
$log_result = $conn->query($log_query);
$grouped_logs = [];
while ($log = $log_result->fetch_assoc()) {
    $grouped_logs[$log['full_name']][] = $log['login_time'];
}

// ✅ Fetch incident reports
$incident_query = "
    SELECT i.id, m.full_name, i.title, i.description, i.reported_at, i.status, i.admin_response
    FROM incident_reports i
    JOIN members m ON i.user_id = m.user_id
    ORDER BY i.reported_at DESC
";
$incident_result = $conn->query($incident_query);
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
        h2, h3 { color: #003366; cursor: pointer; }
        .alert { text-align: center; font-weight: bold; color: #d97706; margin-bottom: 20px; }
        .member-box {
            background: white; margin-bottom: 20px;
            padding: 15px; border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        table {
            width: 100%; border-collapse: collapse; margin-top: 10px;
        }
        th, td {
            padding: 10px 12px; border-bottom: 1px solid #ddd; text-align: center;
        }
        th { background: #f0f4f8; font-weight: bold; }
        button {
            padding: 6px 10px; background-color: #3b82f6;
            color: white; border: none; border-radius: 4px;
            cursor: pointer; font-weight: 500; font-size: 0.9em;
        }
        button:hover { background-color: #2563eb; }
        button:disabled { background-color: #ccc; color: #666; cursor: not-allowed; }
        .badge {
            padding: 5px 10px; border-radius: 20px;
            font-size: 0.85em; font-weight: 600;
        }
        .borrowed { background: #facc15; color: #000; }
        .returned { background: #22c55e; color: white; }
        ul.login-times {
            padding-left: 20px; margin: 0;
            list-style-type: disc;
            max-height: 150px; overflow-y: auto;
        }
        ul.login-times li { margin-bottom: 4px; font-size: 0.95em; color: #333; }
        textarea {
            width: 100%; height: 80px;
            margin-top: 10px; margin-bottom: 10px;
            padding: 8px; resize: vertical;
        }
        select {
            padding: 6px 8px; margin-bottom: 10px;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2><a href="admin_panel.php" style="color:white; text-align: center;">Admin Panel</a></h2>
    <a href="manage_members.php">Manage Members</a>
    <a href="manage_equipment.php">Manage Equipment</a>
    <a href="post_announcements.php">Post Announcements</a>
    <a href="view_reports.php">View Reports</a>
    <a href="settings.php">System Settings</a>
</div>

<!-- Main Content -->
<div class="container">

    <h2 onclick="toggleSection('borrowingTable')">📋 Member Borrowing Overview</h2>
    <?php if ($borrowed_count > 0): ?>
        <div class="alert">⚠️ There are <?= $borrowed_count ?> borrowed equipment(s) to confirm return!</div>
    <?php endif; ?>

    <div id="borrowingTable" style="display:none;">
        <?php foreach ($grouped_records as $member): ?>
            <div class="member-box">
                <h3><?= htmlspecialchars($member['full_name']) ?></h3>
                <small><?= htmlspecialchars($member['email']) ?></small>
                <table>
                    <thead>
                        <tr><th>Equipment</th><th>Status</th><th>Due Date</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($member['borrowings'] as $borrow): ?>
                        <tr>
                            <td><?= htmlspecialchars($borrow['equipment_name']) ?></td>
                            <td>
                                <span class="badge <?= strtolower(trim($borrow['status'])) === 'borrowed' ? 'borrowed' : 'returned' ?>">
                                    <?= ucfirst($borrow['status']) ?>
                                </span>
                            </td>
                            <td><?= date("F j, Y", strtotime($borrow['return_date'])) ?></td>
                            <td>
                                <?php if (strtolower(trim($borrow['status'])) === 'borrowed'): ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="confirm_transaction_id" value="<?= $borrow['transaction_id'] ?>">
                                        <button type="submit">Confirm</button>
                                    </form>
                                <?php else: ?>
                                    <button disabled>Confirm</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 onclick="toggleSection('loginLogs')">🧾 Member Login Logs</h2>
    <div id="loginLogs" style="display:none;">
        <?php if (empty($grouped_logs)): ?>
            <div class="message">No login logs available.</div>
        <?php else: ?>
            <?php foreach ($grouped_logs as $full_name => $times): ?>
                <div class="member-box">
                    <h3><?= htmlspecialchars($full_name) ?></h3>
                    <ul class="login-times">
                        <?php foreach ($times as $time): ?>
                            <li><?= date("F j, Y, g:i a", strtotime($time)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <h2 onclick="toggleSection('incidentReports')">🚨 Incident Reports</h2>
    <div id="incidentReports" style="display:none;">
        <?php while ($incident = $incident_result->fetch_assoc()): ?>
            <div class="member-box">
                <h3><?= htmlspecialchars($incident['full_name']) ?></h3>
                <small>📅 Reported on <?= date("F j, Y, g:i a", strtotime($incident['reported_at'])) ?></small>
                <p><strong>Issue:</strong> <?= htmlspecialchars($incident['title']) ?></p>
                <p><?= nl2br(htmlspecialchars($incident['description'])) ?></p>
                <p><strong>Status:</strong> <span class="badge"><?= ucfirst($incident['status']) ?></span></p>
                <?php if (!empty($incident['admin_response'])): ?>
                    <p><strong>Admin Response:</strong> <?= nl2br(htmlspecialchars($incident['admin_response'])) ?></p>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="incident_id" value="<?= $incident['id'] ?>">
                    <textarea name="admin_response" placeholder="Enter response..." required></textarea>
                    <select name="status">
                        <option value="reviewed">Reviewed</option>
                        <option value="resolved">Resolved</option>
                    </select>
                    <button type="submit" name="update_incident">Update Incident</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
function toggleSection(id) {
    const el = document.getElementById(id);
    el.style.display = (el.style.display === 'none') ? 'block' : 'none';
}
</script>

</body>
</html>
