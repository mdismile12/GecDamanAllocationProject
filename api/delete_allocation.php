<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../dbconfig.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id'], $data['branch'], $data['category'], $data['state'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    $conn->close();
    exit;
}

$id = (int)$data['id'];
$branch = $data['branch'];
$category = $data['category'];
$state = $data['state'];

$conn->begin_transaction();

$deleteStmt = $conn->prepare("DELETE FROM allocations WHERE id = ?");
$deleteStmt->bind_param("i", $id);
$deleteRes = $deleteStmt->execute();
$deleteStmt->close();

if (!$deleteRes) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Failed to delete allocation"]);
    $conn->close();
    exit;
}

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
$newSeats = $seatRow['seats_available'] + 1;

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

$conn->commit();
$conn->close();

echo json_encode(["success" => true]);
exit;
?>
```