<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'member') {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['user_id'];

// Fetch available equipment
$equipment_query = "SELECT equipment_id, name, status, stock FROM equipment WHERE stock > 0";
$equipment_result = $conn->query($equipment_query);

// Count borrowed items
$borrow_count_query = "SELECT COUNT(*) AS borrowed_items FROM borrow_transactions WHERE member_id = ? AND status = 'Borrowed'";
$stmt = $conn->prepare($borrow_count_query);
$stmt->bind_param("i", $member_id);
$stmt->execute();
$borrow_count_result = $stmt->get_result();
$borrowed_items = $borrow_count_result->fetch_assoc()['borrowed_items'];

// Handle borrowing
if (isset($_POST['borrow_id']) && isset($_POST['return_date'])) {
    $borrow_id = $_POST['borrow_id'];
    $return_date = $_POST['return_date'];

    $max_return_date = date('Y-m-d', strtotime('+3 days'));
    if ($return_date < date('Y-m-d') || $return_date > $max_return_date) {
        $_SESSION['confirmation_message'] = "❌ Invalid return date. Choose within the next 3 days.";
    } else {
        $stock_query = "SELECT stock FROM equipment WHERE equipment_id = ?";
        $stmt_stock = $conn->prepare($stock_query);
        $stmt_stock->bind_param("i", $borrow_id);
        $stmt_stock->execute();
        $stock_result = $stmt_stock->get_result();
        $stock_data = $stock_result->fetch_assoc();

        if ($stock_data['stock'] > 0) {
            $conn->query("UPDATE equipment SET stock = stock - 1 WHERE equipment_id = $borrow_id");

            $log_query = "INSERT INTO borrow_transactions (member_id, equipment_id, status, borrow_date, return_date) 
                          VALUES (?, ?, 'Borrowed', NOW(), ?)";
            $log_stmt = $conn->prepare($log_query);
            $log_stmt->bind_param("iis", $member_id, $borrow_id, $return_date);
            $log_stmt->execute();

            $_SESSION['confirmation_message'] = "✅ Borrow successful! Return by $return_date.";
            header("Location: view_equipment.php");
            exit();
        } else {
            $_SESSION['confirmation_message'] = "❌ Item is out of stock.";
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            text-align: left;
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            background-color: #f1f5f9;
            color: #111827;
        }

        form {
            display: flex;
            align-items: center;
            gap: 10px;
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

        @media (max-width: 600px) {
            form {
                flex-direction: column;
                align-items: flex-start;
            }

            table, th, td {
                font-size: 0.9rem;
            }

            header {
                flex-direction: column;
                align-items: flex-start;
            }

            nav {
                margin-top: 10px;
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
                <th>Stock</th>
                <th>Borrow</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $equipment_result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['equipment_id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><?php echo htmlspecialchars($row['stock']); ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="borrow_id" value="<?php echo $row['equipment_id']; ?>">
                        <input type="date" name="return_date" required 
                               min="<?= date('Y-m-d'); ?>" 
                               max="<?= date('Y-m-d', strtotime('+3 days')); ?>">
                        <button type="submit" <?= ($borrowed_items >= 5 || $row['stock'] <= 0) ? 'disabled' : ''; ?>>
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
