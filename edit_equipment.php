<?php
session_start();
include 'db_connect.php';

// Ensure only admin users can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get equipment ID
$equipment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($equipment_id === 0) {
    $_SESSION['error_message'] = "❌ Invalid equipment ID.";
    header("Location: manage_equipment.php");
    exit();
}

// Fetch existing equipment
$stmt = $conn->prepare("SELECT * FROM equipment WHERE equipment_id = ?");
$stmt->bind_param("i", $equipment_id);
$stmt->execute();
$result = $stmt->get_result();
$equipment = $result->fetch_assoc();

if (!$equipment) {
    $_SESSION['error_message'] = "❌ Equipment not found.";
    header("Location: manage_equipment.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name   = trim($_POST['name']);
    $status = $_POST['status'];
    $stock  = intval($_POST['stock']);
    $image_path = $equipment['image_path'];

    if (empty($name)) {
        $_SESSION['error_message'] = "❌ Equipment name is required.";
    } elseif (!in_array($status, ['Available', 'Borrowed', 'Maintenance'])) {
        $_SESSION['error_message'] = "❌ Invalid status.";
    } elseif ($stock < 0) {
        $_SESSION['error_message'] = "❌ Stock cannot be negative.";
    } else {
        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir);
            $filename = time() . "_" . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $filename;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (in_array($imageFileType, $valid_extensions)) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    // Optionally delete old image
                    if (!empty($equipment['image_path']) && file_exists($equipment['image_path'])) {
                        unlink($equipment['image_path']);
                    }
                    $image_path = $target_file;
                } else {
                    $_SESSION['error_message'] = "❌ Failed to upload image.";
                }
            } else {
                $_SESSION['error_message'] = "❌ Invalid file type. Only JPG, PNG, and GIF allowed.";
            }
        }

        // Update DB
        if (!isset($_SESSION['error_message'])) {
            $update = $conn->prepare("UPDATE equipment SET name = ?, status = ?, stock = ?, image_path = ? WHERE equipment_id = ?");
            $update->bind_param("ssisi", $name, $status, $stock, $image_path, $equipment_id);

            if ($update->execute()) {
                $_SESSION['confirmation_message'] = "✅ Equipment updated successfully!";
                header("Location: manage_equipment.php");
                exit();
            } else {
                $_SESSION['error_message'] = "❌ Failed to update equipment.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Equipment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #003366;
        }
        .message {
            text-align: center;
            font-weight: 600;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        label {
            font-size: 14px;
            color: #555;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            background-color: #003366;
            color: white;
            cursor: pointer;
        }
        button:hover {
            background-color: #0055aa;
        }
        a {
            display: inline-block;
            text-align: center;
            text-decoration: none;
            background-color: #ccc;
            padding: 8px 16px;
            border-radius: 5px;
            margin-top: 20px;
            color: black;
        }
        a:hover {
            background-color: #999;
        }
        img.preview {
            max-width: 200px;
            margin-bottom: 10px;
            display: block;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Equipment</h2>

    <?php if (isset($_SESSION['confirmation_message'])): ?>
        <div class="message success"><?php echo $_SESSION['confirmation_message']; unset($_SESSION['confirmation_message']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="message error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($equipment['name']); ?>" required>

        <label for="status">Status:</label>
        <select name="status">
            <option value="Available" <?php echo $equipment['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
            <option value="Borrowed" <?php echo $equipment['status'] === 'Borrowed' ? 'selected' : ''; ?>>Borrowed</option>
            <option value="Maintenance" <?php echo $equipment['status'] === 'Maintenance' ? 'selected' : ''; ?>>Under Maintenance</option>
        </select>

        <label for="stock">Stock:</label>
        <input type="number" name="stock" min="0" value="<?php echo htmlspecialchars($equipment['stock']); ?>" required>

        <label for="image">Upload New Image (optional):</label>
        <input type="file" name="image" accept="image/*">

        <?php if (!empty($equipment['image_path'])): ?>
            <p>Current Image:</p>
            <img src="<?php echo htmlspecialchars($equipment['image_path']); ?>" class="preview" alt="Equipment Image">
        <?php endif; ?>

        <button type="submit">Update Equipment</button>
    </form>

    <a href="manage_equipment.php">Back to Manage Equipment</a>
</div>

</body>
</html>
