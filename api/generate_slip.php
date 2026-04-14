<?php
require_once __DIR__ . '/../dbconfig.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die("Invalid allocation ID");
}

// Fetch allocation details
$stmt = $conn->prepare("SELECT student_name, branch, category, seat_number, state, allocation_date FROM allocations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Allocation not found");
}

$allocation = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Set headers for HTML (user can print to PDF)
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admission Slip</title>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 20px;
            }
            .no-print {
                display: none;
            }
            button {
                display: none;
            }
        }
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .slip-container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #000;
            padding: 20px;
        }
        button {
            margin: 20px auto;
            display: block;
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #45a049;
        }
    </style>
</head>
<body>
    <div class="slip-container">
        <div class="header">
            <img src="C:\xampp\htdocs\desem5\college_logo.png" alt="College Logo" style="width: 100px; float: left; margin-right: 20px;">
            <h2>Government Engineering College, Daman</h2>
            <h3>Admission Seat Allocation Slip</h3>
        </div>
        
        <table style="width: 100%; margin-top: 30px;">
            <tr><td style="padding: 10px;"><strong>Student Name:</strong></td><td><?php echo htmlspecialchars($allocation['student_name']); ?></td></tr>
            <tr><td style="padding: 10px;"><strong>Branch:</strong></td><td><?php echo htmlspecialchars($allocation['branch']); ?></td></tr>
            <tr><td style="padding: 10px;"><strong>Category:</strong></td><td><?php echo htmlspecialchars($allocation['category']); ?></td></tr>
            <tr><td style="padding: 10px;"><strong>Seat Number:</strong></td><td><?php echo htmlspecialchars($allocation['seat_number']); ?></td></tr>
            <tr><td style="padding: 10px;"><strong>State:</strong></td><td><?php echo htmlspecialchars($allocation['state']); ?></td></tr>
            <tr><td style="padding: 10px;"><strong>Allocation Date:</strong></td><td><?php echo htmlspecialchars($allocation['allocation_date']); ?></td></tr>
            <tr><td style="padding: 10px;"><strong>Allocation ID:</strong></td><td><?php echo $id; ?></td></tr>
        </table>
        
        <div style="margin-top: 50px; text-align: right;">
            <p>_____________________<br>Authorized Signature</p>
        </div>
        
        <div style="margin-top: 30px; text-align: center; font-size: 12px;">
            Generated on: <?php echo date('Y-m-d H:i:s'); ?>
        </div>
    </div>
    
    <button onclick="window.print();" class="no-print">Print / Save as PDF</button>
    <button onclick="window.close();" class="no-print">Close</button>
    
    <script>
        // Auto-open print dialog when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>