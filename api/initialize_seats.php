<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../dbconfig.php';

// Create seats table if missing
$sqlSeats = "CREATE TABLE IF NOT EXISTS seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch VARCHAR(100) NOT NULL,
    category VARCHAR(20) NOT NULL,
    state VARCHAR(10) NOT NULL,
    seats_available INT NOT NULL,
    UNIQUE KEY unique_branch_category_state (branch, category, state)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

// Create allocations table if missing (with allocation_date)
$sqlAllocations = "CREATE TABLE IF NOT EXISTS allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(255) NOT NULL,
    branch VARCHAR(100) NOT NULL,
    category VARCHAR(20) NOT NULL,
    seat_number VARCHAR(50),
    state VARCHAR(10) NOT NULL,
    allocated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    allocation_date DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (!$conn->query($sqlSeats) || !$conn->query($sqlAllocations)) {
    http_response_code(500);
    echo json_encode(["error" => "Failed to create tables"]);
    $conn->close();
    exit;
}

// Ensure allocation_date column exists for legacy installations
$checkCol = $conn->query("SHOW COLUMNS FROM allocations LIKE 'allocation_date'");
if ($checkCol && $checkCol->num_rows === 0) {
    $conn->query("ALTER TABLE allocations ADD COLUMN allocation_date DATETIME DEFAULT CURRENT_TIMESTAMP");
}

// Seed seats if empty
$result = $conn->query("SELECT COUNT(*) as cnt FROM seats");
$row = $result->fetch_assoc();
if ($row['cnt'] == 0) {
    $branches = ['Computer Engineering', 'Mechanical Engineering', 'Electrical Engineering', 'Civil Engineering', 'Bio Medical Engineering'];
    $categories = ['General', 'SC', 'ST', 'OBC', 'EWS'];
    $states = ['DD', 'DNH'];

    $seatCounts = [
        'DD' => [
            'Computer Engineering' => ['General' => 2, 'SC' => 1, 'ST' => 1, 'OBC' => 2, 'EWS' => 2],
            'Mechanical Engineering' => ['General' => 2, 'SC' => 1, 'ST' => 0, 'OBC' => 1, 'EWS' => 3],
            'Electrical Engineering' => ['General' => 2, 'SC' => 1, 'ST' => 0, 'OBC' => 1, 'EWS' => 3],
            'Civil Engineering' => ['General' => 10, 'SC' => 2, 'ST' => 1, 'OBC' => 4, 'EWS' => 3],
            'Bio Medical Engineering' => ['General' => 8, 'SC' => 3, 'ST' => 2, 'OBC' => 5, 'EWS' => 3],
        ],
        'DNH' => [
            'Computer Engineering' => ['General' => 3, 'SC' => 0, 'ST' => 2, 'OBC' => 0, 'EWS' => 1],
            'Mechanical Engineering' => ['General' => 3, 'SC' => 0, 'ST' => 1, 'OBC' => 0, 'EWS' => 3],
            'Electrical Engineering' => ['General' => 1, 'SC' => 0, 'ST' => 2, 'OBC' => 1, 'EWS' => 3],
            'Civil Engineering' => ['General' => 9, 'SC' => 0, 'ST' => 8, 'OBC' => 1, 'EWS' => 3],
            'Bio Medical Engineering' => ['General' => 8, 'SC' => 1, 'ST' => 8, 'OBC' => 1, 'EWS' => 3],
        ],
    ];

    $stmt = $conn->prepare("INSERT INTO seats (branch, category, state, seats_available) VALUES (?, ?, ?, ?)");
    foreach ($states as $state) {
        foreach ($branches as $branch) {
            foreach ($categories as $category) {
                $seatsAvailable = $seatCounts[$state][$branch][$category];
                $stmt->bind_param("sssi", $branch, $category, $state, $seatsAvailable);
                $stmt->execute();
            }
        }
    }
    $stmt->close();
}

$conn->close();
echo json_encode(["success" => true]);
exit;
?>
