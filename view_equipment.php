<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// ✅ Fetch available equipment
$equipment_query = "SELECT * FROM equipment WHERE status = 'Available'";
$equipment_result = $conn->query($equipment_query);

// ✅ Count borrowed items
$borrow_count_query = "SELECT COUNT(*) AS borrowed_items FROM borrow_transactions WHERE member_id = ? AND status = 'Borrowed'";
$stmt = $conn->prepare($borrow_count_query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$borrow_count_result = $stmt->get_result();
$borrowed_items = $borrow_count_result->fetch_assoc()['borrowed_items'];

if (isset($_POST['borrow_id'])) {
    if ($borrowed_items < 5) { // ✅ Limit changed to 5
        $borrow_id = $_POST['borrow_id'];
        $update_query = "UPDATE equipment SET status = 'Borrowed' WHERE equipment_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("i", $borrow_id);

        if ($stmt->execute()) {
            $log_query = "INSERT INTO borrow_transactions (member_id, equipment_id, status, borrow_date) VALUES (?, ?, 'Borrowed', NOW())";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("ii", $member_id, $borrow_id);
            $log_stmt->execute();

            $_SESSION['confirmation_message'] = "Borrow successful! Proceed to the sports club office for verification.";
            header("Location: view_equipment.php");
            exit();
        }
    } else {
        $_SESSION['confirmation_message'] = "Borrow limit reached! You can only have 5 items at a time.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrow Equipment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f9fafb; margin: 0; padding: 0; color: #333; }
        header { background: #1e3a8a; color: #fff; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #fff; margin-left: 1rem; text-decoration: none; font-weight: 500; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 1000px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; }
        h2 { text-align: center; color: #1e3a8a; }
        .message { background-color: #e0f2fe; color: #0369a1; padding: 12px; border-radius: 6px; margin: 20px 0; text-align: center; font-weight: 500; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f3f4f6; }
        button { background-color: #3b82f6; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 500; }
        button:disabled { background-color: #94a3b8; cursor: not-allowed; }
        a.back-link { display: inline-block; margin-top: 30px; text-decoration: none; color: #1e3a8a; font-weight: 500; }
        a.back-link:hover { text-decoration: underline; }
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

<div class="container">
    <h2>Available Equipment</h2>
    <p>You have currently borrowed <strong><?php echo $borrowed_items; ?></strong> out of 5 allowed items.</p>

    <?php if (isset($_SESSION['confirmation_message'])): ?>
        <div class="message"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $equipment_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['equipment_id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="borrow_id" value="<?php echo $row['equipment_id']; ?>">
                        <button type="submit" <?php echo ($borrowed_items >= 5) ? 'disabled' : ''; ?>>Borrow</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>