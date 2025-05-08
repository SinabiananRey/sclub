<?php
session_start();
include 'db_connect.php';
require_once('tcpdf/tcpdf.php'); // Include TCPDF library

// Ensure only admin users can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch report data
$member_count_query = "SELECT COUNT(*) AS total_members FROM members";
$member_count_result = $conn->query($member_count_query);
$member_data = $member_count_result->fetch_assoc();

$equipment_count_query = "SELECT COUNT(*) AS total_equipment FROM equipment";
$equipment_count_result = $conn->query($equipment_count_query);
$equipment_data = $equipment_count_result->fetch_assoc();

$borrowed_items_query = "SELECT COUNT(*) AS borrowed_count FROM borrow_transactions WHERE status = 'borrowed'";
$borrowed_items_result = $conn->query($borrowed_items_query);
$borrowed_data = $borrowed_items_result->fetch_assoc();

// Initialize TCPDF
$pdf = new TCPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle("Club Reports");
$pdf->SetHeaderData('', '', 'Sports Club Reports', '');
$pdf->setHeaderFont(Array('helvetica', '', 12));
$pdf->setFooterFont(Array('helvetica', '', 10));
$pdf->SetDefaultMonospacedFont('courier');
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(TRUE, 10);
$pdf->AddPage();

// Create PDF content
$html = "
<h2>Sports Club Report</h2>
<h3>Summary Overview</h3>
<ul>
    <li><strong>Total Members:</strong> {$member_data['total_members']}</li>
    <li><strong>Total Equipment:</strong> {$equipment_data['total_equipment']}</li>
    <li><strong>Currently Borrowed Equipment:</strong> {$borrowed_data['borrowed_count']}</li>
</ul>
";

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Club_Report.pdf', 'D'); // Downloads file
?>