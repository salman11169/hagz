<?php
/**
 * PatientController — Serves patient-related data from DB as JSON
 * Hagz Clinic System
 */

// Buffer ALL output so any PHP notice/warning/error HTML gets discarded
ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

// Global exception handler — converts any uncaught exception to JSON
set_exception_handler(function (Throwable $e) {
    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في الخادم: ' . $e->getMessage(),
        'file'    => basename($e->getFile()),
        'line'    => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
    exit();
});

if (!is_patient()) {
    json_response(false, 'غير مصرح.', ['redirect' => '/auth/login.php']);
}

// patient_id may be 0 if session is stale — re-fetch from DB
$patient_id = (int)($_SESSION['patient_id'] ?? 0);
if ($patient_id === 0 && isset($_SESSION['user_id'])) {
    $pdo = getDB();
    $ps  = $pdo->prepare("SELECT id FROM Patients WHERE user_id = ? LIMIT 1");
    $ps->execute([$_SESSION['user_id']]);
    $patient_id = (int)($ps->fetchColumn() ?: 0);
    if ($patient_id) $_SESSION['patient_id'] = $patient_id;
}

$action = $_GET['action'] ?? '';

match ($action) {
    'dashboard'          => getDashboardData($patient_id),
    'profile'            => getProfile($patient_id),
    'appointments'       => getAppointments($patient_id),
    'medical_records'    => getMedicalRecords($patient_id),
    'prescriptions'      => getPrescriptions($patient_id),
    'bills'              => getBills($patient_id),
    'update_profile'     => updateProfile($patient_id),
    'upload_avatar'      => uploadAvatar($patient_id),
    'cancel_appointment' => cancelAppointment($patient_id),
    default              => json_response(false, 'طلب غير معروف.')
};

// ─────────────────────────────────────────────
function getDashboardData(?int $id): void {
    if (!$id) { json_response(false, 'لم يتم تحديد المريض.'); }
    $pdo = getDB();

    // Upcoming appointments
    // Schema: Appointments(id, patient_id, doctor_id, appointment_date, appointment_time,
    //         status ENUM('Pending','Confirmed','Completed','Cancelled','Transferred'),
    //         priority ENUM('Routine','Medium','Critical'), is_emergency_override, visit_type)
    $stmt = $pdo->prepare("
        SELECT a.id,
               a.appointment_date,
               a.appointment_time,
               a.status,
               a.visit_type,
               COALESCE(a.booking_type, 'regular') AS booking_type,
               COALESCE(tl.ai_predicted_priority, 'Routine') AS priority,
               u.first_name          AS doctor_first_name,
               u.last_name           AS doctor_last_name,
               s.name                AS specialization
        FROM Appointments a
        LEFT JOIN Doctors d        ON a.doctor_id = d.id
        LEFT JOIN Users u          ON d.user_id   = u.id
        LEFT JOIN Specializations s ON d.specialization_id = s.id
        LEFT JOIN Triage_Logs tl  ON tl.appointment_id = a.id
        WHERE a.patient_id = ?
          AND a.appointment_date >= CURDATE()
          AND a.status NOT IN ('Cancelled', 'Completed')
        ORDER BY a.appointment_date ASC, a.appointment_time ASC
        LIMIT 5
    ");
    $stmt->execute([$id]);
    $upcoming = $stmt->fetchAll();

    // Stats
    $statsStmt = $pdo->prepare("
        SELECT
            SUM(CASE WHEN appointment_date >= CURDATE() AND status NOT IN ('Cancelled','Completed') THEN 1 ELSE 0 END) AS upcoming_appointments,
            SUM(CASE WHEN appointment_date  < CURDATE() OR  status = 'Completed' THEN 1 ELSE 0 END)                   AS past_appointments,
            (SELECT COUNT(*) FROM Medical_Records mr
             JOIN Appointments aa ON mr.appointment_id = aa.id
             WHERE aa.patient_id = :pid)  AS medical_records,
            (SELECT COUNT(*) FROM Prescriptions pr
             JOIN Medical_Records mr2 ON pr.record_id = mr2.id
             JOIN Appointments ab ON mr2.appointment_id = ab.id
             WHERE ab.patient_id = :pid2) AS prescriptions
        FROM Appointments WHERE patient_id = :pid3
    ");
    $statsStmt->execute([':pid' => $id, ':pid2' => $id, ':pid3' => $id]);
    $stats = $statsStmt->fetch();

    json_response(true, 'ok', [
        'upcoming_appointments' => $upcoming,
        'stats'                 => $stats
    ]);
}

// ─────────────────────────────────────────────
function getProfile(?int $id): void {
    if (!$id) { json_response(false, 'لم يتم تحديد المريض.'); }
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT u.first_name, u.last_name, u.email, u.phone, u.created_at,
               p.date_of_birth, p.gender, p.blood_type, p.weight, p.height, p.avatar_path
        FROM Patients p
        JOIN Users u ON p.user_id = u.id
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $profile = $stmt->fetch();

    $cd = $pdo->prepare("SELECT disease_name, diagnosed_date FROM Chronic_Diseases WHERE patient_id = ?");
    $cd->execute([$id]);
    $diseases = $cd->fetchAll();

    json_response(true, 'ok', ['profile' => $profile, 'chronic_diseases' => $diseases]);
}

// ─────────────────────────────────────────────
function getAppointments(?int $id): void {
    if (!$id) { json_response(false, 'لم يتم تحديد المريض.'); }
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.appointment_time, a.status,
               a.visit_type, COALESCE(a.booking_type, 'regular') AS booking_type,
               COALESCE(tl.ai_predicted_priority, 'Routine') AS priority,
               CONCAT(u.first_name, ' ', u.last_name) AS doctor_name,
               s.name AS specialization
        FROM Appointments a
        LEFT JOIN Doctors d        ON a.doctor_id = d.id
        LEFT JOIN Users u          ON d.user_id   = u.id
        LEFT JOIN Specializations s ON d.specialization_id = s.id
        LEFT JOIN Triage_Logs tl  ON tl.appointment_id = a.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
    ");
    $stmt->execute([$id]);
    json_response(true, 'ok', ['appointments' => $stmt->fetchAll()]);
}

// ─────────────────────────────────────────────
function getMedicalRecords(?int $id): void {
    if (!$id) { json_response(false, 'لم يتم تحديد المريض.'); }
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT mr.id, mr.doctor_notes, mr.next_follow_up_date, mr.created_at,
               a.id AS appointment_id,
               a.appointment_date,
               a.status,
               CONCAT(u.first_name, ' ', u.last_name) AS doctor_name,
               s.name AS specialization
        FROM Medical_Records mr
        JOIN Appointments a ON mr.appointment_id = a.id
        LEFT JOIN Doctors d ON a.doctor_id = d.id
        LEFT JOIN Users u   ON d.user_id   = u.id
        LEFT JOIN Specializations s ON d.specialization_id = s.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC
    ");
    $stmt->execute([$id]);
    $records = $stmt->fetchAll();

    foreach ($records as &$rec) {
        $syms = $pdo->prepare("SELECT symptom_name, pain_level, duration, condition_type FROM Record_Symptoms WHERE record_id = ?");
        $syms->execute([$rec['id']]);
        $rec['symptoms'] = $syms->fetchAll();

        // fallback: إذا لم تُوجد أعراض في السجل الطبي، اجلبها من triage_logs
        if (empty($rec['symptoms'])) {
            $tl = $pdo->prepare("SELECT raw_symptoms_input FROM triage_logs WHERE appointment_id = ? ORDER BY created_at DESC LIMIT 1");
            $tl->execute([$rec['appointment_id']]);
            $raw = $tl->fetchColumn();
            if ($raw) {
                $parsed = json_decode($raw, true);
                $symsArr = is_array($parsed) ? ($parsed['symptoms'] ?? $parsed) : [];
                if (!empty($symsArr)) {
                    $rec['symptoms'] = array_map(function($s) {
                        return ['symptom_name' => is_array($s) ? ($s['name'] ?? $s['symptom'] ?? json_encode($s)) : $s];
                    }, $symsArr);
                }
            }
        }

        $labs = $pdo->prepare("SELECT test_name, category, result, status FROM Record_Lab_Tests WHERE record_id = ?");
        $labs->execute([$rec['id']]);
        $rec['labs'] = $labs->fetchAll();

        $meds = $pdo->prepare("SELECT medication_name, dosage_strength, frequency, timing FROM Prescriptions WHERE record_id = ?");
        $meds->execute([$rec['id']]);
        $rec['medications'] = $meds->fetchAll();
    }

    json_response(true, 'ok', ['records' => $records]);
}

// ─────────────────────────────────────────────
function getPrescriptions(?int $id): void {
    if (!$id) { json_response(false, 'لم يتم تحديد المريض.'); }
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT p.id AS rx_id, p.medication_name, p.dosage_strength, p.frequency, p.timing,
               a.id AS appointment_id,
               a.appointment_date,
               CONCAT(u.first_name, ' ', u.last_name) AS doctor_name,
               s.name AS specialization
        FROM Prescriptions p
        JOIN Medical_Records mr ON p.record_id = mr.id
        JOIN Appointments a     ON mr.appointment_id = a.id
        LEFT JOIN Doctors d     ON a.doctor_id = d.id
        LEFT JOIN Users u       ON d.user_id   = u.id
        LEFT JOIN Specializations s ON d.specialization_id = s.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC
    ");
    $stmt->execute([$id]);
    json_response(true, 'ok', ['prescriptions' => $stmt->fetchAll()]);
}

// ─────────────────────────────────────────────
function getBills(?int $id): void {
    if (!$id) { json_response(false, 'لم يتم تحديد المريض.'); }
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT b.id, b.total_amount, b.insurance_discount, b.net_amount,
               b.payment_status, b.payment_method, b.created_at,
               a.appointment_date,
               CONCAT(u.first_name, ' ', u.last_name) AS doctor_name,
               s.name AS specialization
        FROM Billing b
        JOIN Appointments a ON b.appointment_id = a.id
        LEFT JOIN Doctors d ON a.doctor_id = d.id
        LEFT JOIN Users u   ON d.user_id   = u.id
        LEFT JOIN Specializations s ON d.specialization_id = s.id
        WHERE a.patient_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$id]);
    $bills = $stmt->fetchAll();

    foreach ($bills as &$bill) {
        $items = $pdo->prepare("SELECT item_name, amount FROM Bill_Items WHERE bill_id = ?");
        $items->execute([$bill['id']]);
        $bill['items'] = $items->fetchAll();
    }

    json_response(true, 'ok', ['bills' => $bills]);
}

// ─────────────────────────────────────────────
function updateProfile(?int $id): void {
    if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $pdo = getDB();

    $phone  = preg_replace('/[\s\-()]+/', '', sanitize($_POST['phone'] ?? ''));
    $weight = (float) ($_POST['weight'] ?? 0);
    $height = (float) ($_POST['height'] ?? 0);
    $blood  = sanitize($_POST['blood_type'] ?? '');

    $pdo->prepare("UPDATE Patients SET weight=?, height=?, blood_type=? WHERE id=?")
        ->execute([$weight ?: null, $height ?: null, $blood ?: null, $id]);
    if ($phone && validate_phone($phone)) {
        $pdo->prepare("UPDATE Users SET phone=? WHERE id=(SELECT user_id FROM Patients WHERE id=?)")
            ->execute([$phone, $id]);
    }
    json_response(true, 'تم تحديث الملف الشخصي بنجاح.');
}

// ───────────────────────────────────────────
function uploadAvatar(?int $id): void {
    if (!$id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'طلب غير صالح.');
    }
    if (empty($_FILES['avatar'])) {
        json_response(false, 'لم يتم اختيار صورة.');
    }
    $pdo = getDB();
    $file    = $_FILES['avatar'];
    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $mime    = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed)) {
        json_response(false, 'نوع الملف غير مدعوم. أرسل JPG أو PNG أو WebP.');
    }
    if ($file['size'] > 3 * 1024 * 1024) {
        json_response(false, 'حجم الصورة يجب أن يكون أقل من 3MB.');
    }
    $ext      = match($mime) { 'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg' };
    $dir      = __DIR__ . '/../assets/img/avatars/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $filename = 'patient_' . $id . '_' . time() . '.' . $ext;
    $destPath = $dir . $filename;
    // Delete old avatar
    $old = $pdo->prepare("SELECT avatar_path FROM Patients WHERE id = ?");
    $old->execute([$id]);
    $oldPath = $old->fetchColumn();
    if ($oldPath) {
        $absOld = __DIR__ . '/../' . ltrim($oldPath, '/');
        if (file_exists($absOld)) @unlink($absOld);
    }
    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        json_response(false, 'فشل حفظ الصورة.');
    }
    $webPath = 'assets/img/avatars/' . $filename;
    $pdo->prepare("UPDATE Patients SET avatar_path = ? WHERE id = ?")->execute([$webPath, $id]);
    json_response(true, 'تم تحديث الصورة بنجاح.', ['avatar_path' => $webPath]);
}

// ─────────────────────────────────────────────
function cancelAppointment(?int $patient_id): void {
    if (!$patient_id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'طلب غير صالح.');
    }
    $body    = json_decode(file_get_contents('php://input'), true);
    $appt_id = (int) ($body['appointment_id'] ?? 0);
    if (!$appt_id) {
        json_response(false, 'رقم الموعد مطلوب.');
    }
    $pdo   = getDB();
    $check = $pdo->prepare("SELECT id FROM Appointments WHERE id = ? AND patient_id = ? AND status NOT IN ('Cancelled','Completed')");
    $check->execute([$appt_id, $patient_id]);
    if (!$check->fetch()) {
        json_response(false, 'الموعد غير موجود أو لا يمكن إلغاؤه.');
    }
    $pdo->prepare("UPDATE Appointments SET status = 'Cancelled' WHERE id = ?")->execute([$appt_id]);
    json_response(true, 'تم إلغاء الموعد بنجاح.');
}
