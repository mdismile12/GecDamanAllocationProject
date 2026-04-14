<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../dbconfig.php';

// Ensure we always have an allocation_date column (for legacy installations)
$checkCol = $conn->query("SHOW COLUMNS FROM allocations LIKE 'allocation_date'");
if ($checkCol && $checkCol->num_rows === 0) {
    $conn->query("ALTER TABLE allocations ADD COLUMN allocation_date DATETIME DEFAULT CURRENT_TIMESTAMP");
}

$result = $conn->query("SELECT id, student_name, branch, category, seat_number, state, COALESCE(allocation_date, allocated_at) AS allocation_date FROM allocations ORDER BY allocation_date DESC");
$allocations = [];
while ($row = $result->fetch_assoc()) {
    $allocations[] = $row;
}
$conn->close();
echo json_encode($allocations);
exit;
?>