<?php

require_once __DIR__ . '/../includes/session.php';
require_role(ROLE_ADMIN);
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'المدير', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المشرف - نظام الفرز الذكي</title>

    <!-- Google Fonts - Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <!-- CSS Files -->
    <link rel="stylesheet" href="../assets/css/shared-dashboard.css?v=1.1">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css?v=1.1">
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <button class="icon-btn mobile-toggle" id="mobileToggle" aria-label="Toggle Sidebar">
                    <i class='bx bx-menu'></i>
                </button>
                <div class="brand-icon">
                    <i class='bx bx-plus-medical'></i>
                </div>
                <div class="brand-text">شفاء<span>+</span></div>
            </div>

            <div class="nav-actions">
                <div class="user-menu">
                    <div class="user-avatar">
                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=2563eb&color=fff&font-family=Cairo"
                            alt="Admin">
                    </div>
                    <div class="user-info">
                        <span class="user-greeting">مرحباً بك،</span>
                        <span class="user-name">
                            <?= $userName ?>
                        </span>
                    </div>
                    <i class='bx bx-chevron-down dropdown-icon'></i>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar (Admin) -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-menu">
            <a href="admin.php" class="menu-item active"><i class='bx bxs-dashboard'></i><span>لوحة التحكم</span></a>
            <a href="Manage_doctors.php" class="menu-item"><i class='bx bx-user-voice'></i><span>إدارة
                    الأطباء</span></a>
            <a href="Manage_patients.php" class="menu-item"><i class='bx bx-group'></i><span>إدارة المرضى</span></a>
            <a href="Reports.php" class="menu-item"><i class='bx bx-bar-chart-alt-2'></i><span>التقارير
                    والإحصائيات</span></a>
            <a href="System_settings.php" class="menu-item"><i class='bx bx-cog'></i><span>إعدادات النظام</span></a>
            <a href="User_permissions.php" class="menu-item"><i class='bx bx-shield-quarter'></i><span>صلاحيات
                    المستخدمين</span></a>
        </div>
        <div class="sidebar-bottom">
            <a href="#" onclick="showLogoutModal(event)" class="menu-item logout"><i
                    class='bx bx-log-out'></i><span>تسجيل
                    الخروج</span></a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-wrap">

            <!-- Welcome Hero -->
            <div class="hero-card">
                <div class="hero-glow"></div>
                <div class="hero-content">
                    <div class="hero-icon-wrap">
                        <i class='bx bxs-dashboard'></i>
                    </div>
                    <h1>لوحة تحكم المشرف</h1>
                    <p>مرحباً بك — يمكنك متابعة أداء النظام وإدارة جميع العمليات مع إحصائيات حية ومحدّثة في الوقت
                        الفعلي.</p>
                    <div class="hero-meta">
                        <div class="hero-meta-item"><i class='bx bx-calendar-check'></i> <span id="heroToday">—</span>
                            حجز اليوم</div>
                        <div class="hero-meta-item"><i class='bx bx-error-circle'></i> <span id="heroCritical">—</span>
                            حالة حرجة</div>
                        <div class="hero-meta-item"><i class='bx bx-time-five'></i> الآن:
                            <?= date('H:i') ?>
                        </div>
                    </div>
                </div>
                <div class="hero-actions">
                    <button class="btn-glass" onclick="window.location.href='Manage_doctors.php'">
                        <i class='bx bx-user-voice'></i> إدارة الأطباء
                    </button>
                    <button class="btn-glass primary" onclick="window.location.href='Reports.php'">
                        <i class='bx bx-bar-chart-alt-2'></i> التقارير
                    </button>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon s-blue"><i class='bx bx-group'></i></div>
                    <div class="stat-info">
                        <h3>إجمالي المرضى</h3>
                        <div class="stat-value" id="statPatients">—</div>
                        <div class="stat-trend positive"><i class='bx bx-trending-up'></i> تحديث مستمر</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon s-purple"><i class='bx bx-plus-medical'></i></div>
                    <div class="stat-info">
                        <h3>الأطباء النشطين</h3>
                        <div class="stat-value" id="statDoctors">—</div>
                        <div class="stat-trend positive"><i class='bx bx-check-circle'></i> جميع العيادات مغطاة</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon s-orange"><i class='bx bx-calendar-star'></i></div>
                    <div class="stat-info">
                        <h3>مواعيد اليوم</h3>
                        <div class="stat-value" id="statToday">—</div>
                        <div class="stat-trend warning"><i class='bx bx-trending-up'></i> مرتبط بقاعدة البيانات</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon s-green"><i class='bx bxs-error-circle'></i></div>
                    <div class="stat-info">
                        <h3>حالات حرجة نشطة</h3>
                        <div class="stat-value" id="statCritical">—</div>
                        <div class="stat-trend negative"><i class='bx bx-error-circle'></i> تحتاج متابعة</div>
                    </div>
                </div>
            </div>

            <!-- Content Grid (Tables & Charts) -->
            <div class="content-grid">

                <div class="content-card col-span-2">
                    <div class="card-header">
                        <h2><i class='bx bx-pulse'></i> أحدث الحجوزات</h2>
                        <button class="btn-text" onclick="window.location.href='Manage_patients.php'">عرض الكل <i
                                class='bx bx-left-arrow-alt'></i></button>
                    </div>
                    <div class="table-responsive">
                        <table class="premium-table">
                            <thead>
                                <tr>
                                    <th>رقم الحجز / التاريخ</th>
                                    <th>المريض</th>
                                    <th>الطبيب</th>
                                    <th>التخصص</th>
                                    <th>نوع الحجز</th>
                                    <th>الأولوية</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody id="recentTableBody">
                                <tr>
                                    <td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8">جاري التحميل...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Side Widget -->
                <div class="content-card">
                    <div class="card-header border-bottom">
                        <h2><i class='bx bx-trophy'></i> أداء الأطباء</h2>
                    </div>

                    <!-- مؤشرات حية -->
                    <div class="system-health" id="sysHealthWrap">
                        <div class="health-item">
                            <div class="h-icon" id="dbIcon"><i class='bx bx-data'></i></div>
                            <div class="h-details">
                                <h4>قاعدة البيانات</h4>
                                <div class="server-status" id="dbStatus">جاري... <span class="pulse-dot"></span></div>
                            </div>
                        </div>
                        <div class="health-item">
                            <div class="h-icon success"><i class='bx bx-broadcast'></i></div>
                            <div class="h-details">
                                <h4>استجابة السيرفر</h4>
                                <div class="server-status" id="pingStatus">جاري... <span class="pulse-dot"></span></div>
                            </div>
                        </div>
                    </div>

                    <!-- أداء الأطباء -->
                    <div class="card-header border-bottom" style="margin-top:1rem">
                        <h3 style="font-size:.9rem;font-weight:700;color:var(--text-secondary)"><i
                                class='bx bx-star'></i> أفضل الأطباء هذا الشهر</h3>
                    </div>
                    <div id="perfList" style="padding:.5rem 0">
                        <div style="text-align:center;padding:1.5rem;color:#94a3b8">جاري التحميل...</div>
                    </div>

                    <!-- إجراءات سريعة -->
                    <div class="quick-actions-box mt-4">
                        <h3>إجراءات سريعة</h3>
                        <div class="qa-grid">
                            <button class="qa-btn" onclick="window.location.href='Add_doctor.php'">
                                <i class='bx bx-user-plus'></i> إضافة طبيب
                            </button>
                            <button class="qa-btn" onclick="window.location.href='Manage_patients.php'">
                                <i class='bx bx-group'></i> المرضى
                            </button>
                            <button class="qa-btn" onclick="window.location.href='Reports.php'">
                                <i class='bx bx-bar-chart-alt-2'></i> التقارير
                            </button>
                            <button class="qa-btn" onclick="window.location.href='System_settings.php'">
                                <i class='bx bx-cog'></i> الإعدادات
                            </button>
                        </div>
                    </div>
                </div>

            </div> <!-- /content-grid -->

        </div> <!-- /dashboard-wrap -->
    </main>

    <script>
        const API = '../controllers/AdminController.php';

        const PRIORITY_MAP = {
            'Critical': '<span class="badge-priority b-danger">حرجة</span>',
            'Medium': '<span class="badge-priority b-warning">عاجلة</span>',
            'Routine': '<span class="badge-priority b-normal">مستقرة</span>',
        };
        const STATUS_MAP = {
            'Pending': '<span class="badge-status b-pending">قيد الانتظار</span>',
            'Confirmed': '<span class="badge-status b-success">مؤكد</span>',
            'Completed': '<span class="badge-status b-success">مكتمل</span>',
            'Cancelled': '<span class="badge-status b-cancelled">ملغي</span>',
        };

        document.addEventListener('DOMContentLoaded', () => {
            initSidebar();
            loadDashboard();
        });

        function loadDashboard() {
            const t0 = performance.now();
            fetch(`${API}?action=dashboard`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;

                    // ── Ping ──
                    const ms = Math.round(performance.now() - t0);
                    document.getElementById('pingStatus').innerHTML =
                        `<strong style="color:#10b981"><i class='bx bx-check-circle'></i> ممتاز (${ms}ms)</strong> <span class="pulse-dot"></span>`;

                    // ── Stats ──
                    const s = data.stats;
                    document.getElementById('statDoctors').textContent = s.total_doctors ?? '—';
                    document.getElementById('statPatients').textContent = s.total_patients ?? '—';
                    document.getElementById('statToday').textContent = s.today_appointments ?? '—';
                    document.getElementById('statCritical').textContent = s.active_critical ?? '—';
                    // ── Hero Meta ──
                    const hToday = document.getElementById('heroToday');
                    const hCritical = document.getElementById('heroCritical');
                    if (hToday) hToday.textContent = s.today_appointments ?? '—';
                    if (hCritical) hCritical.textContent = s.active_critical ?? '—';

                    // ── DB Status ──
                    const total = (parseInt(s.total_patients) || 0) + (parseInt(s.total_doctors) || 0);
                    document.getElementById('dbStatus').innerHTML =
                        `<strong style="color:#10b981"><i class='bx bx-check-circle'></i> متصلة</strong> &mdash; ${Number(total).toLocaleString('ar')} سجل`;

                    // ── Doctor Performance ──
                    const perfList = document.getElementById('perfList');
                    if (Array.isArray(data.performance) && data.performance.length) {
                        perfList.innerHTML = data.performance.map(d => {
                            const pct = d.total > 0 ? Math.round((d.completed / d.total) * 100) : 0;
                            const avg = d.avg_min ? Math.round(d.avg_min) + ' دقيقة' : '—';
                            const color = pct >= 80 ? '#10b981' : pct >= 50 ? '#f59e0b' : '#ef4444';
                            return `
                            <div style="padding:.6rem .8rem;border-bottom:1px solid #f1f5f9">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.35rem">
                                    <span style="font-weight:700;font-size:.82rem">د. ${d.name}</span>
                                    <span style="font-size:.72rem;color:#64748b">${d.specialization}</span>
                                </div>
                                <div style="display:flex;align-items:center;gap:.5rem">
                                    <div style="flex:1;height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden">
                                        <div style="width:${pct}%;height:100%;background:${color};border-radius:99px;transition:width .6s"></div>
                                    </div>
                                    <span style="font-size:.75rem;font-weight:700;color:${color};min-width:32px">${pct}%</span>
                                    <span style="font-size:.7rem;color:#94a3b8">ø${avg}</span>
                                </div>
                            </div>`;
                        }).join('');
                    } else {
                        perfList.innerHTML = '<div style="text-align:center;padding:1.5rem;color:#94a3b8">لا توجد بيانات</div>';
                    }

                    // ── Recent Appointments ──
                    const tbody = document.getElementById('recentTableBody');
                    if (Array.isArray(data.recent) && data.recent.length) {
                        tbody.innerHTML = data.recent.map(a => {
                            const initials = (a.patient_name || '?')[0];
                            const priBadge = PRIORITY_MAP[a.priority] || `<span class="badge-priority">${a.priority}</span>`;
                            const stBadge = STATUS_MAP[a.status] || `<span class="badge-status">${a.status}</span>`;
                            const isCrit = a.priority === 'Critical' ? 'class="emergency-row"' : '';
                            
                            let bookTypeBadge = '<span class="badge-priority b-normal">عادي</span>';
                            if (a.booking_type === 'smart') bookTypeBadge = '<span class="badge-priority b-warning">ذكي</span>';
                            if (a.booking_type === 'emergency') bookTypeBadge = '<span class="badge-priority b-danger"><i class="bx bxs-ambulance"></i> طوارئ</span>';

                            return `<tr ${isCrit}>
                                <td><strong>#${a.id}</strong><br><small style="color:#94a3b8;font-size:.75rem">${a.appointment_date} ${a.appointment_time?.slice(0, 5) ?? ''}</small></td>
                                <td><div class="user-block"><div class="u-avatar">${initials}</div><span>${a.patient_name}</span></div></td>
                                <td>د. ${a.doctor_name}</td>
                                <td>${a.specialization}</td>
                                <td>${bookTypeBadge}</td>
                                <td>${priBadge}</td>
                                <td>${stBadge}</td>
                            </tr>`;
                        }).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem;color:#94a3b8">لا توجد حجوزات</td></tr>';
                    }
                })
                .catch(() => {
                    document.getElementById('pingStatus').innerHTML = '<strong style="color:#ef4444"><i class=\'bx bx-x-circle\'></i> تعذر الاتصال</strong>';
                    document.getElementById('dbStatus').innerHTML = '<strong style="color:#ef4444"><i class=\'bx bx-x-circle\'></i> تعذر</strong>';
                });
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
                const logoutOpen = document.getElementById('logoutOverlay')?.classList.contains('active');
                if (window.innerWidth <= 768 && !logoutOpen && !sidebar.contains(e.target) && !toggle.contains(e.target) && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        }

        function showLogoutModal(e) {
            if (e) { e.preventDefault(); e.stopPropagation(); }
            document.getElementById('logoutOverlay').classList.add('active');
        }
        function closeLogoutModal() {
            document.getElementById('logoutOverlay').classList.remove('active');
        }
        function confirmLogout() {
            window.location.href = '../logout.php';
        }
        // Close on overlay click
        document.addEventListener('DOMContentLoaded', () => {
            const overlay = document.getElementById('logoutOverlay');
            if (overlay) {
                overlay.addEventListener('click', function (e) {
                    if (e.target === this) closeLogoutModal();
                });
                overlay.querySelector('.logout-modal').addEventListener('click', function (e) {
                    e.stopPropagation();
                });
            }
        });
    </script>

    <!-- Logout Confirmation Modal -->
    <div class="logout-overlay" id="logoutOverlay">
        <div class="logout-modal">
            <div class="logout-modal-icon"><i class='bx bx-log-out'></i></div>
            <h3>تسجيل الخروج</h3>
            <p>هل أنت متأكد من رغبتك في تسجيل الخروج من نظام شفاء+؟</p>
            <div class="logout-modal-btns">
                <button class="btn-logout-cancel" onclick="closeLogoutModal()"><i class='bx bx-x'></i> بقاء</button>
                <button class="btn-logout-confirm" onclick="confirmLogout()"><i class='bx bx-log-out'></i> تسجيل
                    الخروج</button>
            </div>
        </div>
    </div>
</body>

</html>
