<?php
/**
 * BookingController — AI-powered appointment booking for patients
 * Hagz Clinic System
 */

ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/ai.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../services/TriageAI.php';

// Global exception handler
set_exception_handler(function (Throwable $e) {
    while (ob_get_level() > 0) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'خطأ في الخادم: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit();
});

if (!is_patient()) {
    json_response(false, 'غير مصرح.', ['redirect' => '/auth/login.php']);
}

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
    'specializations' => getSpecializations(),
    'doctors'         => getDoctors(),
    'slots'           => getAvailableSlots(),
    'triage'          => triagePatient($patient_id),
    'book'            => bookAppointment($patient_id),
    default           => json_response(false, 'طلب غير معروف.')
};

// ─────────────────────────────────────────────
function getSpecializations(): void {
    $pdo  = getDB();
    $stmt = $pdo->query("SELECT id, name, icon FROM Specializations WHERE status = 'Active' ORDER BY name");
    json_response(true, 'ok', ['specializations' => $stmt->fetchAll()]);
}

// ─────────────────────────────────────────────
function getDoctors(): void {
    $spec = sanitize($_GET['spec'] ?? '');
    $pdo  = getDB();

    if ($spec) {
        $stmt = $pdo->prepare("
            SELECT d.id, CONCAT(u.first_name, ' ', u.last_name) AS name,
                   s.name AS specialization, s.id AS spec_id,
                   d.experience_years, d.consultation_fee, d.bio
            FROM Doctors d
            JOIN Users u ON d.user_id = u.id
            JOIN Specializations s ON d.specialization_id = s.id
            WHERE s.name = ? AND u.is_active = 1
            ORDER BY d.experience_years DESC
        ");
        $stmt->execute([$spec]);
    } else {
        $stmt = $pdo->query("
            SELECT d.id, CONCAT(u.first_name, ' ', u.last_name) AS name,
                   s.name AS specialization, s.id AS spec_id,
                   d.experience_years, d.consultation_fee
            FROM Doctors d
            JOIN Users u ON d.user_id = u.id
            JOIN Specializations s ON d.specialization_id = s.id
            WHERE u.is_active = 1
            ORDER BY s.name, d.experience_years DESC
        ");
    }
    json_response(true, 'ok', ['doctors' => $stmt->fetchAll()]);
}

// ─────────────────────────────────────────────
function getAvailableSlots(): void {
    $doctor_id = (int)($_GET['doctor_id'] ?? 0);
    $date      = sanitize($_GET['date'] ?? '');

    if (!$doctor_id || !$date) {
        json_response(false, 'يرجى تحديد الطبيب والتاريخ.');
    }

    $dow = (int)date('w', strtotime($date));
    $pdo = getDB();

    $sch = $pdo->prepare("
        SELECT start_time, end_time, slot_duration_min
        FROM doctor_schedules
        WHERE doctor_id = ? AND day_of_week = ? AND is_available = 1
        ORDER BY shift_number ASC
    ");
    $sch->execute([$doctor_id, $dow]);
    $shifts = $sch->fetchAll();

    if (!$shifts) {
        json_response(true, 'ok', ['slots' => [], 'message' => 'الطبيب غير متاح في هذا اليوم.']);
    }

    $booked = $pdo->prepare("
        SELECT appointment_time FROM appointments
        WHERE doctor_id = ? AND appointment_date = ?
          AND status NOT IN ('Cancelled')
    ");
    $booked->execute([$doctor_id, $date]);
    $bookedTimes = array_column($booked->fetchAll(), 'appointment_time');

    $slots = [];
    $now   = time();

    foreach ($shifts as $shift) {
        $stepMin = max(10, (int)$shift['slot_duration_min']);
        $step  = $stepMin * 60;
        $start = strtotime($date . ' ' . $shift['start_time']);
        $end   = strtotime($date . ' ' . $shift['end_time']);

        while ($start < $end) {
            $t       = date('H:i', $start);
            $slots[] = [
                'time'     => $t,
                'label'    => date('h:i A', $start),
                'duration' => $stepMin,
                'booked'   => in_array($t . ':00', $bookedTimes)
                           || ($date === date('Y-m-d') && $start <= $now),
            ];
            $start += $step;
        }
    }

    json_response(true, 'ok', ['slots' => $slots]);
}

// ════════════════════════════════════════════════════════════════════
// AI TRIAGE — Core engine (حجز ذكي فقط)
// ════════════════════════════════════════════════════════════════════
function triagePatient(int $patient_id): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'طلب غير صالح.');
    }

    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) {
        json_response(false, 'بيانات غير صالحة.');
    }

    $symptoms   = $body['symptoms']   ?? [];
    $pain       = (int)($body['pain_level'] ?? 5);
    $duration   = sanitize($body['duration'] ?? '');
    $conditions = $body['conditions'] ?? [];
    $notes      = sanitize($body['notes'] ?? '');
    $emergency  = (bool)($body['emergency'] ?? false);

    // ── Step 1: Classify priority + specialty ────────────────────────
    if ($emergency) {
        $triage = [
            'priority'   => 'Critical',
            'specialty'  => 'طب طوارئ',
            'wait_time'  => 'فوري — توجه للطوارئ الآن',
            'reasoning'  => 'طلب طوارئ مباشر من المريض.',
            'confidence' => 1.0,
            'source'     => 'emergency_override',
        ];
    } else {
        $triage = TriageAI::classify($symptoms, $pain, $duration, $conditions, $notes);
    }

    $priority  = $triage['priority'];
    $specialty = $triage['specialty'];
    $pdo       = getDB();

    // ── Step 2: Load patient history (chronic diseases + medical records) ──
    $cdStmt = $pdo->prepare("SELECT disease_name, diagnosed_date FROM chronic_diseases WHERE patient_id = ?");
    $cdStmt->execute([$patient_id]);
    $chronicDiseases = $cdStmt->fetchAll();

    $mhStmt = $pdo->prepare("
        SELECT a.appointment_date, mr.diagnosis, mr.doctor_notes
        FROM medical_records mr
        JOIN appointments a ON mr.appointment_id = a.id
        WHERE a.patient_id = ?
        ORDER BY a.appointment_date DESC
        LIMIT 5
    ");
    $mhStmt->execute([$patient_id]);
    $medicalHistory = $mhStmt->fetchAll();

    $patientContext = [
        'symptoms'         => $symptoms,
        'pain_level'       => $pain,
        'duration'         => $duration,
        'conditions'       => $conditions,
        'notes'            => $notes,
        'chronic_diseases' => $chronicDiseases,
        'medical_history'  => $medicalHistory,
    ];

    // ── Step 3: Load doctors with full schedules + appointments (3 days) ──
    $docs = $pdo->prepare("
        SELECT d.id AS doctor_id, CONCAT(u.first_name, ' ', u.last_name) AS name,
               d.experience_years
        FROM Doctors d
        JOIN Users u ON d.user_id = u.id
        JOIN Specializations s ON d.specialization_id = s.id
        WHERE s.name = ? AND u.is_active = 1
        ORDER BY d.experience_years DESC
    ");
    $docs->execute([$specialty]);
    $doctors = $docs->fetchAll();

    // Fallback to طب عام if specialty not found
    if (!$doctors) {
        $docs2 = $pdo->query("
            SELECT d.id AS doctor_id, CONCAT(u.first_name, ' ', u.last_name) AS name,
                   d.experience_years
            FROM Doctors d
            JOIN Users u ON d.user_id = u.id
            JOIN Specializations s ON d.specialization_id = s.id
            WHERE s.name = 'طب عام' AND u.is_active = 1
            ORDER BY d.experience_years DESC LIMIT 3
        ");
        $doctors = $docs2->fetchAll();
        if ($doctors) $triage['specialty'] = 'طب عام';
    }

    // Enrich each doctor with their schedule and upcoming appointments (14 days context)
    $today      = date('Y-m-d');
    $contextEnd = date('Y-m-d', strtotime('+14 days'));
    foreach ($doctors as &$doc) {
        $docId = $doc['doctor_id'];

        // Working schedule (all shifts)
        $schStmt = $pdo->prepare("
            SELECT day_of_week, shift_number, start_time, end_time, slot_duration_min
            FROM doctor_schedules
            WHERE doctor_id = ? AND is_available = 1
            ORDER BY day_of_week, shift_number
        ");
        $schStmt->execute([$docId]);
        $doc['schedule'] = $schStmt->fetchAll();

        // Upcoming appointments (3 days) + priority from triage_logs
        $apptStmt = $pdo->prepare("
            SELECT a.id, a.appointment_date, a.appointment_time, a.booking_type,
                   COALESCE(tl.ai_predicted_priority, 'Routine') AS priority,
                   CONCAT(u.first_name, ' ', u.last_name) AS patient_name
            FROM appointments a
            JOIN patients p  ON a.patient_id = p.id
            JOIN users u     ON p.user_id    = u.id
            LEFT JOIN triage_logs tl ON tl.appointment_id = a.id
            WHERE a.doctor_id = ?
              AND a.appointment_date >= ?
              AND a.appointment_date <= ?
              AND a.status NOT IN ('Cancelled', 'Completed')
            ORDER BY a.appointment_date ASC, a.appointment_time ASC
        ");
        $apptStmt->execute([$docId, $today, $contextEnd]);
        $doc['appointments'] = $apptStmt->fetchAll();
    }
    unset($doc);

    // ── Step 4: Ask AI to schedule (with full patient context) ────────
    $aiDecision = TriageAI::scheduleWithAI($priority, $doctors, $patientContext);

    // Fallback if AI fails
    if (!$aiDecision) {
        $slot = findBestSlot($triage['specialty'], $priority);
        if ($slot['doctor_id']) {
            $aiDecision = [
                'doctor_id'    => $slot['doctor_id'],
                'date'         => $slot['date'],
                'time'         => $slot['time'],
                'reasoning'    => 'تم الاختيار تلقائياً (الـ AI غير متاح).',
                'ai_summary'   => null,
                'ai_reasoning' => null,
                'reschedule'   => [],
            ];
        }
    }

    // ── Step 5: Apply rescheduling decisions ─────────────────────────
    $notifiedPatients = [];
    if ($aiDecision && !empty($aiDecision['reschedule'])) {
        foreach ($aiDecision['reschedule'] as $re) {
            $apptId  = (int)($re['appointment_id'] ?? 0);
            $newDate = sanitize($re['new_date'] ?? '');
            $newTime = sanitize($re['new_time'] ?? '');

            if (!$apptId || !$newDate || !$newTime) continue;

            // Validate the appointment exists and belongs to this doctor
            $chk = $pdo->prepare("
                SELECT a.id, a.patient_id, a.appointment_date, a.appointment_time,
                       CONCAT(u.first_name, ' ', u.last_name) AS patient_name
                FROM Appointments a
                JOIN Patients p ON a.patient_id = p.id
                JOIN Users u ON p.user_id = u.id
                WHERE a.id = ? AND a.doctor_id = ? AND a.status NOT IN ('Cancelled','Completed')
            ");
            $chk->execute([$apptId, $aiDecision['doctor_id']]);
            $existing = $chk->fetch();

            if (!$existing) continue;

            // Validate new date/time against doctor's actual schedule
            $newDow = (int)date('w', strtotime($newDate));
            $schChk = $pdo->prepare("
                SELECT start_time, end_time FROM Doctor_Schedules
                WHERE doctor_id = ? AND day_of_week = ? AND is_available = 1
                LIMIT 1
            ");
            $schChk->execute([$aiDecision['doctor_id'], $newDow]);
            $sch = $schChk->fetch();

            // If doctor doesn't work that day, or time is out of range — find a safe slot
            if (!$sch) {
                // الطبيب لا يعمل ذلك اليوم — ابحث في نفس اليوم ثم الأيام التالية
                $safeSlot = findFirstFreeSlot($pdo, (int)$aiDecision['doctor_id'], $newDate, 7);
                if ($safeSlot) { $newDate = $safeSlot['date']; $newTime = $safeSlot['time']; }
                else continue;
            } else {
                $newTs    = strtotime($newDate . ' ' . $newTime);
                $startTs  = strtotime($newDate . ' ' . $sch['start_time']);
                $endTs    = strtotime($newDate . ' ' . $sch['end_time']);
                if ($newTs < $startTs || $newTs >= $endTs) {
                    // الوقت خارج دوام الطبيب — ابحث في نفس اليوم أولاً
                    $safeSlot = findFirstFreeSlot($pdo, (int)$aiDecision['doctor_id'], $newDate, 7);
                    if ($safeSlot) { $newDate = $safeSlot['date']; $newTime = $safeSlot['time']; }
                    else continue;
                }
            }

            // Apply validated new date/time
            $pdo->prepare("
                UPDATE Appointments
                SET appointment_date = ?, appointment_time = ?
                WHERE id = ?
            ")->execute([$newDate, $newTime . ':00', $apptId]);

            // Create notification for affected patient
            $patientIdAffected = $existing['patient_id'];
            if (!in_array($patientIdAffected, $notifiedPatients)) {
                $message = "تم تعديل موعدك بسبب حالة طارئة. الموعد الجديد: {$newDate} الساعة {$newTime}.";

                $pdo->prepare("
                    INSERT INTO Notifications (patient_id, type, title, message)
                    VALUES (?, 'rescheduled', 'تم تعديل موعدك', ?)
                ")->execute([$patientIdAffected, $message]);

                $notifiedPatients[] = $patientIdAffected;
            }
        }
    }

    // Build slot info for response
    $slot = $aiDecision ? [
        'doctor_id'   => $aiDecision['doctor_id'],
        'doctor_name' => collect_doctor_name($pdo, (int)$aiDecision['doctor_id']),
        'date'        => $aiDecision['date'],
        'time'        => $aiDecision['time'],
        'reasoning'   => $aiDecision['reasoning'] ?? '',
        'rescheduled' => count($notifiedPatients),
    ] : ['doctor_id' => null, 'doctor_name' => null, 'date' => null, 'time' => null];

    // Pass ai_summary and ai_reasoning to response (stored later in bookAppointment)
    $triage['ai_summary']   = $aiDecision['ai_summary']   ?? null;
    $triage['ai_reasoning'] = $aiDecision['ai_reasoning'] ?? null;

    json_response(true, 'تم التصنيف.', [
        'triage'  => $triage,
        'slot'    => $slot,
    ]);
}

/** Helper: get doctor display name */
function collect_doctor_name(\PDO $pdo, int $docId): string
{
    $s = $pdo->prepare("SELECT CONCAT(u.first_name,' ',u.last_name) FROM Doctors d JOIN Users u ON d.user_id=u.id WHERE d.id=?");
    $s->execute([$docId]);
    return $s->fetchColumn() ?: 'طبيب غير معروف';
}


/**
 * Find the best available slot for a new patient, respecting appointment priorities.
 * Fallback when AI is not available.
 *
 * Logic:
 *  - Critical  → earliest possible slot starting TODAY
 *  - Medium    → search starts the day AFTER the last Critical appointment
 *  - Routine   → search starts the day AFTER the last Critical OR Medium appointment
 */
function findBestSlot(string $specialty, string $priority): array
{
    $pdo = getDB();

    // 1. Get available doctors by specialty
    $docs = $pdo->prepare("
        SELECT d.id AS doctor_id, CONCAT(u.first_name, ' ', u.last_name) AS name,
               d.experience_years
        FROM Doctors d
        JOIN Users u ON d.user_id = u.id
        JOIN Specializations s ON d.specialization_id = s.id
        WHERE s.name = ? AND u.is_active = 1
        ORDER BY d.experience_years DESC
    ");
    $docs->execute([$specialty]);
    $doctors = $docs->fetchAll();

    if (!$doctors) {
        // Fallback: طب عام
        $docs2 = $pdo->query("
            SELECT d.id AS doctor_id, CONCAT(u.first_name, ' ', u.last_name) AS name,
                   d.experience_years
            FROM Doctors d
            JOIN Users u ON d.user_id = u.id
            JOIN Specializations s ON d.specialization_id = s.id
            WHERE s.name = 'طب عام' AND u.is_active = 1
            ORDER BY d.experience_years DESC
            LIMIT 3
        ");
        $doctors = $docs2->fetchAll();
    }

    if (!$doctors) {
        return ['doctor_id' => null, 'doctor_name' => null, 'date' => null, 'time' => null];
    }

    // 2. Search for the first free slot — no day limit, priority determines start date
    $maxDays = 30;

    foreach ($doctors as $doc) {
        $docId      = $doc['doctor_id'];
        $startDate  = getSearchStartDate($pdo, $docId, $priority);
        $slot       = findFirstFreeSlot($pdo, $docId, $startDate, $maxDays);

        if ($slot) {
            return [
                'doctor_id'   => $docId,
                'doctor_name' => $doc['name'],
                'date'        => $slot['date'],
                'time'        => $slot['time'],
                'time_label'  => $slot['time_label'],
            ];
        }
    }

    return [
        'doctor_id'   => $doctors[0]['doctor_id'],
        'doctor_name' => $doctors[0]['name'],
        'date'        => null,
        'time'        => null,
        'message'     => 'لا توجد مواعيد خالية في الفترة المحددة، سيتم التواصل معك.',
    ];
}

/**
 * Determine the earliest date we may START placing a new patient,
 * based on existing appointments' priorities.
 */
function getSearchStartDate(\PDO $pdo, int $docId, string $newPriority): string
{
    $today = date('Y-m-d');

    if ($newPriority === 'Critical') {
        return $today;
    }

    $blockers     = ($newPriority === 'Medium') ? ['Critical'] : ['Critical', 'Medium'];
    $placeholders = implode(',', array_fill(0, count($blockers), '?'));

    $stmt = $pdo->prepare("
        SELECT MAX(a.appointment_date) AS last_date
        FROM appointments a
        LEFT JOIN triage_logs tl ON tl.appointment_id = a.id
        WHERE a.doctor_id = ?
          AND COALESCE(tl.ai_predicted_priority, 'Routine') IN ($placeholders)
          AND a.appointment_date >= ?
          AND a.status NOT IN ('Cancelled')
    ");
    $stmt->execute(array_merge([$docId], $blockers, [$today]));
    $row = $stmt->fetch();

    if ($row && $row['last_date']) {
        return date('Y-m-d', strtotime($row['last_date'] . ' +1 day'));
    }

    return $today;
}

/**
 * Find the first available (free) slot for a doctor,
 * starting from $startDate and looking up to $maxDays days ahead.
 */
function findFirstFreeSlot(\PDO $pdo, int $docId, string $startDate, int $maxDays): ?array
{
    $now     = time();
    $current = strtotime($startDate);
    $limit   = strtotime("+{$maxDays} days");

    while ($current <= $limit) {
        $dateStr = date('Y-m-d', $current);
        $dow     = (int)date('w', $current);

        $sch = $pdo->prepare("
            SELECT start_time, end_time, slot_duration_min
            FROM doctor_schedules
            WHERE doctor_id = ? AND day_of_week = ? AND is_available = 1
            ORDER BY shift_number ASC
        ");
        $sch->execute([$docId, $dow]);
        $shifts = $sch->fetchAll();

        if ($shifts) {
            $booked = $pdo->prepare("
                SELECT appointment_time FROM appointments
                WHERE doctor_id = ? AND appointment_date = ?
                  AND status NOT IN ('Cancelled')
            ");
            $booked->execute([$docId, $dateStr]);
            $bookedTimes = array_column($booked->fetchAll(), 'appointment_time');

            foreach ($shifts as $shift) {
                $step      = max(10, (int)$shift['slot_duration_min']) * 60;
                $slotStart = strtotime($dateStr . ' ' . $shift['start_time']);
                $slotEnd   = strtotime($dateStr . ' ' . $shift['end_time']);

                while ($slotStart < $slotEnd) {
                    $t      = date('H:i', $slotStart);
                    $tFull  = $t . ':00';
                    $isPast = ($dateStr === date('Y-m-d') && $slotStart <= $now);

                    if (!in_array($tFull, $bookedTimes) && !$isPast) {
                        return [
                            'date'       => $dateStr,
                            'time'       => $t,
                            'time_label' => date('h:i A', $slotStart),
                        ];
                    }
                    $slotStart += $step;
                }
            }
        }

        $current = strtotime('+1 day', $current);
    }

    return null;
}

// ─────────────────────────────────────────────
function bookAppointment(int $patient_id): void
{
    if (!$patient_id || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'طلب غير صالح.');
    }

    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) {
        json_response(false, 'بيانات غير صالحة.');
    }

    $doctor_id   = (int)($body['doctor_id']   ?? 0);
    $date        = sanitize($body['date']       ?? '');
    $time        = sanitize($body['time']       ?? '');
    $visit_type  = sanitize($body['visit_type'] ?? 'In-person');
    $priority    = sanitize($body['priority']   ?? 'Routine');
    $symptoms    = $body['symptoms']            ?? [];
    $pain_level  = (int)($body['pain_level']   ?? 5);
    $notes       = sanitize($body['notes']      ?? '');
    $duration    = sanitize($body['duration']   ?? '');
    $conditions  = $body['conditions']          ?? [];
    $ai_reasoning   = sanitize($body['ai_reasoning'] ?? '');
    $ai_summary     = sanitize($body['ai_summary']   ?? '');
    $ai_confidence  = (float)($body['ai_confidence'] ?? 0.5);
    $ai_specialty   = sanitize($body['ai_specialty']  ?? '');

    if (!$doctor_id || !$date || !$time) {
        json_response(false, 'بيانات الحجز غير مكتملة.');
    }
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        json_response(false, 'لا يمكن الحجز في تاريخ ماضٍ.');
    }

    $dbPriority = match($priority) {
        'Critical', 'حرجة'   => 'Critical',
        'Medium',   'عاجلة'  => 'Medium',
        default               => 'Routine',
    };
    $dbVisit = match($visit_type) {
        'حضوري', 'In-person' => 'In-person',
        default               => 'Telehealth',
    };

    $pdo         = getDB();
    // booking_mode يُرسل صريحاً من الـ frontend ('smart' | 'regular')
    $bookingMode = sanitize($body['booking_mode'] ?? '');
    // إذا كان booking_mode صريحاً → يُطبَّق بدون تردد
    // إذا لم يُرسل → نحكم من وجود الأعراض (backward compat)
    if ($bookingMode === 'regular') {
        $bookingType = 'regular';
    } elseif ($bookingMode === 'smart') {
        $bookingType = 'smart';
    } else {
        $bookingType = !empty($symptoms) ? 'smart' : 'regular';
    }

    // ── فحص 1: الوقت محجوز عند هذا الطبيب ──────────────────────────
    $check = $pdo->prepare("
        SELECT id FROM appointments
        WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ?
          AND status NOT IN ('Cancelled')
        LIMIT 1
    ");
    $check->execute([$doctor_id, $date, $time . ':00']);
    if ($check->fetch()) {
        json_response(false, 'هذا الوقت محجوز بالفعل عند هذا الطبيب، يرجى اختيار وقت آخر.');
    }

    // ── فحص 2: المريض لديه موعد بنفس اليوم ونفس الوقت عند أي طبيب ──
    $sameTime = $pdo->prepare("
        SELECT id FROM appointments
        WHERE patient_id = ? AND appointment_date = ? AND appointment_time = ?
          AND status NOT IN ('Cancelled')
        LIMIT 1
    ");
    $sameTime->execute([$patient_id, $date, $time . ':00']);
    if ($sameTime->fetch()) {
        json_response(false, 'لديك موعد آخر بنفس اليوم ونفس الوقت. لا يمكن الحجز المتزامن.');
    }

    // ── فحص 3: المريض لديه موعد عند نفس الطبيب في نفس اليوم ──────────
    $sameDoc = $pdo->prepare("
        SELECT id FROM appointments
        WHERE patient_id = ? AND doctor_id = ? AND appointment_date = ?
          AND status NOT IN ('Cancelled')
        LIMIT 1
    ");
    $sameDoc->execute([$patient_id, $doctor_id, $date]);
    if ($sameDoc->fetch()) {
        json_response(false, 'لديك موعد مسبق عند هذا الطبيب في نفس اليوم. لا يمكن الحجز مرتين لنفس الطبيب في اليوم الواحد.');
    }

    // Insert appointment
    $ins = $pdo->prepare("
        INSERT INTO appointments
            (patient_id, doctor_id, appointment_date, appointment_time, status, booking_type, visit_type, notes)
        VALUES (?, ?, ?, ?, 'Pending', ?, ?, ?)
    ");
    $ins->execute([$patient_id, $doctor_id, $date, $time . ':00', $bookingType, $dbVisit, $notes ?: null]);
    $appt_id = (int)$pdo->lastInsertId();

    // Triage log — يُحفظ للذكي دائماً، وللعادي عند وجود أعراض أو ملاحظات
    if ($bookingType === 'smart' || !empty($symptoms) || !empty($notes)) {
        $triageData = [
            'symptoms'   => $symptoms,
            'pain_level' => $pain_level,
            'duration'   => $duration,
            'conditions' => $conditions,
            'notes'      => $notes,
        ];
        $triageStmt = $pdo->prepare("
            INSERT INTO Triage_Logs
                (appointment_id, raw_symptoms_input, ai_predicted_priority,
                 algorithm_confidence_score, ai_summary, ai_reasoning,
                 scheduled_date, scheduled_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $triageStmt->execute([
            $appt_id,
            json_encode($triageData, JSON_UNESCAPED_UNICODE),
            $dbPriority,
            round($ai_confidence * 100, 2),
            $ai_summary  ?: null,
            $ai_reasoning ?: null,
            $date,
            $time . ':00',
        ]);
    }

    $ref = 'APT-' . str_pad($appt_id, 6, '0', STR_PAD_LEFT);

    json_response(true, 'تم الحجز بنجاح!', [
        'appointment_id' => $appt_id,
        'ref'            => $ref,
        'date'           => $date,
        'time'           => $time,
        'priority'       => $dbPriority,
        'doctor_id'      => $doctor_id,
    ]);
}
