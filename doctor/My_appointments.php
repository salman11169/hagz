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
    <title>مواعيدي — شفاء+</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shared-dashboard.css">
    <link rel="stylesheet" href="../assets/css/doctor-dashboard.css?v=4">
</head>

<body>

    <!-- ═══ NAVBAR ═══ -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <button class="icon-btn mobile-toggle" id="mobileToggle"><i class='bx bx-menu'></i></button>
                <div class="brand-icon"><i class='bx bx-plus-medical'></i></div>
                <div class="brand-text">شفاء<span>+</span></div>
            </div>
            <div class="nav-actions">
                <button class="icon-btn notif-btn"><i class='bx bx-bell'></i><span class="badge" id="notifBadge"
                        style="display:none">0</span></button>
                <div class="user-menu">
                    <div class="user-avatar">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=0d9488&color=fff&font-family=Cairo"
                            alt="">
                    </div>
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

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-menu">
            <a href="Doctor_dashboard.php" class="menu-item"><i class='bx bxs-dashboard'></i><span>الرئيسية</span></a>
            <a href="My_appointments.php" class="menu-item active"><i
                    class='bx bx-calendar-check'></i><span>مواعيدي</span></a>
            <a href="My_patients.php" class="menu-item"><i class='bx bx-group'></i><span>المرضى</span></a>
            <a href="Doctor_reports.php" class="menu-item"><i class='bx bx-file-blank'></i><span>التقارير</span></a>
            <a href="Doctor_referrals.php" class="menu-item"><i
                    class='bx bx-transfer-alt'></i><span>التحويلات</span></a>
            <a href="Doctor_profile.php" class="menu-item"><i class='bx bx-user-circle'></i><span>ملفي الشخصي</span></a>
        </div>
        <div class="sidebar-bottom">
            <a href="../logout.php" class="menu-item logout" onclick="event.preventDefault(); if(window.hagzConfirmLogout) window.hagzConfirmLogout('../logout.php'); else window.location.href='../logout.php';"><i class='bx bx-log-out'></i><span>تسجيل الخروج</span></a>
        </div>
    </aside>

    <!-- ═══ MAIN ═══ -->
    <main class="main-content">

        <!-- Hero -->
        <div class="page-hero">
            <div class="page-hero-inner">
                <div>
                    <h1><i class='bx bx-calendar-alt'></i> مواعيدي</h1>
                    <p id="heroDate">جميع مواعيدك في مكان واحد</p>
                </div>
                <div style="display:flex;gap:.6rem;flex-wrap:wrap">
                    <button class="hero-btn" id="btnToday" onclick="setHeroFilter('today',this)"><i
                            class='bx bx-calendar-today'></i> اليوم</button>
                    <button class="hero-btn ghost" id="btnWeek" onclick="setHeroFilter('week',this)"><i
                            class='bx bx-calendar-week'></i> هذا الأسبوع</button>
                    <button class="hero-btn ghost" id="btnAll" onclick="setHeroFilter('all',this)"><i
                            class='bx bx-list-ul'></i> الكل</button>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-mini">
                <div class="stat-mini-icon" style="background:var(--doc-primary-l);color:var(--doc-primary)"><i
                        class='bx bx-calendar'></i></div>
                <div>
                    <div class="stat-mini-num" id="sTot">—</div>
                    <div class="stat-mini-lbl">إجمالي</div>
                </div>
            </div>
            <div class="stat-mini">
                <div class="stat-mini-icon" style="background:rgba(245,158,11,.12);color:#d97706"><i
                        class='bx bx-time'></i></div>
                <div>
                    <div class="stat-mini-num" id="sPend">—</div>
                    <div class="stat-mini-lbl">قيد الانتظار</div>
                </div>
            </div>
            <div class="stat-mini">
                <div class="stat-mini-icon" style="background:rgba(16,185,129,.12);color:#059669"><i
                        class='bx bx-check-circle'></i></div>
                <div>
                    <div class="stat-mini-num" id="sDone">—</div>
                    <div class="stat-mini-lbl">مكتملة</div>
                </div>
            </div>
            <div class="stat-mini">
                <div class="stat-mini-icon" style="background:rgba(239,68,68,.1);color:#dc2626"><i
                        class='bx bxs-error-circle'></i></div>
                <div>
                    <div class="stat-mini-num" id="sCrit">—</div>
                    <div class="stat-mini-lbl">حرجة</div>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls-bar">
            <div class="search-box">
                <i class='bx bx-search'></i>
                <input type="text" id="searchInput" placeholder="ابحث باسم المريض أو رقم الحجز..."
                    oninput="applyFilters()">
            </div>
            <div class="filter-tabs">
                <button class="ft active" data-tab="all" onclick="setTab(this)">الكل</button>
                <button class="ft" data-tab="today" onclick="setTab(this)">اليوم</button>
                <button class="ft" data-tab="upcoming" onclick="setTab(this)">القادمة</button>
                <button class="ft" data-tab="smart" onclick="setTab(this)"><i class='bx bx-bot'></i> ذكية</button>
                <button class="ft" data-tab="regular" onclick="setTab(this)"><i class='bx bx-calendar-check'></i>
                    عادية</button>
                <button class="ft" data-tab="completed" onclick="setTab(this)">مكتملة</button>
                <button class="ft" data-tab="cancelled" onclick="setTab(this)">ملغية</button>
            </div>
        </div>

        <!-- List -->
        <div class="appt-list" id="apptList">
            <div class="empty-state"><i class='bx bx-loader-alt bx-spin'
                    style="font-size:2.5rem;opacity:1;color:var(--doc-primary)"></i></div>
        </div>

    </main>

    <script>
        // ══════════════════════════════════════════════
        // config
        // ══════════════════════════════════════════════
        const API = '../controllers/DoctorController.php';

        let allAppts = [];
        let heroFilter = 'today';
        let currentTab = 'all';

        // ══════════════════════════════════════════════
        // BOOT
        // ══════════════════════════════════════════════
        document.addEventListener('DOMContentLoaded', () => {
            initSidebar();
            document.getElementById('heroDate').textContent =
                new Date().toLocaleDateString('ar-SA', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
            loadAppointments();
        });

        // ══════════════════════════════════════════════
        // LOAD
        // ══════════════════════════════════════════════
        async function loadAppointments() {
            document.getElementById('apptList').innerHTML =
                `<div class="empty-state"><i class='bx bx-loader-alt bx-spin' style="font-size:2.5rem;opacity:1;color:var(--doc-primary)"></i></div>`;

            const data = await fetch(`${API}?action=appointments`).then(r => r.json()).catch(() => null);
            if (!data?.success) {
                document.getElementById('apptList').innerHTML =
                    `<div class="empty-state" style="color:#ef4444">تعذر تحميل المواعيد</div>`;
                return;
            }

            allAppts = data.appointments || [];
            updateStats();
            applyFilters();
        }

        // ══════════════════════════════════════════════
        // STATS
        // ══════════════════════════════════════════════
        function updateStats() {
            document.getElementById('sTot').textContent = allAppts.length;
            document.getElementById('sPend').textContent = allAppts.filter(a => a.status === 'Pending' || a.status === 'Confirmed').length;
            document.getElementById('sDone').textContent = allAppts.filter(a => a.status === 'Completed').length;
            document.getElementById('sCrit').textContent = allAppts.filter(a => a.priority === 'Critical').length;
        }

        // ══════════════════════════════════════════════
        // FILTERS
        // ══════════════════════════════════════════════
        function todayStr(offset = 0) {
            const d = new Date(); d.setDate(d.getDate() + offset);
            return d.toISOString().split('T')[0];
        }

        function setHeroFilter(type, btn) {
            heroFilter = type;
            document.querySelectorAll('.hero-btn').forEach(b => b.classList.add('ghost'));
            btn.classList.remove('ghost');
            // إعادة تعيين الـ tab إلى "الكل" عند تغيير heroFilter
            currentTab = 'all';
            document.querySelectorAll('.ft').forEach(b => b.classList.remove('active'));
            document.querySelector('.ft[data-tab="all"]').classList.add('active');
            applyFilters();
        }

        function setTab(btn) {
            currentTab = btn.dataset.tab;
            document.querySelectorAll('.ft').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            // إعادة heroFilter إلى "الكل" عند اختيار أي tab
            // حتى لا يتعارض مع فلترة اليوم
            heroFilter = 'all';
            document.querySelectorAll('.hero-btn').forEach(b => b.classList.add('ghost'));
            applyFilters();
        }

        function applyFilters() {
            const q = document.getElementById('searchInput').value.trim().toLowerCase();
            const today = todayStr();
            let list = allAppts.slice();

            // ── hero filter (date range) ──
            if (heroFilter === 'today') {
                list = list.filter(a => a.appointment_date === today);
            } else if (heroFilter === 'week') {
                const weekEnd = todayStr(6);
                list = list.filter(a => a.appointment_date >= today && a.appointment_date <= weekEnd);
            }
            // 'all' => no date restriction

            // ── tab filter ──
            if (currentTab === 'today') list = list.filter(a => a.appointment_date === today);
            if (currentTab === 'upcoming') list = list.filter(a => a.appointment_date > today && a.status !== 'Cancelled');
            if (currentTab === 'smart') list = list.filter(a => (a.booking_type || '').toLowerCase() === 'smart');
            if (currentTab === 'regular') list = list.filter(a => (a.booking_type || '').toLowerCase() !== 'smart');
            if (currentTab === 'completed') list = list.filter(a => a.status === 'Completed');
            if (currentTab === 'cancelled') list = list.filter(a => a.status === 'Cancelled');

            // ── search ──
            if (q) list = list.filter(a =>
                (a.patient_name || '').toLowerCase().includes(q) ||
                String(a.id).includes(q)
            );

            list.sort((a, b) => (a.appointment_date + a.appointment_time).localeCompare(b.appointment_date + b.appointment_time));
            renderList(list);
        }

        // ══════════════════════════════════════════════
        // RENDER
        // ══════════════════════════════════════════════
        function renderList(list) {
            const el = document.getElementById('apptList');
            if (!list.length) {
                el.innerHTML = `<div class="empty-state"><i class='bx bx-calendar-x'></i><p>لا توجد مواعيد مطابقة</p></div>`;
                return;
            }

            el.innerHTML = list.map(a => {
                const priKey = a.priority === 'Critical' ? 'emergency' : a.priority === 'Medium' ? 'urgent' : 'normal';
                const priLabel = a.priority === 'Critical' ? 'حرجة' : a.priority === 'Medium' ? 'متوسطة' : 'مستقرة';
                const priColor = { emergency: '#ef4444', urgent: '#f59e0b', normal: '#10b981' }[priKey];
                const initials = (a.patient_name || 'م').split(' ').map(w => w[0]).slice(0, 2).join('');
                const date = a.appointment_date
                    ? new Date(a.appointment_date).toLocaleDateString('ar-SA', { day: 'numeric', month: 'short' })
                    : '—';

                return `
    <div class="appt-card ${priKey}" onclick="openAppt(${a.id})">
      <div class="appt-time">
        <div class="appt-time-val">${fmtTime(a.appointment_time)}</div>
        <div class="appt-time-date">${date}</div>
      </div>
      <div class="appt-av">${esc(initials)}</div>
      <div class="appt-info">
        <div class="appt-name">${esc(a.patient_name || '—')}</div>
        <div class="appt-meta">
          <span><i class='bx bx-id-card'></i> #${a.id}</span>
          <span><i class='bx bx-user'></i> ${a.age ? a.age + ' سنة' : '—'} · ${genderAr(a.gender)}</span>
          <span style="color:${priColor}"><i class='bx bx-flag'></i> ${priLabel}</span>
          ${typeBadge(a.booking_type)}
        </div>
      </div>
      <span class="status-badge ${statusCls(a.status)}">${statusAr(a.status)}</span>
      <div class="appt-actions" onclick="event.stopPropagation()">
        <button class="act-btn" title="التفاصيل" onclick="openAppt(${a.id})"><i class='bx bx-show'></i></button>
        <button class="act-btn danger" title="إلغاء" onclick="cancelAppt(${a.id}, this)"><i class='bx bx-x-circle'></i></button>
      </div>
    </div>`;
            }).join('');
        }

        // ══════════════════════════════════════════════
        // ACTIONS
        // ══════════════════════════════════════════════
        function openAppt(id) {
            window.location.href = 'Appointment_details.php?id=' + id;
        }

        async function cancelAppt(id, btn) {
            if (!confirm('إلغاء هذا الموعد؟')) return;
            btn.disabled = true;
            const fd = new FormData();
            fd.append('appointment_id', id);
            fd.append('status', 'Cancelled');
            const data = await fetch(`${API}?action=update_status`, { method: 'POST', body: fd }).then(r => r.json()).catch(() => null);
            if (data?.success) {
                const appt = allAppts.find(x => x.id == id);
                if (appt) appt.status = 'Cancelled';
                updateStats(); applyFilters();
            } else {
                alert(data?.message || 'تعذر الإلغاء');
                btn.disabled = false;
            }
        }

        // ══════════════════════════════════════════════
        // HELPERS
        // ══════════════════════════════════════════════
        function typeBadge(type) {
            if (type === 'smart') return `<span class="type-badge type-smart"><i class='bx bx-bot'></i> ذكي</span>`;
            if (type === 'regular') return `<span class="type-badge type-regular"><i class='bx bx-calendar'></i> عادي</span>`;
            return '';
        }
        function statusCls(s) {
            return {
                Pending: 'sb-pending', Confirmed: 'sb-confirmed', Completed: 'sb-completed',
                Cancelled: 'sb-cancelled', Transferred: 'sb-transferred'
            }[s] || 'sb-pending';
        }
        function statusAr(s) {
            return {
                Pending: 'قيد الانتظار', Confirmed: 'مؤكد', Completed: 'مكتمل',
                Cancelled: 'ملغي', Transferred: 'محوَّل'
            }[s] || s;
        }
        function genderAr(g) { return g === 'Male' ? 'ذكر' : g === 'Female' ? 'أنثى' : '—'; }
        function fmtTime(t) {
            if (!t) return '—';
            const [h, m] = t.split(':').map(Number);
            return `${h % 12 || 12}${m ? ':' + String(m).padStart(2, '0') : ''} ${h >= 12 ? 'م' : 'ص'}`;
        }
        function esc(s) {
            return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }
        function initSidebar() {
            const toggle = document.getElementById('mobileToggle');
            const sidebar = document.getElementById('sidebar');
            if (!toggle || !sidebar) return;
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            });
            document.addEventListener('click', e => {
                if (window.innerWidth <= 768 && !sidebar.contains(e.target)
                    && !toggle.contains(e.target) && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active'); document.body.style.overflow = '';
                }
            });
        }
    </script>
    <script src="../assets/js/hagz-ui.js?v=2"></script>
    <script src="../assets/js/notif-badge.js" defer></script>
</body>

</html>
