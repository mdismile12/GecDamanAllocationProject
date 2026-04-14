<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../dbconfig.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['branch'], $data['category'], $data['student_name'], $data['state'], $data['seat_number'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    $conn->close();
    exit;
}

$branch = trim($data['branch']);
$category = trim($data['category']);
$student_name = trim($data['student_name']);
$state = trim($data['state']);
$seat_number = trim($data['seat_number']);

if ($seat_number === '') {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Seat number is required."]);
    $conn->close();
    exit;
}

// Prevent duplicate seat number
$dupStmt = $conn->prepare("SELECT id FROM allocations WHERE seat_number = ? LIMIT 1");
$dupStmt->bind_param("s", $seat_number);
$dupStmt->execute();
$dupResult = $dupStmt->get_result();
if ($dupResult && $dupResult->num_rows > 0) {
    $dupStmt->close();
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "Seat number already allocated."]);
    $conn->close();
    exit;
}
$dupStmt->close();

$conn->begin_transaction();

$seatStmt = $conn->prepare("SELECT seats_available FROM seats WHERE branch = ? AND category = ? AND state = ? FOR UPDATE");
$seatStmt->bind_param("sss", $branch, $category, $state);
$seatStmt->execute();
$seatResult = $seatStmt->get_result();
if ($seatResult->num_rows === 0) {
    $seatStmt->close();
    $conn->rollback();
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Branch, category or state not found"]);
    $conn->close();
    exit;
}

$seatRow = $seatResult->fetch_assoc();
$seatStmt->close();

if ($seatRow['seats_available'] <= 0) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "No more seats available for selected branch/category/state."]);
    $conn->close();
    exit;
}

$newSeats = $seatRow['seats_available'] - 1;
$updateStmt = $conn->prepare("UPDATE seats SET seats_available = ? WHERE branch = ? AND category = ? AND state = ?");
$updateStmt->bind_param("isss", $newSeats, $branch, $category, $state);
$updateRes = $updateStmt->execute();
$updateStmt->close();

if (!$updateRes) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to update seats"]);
    $conn->close();
    exit;
}

$insertStmt = $conn->prepare("INSERT INTO allocations (student_name, branch, category, seat_number, state) VALUES (?, ?, ?, ?, ?)");
$insertStmt->bind_param("sssss", $student_name, $branch, $category, $seat_number, $state);
$insertRes = $insertStmt->execute();
$allocId = $insertStmt->insert_id;
$insertStmt->close();

if (!$insertRes) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to allocate seat"]);
    $conn->close();
    exit;
}

$conn->commit();
$conn->close();

echo json_encode(["success" => true, "id" => $allocId]);
exit;
?>
