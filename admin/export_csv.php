<?php
// admin/export_csv.php
require_once '../includes/config.php';
require_once '../includes/auth_check.php';
checkLogin();
checkAdmin();

$selected_date = $_GET['date'] ?? date('Y-m-d');

// Fetch Data for the selected date
$stmt = $pdo->prepare("
    SELECT t.tower_name, t.category, t.location, du.status, du.score, du.remarks, du.update_date, u.username
    FROM towers t
    LEFT JOIN daily_updates du ON t.id = du.tower_id AND du.update_date = ?
    LEFT JOIN users u ON du.user_id = u.id
    ORDER BY t.tower_name ASC
");
$stmt->execute([$selected_date]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

$filename = "tower_details_" . str_replace('-', '_', $selected_date) . ".csv";

// Clear any previous output buffers to avoid corrupted CSV
if (ob_get_level()) ob_end_clean();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);

$output = fopen('php://output', 'w');

// Output column headings
fputcsv($output, ['Tower Name', 'Category', 'Location', 'Status (Signal)', 'Score (0-5)', 'Remarks', 'Update Date', 'Reported By']);

foreach ($reports as $row) {
    fputcsv($output, [
        $row['tower_name'],
        $row['category'],
        $row['location'],
        $row['status'] ?: 'Pending',
        $row['score'] !== null ? $row['score'] : 'N/A',
        $row['remarks'] ?: 'No remarks',
        $row['update_date'] ?: 'N/A',
        $row['username'] ?: 'Unassigned/No Report'
    ]);
}

fclose($output);
exit;
