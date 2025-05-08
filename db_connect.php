<?php
$host = "localhost";
$user = "root"; // Change if using a different username
$password = "";
$database = "sports_club";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>