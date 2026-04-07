<?php
require_once __DIR__ . '/../includes/session.php';
require_role(ROLE_DOCTOR);
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'الطبيب', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>التحويلات الطبية — شفاء+</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shared-dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/doctor-dashboard.css?v=6">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <button class="icon-btn mobile-toggle" id="mobileToggle"><i class='bx bx-menu'></i></button>
                <div class="brand-icon"><i class='bx bx-plus-medical'></i></div>
                <div class="brand-text">شفاء<span>+</span></div>
            </div>
            <div class="nav-actions">
                <button class="icon-btn notif-btn"><i class='bx bx-bell'></i><span class="badge" id="notifBadge"
                        style="display:none;">0</span></button>
                <div class="user-menu">
                    <div class="user-avatar"><img
                            src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=0d9488&color=fff&font-family=Cairo"
                            alt="Doctor"></div>
                    <div class="user-info">
                        <span class="user-greeting">مرحباً دكتور،</span>
                        <span class="user-name">
                            <?= $userName ?>
                        </span>
                    </div>
                    <i class='bx bx-chevron-down dropdown-icon'></i>
                </div>
            </div>
        </div>
    </nav>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-menu">
            <a href="Doctor_dashboard.php" class="menu-item"><i class='bx bxs-dashboard'></i><span>الرئيسية</span></a>
            <a href="My_appointments.php" class="menu-item"><i class='bx bx-calendar-check'></i><span>مواعيدي</span></a>
            <a href="My_patients.php" class="menu-item"><i class='bx bx-group'></i><span>المرضى</span></a>
            <a href="Doctor_reports.php" class="menu-item"><i class='bx bx-file-blank'></i><span>التقارير</span></a>
            <a href="Doctor_referrals.php" class="menu-item active"><i
                    class='bx bx-transfer-alt'></i><span>التحويلات</span></a>
            <a href="Doctor_profile.php" class="menu-item"><i class='bx bx-user-circle'></i><span>ملفي الشخصي</span></a>
        </div>
        <div class="sidebar-bottom">
            <a href="../logout.php" class="menu-item logout" onclick="event.preventDefault(); if(window.hagzConfirmLogout) window.hagzConfirmLogout('../logout.php'); else window.location.href='../logout.php';"><i class='bx bx-log-out'></i><span>تسجيل الخروج</span></a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="main-content">

        <div class="page-hero">
            <div class="page-hero-inner">
                <div>
                    <h1><i class='bx bx-transfer-alt'></i> التحويلات الطبية</h1>
                    <p>التحويلات المرسلة والمستقبلة بين الأطباء</p>
                </div>
                <div style="background:rgba(255,255,255,.2);border-radius:14px;padding:.6rem 1.2rem;color:#fff;font-weight:800;font-size:.88rem;border:1.5px solid rgba(255,255,255,.3);"
                    id="refCountBadge">—</div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="tab-bar">
            <button class="tab-btn active" onclick="switchTab('sent',this)"><i class='bx bx-send'></i>
                المُرسَلة</button>
            <button class="tab-btn" onclick="switchTab('received',this)"><i class='bx bx-inbox'></i>
                المُستقبَلة</button>
        </div>

        <!-- List -->
        <div class="ref-list" id="refList">
            <div class="empty-state"><i class='bx bx-loader-alt bx-spin'
                    style="font-size:2.5rem;opacity:1;color:var(--doc-primary)"></i></div>
        </div>

    </main>

    <script>
        const API = '../controllers/DoctorController.php';
        let currentTab = 'sent';

        document.addEventListener('DOMContentLoaded', () => {
            initSidebar();
            loadReferrals('sent');
        });

        function switchTab(type, el) {
            currentTab = type;
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            el.classList.add('active');
            loadReferrals(type);
        }

        function loadReferrals(type) {
            const list = document.getElementById('refList');
            list.innerHTML = `<div class="empty-state"><i class='bx bx-loader-alt bx-spin' style="font-size:2.5rem;opacity:1;color:var(--doc-primary)"></i></div>`;

            fetch(`${API}?action=get_referrals&type=${type}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error(data.message);
                    render(data.referrals || [], type);
                })
                .catch(err => {
                    list.innerHTML = `<div class="empty-state" style="color:#ef4444"><i class='bx bx-error-circle'></i>تعذر التحميل: ${err.message}</div>`;
                });
        }

        function render(refs, type) {
            const list = document.getElementById('refList');
            document.getElementById('refCountBadge').textContent = refs.length + ' تحويل';

            if (!refs.length) {
                list.innerHTML = `<div class="empty-state"><i class='bx bx-transfer-alt'></i>لا توجد تحويلات ${type === 'sent' ? 'مرسلة' : 'مستقبلة'} بعد</div>`;
                return;
            }

            const priLabel = { Routine: 'روتيني', Urgent: 'عاجل', Emergency: 'طارئ' };
            const statusLabel = { pending: 'قيد الانتظار', accepted: 'مقبول', rejected: 'مرفوض' };

            list.innerHTML = refs.map(r => {
                const fmtDate = r.created_at
                    ? new Date(r.created_at).toLocaleDateString('ar-SA', { day: 'numeric', month: 'long', year: 'numeric' })
                    : '—';
                const otherDoc = type === 'sent' ? `إلى: د. ${esc(r.to_doctor_name)}` : `من: د. ${esc(r.from_doctor_name)}`;
                const status = (r.status || 'pending').toLowerCase();
                const summaryHtml = r.clinical_summary
                    ? `<div class="ref-summary"><i class='bx bx-note' style="color:var(--doc-primary);margin-left:.4rem;"></i>${esc(r.clinical_summary)}</div>`
                    : '';

                return `<div class="ref-card">
          <div class="ref-top">
            <div class="ref-icon"><i class='bx bx-transfer-alt'></i></div>
            <div>
              <div class="ref-patient">${esc(r.patient_name)}</div>
              <div class="ref-sub">${otherDoc} · #${r.id}</div>
            </div>
            <div class="ref-badges">
              <span class="badge-pri ${r.priority}">${priLabel[r.priority] || r.priority}</span>
              <span class="badge-status ${status}">${statusLabel[status] || status}</span>
            </div>
          </div>
          <div class="ref-reason"><strong>السبب:</strong> ${esc(r.reason)}</div>
          ${summaryHtml}
          <div class="ref-meta">
            <span><i class='bx bx-calendar'></i> ${fmtDate}</span>
            ${r.responded_at ? `<span><i class='bx bx-check-double'></i> تم الرد: ${new Date(r.responded_at).toLocaleDateString('ar-SA', { day: 'numeric', month: 'short' })}</span>` : ''}
          </div>
        </div>`;
            }).join('');
        }

        function esc(s) {
            return String(s ?? '').replace(/[&<>"']/g, c =>
                ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
        }

        function initSidebar() {
            const toggle = document.getElementById('mobileToggle');
            const sidebar = document.getElementById('sidebar');
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            });
            document.addEventListener('click', e => {
                if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggle.contains(e.target) && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }
    </script>
    <script src="../assets/js/hagz-ui.js?v=2"></script>
    <script src="../assets/js/notif-badge.js" defer></script>
</body>

</html>
