/**
 * notif-badge.js — تحديث ديناميكي لعداد الإشعارات في هيدر جميع صفحات الدكتور
 * يُجلب عدد الحالات الحرجة النشطة ويعرضها على أيقونة الجرس
 */
(function () {
    const API = '/controllers/DoctorController.php';
    const badge = document.getElementById('notifBadge');
    if (!badge) return;

    fetch(`${API}?action=today_queue`)
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.queue) return;
            const critical = data.queue.filter(p =>
                p.priority === 'Critical' && !['Completed', 'Cancelled'].includes(p.status)
            ).length;
            if (critical > 0) {
                badge.textContent = critical;
                badge.style.display = '';
            } else {
                badge.style.display = 'none';
            }
        })
        .catch(() => { /* صامت عند فشل الاتصال */ });
})();
