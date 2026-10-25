<?php
declare(strict_types=1);

require __DIR__ . '/../../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['success' => false, 'message' => 'Method not allowed.']);
}

$input = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($input)) {
    respond(400, ['success' => false, 'message' => 'Invalid request.']);
}

$employeeId = strtoupper(trim((string) ($input['employee_id'] ?? '')));
$fullName   = trim((string) ($input['full_name'] ?? ''));
$department = trim((string) ($input['department'] ?? ''));
$phone      = trim((string) ($input['phone'] ?? ''));
$type       = (string) ($input['type'] ?? '');

$errors = [];
if (!preg_match('/^[A-Z0-9][A-Z0-9\-_\/]{1,31}$/', $employeeId)) {
    $errors['employee_id'] = 'Employee ID must be 2-32 letters, digits, - _ or /.';
}
if ($fullName === '' || mb_strlen($fullName) > 100) {
    $errors['full_name'] = 'Full name is required (max 100 characters).';
}
if ($department === '' || mb_strlen($department) > 80) {
    $errors['department'] = 'Department is required (max 80 characters).';
}
if ($phone !== '' && !preg_match('/^\+?[0-9 \-()]{7,20}$/', $phone)) {
    $errors['phone'] = 'Phone number is not valid.';
}
if (!in_array($type, ['check_in', 'check_out'], true)) {
    $errors['type'] = 'Choose check in or check out.';
}
if ($errors) {
    respond(422, ['success' => false, 'message' => 'Please correct the highlighted fields.', 'errors' => $errors]);
}

// The date and time always come from the server clock, never from the browser.
$now  = new DateTimeImmutable('now');
$date = $now->format('Y-m-d');
$time = $now->format('Y-m-d H:i:s');

$pdo = db($config);

try {
    $pdo->beginTransaction();

    // Serialise requests for the same employee so two quick clicks cannot both pass the checks.
    $pdo->prepare('SELECT id FROM attendance WHERE employee_id = ? AND attendance_date = ? FOR UPDATE')
        ->execute([$employeeId, $date]);

    $check = $pdo->prepare('SELECT COUNT(*) FROM attendance WHERE employee_id = ? AND attendance_date = ? AND type = ?');

    $check->execute([$employeeId, $date, 'check_in']);
    $hasIn = (int) $check->fetchColumn() > 0;
    $check->execute([$employeeId, $date, 'check_out']);
    $hasOut = (int) $check->fetchColumn() > 0;

    if ($type === 'check_in' && $hasIn) {
        $pdo->rollBack();
        respond(409, ['success' => false, 'message' => 'You have already checked in today.']);
    }
    if ($type === 'check_out' && !$hasIn) {
        $pdo->rollBack();
        respond(409, ['success' => false, 'message' => 'You must check in before checking out.']);
    }
    if ($type === 'check_out' && $hasOut) {
        $pdo->rollBack();
        respond(409, ['success' => false, 'message' => 'You have already checked out today.']);
    }

    $pdo->prepare(
        'INSERT INTO attendance (employee_id, full_name, department, phone, type, attendance_date, recorded_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    )->execute([$employeeId, $fullName, $department, $phone !== '' ? $phone : null, $type, $date, $time]);

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Attendance insert failed: ' . $e->getMessage());
    respond(500, ['success' => false, 'message' => 'Could not save attendance. Please try again.']);
}

respond(201, [
    'success' => true,
    'message' => $type === 'check_in' ? 'Checked in successfully.' : 'Checked out successfully.',
    'data' => ['full_name' => $fullName, 'type' => $type, 'recorded_at' => $time],
]);
