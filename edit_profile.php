<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// ✅ Update overdue items only if the return date has passed for 1 day and it's not returned yet
$update_status_query = "UPDATE borrow_transactions  
                        SET status = 'overdue'  
                        WHERE member_id = ? 
                          AND return_date < NOW() - INTERVAL 1 DAY
                          AND (status IS NULL OR status = '' OR status = 'borrowed') 
                          AND (returned_date IS NULL OR returned_date = '')";
$stmt = $conn->prepare($update_status_query);
$stmt->bind_param("i", $member_id);
$stmt->execute();

// ✅ Fetch user info
$query = "SELECT username, email, profile_image FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ✅ Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'], $_POST['email'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $target_dir = "uploads/";
    $image_path = $user['profile_image'];

    if (!empty($_FILES['profile_image']['name'])) {
        $image_name = basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $image_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (in_array($file_type, ['jpg', 'jpeg', 'png'])) {
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            }
        }
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET username = ?, email = ?, password = ?, profile_image = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssi", $username, $email, $hashed_password, $image_path, $member_id);
        } else {
            $update_query = "UPDATE users SET username = ?, email = ?, profile_image = ? WHERE user_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sssi", $username, $email, $image_path, $member_id);
        }

        if ($stmt->execute()) {
            $_SESSION['confirmation_message'] = "✅ Profile updated successfully!";
            header("Location: edit_profile.php");
            exit();
        } else {
            $error = "❌ Error updating profile.";
        }
    }
}

// ✅ Fetch borrowing history
$history_query = "SELECT b.transaction_id, b.borrow_date, b.return_date, b.returned_date, e.name, b.status
                  FROM borrow_transactions b
                  JOIN equipment e ON b.equipment_id = e.equipment_id
                  WHERE b.member_id = ?
                  ORDER BY b.borrow_date DESC";
$stmt = $conn->prepare($history_query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$history_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile & History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* Same styling as you had */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #333;
        }
        header {
            background:  #1e3a8a;
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav a {
            color: #cbd5e1;
            margin-left: 1rem;
            text-decoration: none;
            font-weight: 500;
        }
        nav a:hover { color: #fff; }
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        h2 { text-align: center; color: #0f172a; margin-bottom: 25px; }
        label {
            display: block; margin-top: 15px; font-weight: 600; color: #334155;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
            width: 100%; padding: 12px; margin-top: 8px;
            border: 1px solid #cbd5e1; border-radius: 6px; background: #f8fafc;
        }
        button {
            margin-top: 25px; padding: 12px 20px;
            background-color: #3b82f6; color: #fff;
            border: none; border-radius: 6px; cursor: pointer;
            font-weight: 600;
        }
        button:hover { background-color: #2563eb; }
        .toggle-btn { background-color: #0ea5e9; }
        .toggle-btn:hover { background-color: #0284c7; }
        .logout-btn { background-color: #ef4444; }
        .logout-btn:hover { background-color: #dc2626; }
        .message {
            background-color: #dbeafe; color: #1e3a8a;
            padding: 15px; border-radius: 8px;
            text-align: center; margin-top: 20px; font-weight: 500;
        }
        .error {
            color: #dc2626;
            text-align: center;
            font-weight: 600;
            margin-top: 20px;
        }
        .profile {
            display: block;
            margin: 20px auto;
            max-width: 130px;
            border-radius: 50%;
            border: 4px solid #e2e8f0;
        }
        table {
            width: 100%; border-collapse: collapse; margin-top: 25px;
        }
        th, td {
            padding: 12px; border-bottom: 1px solid #e2e8f0; text-align: center;
        }
        th { background: #f8fafc; }
        .badge {
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }
        .borrowed { background: #fde68a; color: #78350f; }
        .returned { background: #86efac; color: #064e3b; }
        .overdue { background: #fca5a5; color: #7f1d1d; }

        @media (max-width: 600px) {
            .container { padding: 20px; }
            table { font-size: 0.9rem; }
        }
    </style>
</head>
<body>

<header>
    <div><strong>🏋️ Sports Club</strong></div>
    <nav>
        <a href="member_dashboard.php">Home</a>
        <a href="view_equipment.php">Equipment</a>
        <a href="edit_profile.php">Settings</a>
    </nav>
</header>

<div class="container">
    <h2>Edit Profile</h2>
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    <?php if (isset($_SESSION['confirmation_message'])): ?>
        <div class="message"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
    <?php endif; ?>

    <?php if (!empty($user['profile_image'])): ?>
        <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture" class="profile">
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Username</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label>New Password (optional)</label>
        <input type="password" name="password">

        <label>Confirm Password</label>
        <input type="password" name="confirm_password">

        <label>Profile Picture</label>
        <input type="file" name="profile_image" accept="image/*">

        <button type="submit">💾 Save Changes</button>
    </form>

    <form action="logout.php" method="POST" onsubmit="return confirm('Are you sure you want to logout?');" style="text-align: left;">
        <button class="logout-btn">🔒 Logout</button>
    </form>

    <button class="toggle-btn" onclick="toggleHistory()">📂 Borrowing History</button>

    <div id="historyContainer" style="display: none;">
        <h2>Borrowing History</h2>
        <table>
            <thead>
                <tr>
                    <th>Equipment</th>
                    <th>Status</th>
                    <th>Borrow Date</th>
                    <th>Return Date</th>
                    <th>Returned Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $history_result->fetch_assoc()): 
                    $status = strtolower($row['status']);
                    $badgeClass = match($status) {
                        'borrowed' => 'badge borrowed',
                        'overdue' => 'badge overdue',
                        'returned' => 'badge returned',
                        default => 'badge',
                    };
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><span class="<?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span></td>
                    <td><?php echo htmlspecialchars($row['borrow_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['return_date']); ?></td>
                    <td>
                        <?php echo (!empty($row['returned_date']) && $row['returned_date'] !== '0000-00-00 00:00:00')
                            ? htmlspecialchars($row['returned_date'])
                            : 'Not Returned'; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function toggleHistory() {
        const historyContainer = document.getElementById('historyContainer');
        historyContainer.style.display = historyContainer.style.display === 'none' ? 'block' : 'none';
    }
</script>

</body>
</html>
