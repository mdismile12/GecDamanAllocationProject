<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../dbconfig.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid allocation ID"]);
    exit;
}

// Fetch allocation details
$stmt = $conn->prepare("SELECT student_name, branch, category, seat_number, state, allocation_date FROM allocations WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Allocation not found"]);
    $stmt->close();
    $conn->close();
    exit;
}

$allocation = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Generate PDF using FPDF
require_once __DIR__ . '/../lib/fpdf.php';

class AdmissionSlipPDF extends FPDF {
    function Header() {
        // College logo (assuming it's in the same directory)
        if (file_exists(__DIR__ . '/../college_logo.png')) {
            $this->Image(__DIR__ . '/../college_logo.png', 10, 10, 30);
        }

        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Government Engineering College, Daman', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Admission Seat Allocation Slip', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Generated on ' . date('Y-m-d H:i:s'), 0, 0, 'C');
    }
}

$pdf = new AdmissionSlipPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

// Allocation details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Student Name:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $allocation['student_name'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Branch:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $allocation['branch'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Category:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $allocation['category'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Seat Number:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $allocation['seat_number'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'State:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $allocation['state'], 0, 1);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Date & Time:', 0, 0);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, $allocation['allocation_date'], 0, 1);

$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Allocation ID: ' . $id, 0, 1, 'C');

// Output PDF
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="admission_slip.pdf"');
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');
$pdf->Output('D');
exit;
?> -->


