<?php
/**
 * AdminController — Serves admin-related data from DB as JSON
 * Hagz Clinic System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

if (!is_admin()) {
    json_response(false, 'غير مصرح.', ['redirect' => '/auth/login.php']);
}

$action = $_GET['action'] ?? '';

match ($action) {
    'dashboard'              => getDashboardData(),
    'doctors'                => getDoctors(),
    'get_doctor'             => getDoctorById(),
    'add_doctor'             => addDoctor(),
    'update_doctor'          => updateDoctor(),
    'save_doctor_schedule'   => saveDoctorSchedule(),
    'upload_doctor_avatar'   => uploadDoctorAvatar(),
    'toggle_doctor'          => toggleDoctorStatus(),
    'specializations'        => getSpecializations(),
    'patients'               => getPatients(),
    'toggle_patient'         => togglePatientStatus(),
    'reports'                => getReports(),
    'get_settings'           => getSettings(),
    'save_settings'          => saveSettings(),
    'user_permissions'       => getUserPermissions(),
    'update_permission'      => updatePermission(),
    default                  => json_response(false, 'طلب غير معروف.')
};

// ─────────────────────────────────────────────
function getDashboardData(): void {
    $pdo = getDB();

    $stats = $pdo->query("
        SELECT
            (SELECT COUNT(*) FROM doctors)   AS total_doctors,
            (SELECT COUNT(*) FROM patients)  AS total_patients,
            (SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()) AS today_appointments,
            (SELECT COUNT(DISTINCT tl.appointment_id)
             FROM triage_logs tl
             JOIN appointments a ON a.id = tl.appointment_id
             WHERE tl.ai_predicted_priority = 'Critical'
               AND a.status NOT IN ('Completed','Cancelled')
            ) AS active_critical,
            (SELECT COUNT(*) FROM appointments WHERE status = 'Pending') AS pending_count
    ")->fetch();

    // Appointments by priority for last 30 days (from triage_logs)
    $chart = $pdo->query("
        SELECT tl.ai_predicted_priority AS priority, COUNT(*) AS count
        FROM triage_logs tl
        JOIN appointments a ON a.id = tl.appointment_id
        WHERE a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY tl.ai_predicted_priority
    ")->fetchAll();

    // Doctor performance (top 5 this month)
    $perf = $pdo->query("
        SELECT d.id,
               CONCAT(u.first_name,' ',u.last_name) AS name,
               s.name AS specialization,
               COUNT(a.id) AS total,
               SUM(a.status='Completed') AS completed,
               AVG(TIMESTAMPDIFF(MINUTE,a.consultation_start_time,a.consultation_end_time)) AS avg_min
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        JOIN specializations s ON d.specialization_id = s.id
        LEFT JOIN appointments a ON a.doctor_id = d.id
          AND a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        GROUP BY d.id
        ORDER BY completed DESC
        LIMIT 5
    ")->fetchAll();

    // آخر الحجوزات
    $recent = $pdo->query("
        SELECT a.id, a.appointment_date, a.appointment_time, a.status, a.booking_type,
               COALESCE(tl.ai_predicted_priority, 'Routine') AS priority,
               CONCAT(pu.first_name,' ',pu.last_name) AS patient_name,
               CONCAT(du.first_name,' ',du.last_name) AS doctor_name,
               sp.name AS specialization
        FROM appointments a
        JOIN patients p     ON a.patient_id = p.id
        JOIN users pu       ON p.user_id = pu.id
        JOIN doctors d      ON a.doctor_id = d.id
        JOIN users du       ON d.user_id = du.id
        JOIN specializations sp ON d.specialization_id = sp.id
        LEFT JOIN triage_logs tl ON tl.appointment_id = a.id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
        LIMIT 8
    ")->fetchAll();

    json_response(true, 'ok', ['stats' => $stats, 'chart' => $chart, 'performance' => $perf, 'recent' => $recent]);
}

// ─────────────────────────────────────────────
function getDoctors(): void {
    $pdo  = getDB();
    $stmt = $pdo->query("
        SELECT d.id AS doctor_id, d.id, u.first_name, u.last_name, u.email, u.phone, u.is_active, u.created_at,
               d.license_number, d.experience_years, d.consultation_fee, d.user_id,
               s.name AS specialization,
               SUM(a.appointment_date = CURDATE()) AS today_appointments
        FROM Doctors d
        JOIN Users u ON d.user_id = u.id
        JOIN Specializations s ON d.specialization_id = s.id
        LEFT JOIN Appointments a ON a.doctor_id = d.id
        GROUP BY d.id
        ORDER BY u.created_at DESC
    ");
    json_response(true, 'ok', ['doctors' => $stmt->fetchAll()]);
}

// ─────────────────────────────────────────────
function getDoctorById(): void {
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) json_response(false, 'معرّف الطبيب مطلوب.');
    $pdo = getDB();

    $stmt = $pdo->prepare("
        SELECT d.id, d.specialization_id, d.license_number, d.experience_years,
               d.consultation_fee, d.bio, d.avatar_path,
               u.first_name, u.last_name, u.email, u.phone, u.is_active
        FROM doctors d
        JOIN users u ON d.user_id = u.id
        WHERE d.id = ?
    ");
    $stmt->execute([$id]);
    $doctor = $stmt->fetch();
    if (!$doctor) json_response(false, 'الطبيب غير موجود.');

    // Load work schedule
    $sched = $pdo->prepare("
        SELECT day_of_week, shift_number, start_time, end_time, is_available
        FROM doctor_schedules
        WHERE doctor_id = ?
        ORDER BY day_of_week ASC, shift_number ASC
    ");
    $sched->execute([$id]);
    $doctor['schedule'] = $sched->fetchAll();

    json_response(true, 'ok', ['doctor' => $doctor]);
}

// ─────────────────────────────────────────────
function addDoctor(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    try {
        $pdo = getDB();

        $firstName  = sanitize($_POST['first_name']        ?? '');
        $lastName   = sanitize($_POST['last_name']         ?? '');
        $email      = trim($_POST['email']                 ?? '');
        $phone      = preg_replace('/[\s\-()]+/', '', sanitize($_POST['phone'] ?? ''));
        $phone      = preg_replace('/^(\+966|00966)/', '0', $phone);
        $password   = $_POST['password']                   ?? 'Doctor@12345';
        $specId     = (int) ($_POST['specialization_id']   ?? 0);
        $license    = sanitize($_POST['license_number']    ?? '');
        $experience = (int) ($_POST['experience_years']    ?? 0);
        $fee        = (float) ($_POST['consultation_fee']  ?? 0);
        $bio        = sanitize($_POST['bio']               ?? '');
        $isActive   = (int) ($_POST['is_active']           ?? 1);

        if (!$firstName || !$email || !$specId || !$license) {
            json_response(false, 'يرجى ملء جميع الحقول الإلزامية (الاسم + البريد + التخصص + الترخيص).');
        }
        if (!validate_email($email)) {
            json_response(false, 'البريد الإلكتروني غير صحيح.');
        }

        // Check duplicate email
        $check = $pdo->prepare('SELECT id FROM Users WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetch()) {
            json_response(false, 'البريد الإلكتروني مسجل مسبقاً.');
        }

        if (!$password) $password = 'Doctor@12345';
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Insert User (role_id 2 = Doctor)
        $pdo->prepare('INSERT INTO Users (role_id, first_name, last_name, email, phone, password_hash, is_active) VALUES (2,?,?,?,?,?,?)')
            ->execute([$firstName, $lastName, $email, $phone ?: null, $hash, $isActive]);
        $userId = (int) $pdo->lastInsertId();

        // Insert Doctor record
        $pdo->prepare('INSERT INTO Doctors (user_id, specialization_id, license_number, experience_years, consultation_fee, bio) VALUES (?,?,?,?,?,?)')
            ->execute([$userId, $specId, $license, $experience, $fee, $bio]);
        $doctorId = (int) $pdo->lastInsertId();

        // Insert doctor_schedules — work_days sent as JSON string
        $workDays  = json_decode($_POST['work_days']  ?? '[]', true) ?: [];
        $startTime = $_POST['start_time'] ?? '08:00:00';
        $endTime   = $_POST['end_time']   ?? '17:00:00';

        if (!empty($workDays)) {
            $schedStmt = $pdo->prepare(
                'INSERT INTO doctor_schedules
                    (doctor_id, day_of_week, shift_number, start_time, end_time, is_available, slot_duration_min)
                 VALUES (?, ?, 1, ?, ?, 1, 30)
                 ON DUPLICATE KEY UPDATE start_time=VALUES(start_time), end_time=VALUES(end_time)'
            );
            foreach ($workDays as $day) {
                $dayInt = (int) $day;
                if ($dayInt >= 0 && $dayInt <= 6) {
                    $schedStmt->execute([$doctorId, $dayInt, $startTime, $endTime]);
                }
            }
        }

        // Handle doctor image upload — avoid finfo dependency
        if (!empty($_FILES['doctor_image']['tmp_name']) && $_FILES['doctor_image']['error'] === UPLOAD_ERR_OK) {
            $file        = $_FILES['doctor_image'];
            $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg','jpeg','png','webp','gif'];
            if (in_array($ext, $allowedExts) && $file['size'] <= 3 * 1024 * 1024) {
                $safeExt  = ($ext === 'jpeg') ? 'jpg' : $ext;
                $filename = 'doc_' . $doctorId . '_' . time() . '.' . $safeExt;
                $dir      = __DIR__ . '/../uploads/doctors/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                if (move_uploaded_file($file['tmp_name'], $dir . $filename)) {
                    $pdo->prepare('UPDATE doctors SET avatar_path = ? WHERE id = ?')
                        ->execute(['/uploads/doctors/' . $filename, $doctorId]);
                }
            }
        }

        json_response(true, "تم إضافة الطبيب {$firstName} {$lastName} بنجاح.", ['doctor_id' => $doctorId]);

    } catch (PDOException $e) {
        error_log('addDoctor PDO Error: ' . $e->getMessage());
        if ($e->getCode() === '23000') {
            if (stripos($e->getMessage(), "'phone'") !== false || stripos($e->getMessage(), 'phone') !== false) {
                json_response(false, 'رقم الجوال مسجل مسبقاً. يرجى استخدام رقم آخر أو تركه فارغاً.');
            }
            if (stripos($e->getMessage(), 'email') !== false) {
                json_response(false, 'البريد الإلكتروني مسجل مسبقاً.');
            }
            json_response(false, 'بيانات مكررة: يرجى مراجعة الحقول.');
        }
        json_response(false, 'خطأ في قاعدة البيانات: ' . $e->getMessage());
    } catch (Throwable $e) {
        error_log('addDoctor Error: ' . $e->getMessage());
        json_response(false, 'خطأ غير متوقع: ' . $e->getMessage());
    }
}


// ─────────────────────────────────────────────
function updateDoctor(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

    try {
        $pdo = getDB();

        $doctorId   = (int)   ($_POST['doctor_id']        ?? 0);
        $firstName  = sanitize($_POST['first_name']        ?? '');
        $lastName   = sanitize($_POST['last_name']         ?? '');
        $email      = trim($_POST['email']                 ?? '');
        $phone      = preg_replace('/[\s\-()]+/', '', sanitize($_POST['phone'] ?? ''));
        $phone      = preg_replace('/^(\+966|00966)/', '0', $phone);
        $specId     = (int)   ($_POST['specialization_id'] ?? 0);
        $license    = sanitize($_POST['license_number']    ?? '');
        $experience = (int)   ($_POST['experience_years']  ?? 0);
        $fee        = (float) ($_POST['consultation_fee']  ?? 0);
        $bio        = sanitize($_POST['bio']               ?? '');
        $isActive   = (int)   ($_POST['is_active']         ?? 1);

        if (!$doctorId) json_response(false, 'doctor_id مطلوب.');

        $row = $pdo->prepare('SELECT user_id FROM Doctors WHERE id = ?');
        $row->execute([$doctorId]);
        $r = $row->fetch();
        if (!$r) json_response(false, 'الطبيب غير موجود.');
        $userId = (int) $r['user_id'];

        $pdo->prepare('UPDATE Users SET first_name=?, last_name=?, email=?, phone=?, is_active=? WHERE id=?')
            ->execute([$firstName, $lastName, $email, $phone ?: null, $isActive, $userId]);

        $pdo->prepare('UPDATE Doctors SET specialization_id=?, license_number=?, experience_years=?, consultation_fee=?, bio=? WHERE id=?')
            ->execute([$specId, $license, $experience, $fee, $bio, $doctorId]);

        json_response(true, 'تم تحديث بيانات الطبيب بنجاح.');

    } catch (PDOException $e) {
        error_log('updateDoctor PDO Error: ' . $e->getMessage());
        json_response(false, 'خطأ في قاعدة البيانات: ' . $e->getMessage());
    } catch (Throwable $e) {
        error_log('updateDoctor Error: ' . $e->getMessage());
        json_response(false, 'خطأ غير متوقع: ' . $e->getMessage());
    }
}

// ─────────────────────────────────────────────
// مطابق لـ DoctorController::saveSchedule — يقبل JSON body
// { days: [{day:0,morning_start:'08:00',morning_end:'17:00',is_active:1}, ...] }
function saveDoctorSchedule(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $doctorId = (int) ($_GET['doctor_id'] ?? 0);
    if (!$doctorId) json_response(false, 'doctor_id مطلوب.');

    try {
        $pdo  = getDB();
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $days = $body['days'] ?? [];
        if (empty($days)) json_response(false, 'لا توجد أيام للحفظ.');

        // Verify doctor exists
        $chk = $pdo->prepare('SELECT id FROM doctors WHERE id = ?');
        $chk->execute([$doctorId]);
        if (!$chk->fetch()) json_response(false, 'الطبيب غير موجود.');

        // Delete old schedule and re-insert (same as DoctorController)
        $pdo->prepare('DELETE FROM doctor_schedules WHERE doctor_id = ?')->execute([$doctorId]);

        $ins = $pdo->prepare('
            INSERT INTO doctor_schedules
                (doctor_id, day_of_week, shift_number, start_time, end_time, is_available, slot_duration_min)
            VALUES (?, ?, 1, ?, ?, ?, 30)
        ');

        foreach ($days as $day) {
            $dow    = (int) ($day['day']        ?? -1);
            $mStart = $day['morning_start']     ?? '08:00';
            $mEnd   = $day['morning_end']       ?? '17:00';
            $active = (int) ($day['is_active']  ?? 1);
            if ($dow < 0 || $dow > 6) continue;
            $ins->execute([$doctorId, $dow, $mStart . ':00', $mEnd . ':00', $active]);
        }

        json_response(true, 'تم حفظ جدول العمل بنجاح.');
    } catch (Throwable $e) {
        json_response(false, 'خطأ في حفظ الجدول: ' . $e->getMessage());
    }
}

// ─────────────────────────────────────────────
// مطابق لـ DoctorController::uploadAvatar — يقبل FormData (field: avatar)
function uploadDoctorAvatar(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $doctorId = (int) ($_GET['doctor_id'] ?? 0);
    if (!$doctorId) json_response(false, 'doctor_id مطلوب.');

    try {
        $pdo  = getDB();
        $file = $_FILES['avatar'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) json_response(false, 'لم يتم استلام الصورة.');

        $ext         = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowedExts))           json_response(false, 'صيغة غير مدعومة.');
        if ($file['size'] > 3 * 1024 * 1024)         json_response(false, 'الحجم يتجاوز 3 ميغا.');

        $safeExt  = ($ext === 'jpeg') ? 'jpg' : $ext;
        $filename = 'doc_' . $doctorId . '_' . time() . '.' . $safeExt;
        $dir      = __DIR__ . '/../uploads/doctors/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            json_response(false, 'فشل حفظ الصورة.');
        }

        $path = '/uploads/doctors/' . $filename;
        $pdo->prepare('UPDATE doctors SET avatar_path = ? WHERE id = ?')->execute([$path, $doctorId]);
        json_response(true, 'تم تحديث الصورة.', ['avatar_url' => $path]);
    } catch (Throwable $e) {
        json_response(false, 'خطأ في رفع الصورة: ' . $e->getMessage());
    }
}

// ─────────────────────────────────────────────
function getSpecializations(): void {
    $pdo  = getDB();
    $stmt = $pdo->query("SELECT id, name FROM Specializations ORDER BY name ASC");
    json_response(true, 'ok', ['specializations' => $stmt->fetchAll()]);
}

// ─────────────────────────────────────────────
function toggleDoctorStatus(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $pdo  = getDB();
    $body = json_decode(file_get_contents('php://input'), true) ?? [];

    // Accept either doctor_id or user_id from the request body
    $doctor_id = (int) ($body['doctor_id'] ?? $_POST['doctor_id'] ?? 0);
    $user_id   = (int) ($body['user_id']   ?? $_POST['user_id']   ?? 0);
    $is_active = isset($body['is_active']) ? (int) $body['is_active'] : null;

    if ($doctor_id) {
        // Resolve user_id from doctor_id
        $row = $pdo->prepare("SELECT user_id FROM Doctors WHERE id = ?");
        $row->execute([$doctor_id]);
        $r = $row->fetch();
        if (!$r) json_response(false, 'الطبيب غير موجود.');
        $user_id = (int) $r['user_id'];
    }

    if (!$user_id) json_response(false, 'بيانات غير كافية.');

    if ($is_active !== null) {
        $pdo->prepare("UPDATE Users SET is_active = ? WHERE id = ?")->execute([$is_active, $user_id]);
    } else {
        $pdo->prepare("UPDATE Users SET is_active = NOT is_active WHERE id = ?")->execute([$user_id]);
    }
    json_response(true, 'تم تحديث حالة الطبيب.');
}

// ─────────────────────────────────────────────
function getPatients(): void {
    $pdo  = getDB();
    $stmt = $pdo->query("
        SELECT p.id, u.first_name, u.last_name, u.email, u.phone, u.is_active, u.created_at,
               p.gender, p.blood_type,
               TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
               COUNT(a.id) AS total_appointments,
               MAX(CASE WHEN a.status = 'Completed' THEN a.appointment_date END) AS last_visit,
               0 AS has_chronic
        FROM Patients p
        JOIN Users u ON p.user_id = u.id
        LEFT JOIN Appointments a ON a.patient_id = p.id
        GROUP BY p.id
        ORDER BY u.created_at DESC
    ");
    json_response(true, 'ok', ['patients' => $stmt->fetchAll()]);
}

// ─────────────────────────────────────────────
function togglePatientStatus(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $pdo     = getDB();
    $user_id = (int) ($_POST['user_id'] ?? 0);
    if (!$user_id) json_response(false, 'رقم المستخدم مطلوب.');
    $pdo->prepare("UPDATE Users SET is_active = NOT is_active WHERE id = ?")->execute([$user_id]);
    json_response(true, 'تم تحديث حالة المريض.');
}

// ─────────────────────────────────────────────
function getReports(): void {
    $pdo    = getDB();
    $period = $_GET['period'] ?? 'week';

    $days = match ($period) {
        'today'   => 1,
        'week'    => 7,
        'month'   => 30,
        'quarter' => 90,
        'year'    => 365,
        default   => 7,
    };

    // ── KPI Summary (no triage JOIN to avoid row inflation) ──
    $kpi = $pdo->prepare("
        SELECT
            COUNT(id)                                                  AS total_appointments,
            SUM(status = 'Completed')                                  AS completed_appointments,
            SUM(status = 'Cancelled')                                  AS cancelled_appointments,
            AVG(TIMESTAMPDIFF(MINUTE, consultation_start_time,
                                      consultation_end_time))          AS avg_wait_time
        FROM appointments
        WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
    ");
    $kpi->execute([$days]);
    $kpiRow = $kpi->fetch();

    // ── Emergency cases: distinct appointments with Critical triage in period ──
    $emergencyStmt = $pdo->prepare("
        SELECT COUNT(DISTINCT tl.appointment_id) AS emergency_cases
        FROM triage_logs tl
        JOIN appointments a ON a.id = tl.appointment_id
        WHERE tl.ai_predicted_priority = 'Critical'
          AND a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
    ");
    $emergencyStmt->execute([$days]);
    $emergencyCount = (int) ($emergencyStmt->fetchColumn() ?? 0);

    $totalPatients = $pdo->query("SELECT COUNT(*) FROM Patients")->fetchColumn();

    // ── Daily stats (last 7 days fixed — chart always shows 7-day view) ──
    $dailyStmt = $pdo->prepare("
        SELECT
            DATE_FORMAT(appointment_date, '%Y-%m-%d') AS date,
            DAYNAME(appointment_date)                  AS day_name,
            COUNT(id)                                  AS count
        FROM appointments
        WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY appointment_date
        ORDER BY appointment_date ASC
    ");
    $dailyStmt->execute();
    $daily = $dailyStmt->fetchAll();


    $dayLabels = ['Sunday'=>'الأحد','Monday'=>'الإثنين','Tuesday'=>'الثلاثاء',
                  'Wednesday'=>'الأربعاء','Thursday'=>'الخميس','Friday'=>'الجمعة','Saturday'=>'السبت'];
    foreach ($daily as &$d) {
        $d['day_name'] = $dayLabels[$d['day_name']] ?? $d['day_name'];
    }
    unset($d);

    // ── Priority distribution using triage_logs ──
    $prioStmt = $pdo->prepare("
        SELECT COALESCE(tl.ai_predicted_priority, 'Routine') AS priority,
               COUNT(a.id) AS count
        FROM appointments a
        LEFT JOIN triage_logs tl ON tl.appointment_id = a.id
        WHERE a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY priority
        ORDER BY FIELD(priority, 'Critical', 'Medium', 'Routine')
    ");
    $prioStmt->execute([$days]);
    $priorities = $prioStmt->fetchAll();

    // ── Top specialties ──
    $specStmt = $pdo->prepare("
        SELECT s.name AS specialization, COUNT(a.id) AS count
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN specializations s ON d.specialization_id = s.id
        WHERE a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY s.id
        ORDER BY count DESC
        LIMIT 5
    ");
    $specStmt->execute([$days]);
    $specialties = $specStmt->fetchAll();

    // ── Top doctors ──
    $docStmt = $pdo->prepare("
        SELECT CONCAT(u.first_name, ' ', u.last_name) AS doctor_name,
               COUNT(a.id) AS count
        FROM appointments a
        JOIN doctors d ON a.doctor_id = d.id
        JOIN users u ON d.user_id = u.id
        WHERE a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        GROUP BY d.id
        ORDER BY count DESC
        LIMIT 5
    ");
    $docStmt->execute([$days]);
    $topDoctors = $docStmt->fetchAll();

    // ── Recent appointments (up to 50 — same period as KPI) ──
    $recentStmt = $pdo->prepare("
        SELECT a.appointment_date, a.status,
               COALESCE(tl.ai_predicted_priority, 'Routine') AS priority,
               CONCAT(pu.first_name,' ',pu.last_name)        AS patient_name,
               CONCAT(du.first_name,' ',du.last_name)        AS doctor_name,
               sp.name                                        AS specialization
        FROM appointments a
        JOIN patients p      ON a.patient_id = p.id
        JOIN users pu        ON p.user_id    = pu.id
        JOIN doctors d       ON a.doctor_id  = d.id
        JOIN users du        ON d.user_id    = du.id
        JOIN specializations sp ON d.specialization_id = sp.id
        LEFT JOIN triage_logs tl ON tl.appointment_id = a.id
        WHERE a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
        ORDER BY a.appointment_date DESC, a.appointment_time DESC
        LIMIT 500
    ");
    $recentStmt->execute([$days]);
    $recent = $recentStmt->fetchAll();


    json_response(true, 'ok', [
        'total_appointments'     => (int)   ($kpiRow['total_appointments']     ?? 0),
        'completed_appointments' => (int)   ($kpiRow['completed_appointments'] ?? 0),
        'cancelled_appointments' => (int)   ($kpiRow['cancelled_appointments'] ?? 0),
        'emergency_cases'        => $emergencyCount,
        'avg_wait_time'          => round((float) ($kpiRow['avg_wait_time']    ?? 0)),
        'total_patients'         => (int)   $totalPatients,
        'daily_stats'            => $daily,
        'priorities'             => $priorities,
        'specialties'            => $specialties,
        'top_doctors'            => $topDoctors,
        'recent_appointments'    => $recent,
    ]);
}

// ─────────────────────────────────────────────
function getSettings(): void {
    $pdo  = getDB();
    $row  = $pdo->query("SELECT * FROM System_Settings WHERE id = 1")->fetch();
    json_response(true, 'ok', ['settings' => $row]);
}

// ─────────────────────────────────────────────
function saveSettings(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $pdo = getDB();

    $fields = [
        'hospital_name'                  => sanitize($_POST['hospital_name'] ?? 'شفاء+'),
        'default_language'               => sanitize($_POST['default_language'] ?? 'ar'),
        'timezone'                       => sanitize($_POST['timezone'] ?? 'Asia/Riyadh'),
        'maintenance_mode'               => isset($_POST['maintenance_mode']) ? 1 : 0,
        'ai_triage_enabled'              => isset($_POST['ai_triage_enabled']) ? 1 : 0,
        'critical_pain_threshold'        => (int) ($_POST['critical_pain_threshold'] ?? 8),
        'require_manual_triage_approval' => isset($_POST['require_manual_triage_approval']) ? 1 : 0,
        'default_consultation_minutes'   => (int) ($_POST['default_consultation_minutes'] ?? 30),
        'allow_telehealth'               => isset($_POST['allow_telehealth']) ? 1 : 0,
        'patient_reminder_hours'         => (int) ($_POST['patient_reminder_hours'] ?? 24),
    ];

    $sets   = implode(', ', array_map(fn($k) => "`$k` = ?", array_keys($fields)));
    $values = array_values($fields);
    $pdo->prepare("UPDATE System_Settings SET $sets WHERE id = 1")->execute($values);

    json_response(true, 'تم حفظ الإعدادات بنجاح.');
}

// ─────────────────────────────────────────────
function getUserPermissions(): void {
    $pdo  = getDB();
    $stmt = $pdo->query("
        SELECT u.id, u.first_name, u.last_name, u.email, u.is_active, r.name AS role
        FROM Users u
        JOIN Roles r ON u.role_id = r.id
        ORDER BY r.id ASC, u.created_at DESC
    ");
    json_response(true, 'ok', ['users' => $stmt->fetchAll()]);
}

// ─────────────────────────────────────────────
function updatePermission(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $pdo     = getDB();
    $user_id = (int) ($_POST['user_id']  ?? 0);
    $role_id = (int) ($_POST['role_id']  ?? 0);
    if (!$user_id || !$role_id) json_response(false, 'بيانات غير صحيحة.');
    $pdo->prepare("UPDATE Users SET role_id = ? WHERE id = ?")->execute([$role_id, $user_id]);
    json_response(true, 'تم تحديث صلاحية المستخدم.');
}
