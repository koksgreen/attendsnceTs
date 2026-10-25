<?php
declare(strict_types=1);

require __DIR__ . '/../../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respond(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$date = $_GET['date'] ?? date('Y-m-d');
$d = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $date);
if ($d === false || $d->format('Y-m-d') !== $date) {
    respond(422, ['success' => false, 'message' => 'Invalid date.']);
}

$search = trim((string) ($_GET['search'] ?? ''));
$sql = 'SELECT employee_id, full_name, department, type, recorded_at FROM attendance WHERE attendance_date = ?';
$params = [$date];
if ($search !== '') {
    $like = '%' . addcslashes($search, '%_\\') . '%';
    $sql .= ' AND (employee_id LIKE ? OR full_name LIKE ? OR department LIKE ?)';
    array_push($params, $like, $like, $like);
}
$sql .= ' ORDER BY recorded_at DESC LIMIT 500';

$stmt = db($config)->prepare($sql);
$stmt->execute($params);

respond(200, ['success' => true, 'data' => ['date' => $date, 'records' => $stmt->fetchAll()]]);
