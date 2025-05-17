<?php
session_start();
include 'db_connect.php';

// ✅ Ensure only members can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: login.php");
    exit();
}

// ✅ Fetch borrowing limit from settings
$settings_query = "SELECT borrowing_limit FROM settings WHERE id = 1";
$settings_result = $conn->query($settings_query);
$settings = $settings_result->fetch_assoc();
$borrow_limit = $settings['borrowing_limit'];

// ✅ Fetch available equipment (include image_path)
$equipment_query = "SELECT equipment_id, name, status, stock, image_path FROM equipment WHERE stock > 0";
$equipment_result = $conn->query($equipment_query);

// ✅ Count borrowed items
$borrow_count_query = "SELECT COUNT(*) AS borrowed_items FROM borrow_transactions WHERE member_id = ? AND status = 'Borrowed'";
$stmt = $conn->prepare($borrow_count_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$borrow_count_result = $stmt->get_result();
$borrowed_items = $borrow_count_result->fetch_assoc()['borrowed_items'];

// ✅ Handle borrowing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['borrow_id'], $_POST['return_date'])) {
    $borrow_id = $_POST['borrow_id'];
    $return_date = $_POST['return_date'];

    // ✅ Validate return date
    $max_return_date = date('Y-m-d', strtotime('+3 days'));
    if ($return_date < date('Y-m-d') || $return_date > $max_return_date) {
        $_SESSION['confirmation_message'] = "❌ Invalid return date. Choose within the next 3 days.";
    } else {
        // ✅ Check stock availability
        $stock_query = "SELECT stock FROM equipment WHERE equipment_id = ?";
        $stmt_stock = $conn->prepare($stock_query);
        $stmt_stock->bind_param("i", $borrow_id);
        $stmt_stock->execute();
        $stock_result = $stmt_stock->get_result();
        $stock_data = $stock_result->fetch_assoc();

        if ($stock_data['stock'] > 0 && $borrowed_items < $borrow_limit) {
            // ✅ Deduct stock and log transaction
            $conn->query("UPDATE equipment SET stock = stock - 1 WHERE equipment_id = $borrow_id");
            $log_query = "INSERT INTO borrow_transactions (member_id, equipment_id, status, borrow_date, return_date) 
                          VALUES (?, ?, 'Borrowed', NOW(), ?)";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("iis", $_SESSION['user_id'], $borrow_id, $return_date);
            $log_stmt->execute();

            $_SESSION['confirmation_message'] = "✅ Borrow successful! Return by $return_date.";
            header("Location: view_equipment.php");
            exit();
        } else {
            $_SESSION['confirmation_message'] = "❌ Item is out of stock or borrowing limit reached.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Borrow Equipment | Sports Club</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            color: #333;
        }

        header {
            background-color: #1e3a8a;
            color: #fff;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            margin-left: 1rem;
            font-weight: 500;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        h2 {
            text-align: center;
            color: #1e3a8a;
            margin-bottom: 10px;
        }

        p {
            text-align: center;
            font-weight: 500;
            margin-bottom: 30px;
        }

        .message {
            background-color: #e0f2fe;
            color: #0369a1;
            padding: 14px;
            border-radius: 6px;
            text-align: center;
            font-weight: 500;
            margin-bottom: 20px;
        }

        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .equipment-card {
            background: #f1f5f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .equipment-card img {
            width: 100%;
            max-height: 180px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 12px;
        }

        .equipment-card h3 {
            color: #1e3a8a;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .equipment-card p {
            margin: 8px 0;
        }

        .equipment-card form {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        input[type="date"] {
            padding: 6px 10px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-family: inherit;
        }

        button {
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s ease;
        }

        button:hover:not(:disabled) {
            background-color: #2563eb;
        }

        button:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
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
    <h2>Available Equipment</h2>
    <p>You have currently borrowed <strong><?php echo $borrowed_items; ?></strong> out of <?php echo $borrow_limit; ?> allowed items.</p>

    <?php if (isset($_SESSION['confirmation_message'])): ?>
        <div class="message"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
    <?php endif; ?>

    <div class="equipment-grid">
        <?php while ($row = $equipment_result->fetch_assoc()) { ?>
            <div class="equipment-card">
                <?php if (!empty($row['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="Equipment Image">
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                <p><strong>Status:</strong> <?php echo htmlspecialchars($row['status']); ?></p>
                <p><strong>Stock:</strong> <?php echo htmlspecialchars($row['stock']); ?></p>
                <form method="POST">
                    <input type="hidden" name="borrow_id" value="<?php echo $row['equipment_id']; ?>">
                    <input type="date" name="return_date" required 
                        min="<?= date('Y-m-d'); ?>" 
                        max="<?= date('Y-m-d', strtotime('+3 days')); ?>">
                    <button type="submit" 
                        <?= ($borrowed_items >= $borrow_limit || $row['stock'] <= 0) ? 'disabled' : ''; ?>>
                        Borrow
                    </button>
                </form>
            </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
