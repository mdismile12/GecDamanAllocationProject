<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../dbconfig.php';

// Fetch all allocations
$stmt = $conn->prepare("SELECT student_name, branch, category, seat_number, state, allocation_date FROM allocations ORDER BY allocation_date DESC");
$stmt->execute();
$result = $stmt->get_result();

$allocations = [];
while ($row = $result->fetch_assoc()) {
    $allocations[] = $row;
}

$stmt->close();
$conn->close();

// Generate CSV content
$csv = "Student Name,Branch,Category,Seat Number,State,Allocation Date\n";

foreach ($allocations as $alloc) {
    $csv .= '"' . str_replace('"', '""', $alloc['student_name']) . '",';
    $csv .= '"' . str_replace('"', '""', $alloc['branch']) . '",';
    $csv .= '"' . str_replace('"', '""', $alloc['category']) . '",';
    $csv .= '"' . str_replace('"', '""', $alloc['seat_number']) . '",';
    $csv .= '"' . str_replace('"', '""', $alloc['state']) . '",';
    $csv .= '"' . str_replace('"', '""', $alloc['allocation_date']) . '"' . "\n";
}

// Output as Excel-compatible CSV
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="allocations.csv"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
echo $csv;
exit;
?>