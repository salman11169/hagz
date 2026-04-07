<?php
/**
 * DoctorController — JSON API for the Doctor Portal
 * Hagz Clinic System
 *
 * Pattern:
 *   - All responses via json_response(bool, string, array)
 *   - No safe migrations — DB schema is managed by migration files
 *   - Action routing via match()
 *   - Helpers: requireId(), requirePost(), parseBody()
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

if (!is_doctor()) {
    json_response(false, 'غير مصرح.', ['redirect' => '/Hagz/auth/login.php']);
}

$doctor_id = (int) ($_SESSION['doctor_id'] ?? 0);
$action    = $_GET['action'] ?? '';

match ($action) {
    'dashboard'          => getDashboardData($doctor_id),
    'today_queue'        => getTodayQueue($doctor_id),
    'appointments'       => getAppointments($doctor_id),
    'appointment_detail' => getAppointmentDetail($doctor_id),
    'save_treatment'     => saveTreatment($doctor_id),
    'update_status'        => updateAppointmentStatus($doctor_id),
    'patients'             => getMyPatients($doctor_id),
    'reports'              => getReports($doctor_id),
    'profile'              => getDoctorProfile($doctor_id),
    'update_profile'       => updateDoctorProfile($doctor_id),
    'upload_avatar'        => uploadAvatar($doctor_id),
    'get_schedule'         => getSchedule($doctor_id),
    'save_schedule'        => saveSchedule($doctor_id),
    'update_slot_duration' => updateSlotDuration($doctor_id),
    'get_skills'           => getSkills($doctor_id),
    'save_skills'          => saveSkills($doctor_id),
    'create_referral'      => createReferral($doctor_id),
    'get_referrals'        => getReferrals($doctor_id),
    'doctors'              => getDoctorsList(),
    default                => json_response(false, 'طلب غير معروف.')
};

// ══════════════════════════════════════════════
// HELPERS
// ══════════════════════════════════════════════

function requireId(int $id): void {
    if (!$id) json_response(false, 'معرّف الطبيب مطلوب.');
}

function requirePost(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        json_response(false, 'يجب أن يكون الطلب POST.');
}

function parseBody(): array {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

// ══════════════════════════════════════════════
// DASHBOARD
// ══════════════════════════════════════════════

function getDashboardData(int $id): void {
    requireId($id);
    $pdo = getDB();

    $statsStmt = $pdo->prepare("
        SELECT
            COUNT(a.id) AS today_total,
            SUM(
                COALESCE(
                    (SELECT ai_predicted_priority FROM triage_logs
                     WHERE appointment_id = a.id ORDER BY created_at DESC LIMIT 1),
                'Routine') = 'Critical'
                AND a.status NOT IN ('Completed','Cancelled')
            ) AS critical_today,
            SUM(
                COALESCE(
                    (SELECT ai_predicted_priority FROM triage_logs
                     WHERE appointment_id = a.id ORDER BY created_at DESC LIMIT 1),
                'Routine') = 'Medium'
                AND a.status NOT IN ('Completed','Cancelled')
            ) AS medium_today,
            SUM(
                COALESCE(
                    (SELECT ai_predicted_priority FROM triage_logs
                     WHERE appointment_id = a.id ORDER BY created_at DESC LIMIT 1),
                'Routine') = 'Routine'
                AND a.status NOT IN ('Completed','Cancelled')
            ) AS routine_today,
            SUM(a.status = 'Completed') AS completed_today
        FROM appointments a
        WHERE a.doctor_id = ? AND a.appointment_date = CURDATE()
    ");
    $statsStmt->execute([$id]);

    $upcomingStmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.appointment_time,
               a.status, a.visit_type, a.booking_type,
               tl.ai_predicted_priority                        AS priority,
               CONCAT(u.first_name, ' ', u.last_name)         AS patient_name,
               u.phone                                         AS patient_phone
        FROM appointments a
        JOIN patients p      ON a.patient_id  = p.id
        JOIN users u         ON p.user_id     = u.id
        LEFT JOIN triage_logs tl ON tl.appointment_id = a.id
        WHERE a.doctor_id = ?
          AND a.appointment_date >= CURDATE()
          AND a.status NOT IN ('Cancelled','Completed')
        ORDER BY
            FIELD(COALESCE(tl.ai_predicted_priority, 'Routine'), 'Critical','Medium','Routine'),
            a.appointment_date ASC,
            a.appointment_time ASC
        LIMIT 5
    ");
    $upcomingStmt->execute([$id]);

    json_response(true, 'ok', [
        'stats'    => $statsStmt->fetch(),
        'upcoming' => $upcomingStmt->fetchAll(),
    ]);
}

// ══════════════════════════════════════════════
// TODAY'S QUEUE
// ══════════════════════════════════════════════

function getTodayQueue(int $id): void {
    requireId($id);
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.appointment_time, a.status,
               a.visit_type, a.booking_type,
               COALESCE(
                   (SELECT ai_predicted_priority FROM triage_logs
                    WHERE appointment_id = a.id ORDER BY created_at DESC LIMIT 1),
               'Routine') AS priority,
               (SELECT ai_summary FROM triage_logs
                WHERE appointment_id = a.id ORDER BY created_at DESC LIMIT 1) AS ai_summary,
               CONCAT(u.first_name, ' ', u.last_name) AS patient_name,
               p.gender,
               TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age
        FROM appointments a
        JOIN patients p ON a.patient_id  = p.id
        JOIN users u    ON p.user_id     = u.id
        WHERE a.doctor_id = ?
          AND a.appointment_date <= CURDATE()
          AND a.status IN ('Pending', 'Confirmed')
        ORDER BY
            a.appointment_date DESC,
            FIELD(
                COALESCE(
                    (SELECT ai_predicted_priority FROM triage_logs
                     WHERE appointment_id = a.id ORDER BY created_at DESC LIMIT 1),
                'Routine'),
            'Critical','Medium','Routine'),
            a.appointment_time ASC
        LIMIT 50
    ");
    $stmt->execute([$id]);
    json_response(true, 'ok', ['queue' => $stmt->fetchAll()]);
}

// ══════════════════════════════════════════════
// APPOINTMENTS LIST
// ══════════════════════════════════════════════

function getAppointments(int $id): void {
    requireId($id);
    $pdo = getDB();

    $status = sanitize($_GET['status'] ?? '');
    $date   = sanitize($_GET['date']   ?? '');
    $type   = sanitize($_GET['type']   ?? ''); // 'smart' | 'regular'

    $sql    = "
        SELECT a.id, a.appointment_date, a.appointment_time,
               a.status, a.visit_type, a.booking_type,
               tl.ai_predicted_priority                        AS priority,
               CONCAT(u.first_name, ' ', u.last_name)         AS patient_name,
               u.phone                                         AS patient_phone,
               p.gender,
               TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age
        FROM appointments a
        JOIN patients p      ON a.patient_id  = p.id
        JOIN users u         ON p.user_id     = u.id
        LEFT JOIN triage_logs tl ON tl.appointment_id = a.id
        WHERE a.doctor_id = ?
    ";
    $params = [$id];

    if ($status) { $sql .= ' AND a.status       = ?'; $params[] = $status; }
    if ($date)   { $sql .= ' AND a.appointment_date = ?'; $params[] = $date; }
    if ($type)   { $sql .= ' AND a.booking_type  = ?'; $params[] = $type; }

    $sql .= ' ORDER BY a.appointment_date DESC, a.appointment_time DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_response(true, 'ok', ['appointments' => $stmt->fetchAll()]);
}

// ══════════════════════════════════════════════
// APPOINTMENT DETAIL
// ══════════════════════════════════════════════

function getAppointmentDetail(int $doctor_id): void {
    $appt_id = (int) ($_GET['id'] ?? 0);
    if (!$appt_id) json_response(false, 'رقم الموعد غير صحيح.');
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT a.id, a.appointment_date, a.appointment_time,
               a.status, a.visit_type, a.booking_type, a.notes,
               a.consultation_start_time, a.consultation_end_time,
               a.created_at, a.confirmed_at, a.completed_at,
               CONCAT(u.first_name, ' ', u.last_name)         AS patient_name,
               u.phone AS patient_phone, u.email AS patient_email,
               p.id AS patient_id, p.date_of_birth, p.gender,
               p.blood_type, p.weight, p.height,
               TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
               COALESCE(tl.ai_predicted_priority, 'Routine')  AS priority
        FROM appointments a
        JOIN patients p  ON a.patient_id = p.id
        JOIN users u     ON p.user_id    = u.id
        LEFT JOIN triage_logs tl ON tl.appointment_id = a.id
        WHERE a.id = ? AND a.doctor_id = ?
    ");
    $stmt->execute([$appt_id, $doctor_id]);
    $appt = $stmt->fetch();
    if (!$appt) json_response(false, 'الموعد غير موجود أو غير مصرح.');

    // الأمراض المزمنة
    $cd = $pdo->prepare("SELECT disease_name, diagnosed_date FROM chronic_diseases WHERE patient_id = ?");
    $cd->execute([$appt['patient_id']]);
    $appt['chronic_diseases'] = $cd->fetchAll();

    // السجل الطبي
    $mr = $pdo->prepare("SELECT * FROM medical_records WHERE appointment_id = ?");
    $mr->execute([$appt_id]);
    $record = $mr->fetch();

    if ($record) {
        $syms = $pdo->prepare("SELECT * FROM record_symptoms  WHERE record_id = ?");
        $syms->execute([$record['id']]);
        $record['symptoms'] = $syms->fetchAll();

        $labs = $pdo->prepare("SELECT * FROM record_lab_tests WHERE record_id = ?");
        $labs->execute([$record['id']]);
        $record['labs'] = $labs->fetchAll();

        $meds = $pdo->prepare("SELECT * FROM prescriptions    WHERE record_id = ?");
        $meds->execute([$record['id']]);
        $record['medications'] = $meds->fetchAll();
    }

    // triage_logs — لكلا النوعين (smart و regular)
    $tl = $pdo->prepare("
        SELECT raw_symptoms_input,
               ai_predicted_priority, ai_summary, ai_reasoning,
               algorithm_confidence_score, scheduled_date, scheduled_time, created_at
        FROM triage_logs
        WHERE appointment_id = ?
        ORDER BY created_at DESC LIMIT 1
    ");
    $tl->execute([$appt_id]);
    $triage = $tl->fetch() ?: null;

    json_response(true, 'ok', [
        'appointment'    => $appt,
        'medical_record' => $record ?: null,
        'triage'         => $triage,
    ]);
}

// ══════════════════════════════════════════════
// SAVE TREATMENT
// ══════════════════════════════════════════════

function saveTreatment(int $doctor_id): void {
    requirePost();
    $pdo = getDB();

    $appt_id   = (int)    ($_POST['appointment_id'] ?? 0);
    $notes     = sanitize($_POST['notes']            ?? '');
    $diagnosis = sanitize($_POST['diagnosis']        ?? '');
    $followup  = sanitize($_POST['followup_date']    ?? '');
    $labs      = json_decode($_POST['labs']          ?? '[]', true) ?: [];
    $meds      = json_decode($_POST['meds']          ?? '[]', true) ?: [];
    $symptoms  = json_decode($_POST['symptoms']      ?? '[]', true) ?: [];

    if (!$appt_id) json_response(false, 'رقم الموعد مطلوب.');

    // التحقق من الملكية
    $chk = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND doctor_id = ?");
    $chk->execute([$appt_id, $doctor_id]);
    if (!$chk->fetch()) json_response(false, 'غير مصرح بالوصول لهذا الموعد.');

    // Upsert السجل الطبي
    $existing = $pdo->prepare("SELECT id FROM medical_records WHERE appointment_id = ?");
    $existing->execute([$appt_id]);
    $record_id = $existing->fetchColumn();

    if ($record_id) {
        $pdo->prepare("
            UPDATE medical_records
            SET doctor_notes=?, diagnosis=?, next_follow_up_date=?
            WHERE id=?
        ")->execute([$notes, $diagnosis, $followup ?: null, $record_id]);
    } else {
        $pdo->prepare("
            INSERT INTO medical_records (appointment_id, doctor_notes, diagnosis, next_follow_up_date)
            VALUES (?,?,?,?)
        ")->execute([$appt_id, $notes, $diagnosis, $followup ?: null]);
        $record_id = (int) $pdo->lastInsertId();
    }

    // حذف وإعادة إدراج التفاصيل
    $pdo->prepare("DELETE FROM record_lab_tests WHERE record_id = ?")->execute([$record_id]);
    $pdo->prepare("DELETE FROM prescriptions    WHERE record_id = ?")->execute([$record_id]);
    $pdo->prepare("DELETE FROM record_symptoms  WHERE record_id = ?")->execute([$record_id]);

    foreach ($labs as $lab) {
        $pdo->prepare("INSERT INTO record_lab_tests (record_id, test_name, category) VALUES (?,?,?)")
            ->execute([$record_id, $lab['name'] ?? $lab, $lab['category'] ?? null]);
    }
    foreach ($meds as $med) {
        $pdo->prepare("INSERT INTO prescriptions (record_id, medication_name, dosage_strength, frequency, timing) VALUES (?,?,?,?,?)")
            ->execute([$record_id, $med['name'] ?? '', $med['strength'] ?? null, $med['freq'] ?? null, $med['timing'] ?? null]);
    }
    foreach ($symptoms as $sym) {
        $pdo->prepare("INSERT INTO record_symptoms (record_id, symptom_name, pain_level, duration, condition_type) VALUES (?,?,?,?,?)")
            ->execute([$record_id, $sym['name'] ?? $sym, $sym['pain'] ?? null, $sym['duration'] ?? null, $sym['condition'] ?? 'Acute']);
    }

    $pdo->prepare("
        UPDATE appointments
        SET consultation_start_time = COALESCE(consultation_start_time, NOW()),
            status = 'Completed',
            completed_at = NOW()
        WHERE id = ?
    ")->execute([$appt_id]);

    json_response(true, 'تم حفظ المعالجة بنجاح.');
}

// ══════════════════════════════════════════════
// UPDATE APPOINTMENT STATUS
// ══════════════════════════════════════════════

function updateAppointmentStatus(int $doctor_id): void {
    requirePost();
    $pdo = getDB();

    $appt_id = (int)    ($_POST['appointment_id'] ?? 0);
    $status  = sanitize($_POST['status']          ?? '');
    $notes   = sanitize($_POST['notes']           ?? '');
    $allowed = ['Pending','Confirmed','Completed','Cancelled','Transferred'];

    if (!$appt_id || !in_array($status, $allowed)) json_response(false, 'بيانات غير صحيحة.');

    $chk = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND doctor_id = ?");
    $chk->execute([$appt_id, $doctor_id]);
    if (!$chk->fetch()) json_response(false, 'غير مصرح.');

    $fields = ['status = ?'];
    $params = [$status];

    if ($status === 'Confirmed')  $fields[] = 'confirmed_at  = NOW()';
    if ($status === 'Completed') { $fields[] = 'completed_at  = NOW()'; $fields[] = 'consultation_end_time = NOW()'; }
    if ($status === 'Cancelled')  $fields[] = 'cancelled_at  = NOW()';
    if ($notes)                  { $fields[] = 'notes = ?'; $params[] = $notes; }

    $params[] = $appt_id;
    $pdo->prepare('UPDATE appointments SET ' . implode(', ', $fields) . ' WHERE id = ?')
        ->execute($params);

    json_response(true, 'تم تحديث حالة الموعد.');
}

// ══════════════════════════════════════════════
// PATIENTS
// ══════════════════════════════════════════════

function getMyPatients(int $id): void {
    requireId($id);
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT p.id,
               u.first_name, u.last_name, u.phone, u.email,
               p.gender, p.blood_type,
               TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
               COUNT(a.id)           AS total_visits,
               MAX(a.appointment_date) AS last_visit
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN users u    ON p.user_id    = u.id
        WHERE a.doctor_id = ? AND a.status = 'Completed'
        GROUP BY p.id
        ORDER BY last_visit DESC
    ");
    $stmt->execute([$id]);
    json_response(true, 'ok', ['patients' => $stmt->fetchAll()]);
}

// ══════════════════════════════════════════════
// REPORTS
// ══════════════════════════════════════════════

function getReports(int $id): void {
    requireId($id);
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT
            COUNT(a.id)                                                      AS total_appointments,
            SUM(a.status = 'Completed')                                      AS completed,
            SUM(a.status = 'Cancelled')                                      AS cancelled,
            SUM(tl.ai_predicted_priority = 'Critical')                       AS critical_cases,
            AVG(TIMESTAMPDIFF(MINUTE, a.consultation_start_time,
                                      a.consultation_end_time))              AS avg_consultation_min
        FROM appointments a
        LEFT JOIN triage_logs tl ON tl.appointment_id = a.id
        WHERE a.doctor_id = ?
          AND a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$id]);
    json_response(true, 'ok', ['report' => $stmt->fetch()]);
}

// ══════════════════════════════════════════════
// PROFILE
// ══════════════════════════════════════════════

function getDoctorProfile(int $id): void {
    requireId($id);
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT u.first_name, u.last_name, u.email, u.phone,
               d.id AS doctor_id, d.license_number, d.experience_years,
               d.consultation_fee, d.bio, d.avatar_path,
               s.name AS specialization
        FROM doctors d
        JOIN users u           ON d.user_id          = u.id
        JOIN specializations s ON d.specialization_id = s.id
        WHERE d.id = ?
    ");
    $stmt->execute([$id]);
    $profile = $stmt->fetch();

    // جدول الدوام — جمع shift_number 1=صباحي ، 2=مسائي
    $sched = $pdo->prepare("
        SELECT day_of_week, shift_number, start_time, end_time, is_available, slot_duration_min
        FROM doctor_schedules WHERE doctor_id = ?
        ORDER BY day_of_week, shift_number
    ");
    $sched->execute([$id]);
    $rawRows = $sched->fetchAll();

    // تحويل الصفوف إلى بنية يوم واحد مع morning/evening
    $dayMap = [];
    foreach ($rawRows as $r) {
        $d = (int)$r['day_of_week'];
        if (!isset($dayMap[$d])) {
            $dayMap[$d] = [
                'day_of_week'   => $d,
                'is_active'     => 0,
                'morning_start' => null, 'morning_end' => null,
                'evening_start' => null, 'evening_end' => null,
            ];
        }
        if ($r['shift_number'] == 1) {
            $dayMap[$d]['is_active']     = (int)$r['is_available'];
            $dayMap[$d]['morning_start'] = substr($r['start_time'], 0, 5);
            $dayMap[$d]['morning_end']   = substr($r['end_time'],   0, 5);
            $dayMap[$d]['slot_duration_min'] = (int)$r['slot_duration_min'];
        } elseif ($r['shift_number'] == 2) {
            $dayMap[$d]['evening_start'] = substr($r['start_time'], 0, 5);
            $dayMap[$d]['evening_end']   = substr($r['end_time'],   0, 5);
        }
    }

    // مدة الجلسة — نأخذها من أول شفت نشط (صباحي)
    $slotMin = 20; // default
    foreach ($rawRows as $r) {
        if ((int)$r['is_available'] && (int)$r['shift_number'] === 1) {
            $slotMin = (int)$r['slot_duration_min'];
            break;
        }
    }
    $profile['consultation_duration'] = $slotMin;

    if (!empty($dayMap)) {
        $profile['schedule'] = array_values($dayMap);
    } else {
        // افتراضي إذا لا يوجد جدول
        $profile['schedule'] = array_map(fn($d) => [
            'day_of_week'   => $d,
            'is_active'     => ($d <= 4) ? 1 : 0,
            'morning_start' => ($d <= 4) ? '08:00' : null,
            'morning_end'   => ($d <= 4) ? '14:00' : null,
            'evening_start' => null,
            'evening_end'   => null,
        ], range(0, 6));
    }
    if (!$profile) json_response(false, 'الطبيب غير موجود.');

    // إحصاءات
    $s = $pdo->prepare("
        SELECT COUNT(*) AS total, COUNT(DISTINCT patient_id) AS patients,
               SUM(status = 'Completed') AS completed
        FROM appointments WHERE doctor_id = ?
    ");
    $s->execute([$id]);
    $stats = $s->fetch();

    $profile['stat_patients']     = (int) $stats['patients'];
    $profile['stat_appointments'] = (int) $stats['total'];
    $profile['stat_rating']       = $stats['total'] > 0
        ? round(($stats['completed'] / $stats['total']) * 5, 1)
        : null;

    // المهارات
    $sk = $pdo->prepare("SELECT skill FROM doctor_skills WHERE doctor_id = ?");
    $sk->execute([$id]);
    $profile['skills'] = $sk->fetchAll(PDO::FETCH_COLUMN);

    json_response(true, 'ok', ['profile' => $profile]);
}

function updateDoctorProfile(int $id): void {
    requireId($id);
    requirePost();
    $pdo = getDB();

    $bio   = sanitize($_POST['bio']               ?? '');
    $fee   = (float)  ($_POST['consultation_fee'] ?? 0);
    $exp   = (int)    ($_POST['experience_years'] ?? 0);
    $fname = sanitize($_POST['first_name']        ?? '');
    $lname = sanitize($_POST['last_name']         ?? '');
    $phone = sanitize($_POST['phone']             ?? '');
    $email = sanitize($_POST['email']             ?? '');

    $pdo->prepare('UPDATE doctors SET bio=?, consultation_fee=?, experience_years=? WHERE id=?')
        ->execute([$bio, $fee, $exp, $id]);

    $sets = []; $params = [];
    if ($fname !== '') { $sets[] = 'u.first_name = ?'; $params[] = $fname; }
    if ($lname !== '') { $sets[] = 'u.last_name  = ?'; $params[] = $lname; }
    if ($phone !== '') { $sets[] = 'u.phone      = ?'; $params[] = $phone; }
    if ($email !== '') { $sets[] = 'u.email      = ?'; $params[] = $email; }

    if (!empty($sets)) {
        $params[] = $id;
        $pdo->prepare('UPDATE users u JOIN doctors d ON d.user_id = u.id SET '
            . implode(', ', $sets) . ' WHERE d.id = ?')->execute($params);
    }

    json_response(true, 'تم تحديث الملف الشخصي.');
}

function uploadAvatar(int $id): void {
    requireId($id);
    requirePost();

    $file = $_FILES['avatar'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) json_response(false, 'لم يتم الرفع.');

    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $mime    = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!in_array($mime, $allowed))          json_response(false, 'صيغة غير مدعومة. استخدم JPG, PNG, أو WebP.');
    if ($file['size'] > 3 * 1024 * 1024)    json_response(false, 'الحجم يتجاوز 3 ميغا.');

    $ext  = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'][$mime];
    $dir  = __DIR__ . '/../uploads/doctors/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = 'doc_' . $id . '_' . time() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . $name)) json_response(false, 'فشل حفظ الصورة.');

    $path = '/uploads/doctors/' . $name;
    getDB()->prepare("UPDATE doctors SET avatar_path=? WHERE id=?")->execute([$path, $id]);
    json_response(true, 'تم تحديث الصورة.', ['avatar_url' => $path]);
}

// ══════════════════════════════════════════════
// SCHEDULE — SHIFT SUPPORT
// ══════════════════════════════════════════════

function getSchedule(int $id): void {
    requireId($id);
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT day_of_week, shift_number, start_time, end_time,
               is_available, slot_duration_min
        FROM doctor_schedules
        WHERE doctor_id = ?
        ORDER BY day_of_week ASC, shift_number ASC
    ");
    $stmt->execute([$id]);

    // day_of_week => [shifts...]
    $map = [];
    foreach ($stmt->fetchAll() as $r) {
        $map[(int) $r['day_of_week']][] = $r;
    }
    json_response(true, 'ok', ['schedule' => $map]);
}

function saveSchedule(int $id): void {
    requireId($id);
    requirePost();
    $pdo  = getDB();
    $body = parseBody();
    $days = $body['days'] ?? [];

    if (empty($days)) json_response(false, 'لا توجد بيانات للحفظ.');

    // حفظ مدة المعاينة إن أرسلت
    if (isset($body['consultation_duration'])) {
        $dur = max(5, (int) $body['consultation_duration']);
        $pdo->prepare('UPDATE doctors SET consultation_duration = ? WHERE id = ?')
            ->execute([$dur, $id]);
    }

    // حذف الجدول القديم
    $pdo->prepare('DELETE FROM doctor_schedules WHERE doctor_id = ?')->execute([$id]);

    $ins = $pdo->prepare('
        INSERT INTO doctor_schedules
            (doctor_id, day_of_week, shift_number, start_time, end_time, is_available, slot_duration_min)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');

    $dur = max(5, (int)($body['consultation_duration'] ?? 20));

    foreach ($days as $day) {
        $dow      = (int) ($day['day'] ?? -1);
        $active   = (int) ($day['is_active'] ?? 1);
        if ($dow < 0 || $dow > 6) continue;

        $mStart = $day['morning_start'] ?? '';
        $mEnd   = $day['morning_end']   ?? '';
        $eStart = $day['evening_start'] ?? '';
        $eEnd   = $day['evening_end']   ?? '';

        // الشفت الصباحي دائما يُحفظ إذا كان اليوم نشطاً
        if ($active || $mStart) {
            $ins->execute([
                $id, $dow, 1,
                ($mStart ?: '08:00') . ':00',
                ($mEnd   ?: '14:00') . ':00',
                $active,
                $dur,
            ]);
        }

        // الشفت المسائي اختياري — يُحفظ فقط إذا حددنا وقتاً
        if ($active && $eStart && $eEnd) {
            $ins->execute([
                $id, $dow, 2,
                $eStart . ':00',
                $eEnd   . ':00',
                1,
                $dur,
            ]);
        }
    }

    json_response(true, 'تم حفظ جدول الدوام بنجاح.');
}

// ══════════════════════════════════════════════
// SKILLS
// ══════════════════════════════════════════════

function getSkills(int $id): void {
    requireId($id);
    $stmt = getDB()->prepare("SELECT skill FROM doctor_skills WHERE doctor_id = ? ORDER BY skill");
    $stmt->execute([$id]);
    json_response(true, 'ok', ['skills' => $stmt->fetchAll(PDO::FETCH_COLUMN)]);
}

function saveSkills(int $id): void {
    requireId($id);
    requirePost();
    $pdo    = getDB();
    $skills = array_values(array_unique(array_filter(
        array_map('trim', parseBody()['skills'] ?? [])
    )));

    $pdo->prepare("DELETE FROM doctor_skills WHERE doctor_id = ?")->execute([$id]);
    $ins = $pdo->prepare("INSERT IGNORE INTO doctor_skills (doctor_id, skill) VALUES (?, ?)");
    foreach ($skills as $skill) {
        $ins->execute([$id, $skill]);
    }

    json_response(true, 'تم حفظ المهارات بنجاح.');
}

// ══════════════════════════════════════════════
// REFERRALS
// ══════════════════════════════════════════════

function createReferral(int $doctor_id): void {
    requireId($doctor_id);
    requirePost();
    $pdo  = getDB();
    $body = parseBody();

    $appt_id  = (int)    ($body['appointment_id']   ?? 0);
    $to_doc   = (int)    ($body['to_doctor_id']     ?? 0);
    $reason   = sanitize($body['reason']            ?? '');
    $summary  = sanitize($body['clinical_summary']  ?? '');
    $priority = sanitize($body['priority']          ?? 'Routine');

    if (!$appt_id || !$to_doc || !$reason)
        json_response(false, 'بيانات التحويل ناقصة (الموعد، الطبيب، السبب مطلوبة).');

    if (!in_array($priority, ['Routine','Urgent','Emergency'])) $priority = 'Routine';

    // التحقق من الملكية
    $chk = $pdo->prepare("SELECT id FROM appointments WHERE id = ? AND doctor_id = ?");
    $chk->execute([$appt_id, $doctor_id]);
    if (!$chk->fetch()) json_response(false, 'غير مصرح بالوصول لهذا الموعد.');

    $pdo->prepare("
        INSERT INTO referrals
            (appointment_id, from_doctor_id, to_doctor_id, reason, clinical_summary, priority)
        VALUES (?, ?, ?, ?, ?, ?)
    ")->execute([$appt_id, $doctor_id, $to_doc, $reason, $summary, $priority]);

    json_response(true, 'تم إرسال التحويل بنجاح.', ['referral_id' => (int) $pdo->lastInsertId()]);
}

function getReferrals(int $doctor_id): void {
    requireId($doctor_id);
    $pdo  = getDB();
    $type = sanitize($_GET['type'] ?? 'sent'); // 'sent' | 'received'
    $col  = $type === 'received' ? 'r.to_doctor_id' : 'r.from_doctor_id';

    $stmt = $pdo->prepare("
        SELECT r.id, r.reason, r.clinical_summary, r.priority, r.status,
               r.created_at, r.responded_at,
               CONCAT(fu.first_name, ' ', fu.last_name) AS from_doctor_name,
               CONCAT(tu.first_name, ' ', tu.last_name) AS to_doctor_name,
               CONCAT(pu.first_name, ' ', pu.last_name) AS patient_name
        FROM referrals r
        JOIN doctors  fd ON r.from_doctor_id  = fd.id
        JOIN users    fu ON fd.user_id         = fu.id
        JOIN doctors  td ON r.to_doctor_id    = td.id
        JOIN users    tu ON td.user_id         = tu.id
        JOIN appointments a ON r.appointment_id = a.id
        JOIN patients pat   ON a.patient_id     = pat.id
        JOIN users    pu    ON pat.user_id       = pu.id
        WHERE {$col} = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$doctor_id]);
    json_response(true, 'ok', ['referrals' => $stmt->fetchAll()]);
}

// ─────────────────────────────────────────────
function updateSlotDuration(int $id): void {
    requireId($id);
    requirePost();
    $pdo  = getDB();
    $body = parseBody();
    $dur  = max(5, (int)($body['slot_duration_min'] ?? 20));

    // تحديث جميع شفتات الطبيب في doctor_schedules
    $rowsAffected = $pdo->prepare(
        'UPDATE doctor_schedules SET slot_duration_min = ? WHERE doctor_id = ?'
    );
    $rowsAffected->execute([$dur, $id]);

    $count = $rowsAffected->rowCount();

    // إذا لم توجد شفتات بعد، لا يوجد شيء لتحديثه الآن
    // سيُحفظ التالي حين يُنشئ الجدول
    json_response(true, "تم تحديث مدة المعاينة ({$dur} دقيقة) في {$count} شفت.", [
        'updated' => $count,
        'duration' => $dur
    ]);
}

// ══════════════════════════════════════════════
// DOCTORS LIST (for referral transfer modal)
// ══════════════════════════════════════════════

function getDoctorsList(): void {
    $pdo  = getDB();
    $stmt = $pdo->query("
        SELECT d.id, CONCAT(u.first_name, ' ', u.last_name) AS name,
               s.name AS specialization
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        JOIN specializations s ON d.specialization_id = s.id
        WHERE u.is_active = 1
        ORDER BY s.name, u.first_name
    ");
    json_response(true, 'ok', ['doctors' => $stmt->fetchAll()]);
}
