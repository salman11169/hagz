<?php
/**
 * NotificationController — Patient notifications API
 * Hagz Clinic System
 */

ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

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
    'list'      => listNotifications($patient_id),
    'mark_read' => markRead($patient_id),
    'unread_count' => unreadCount($patient_id),
    default     => json_response(false, 'طلب غير معروف.')
};

// ─────────────────────────────────────────────
function listNotifications(int $patient_id): void
{
    if (!$patient_id) { json_response(false, 'لم يتم تحديد المريض.'); }
    $pdo  = getDB();
    $stmt = $pdo->prepare("
        SELECT id, type, title, message, is_read, created_at
        FROM Notifications
        WHERE patient_id = ?
        ORDER BY created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$patient_id]);
    json_response(true, 'ok', ['notifications' => $stmt->fetchAll()]);
}

// ─────────────────────────────────────────────
function markRead(int $patient_id): void
{
    if (!$patient_id) { json_response(false, 'لم يتم تحديد المريض.'); }
    $pdo = getDB();

    // Mark a specific notification or all
    $body = json_decode(file_get_contents('php://input'), true);
    $notifId = (int)($body['notification_id'] ?? 0);

    if ($notifId) {
        $pdo->prepare("UPDATE Notifications SET is_read = 1 WHERE id = ? AND patient_id = ?")
            ->execute([$notifId, $patient_id]);
    } else {
        $pdo->prepare("UPDATE Notifications SET is_read = 1 WHERE patient_id = ?")
            ->execute([$patient_id]);
    }

    json_response(true, 'تم تحديث حالة الإشعار.');
}

// ─────────────────────────────────────────────
function unreadCount(int $patient_id): void
{
    if (!$patient_id) { json_response(false, 'لم يتم تحديد المريض.'); }
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Notifications WHERE patient_id = ? AND is_read = 0");
    $stmt->execute([$patient_id]);
    json_response(true, 'ok', ['count' => (int)$stmt->fetchColumn()]);
}
