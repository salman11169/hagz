<?php
require_once __DIR__ . '/../includes/session.php';
require_role(ROLE_PATIENT);
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'المريض', ENT_QUOTES, 'UTF-8');
$activeNav = 'notif';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإشعارات - شفاء+</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="../assets/css/patient.css?v=18.0">
</head>

<body>

    <?php include 'partials/patient-nav.php'; ?>

    <main class="p-main" id="mainContent">
        <div class="container-fluid py-4">

            <!-- Hero -->
            <div class="dash-hero mb-4">
                <div class="dh-text">
                    <h1><i class='bx bx-bell'></i> الإشعارات</h1>
                    <p id="headerSub">جاري التحميل...</p>
                </div>
                <div class="dh-action d-none d-md-block">
                    <button
                        class="hero-btn border-0 py-2 px-4 rounded-pill bg-white text-primary fw-bold fs-sm shadow-sm"
                        onclick="markAll()">
                        <i class='bx bx-check-double'></i> تحديد الكل كمقروء
                    </button>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="p-card border-0 shadow-sm p-4 mb-4 rounded-4 bg-white">

                        <!-- Search & Filters -->
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                            <!-- Filter bar -->
                            <div class="d-flex flex-nowrap overflow-x-auto gap-2 w-100 flex-grow-1 pb-2 scrollbar-hide">
                                <button class="p-filter-pill active ft border-0 py-2 fw-bold text-nowrap px-4 fs-sm rounded-pill flex-shrink-0" data-filter="all">الكل</button>
                                <button class="p-filter-pill ft border-0 py-2 fw-bold text-nowrap px-4 fs-sm rounded-pill flex-shrink-0" data-filter="unread">غير مقروء</button>
                                <button class="p-filter-pill ft border-0 py-2 fw-bold text-nowrap px-4 fs-sm rounded-pill flex-shrink-0" data-filter="rescheduled">تعديل مواعيد</button>
                                <button class="p-filter-pill ft border-0 py-2 fw-bold text-nowrap px-4 fs-sm rounded-pill flex-shrink-0" data-filter="reminder">تذكيرات</button>
                            </div>
                            <!-- Mobile Action -->
                            <div class="d-md-none w-100 mt-2">
                                <button
                                    class="w-100 p-btn p-btn-primary rounded-pill py-2 fw-bold fs-sm border-0 d-flex justify-content-center gap-2"
                                    onclick="markAll()">
                                    <i class='bx bx-check-double'></i> تحديد الكل كمقروء
                                </button>
                            </div>
                        </div>

                        <!-- List -->
                        <div id="notifList" class="d-flex flex-column gap-3">
                            <div class="text-center p-5 text-muted-p fs-5 border rounded-4 bg-light">
                                <i class='bx bx-loader-alt bx-spin fs-1 mb-2 text-primary'></i>
                                <p class="fw-bold mb-0">جاري تحميل الإشعارات...</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </main>

    <div id="hagzUiContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/hagz-ui.js"></script>
    <script>
        const NOTIF_API = '../controllers/NotificationController.php';
        let allNotifs = [], currentFilter = 'all';

        function timeAgo(dateStr) {
            const d = new Date(dateStr), now = new Date();
            const diff = Math.floor((now - d) / 1000);
            if (diff < 60) return 'الآن';
            if (diff < 3600) return 'منذ ' + Math.floor(diff / 60) + ' دقيقة';
            if (diff < 86400) return 'منذ ' + Math.floor(diff / 3600) + ' ساعة';
            return 'منذ ' + Math.floor(diff / 86400) + ' يوم';
        }

        function iconFor(type) {
            const map = {
                rescheduled: { bg: 'bg-warning text-dark', icon: 'bx-calendar-edit' },
                reminder: { bg: 'bg-info text-white', icon: 'bx-alarm' },
                cancelled: { bg: 'bg-danger text-white', icon: 'bx-calendar-x' },
            };
            return map[type] || { bg: 'bg-primary text-white', icon: 'bx-bell' };
        }

        function renderNotifs(notifs) {
            const el = document.getElementById('notifList');
            if (!notifs.length) {
                el.innerHTML = '<div class="text-center p-5 text-muted-p bg-light rounded-4 border"><i class="bx bx-bell-off display-1 mb-3 opacity-25"></i><h5 class="fw-bold">لا توجد إشعارات في هذه الفئة</h5><p class="fs-sm mb-0">لا يوجد شيء جديد لعرضه حالياً.</p></div>';
                return;
            }

            el.innerHTML = notifs.map(n => {
                const icon = iconFor(n.type);
                const unread = (n.is_read == 0);

                return '<div class="p-3 bg-white shadow-sm position-relative rounded-4 border transition-all cursor-pointer ' + (unread ? 'border-primary border-2 shadow-md' : 'border-light') + '" data-id="' + n.id + '" onclick="markRead(' + n.id + ', this)">' +
                    (unread ? '<span class="position-absolute top-0 start-0 translate-middle p-2 bg-danger border border-light rounded-circle ms-2 mt-2"></span>' : '') +
                    '<div class="d-flex gap-3 align-items-start">' +
                    '<div class="pane-icon-sm ' + icon.bg + ' rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fs-5"><i class="bx ' + icon.icon + '"></i></div>' +
                    '<div class="flex-grow-1 w-100 overflow-hidden">' +
                    '<div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mb-1 gap-1">' +
                    '<h6 class="fw-bold mb-0 text-dark text-truncate">' + n.title + '</h6>' +
                    '<span class="text-muted-p fs-xs fw-bold text-nowrap"><i class="bx bx-time-five"></i> ' + timeAgo(n.created_at) + '</span>' +
                    '</div>' +
                    '<p class="mb-0 text-muted-p fs-sm lh-base">' + n.message + '</p>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            }).join('');
        }

        function applyFilter(filter) {
            currentFilter = filter;
            let list = allNotifs;
            if (filter === 'unread') list = allNotifs.filter(n => n.is_read == 0);
            else if (filter !== 'all') list = allNotifs.filter(n => n.type === filter);
            renderNotifs(list);
        }

        function loadNotifications() {
            fetch(NOTIF_API + '?action=list')
                .then(r => r.json())
                .then(data => {
                    if (!data.success) { showError(); return; }
                    allNotifs = data.notifications || [];
                    const unread = allNotifs.filter(n => n.is_read == 0).length;
                    document.getElementById('headerSub').textContent =
                        unread > 0 ? 'لديك ' + unread + ' إشعار غير مقروء' : 'جميع الإشعارات مقروءة';

                    const badge = document.querySelector('.badge.bg-danger');
                    if (badge) {
                        badge.textContent = unread;
                        if (unread === 0) badge.classList.add('d-none'); else badge.classList.remove('d-none');
                    }
                    applyFilter(currentFilter);
                })
                .catch(showError);
        }

        function showError() {
            document.getElementById('notifList').innerHTML =
                '<div class="text-center p-5 text-danger bg-light rounded-4 border border-danger border-opacity-25"><i class="bx bx-error display-1 mb-3 opacity-25"></i><h5 class="fw-bold fs-5">فشل تحميل الإشعارات</h5><p class="fs-sm mb-0">يرجى المحاولة مرة أخرى لاحقاً.</p></div>';
        }

        function markRead(id, el) {
            if (!el.classList.contains('border-primary')) return;
            fetch(NOTIF_API + '?action=mark_read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notification_id: id })
            }).then(() => {
                el.classList.remove('border-primary', 'border-2', 'shadow-md');
                el.classList.add('border-light');
                const dot = el.querySelector('.bg-danger.border-light');
                if (dot) dot.remove();

                const n = allNotifs.find(x => x.id == id);
                if (n) n.is_read = 1;
                const unread = allNotifs.filter(n => n.is_read == 0).length;
                document.getElementById('headerSub').textContent =
                    unread > 0 ? 'لديك ' + unread + ' إشعار غير مقروء' : 'جميع الإشعارات مقروءة';

                const badge = document.querySelector('.badge.bg-danger');
                if (badge) {
                    badge.textContent = unread;
                    if (unread === 0) badge.classList.add('d-none'); else badge.classList.remove('d-none');
                }
            });
        }

        function markAll() {
            fetch(NOTIF_API + '?action=mark_read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({})
            }).then(() => {
                allNotifs.forEach(n => n.is_read = 1);
                document.getElementById('headerSub').textContent = 'جميع الإشعارات مقروءة';
                const badge = document.querySelector('.badge.bg-danger');
                if (badge) { badge.classList.add('d-none'); badge.textContent = '0'; }
                applyFilter(currentFilter);
                if (window.HagzUI) HagzUI.toast('تم تحديد جميع الإشعارات كمقروءة', 'success');
            });
        }

        document.querySelectorAll('.p-filter-pill').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.p-filter-pill').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                applyFilter(btn.dataset.filter);
            });
        });

        document.addEventListener('DOMContentLoaded', loadNotifications);
    </script>
</body>

</html>