<?php
include '../../includes/config.php';
include '../../includes/auth.php';

if($_SESSION['role'] != 'organizer') {
    header('Location: ../../index.php');
    exit();
}

// Get applicants data for export - FIXED QUERY
$sql = "SELECT a.*, e.name as event_name, s.full_name, u.email, s.portfolio 
        FROM applications a 
        JOIN events e ON a.event_id = e.id 
        JOIN students s ON a.student_id = s.user_id 
        JOIN users u ON s.user_id = u.id 
        WHERE e.organizer_id = ? 
        ORDER BY e.name, a.applied_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$applicants = $stmt->fetchAll();

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="applicants_' . date('Y-m-d') . '.xls"');

echo "Event Name\tApplicant Name\tEmail\tPortfolio\tApplied At\tStatus\n";

foreach($applicants as $app) {
    echo $app['event_name'] . "\t";
    echo $app['full_name'] . "\t";
    echo $app['email'] . "\t";
    echo $app['portfolio'] . "\t";
    echo $app['applied_at'] . "\t";
    echo ucfirst($app['status']) . "\n";
}
exit();