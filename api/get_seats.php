<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../dbconfig.php';

$result = $conn->query("SELECT branch, category, state, seats_available FROM seats ORDER BY state, branch, category");
$seats = [];
while ($row = $result->fetch_assoc()) {
    $seats[] = $row;
}
$conn->close();
echo json_encode($seats);
exit;
?>