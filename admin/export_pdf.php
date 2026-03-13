<?php
// admin/export_pdf.php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
require_once '../assets/lib/fpdf/fpdf.php';
checkLogin();
checkAdmin();

$selected_date = $_GET['date'] ?? date('Y-m-d');

// Fetch Data for the selected date
$stmt = $pdo->prepare("
    SELECT t.tower_name, t.category, du.status, du.score, du.remarks, u.username
    FROM towers t
    LEFT JOIN daily_updates du ON t.id = du.tower_id AND du.update_date = ?
    LEFT JOIN users u ON du.user_id = u.id
    ORDER BY t.tower_name ASC
");
$stmt->execute([$selected_date]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

class PDF extends FPDF {
    // Page header
    function Header() {
        $this->SetFont('Arial', 'B', 15);
        $this->Cell(0, 10, 'Tower Signal Report', 0, 1, 'C');
        $this->SetFont('Arial', '', 11);
        global $selected_date;
        $this->Cell(0, 10, 'Date: ' . $selected_date, 0, 1, 'C');
        $this->Ln(5);
        
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(230, 230, 230);
        $this->Cell(45, 10, 'Tower Name', 1, 0, 'C', true);
        $this->Cell(25, 10, 'Signal', 1, 0, 'C', true);
        $this->Cell(15, 10, 'Score', 1, 0, 'C', true);
        $this->Cell(30, 10, 'Reported By', 1, 0, 'C', true);
        $this->Cell(75, 10, 'Remarks', 1, 1, 'C', true);
    }

    // Page footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Clear any previous output buffers to avoid corrupted PDF
if (ob_get_level()) ob_end_clean();

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 9);

$total_towers = count($reports);
$green = $orange = $red = $pending = 0;

foreach ($reports as $row) {
    if ($row['status'] == 'Green') $green++;
    elseif ($row['status'] == 'Orange') $orange++;
    elseif ($row['status'] == 'Red') $red++;
    else $pending++;
    
    // Using string truncate for long remarks
    $remarks = $row['remarks'] ? substr($row['remarks'], 0, 45) . (strlen($row['remarks']) > 45 ? '...' : '') : 'No remarks';
    
    $pdf->Cell(45, 8, substr($row['tower_name'], 0, 25), 1);
    $pdf->Cell(25, 8, $row['status'] ?: 'Pending', 1, 0, 'C');
    $pdf->Cell(15, 8, $row['score'] !== null ? $row['score'] : '-', 1, 0, 'C');
    $pdf->Cell(30, 8, substr($row['username'] ?: 'N/A', 0, 15), 1);
    $pdf->Cell(75, 8, $remarks, 1, 1);
}

$pdf->Ln(10);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(0, 10, 'Summary Statistics', 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(50, 8, "Total Assets: $total_towers", 0, 1);
$pdf->Cell(50, 8, "Operational (Green): $green", 0, 1);
$pdf->Cell(50, 8, "Minor Issues (Orange): $orange", 0, 1);
$pdf->Cell(50, 8, "Critical Alerts (Red): $red", 0, 1);
$pdf->Cell(50, 8, "Pending Updates: $pending", 0, 1);

$filename = "tower_details_" . str_replace('-', '_', $selected_date) . ".pdf";
$pdf->Output('D', $filename);
exit;
