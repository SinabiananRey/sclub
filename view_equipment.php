<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// ✅ Fetch available equipment with stock
$equipment_query = "SELECT equipment_id, name, status, stock FROM equipment WHERE stock > 0";
$equipment_result = $conn->query($equipment_query);

// ✅ Count borrowed items
$borrow_count_query = "SELECT COUNT(*) AS borrowed_items FROM borrow_transactions WHERE member_id = ? AND status = 'Borrowed'";
$stmt = $conn->prepare($borrow_count_query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$borrow_count_result = $stmt->get_result();
$borrowed_items = $borrow_count_result->fetch_assoc()['borrowed_items'];

if (isset($_POST['borrow_id'])) {
    if ($borrowed_items < 5) { // ✅ Borrow limit check
        $borrow_id = $_POST['borrow_id'];

        // ✅ Step 1: Check available stock
        $stock_query = "SELECT stock FROM equipment WHERE equipment_id = ?";
        $stmt_stock = $conn->prepare($stock_query);
        $stmt_stock->bind_param("i", $borrow_id);
        $stmt_stock->execute();
        $stock_result = $stmt_stock->get_result();
        $stock_data = $stock_result->fetch_assoc();

        if ($stock_data['stock'] > 0) { // ✅ Ensure stock is available
            // ✅ Step 2: Reduce stock in `equipment`
            $update_stock_query = "UPDATE equipment SET stock = stock - 1 WHERE equipment_id = ?";
            $stmt_stock_update = $conn->prepare($update_stock_query);
            $stmt_stock_update->bind_param("i", $borrow_id);
            $stmt_stock_update->execute();

            // ✅ Step 3: Insert borrow transaction
            $log_query = "INSERT INTO borrow_transactions (member_id, equipment_id, status, borrow_date) VALUES (?, ?, 'Borrowed', NOW())";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("ii", $member_id, $borrow_id);
            $log_stmt->execute();

            $_SESSION['confirmation_message'] = "Borrow successful! Stock updated.";
            header("Location: view_equipment.php");
            exit();
        } else {
            $_SESSION['confirmation_message'] = "This item is out of stock!";
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
    <style>
        body { font-family: Arial, sans-serif; background: #f9fafb; color: #333; }
        .container { max-width: 1000px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; }
        h2 { text-align: center; color: #1e3a8a; }
        .message { background-color: #e0f2fe; color: #0369a1; padding: 12px; border-radius: 6px; margin: 20px 0; text-align: center; font-weight: 500; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f3f4f6; }
        button { background-color: #3b82f6; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 500; }
        button:disabled { background-color: #94a3b8; cursor: not-allowed; }
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
                <th>Stock</th> <!-- ✅ Stock column added -->
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $equipment_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['equipment_id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><?php echo htmlspecialchars($row['stock']); ?></td> <!-- ✅ Stock displayed -->
                <td>
                    <form method="POST">
                        <input type="hidden" name="borrow_id" value="<?php echo $row['equipment_id']; ?>">
                        <button type="submit" <?php echo ($borrowed_items >= 5 || $row['stock'] <= 0) ? 'disabled' : ''; ?>>
                            Borrow
                        </button>
                    </form>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>