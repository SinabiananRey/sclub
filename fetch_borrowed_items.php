<?php
include 'db_connect.php';

$borrow_query = "SELECT COUNT(*) AS borrowed_count FROM borrow_transactions WHERE status = 'borrowed'";
$borrow_result = $conn->query($borrow_query);
$borrow_data = $borrow_result->fetch_assoc();

echo $borrow_data['borrowed_count'];
?>