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
    <title>التقارير الطبية — شفاء+</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shared-dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/doctor-dashboard.css?v=7">
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
                            alt=""></div>
                    <div class="user-info"><span class="user-greeting">مرحباً دكتور،</span><span class="user-name">
                            <?= $userName ?>
                        </span></div>
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
            <a href="Doctor_reports.php" class="menu-item active"><i
                    class='bx bx-file-blank'></i><span>التقارير</span></a>
            <a href="Doctor_referrals.php" class="menu-item"><i
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
                    <h1><i class='bx bx-bar-chart-alt-2'></i> التقارير الطبية</h1>
                    <p>ملخص أدائك الطبي وإحصائيات نشاطك</p>
                </div>
                <div style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;">
                    <select onchange=""
                        style="appearance:none; -webkit-appearance:none; padding:.6rem 1rem .6rem 2.5rem; border-radius:12px; border:1.5px solid rgba(255,255,255,.4); background: rgba(255,255,255,.2) url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%23ffffff%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E') no-repeat left 1rem center / 12px; color:#fff; font-family:'Cairo',sans-serif; font-weight:700; outline:none; cursor:pointer;">
                        <option style="color:#0f172a; background:#fff;">هذا الشهر</option>
                        <option style="color:#0f172a; background:#fff;">الأسبوع الماضي</option>
                        <option style="color:#0f172a; background:#fff;">هذا العام</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- KPIs -->
        <div class="kpi-row">
            <div class="kpi">
                <div class="kpi-icon" style="color:var(--doc-primary);"><i class='bx bx-calendar-check'></i></div>
                <div class="kpi-num" id="kpiTotal"><i class='bx bx-loader-alt bx-spin'></i></div>
                <div class="kpi-lbl">إجمالي المواعيد (30 يوم)</div>
            </div>
            <div class="kpi">
                <div class="kpi-icon" style="color:#10b981;"><i class='bx bx-check-circle'></i></div>
                <div class="kpi-num" id="kpiCompleted"><i class='bx bx-loader-alt bx-spin'></i></div>
                <div class="kpi-lbl">مواعيد مكتملة</div>
            </div>
            <div class="kpi">
                <div class="kpi-icon" style="color:#f59e0b;"><i class='bx bx-time-five'></i></div>
                <div class="kpi-num" id="kpiAvgMin"><i class='bx bx-loader-alt bx-spin'></i></div>
                <div class="kpi-lbl">متوسط وقت الفحص (دقيقة)</div>
            </div>
            <div class="kpi">
                <div class="kpi-icon" style="color:#ef4444;"><i class='bx bxs-error-circle'></i></div>
                <div class="kpi-num" id="kpiCritical"><i class='bx bx-loader-alt bx-spin'></i></div>
                <div class="kpi-lbl">حالات حرجة</div>
            </div>
        </div>

        <div class="reports-layout">
            <div class="reports-left">

                <!-- Appointments by day (bar chart) -->
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class='bx bx-bar-chart'></i> المواعيد حسب اليوم (هذا الأسبوع)</div>
                    </div>
                    <div class="bar-chart" id="dayChart"></div>
                </div>

                <!-- Top diagnoses -->
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class='bx bx-health'></i> أكثر التشخيصات شيوعاً</div>
                    </div>
                    <div class="bar-chart" id="diagChart"></div>
                </div>

                <!-- Recent Activity -->
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class='bx bx-history'></i> النشاط الأخير</div>
                    </div>
                    <div id="activityList"></div>
                </div>

            </div>

            <div class="reports-right">

                <!-- Priority breakdown -->
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class='bx bx-flag'></i> الأولوية</div>
                    </div>
                    <div id="priorityBreakdown">
                        <div style="text-align:center;padding:1rem;color:var(--doc-muted)"><i
                                class='bx bx-loader-alt bx-spin'></i></div>
                    </div>
                </div>

                <!-- Visit types donut -->
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class='bx bx-pie-chart-alt'></i> نوع الزيارة</div>
                    </div>
                    <div class="donut-wrap" id="visitDonutWrap">
                        <div class="donut" id="visitDonut"
                            style="background:conic-gradient(var(--doc-primary) 0% 0%, #e2e8f0 0% 100%);"></div>
                        <div class="donut-legend" id="visitLegend">
                            <div class="dl-item">
                                <div class="dl-dot" style="background:var(--doc-primary);"></div> حضوري — 0%
                            </div>
                            <div class="dl-item">
                                <div class="dl-dot" style="background:#e2e8f0;"></div> عن بُعد — 0%
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gender donut -->
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class='bx bx-user-voice'></i> المرضى</div>
                    </div>
                    <div class="donut-wrap" id="genderDonutWrap">
                        <div class="donut" id="genderDonut"
                            style="background:conic-gradient(var(--doc-primary) 0% 0%, #f43f5e 0% 100%);"></div>
                        <div class="donut-legend" id="genderLegend">
                            <div class="dl-item">
                                <div class="dl-dot" style="background:var(--doc-primary);"></div> ذكور — 0%
                            </div>
                            <div class="dl-item">
                                <div class="dl-dot" style="background:#f43f5e;"></div> إناث — 0%
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export -->
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class='bx bx-download'></i> تصدير التقرير</div>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:.7rem;">
                        <button class="export-btn" onclick="alert('جاري تصدير PDF...')"><i class='bx bxs-file-pdf'></i>
                            تصدير PDF</button>
                        <button class="export-btn outline" onclick="alert('جاري تصدير Excel...')"><i
                                class='bx bx-spreadsheet'></i> تصدير Excel</button>
                        <button class="export-btn outline" onclick="window.print()"><i class='bx bx-printer'></i>
                            طباعة</button>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <script>
        const API = '../controllers/DoctorController.php';
        const DAY_NAMES = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

        document.addEventListener('DOMContentLoaded', () => {
            initSidebar();
            loadReports();
            loadRecentActivity();
        });

        // ── KPIs + Priority breakdown ──────────────────────────────────
        function loadReports() {
            fetch(`${API}?action=reports`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success || !data.report) return;
                    const r = data.report;
                    const total = parseInt(r.total_appointments) || 0;
                    const completed = parseInt(r.completed) || 0;
                    const cancelled = parseInt(r.cancelled) || 0;
                    const critical = parseInt(r.critical_cases) || 0;
                    const avgMin = r.avg_consultation_min ? Math.round(parseFloat(r.avg_consultation_min)) : '—';

                    document.getElementById('kpiTotal').textContent = total;
                    document.getElementById('kpiCompleted').textContent = completed;
                    document.getElementById('kpiAvgMin').textContent = avgMin;
                    document.getElementById('kpiCritical').textContent = critical;

                    // Priority breakdown (approximate from reports data)
                    // We'll fetch appointments to get proper breakdown
                    renderPriorityBreakdown(critical, total - critical - completed, total - critical - (total - critical - completed));
                })
                .catch(() => { });

            // Fetch appointments for proper priority breakdown
            fetch(`${API}?action=appointments`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const appts = data.appointments || [];
                    const crit = appts.filter(a => a.priority === 'Critical').length;
                    const medium = appts.filter(a => a.priority === 'Medium').length;
                    const routine = appts.filter(a => a.priority === 'Routine').length;
                    const total = appts.length || 1;
                    renderPriorityBreakdown(crit, medium, routine, total);

                    // Day chart: count appointments per day of week
                    const dayCounts = Array(7).fill(0);
                    let inPerson = 0, telehealth = 0;
                    let males = 0, females = 0;
                    
                    appts.forEach(a => {
                        if (a.appointment_date) {
                            const dow = new Date(a.appointment_date).getDay();
                            dayCounts[dow]++;
                        }
                        if (a.visit_type === 'Telehealth') telehealth++;
                        else inPerson++;
                        
                        if (a.gender === 'Female') females++;
                        else if(a.gender === 'Male') males++;
                    });
                    renderDayChart(dayCounts);
                    
                    // Render Visit Types Donut
                    const totalVisits = inPerson + telehealth || 1;
                    const inPersonPct = Math.round(inPerson / totalVisits * 100);
                    const tlPct = 100 - inPersonPct;
                    document.getElementById('visitDonut').style.background = `conic-gradient(var(--doc-primary) 0% ${inPersonPct}%, #e2e8f0 ${inPersonPct}% 100%)`;
                    document.getElementById('visitLegend').innerHTML = `
                        <div class="dl-item"><div class="dl-dot" style="background:var(--doc-primary);"></div> حضوري — ${inPersonPct}%</div>
                        <div class="dl-item"><div class="dl-dot" style="background:#e2e8f0;"></div> عن بُعد — ${tlPct}%</div>
                    `;
                    
                    // Render Gender Donut
                    const totalGender = males + females || 1;
                    const malePct = Math.round(males / totalGender * 100);
                    const femalePct = 100 - malePct;
                    document.getElementById('genderDonut').style.background = `conic-gradient(var(--doc-primary) 0% ${malePct}%, #f43f5e ${malePct}% 100%)`;
                    document.getElementById('genderLegend').innerHTML = `
                        <div class="dl-item"><div class="dl-dot" style="background:var(--doc-primary);"></div> ذكور — ${malePct}%</div>
                        <div class="dl-item"><div class="dl-dot" style="background:#f43f5e;"></div> إناث — ${femalePct}%</div>
                    `;

                })
                .catch(() => { });
        }

        function renderPriorityBreakdown(crit, medium, routine, total) {
            total = total || 1;
            const items = [
                { color: '#ef4444', name: 'حرجة', count: crit },
                { color: '#f59e0b', name: 'عاجلة', count: medium },
                { color: '#10b981', name: 'مستقرة', count: routine },
            ];
            document.getElementById('priorityBreakdown').innerHTML = items.map(it => {
                const pct = Math.round(it.count / total * 100);
                return `<div class="pri-item">
                  <div class="pri-left">
                    <div class="pri-dot-lg" style="background:${it.color};"></div>
                    <div><div class="pri-name">${it.name}</div><div class="pri-pct">${pct}%</div></div>
                  </div>
                  <div class="pri-right">${it.count}</div>
                </div>`;
            }).join('');
        }

        function renderDayChart(dayCounts) {
            const maxDay = Math.max(...dayCounts, 1);
            document.getElementById('dayChart').innerHTML = DAY_NAMES.map((name, i) => {
                const pct = Math.round(dayCounts[i] / maxDay * 100);
                return `<div class="bc-row">
                  <div class="bc-label">${name}</div>
                  <div class="bc-bar-wrap"><div class="bc-bar" style="width:0%;background:var(--doc-primary);" data-target="${pct}"></div></div>
                  <div class="bc-val">${dayCounts[i]}</div>
                </div>`;
            }).join('');
            setTimeout(() => {
                document.querySelectorAll('.bc-bar').forEach(b => b.style.width = b.dataset.target + '%');
            }, 200);
        }

        // ── Recent activity: آخر 5 مواعيد ──────────────────────────────
        function loadRecentActivity() {
            fetch(`${API}?action=appointments`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const appts = (data.appointments || []).slice(0, 5);
                    const actEl = document.getElementById('activityList');
                    if (!appts.length) { actEl.innerHTML = '<div style="text-align:center;color:var(--doc-muted);padding:1rem;">لا توجد نشاطات</div>'; return; }

                    const statusMap = {
                        Completed: { icon: 'bx-check-circle', color: '#10b981', bg: 'rgba(16,185,129,.1)', label: 'اكتمل فحص' },
                        Cancelled: { icon: 'bx-x-circle', color: '#ef4444', bg: 'rgba(239,68,68,.1)', label: 'إلغاء موعد' },
                        Transferred: { icon: 'bx-transfer-alt', color: '#f59e0b', bg: 'rgba(245,158,11,.12)', label: 'تحويل' },
                        Confirmed: { icon: 'bx-calendar-check', color: '#0d9488', bg: 'var(--doc-primary-l)', label: 'تأكيد موعد' },
                        Pending: { icon: 'bx-time', color: '#64748b', bg: '#f1f5f9', label: 'موعد قادم' },
                    };

                    actEl.innerHTML = appts.map(a => {
                        const s = statusMap[a.status] || statusMap.Pending;
                        const fmtDate = a.appointment_date
                            ? new Date(a.appointment_date).toLocaleDateString('ar-SA', { day: 'numeric', month: 'short' })
                            : '—';
                        return `<div class="activity-item">
                          <div class="act-icon" style="background:${s.bg};color:${s.color};"><i class='bx ${s.icon}'></i></div>
                          <div class="act-info">
                            <div class="act-title">${s.label} ${a.patient_name || ''}</div>
                            <div class="act-sub">#${a.id} · ${a.priority === 'Critical' ? 'حرجة' : a.priority === 'Medium' ? 'متوسطة' : 'مستقرة'}</div>
                          </div>
                          <div class="act-time">${fmtDate}</div>
                        </div>`;
                    }).join('');

                    // Diag chart placeholder (no diagnosis API yet — show by priority)
                    const all = data.appointments || [];
                    const diagData = [
                        { name: 'حرجة', val: all.filter(a => a.priority === 'Critical').length },
                        { name: 'متوسطة', val: all.filter(a => a.priority === 'Medium').length },
                        { name: 'مستقرة', val: all.filter(a => a.priority === 'Routine').length },
                        { name: 'مكتملة', val: all.filter(a => a.status === 'Completed').length },
                        { name: 'ملغية', val: all.filter(a => a.status === 'Cancelled').length },
                    ];
                    const maxD = Math.max(...diagData.map(d => d.val), 1);
                    document.getElementById('diagChart').innerHTML = diagData.map((d, i) => {
                        const pct = Math.round(d.val / maxD * 100);
                        return `<div class="bc-row">
                          <div class="bc-label">${d.name}</div>
                          <div class="bc-bar-wrap"><div class="bc-bar" style="width:0%;background:hsl(${170 + i * 25},65%,45%);" data-target="${pct}"></div></div>
                          <div class="bc-val">${d.val}</div>
                        </div>`;
                    }).join('');
                    setTimeout(() => document.querySelectorAll('.bc-bar').forEach(b => b.style.width = b.dataset.target + '%'), 200);
                })
                .catch(() => { });
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
                    sidebar.classList.remove('active'); document.body.style.overflow = '';
                }
            });
        }
    </script>
    <script src="../assets/js/hagz-ui.js?v=2"></script>
    <script src="../assets/js/notif-badge.js" defer></script>
</body>

</html>
