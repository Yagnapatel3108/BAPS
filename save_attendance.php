<?php
require_once 'config.php';
checkRole(['Saint', 'Nirdheshak', 'Agresar', 'Nirikshak', 'Karyakar', 'Sah-Karyakar']);

$date   = $_POST['attendance_date'] ?? date('Y-m-d');
$status = $_POST['status'] ?? [];

if (empty($status)) {
    header("Location: attendance.php?date=" . urlencode($date) . "&error=no_data");
    exit();
}

$saved = 0;
foreach ($status as $balak_id => $st) {
    $balak_id = (int)$balak_id;
    $st       = in_array($st, ['Present','Absent']) ? $st : 'Present';

    // Check if already exists for this date
    $check = $pdo->prepare("SELECT id FROM attendance WHERE balak_id=? AND attendance_date=?");
    $check->execute([$balak_id, $date]);
    $existing = $check->fetchColumn();

    if ($existing) {
        $upd = $pdo->prepare("UPDATE attendance SET status=?, marked_by=? WHERE balak_id=? AND attendance_date=?");
        $upd->execute([$st, $_SESSION['user_id'], $balak_id, $date]);
    } else {
        $ins = $pdo->prepare("INSERT INTO attendance (balak_id, status, attendance_date, marked_by) VALUES (?,?,?,?)");
        $ins->execute([$balak_id, $st, $date, $_SESSION['user_id']]);
    }
    $saved++;
}

header("Location: attendance_report.php?date=" . urlencode($date) . "&saved=" . $saved);
exit();
?>
