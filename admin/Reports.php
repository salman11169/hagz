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
  <title>التقارير المفصلة - نظام فرز المواعيد الذكي</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/shared-dashboard.css?v=1.1">
  <link rel="stylesheet" href="../assets/css/admin-dashboard.css?v=1.1">
  <style>
    .loading-shimmer {
      background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
      background-size: 200% 100%;
      animation: shimmer 1.5s infinite;
      border-radius: 8px;
      height: 48px;
    }
    @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
    .stat-value.loading { color: transparent; position: relative; }
    .stat-value.loading::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
      background-size: 200% 100%;
      animation: shimmer 1.5s infinite;
      border-radius: 6px;
    }
    .period-bar {
      display: flex;
      gap: 1rem;
      align-items: center;
      background: rgba(255,255,255,0.1);
      padding: 0.5rem 1rem;
      border-radius: 12px;
      backdrop-filter: blur(10px);
      flex-shrink: 0;
    }
    .period-bar select {
      background: transparent;
      color: white;
      border: none;
      font-size: 0.95rem;
      font-weight: 700;
      outline: none;
      cursor: pointer;
      font-family: 'Cairo', sans-serif;
    }
    .period-bar select option { color: #1e293b; background: white; }
    .period-sep { width: 1px; height: 20px; background: rgba(255,255,255,0.3); }
    .export-btn {
      background: none;
      border: none;
      color: white;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 0.3rem;
      font-weight: 700;
      white-space: nowrap;
      font-family: 'Cairo', sans-serif;
      font-size: 0.95rem;
    }
    .toast-notif {
      position: fixed;
      bottom: 2rem;
      left: 50%;
      transform: translateX(-50%) translateY(100px);
      background: #1e293b;
      color: white;
      padding: 0.8rem 2rem;
      border-radius: 12px;
      font-family: 'Cairo', sans-serif;
      font-weight: 700;
      z-index: 9999;
      transition: transform 0.3s ease;
      box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    }
    .toast-notif.show { transform: translateX(-50%) translateY(0); }
    #dailyChart { height: 250px; position: relative; }
    .chart-bar-wrap {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-end;
      height: 100%;
      flex: 1;
      gap: 8px;
    }
    .chart-bar {
      width: 100%;
      max-width: 40px;
      background: linear-gradient(to top, var(--admin-primary, #2563eb), #3b82f6);
      border-radius: 6px 6px 0 0;
      transition: height 1s ease;
      min-height: 4px;
    }
    .chart-label { font-size: .8rem; color: #64748b; font-weight: 600; white-space: nowrap; }
    .chart-count { font-weight: 700; color: #475569; font-size: .9rem; }
    .progress-bar-wrap { margin-bottom: 1.2rem; }
    .progress-row { display: flex; justify-content: space-between; margin-bottom: .4rem; }
    .progress-track { width: 100%; height: 8px; background: #f1f5f9; border-radius: 4px; }
    .progress-fill { height: 100%; border-radius: 4px; transition: width 1.2s ease; }
  </style>
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
        <button class="icon-btn notif-btn">
          <i class='bx bx-bell'></i>
          <span class="badge" id="notifBadge">0</span>
        </button>
        <div class="user-menu">
          <div class="user-avatar">
            <img
              src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=2563eb&color=fff&font-family=Cairo"
              alt="User">
          </div>
          <div class="user-info">
            <span class="user-greeting">مرحباً بك،</span>
            <span class="user-name"><?= $userName ?></span>
          </div>
          <i class='bx bx-chevron-down dropdown-icon'></i>
        </div>
      </div>
    </div>
  </nav>

  <!-- Sidebar (Admin) -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-menu">
      <a href="admin.php" class="menu-item"><i class='bx bxs-dashboard'></i><span>لوحة التحكم</span></a>
      <a href="Manage_doctors.php" class="menu-item"><i class='bx bx-user-pin'></i><span>إدارة الأطباء</span></a>
      <a href="Manage_patients.php" class="menu-item"><i class='bx bx-group'></i><span>إدارة المرضى</span></a>
      <a href="Reports.php" class="menu-item active"><i class='bx bx-chart'></i><span>التقارير والإحصائيات</span></a>
      <a href="System_settings.php" class="menu-item"><i class='bx bx-cog'></i><span>إعدادات النظام</span></a>
      <a href="User_permissions.php" class="menu-item"><i class='bx bx-shield-quarter'></i><span>صلاحيات المستخدمين</span></a>
    </div>
    <div class="sidebar-bottom">
      <a href="#" onclick="showLogoutModal(event)" class="menu-item logout"><i class='bx bx-log-out'></i><span>تسجيل الخروج</span></a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <div class="dashboard-wrap">

      <!-- Page Header -->
      <div class="hero-card" style="padding: 2rem; margin-bottom: 2rem; background: linear-gradient(135deg, #4338ca 0%, #312e81 100%);">
        <div class="hero-content" style="width: 100%; max-width: 100%;">
          <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 1rem;">

            <div class="period-bar">
              <select id="periodFilter" onchange="loadReports()">
                <option value="today">اليوم</option>
                <option value="week">هذا الأسبوع</option>
                <option value="month" selected>هذا الشهر</option>
                <option value="quarter">هذا الربع</option>
                <option value="year">هذا العام</option>
              </select>
              <div class="period-sep"></div>
              <button class="export-btn" onclick="exportReport()">
                <i class='bx bx-download' style="font-size: 1.2rem;"></i> تصدير CSV
              </button>
            </div>

            <div>
              <h1 style="font-size: 1.8rem; margin-bottom: 0;"><i class='bx bx-bar-chart-alt-2'></i> التقارير والإحصائيات المفصلة</h1>
              <p style="margin-top: 0.5rem; font-size: 1rem; opacity: 0.85;">مراقبة الأداء وتحليل البيانات الحية للنظام</p>
            </div>

          </div>
        </div>
        <div class="hero-glow"></div>
      </div>

      <!-- KPIs -->
      <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="stat-card">
          <div class="stat-icon s-blue"><i class='bx bx-calendar'></i></div>
          <div class="stat-info">
            <h3>إجمالي المواعيد</h3>
            <div class="stat-value loading" id="totalAppts">…</div>
            <div class="stat-trend" id="trendTotalAppts"><i class='bx bx-loader-alt bx-spin'></i> جارٍ التحميل</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon s-green"><i class='bx bx-check-circle'></i></div>
          <div class="stat-info">
            <h3>مواعيد مكتملة</h3>
            <div class="stat-value loading" id="completedAppts">…</div>
            <div class="stat-trend" id="trendCompletedAppts"><i class='bx bx-loader-alt bx-spin'></i> جارٍ التحميل</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon s-orange"><i class='bx bx-time'></i></div>
          <div class="stat-info">
            <h3>متوسط وقت الاستشارة</h3>
            <div class="stat-value loading"><span id="avgWaitTime">…</span> <span class="currency" id="waitUnit"></span></div>
            <div class="stat-trend" id="trendWait"><i class='bx bx-loader-alt bx-spin'></i> جارٍ التحميل</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" style="background: linear-gradient(135deg, #ef4444, #b91c1c);"><i class='bx bx-error-circle'></i></div>
          <div class="stat-info">
            <h3>حالات حرجة</h3>
            <div class="stat-value loading" id="emergencyCases">…</div>
            <div class="stat-trend warning" id="trendEmergencyCases"><i class='bx bx-loader-alt bx-spin'></i> جارٍ التحميل</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon s-purple"><i class='bx bx-group'></i></div>
          <div class="stat-info">
            <h3>إجمالي المرضى</h3>
            <div class="stat-value loading" id="totalPatients">…</div>
            <div class="stat-trend positive" id="trendTotalPatients"><i class='bx bx-loader-alt bx-spin'></i> جارٍ التحميل</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon s-blue" style="background: linear-gradient(135deg, #0ea5e9, #0369a1);"><i class='bx bx-x-circle'></i></div>
          <div class="stat-info">
            <h3>مواعيد ملغاة</h3>
            <div class="stat-value loading" id="cancelledAppts">…</div>
            <div class="stat-trend warning" id="trendCancelledAppts"><i class='bx bx-loader-alt bx-spin'></i> جارٍ التحميل</div>
          </div>
        </div>
      </div>

      <!-- Charts & Content Grid -->
      <div class="content-grid" style="margin-top: 2rem;">

        <!-- Daily Chart -->
        <div class="content-card col-span-2">
          <div class="card-header border-bottom">
            <h2><i class='bx bx-line-chart'></i> أداء المواعيد اليومي (آخر 7 أيام)</h2>
            <button class="btn-text" onclick="loadReports()"><i class='bx bx-refresh'></i> تحديث</button>
          </div>
          <div class="card-body" style="padding: 1.5rem;">
            <div id="dailyChart" style="display: flex; align-items: flex-end; justify-content: space-around; gap: 10px; padding-top: 20px; border-bottom: 2px solid #e2e8f0;">
              <div class="loading-shimmer" style="width:100%;"></div>
            </div>
          </div>
        </div>

        <!-- Priority Distribution -->
        <div class="content-card">
          <div class="card-header border-bottom">
            <h2><i class='bx bx-pie-chart-alt-2'></i> توزيع الأولويات</h2>
          </div>
          <div class="card-body" style="padding: 1.5rem;">
            <div id="priorityBars">
              <div class="loading-shimmer" style="margin-bottom:1rem;"></div>
              <div class="loading-shimmer" style="margin-bottom:1rem;"></div>
              <div class="loading-shimmer"></div>
            </div>
          </div>
        </div>

        <!-- Top Specialties -->
        <div class="content-card">
          <div class="card-header border-bottom">
            <h2><i class='bx bx-clinic'></i> التخصصات الأكثر طلباً</h2>
          </div>
          <div class="card-body" style="padding: 1.5rem;">
            <div id="specialtiesBars">
              <div class="loading-shimmer" style="margin-bottom:1rem;"></div>
              <div class="loading-shimmer" style="margin-bottom:1rem;"></div>
              <div class="loading-shimmer"></div>
            </div>
          </div>
        </div>

        <!-- Top Doctors -->
        <div class="content-card">
          <div class="card-header border-bottom">
            <h2><i class='bx bx-user-pin'></i> أداء الأطباء (الأعلى حجوزات)</h2>
          </div>
          <div class="card-body" style="padding: 1.5rem;">
            <div id="doctorsBars">
              <div class="loading-shimmer" style="margin-bottom:1rem;"></div>
              <div class="loading-shimmer" style="margin-bottom:1rem;"></div>
              <div class="loading-shimmer"></div>
            </div>
          </div>
        </div>

        <!-- Completion Rate Card -->
        <div class="content-card">
          <div class="card-header border-bottom" style="background: linear-gradient(to left, #f8fafc, white);">
            <h2><i class='bx bx-info-circle' style="color: var(--admin-primary)"></i> معدل الإنجاز</h2>
          </div>
          <div class="card-body" style="padding: 1.5rem; text-align: center;">
            <div style="position:relative; width:120px; height:120px; margin: 0 auto 1rem;">
              <svg viewBox="0 0 36 36" style="width:120px;height:120px;transform:rotate(-90deg);">
                <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f1f5f9" stroke-width="3"></circle>
                <circle id="completionCircle" cx="18" cy="18" r="15.9" fill="none" stroke="#10b981" stroke-width="3"
                  stroke-dasharray="0 100" stroke-linecap="round" style="transition:stroke-dasharray 1.2s ease;"></circle>
              </svg>
              <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column;">
                <span id="completionPct" style="font-size:1.4rem;font-weight:900;color:#10b981;">0%</span>
                <span style="font-size:.7rem;color:#64748b;font-weight:600;">إنجاز</span>
              </div>
            </div>
            <h4 style="color: var(--surface-dark); font-weight: 800; margin-bottom: 0.5rem;">معدل الإتمام</h4>
            <p style="color: #64748b; font-size: 0.9rem;" id="completionNote">يتم احتساب المعدل من المواعيد المكتملة</p>
            <button class="btn-glass primary" style="width: 100%; margin-top: 1rem; justify-content: center;"
              onclick="window.location.href='System_settings.php'">
              إدارة النظام
            </button>
          </div>
        </div>

      </div>

      <!-- Recent Appointments Table -->
      <div class="content-card" style="margin-top: 2rem;">
        <div class="card-header border-bottom">
          <h2><i class='bx bx-table'></i> تفاصيل المواعيد الأخيرة</h2>
          <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <span id="tableCount" style="font-size:0.85rem;color:#64748b;font-weight:600;"></span>
            <div style="display:flex;align-items:center;gap:.4rem;">
              <label style="font-size:.82rem;color:#64748b;font-weight:600;">عرض:</label>
              <select id="pageSizeSelect" onchange="changePageSize()"
                style="border:1.5px solid #e2e8f0;border-radius:8px;padding:.2rem .5rem;
                       font-family:'Cairo',sans-serif;font-size:.82rem;font-weight:700;
                       color:#1e293b;outline:none;cursor:pointer;">
                <option value="10">10</option>
                <option value="20" selected>20</option>
                <option value="50">50</option>
              </select>
            </div>
          </div>
        </div>
        <div class="table-responsive" style="padding-top: 1rem;">
          <table class="premium-table">
            <thead>
              <tr>
                <th>#</th>
                <th>التاريخ</th>
                <th>المريض</th>
                <th>الطبيب</th>
                <th>التخصص</th>
                <th>الأولوية</th>
                <th>الحالة</th>
              </tr>
            </thead>
            <tbody id="reportsTable">
              <tr><td colspan="7" style="text-align:center;padding:2rem;">
                <i class='bx bx-loader-alt bx-spin' style="font-size:2rem;color:#cbd5e1;"></i>
              </td></tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination Controls -->
        <div id="paginationBar" style="display:none;padding:1rem 1.5rem;
             border-top:1px solid #f1f5f9;display:flex;align-items:center;
             justify-content:space-between;flex-wrap:wrap;gap:.8rem;">
          <span id="paginationInfo" style="font-size:.85rem;color:#64748b;font-weight:600;"></span>
          <div id="paginationBtns" style="display:flex;gap:.4rem;flex-wrap:wrap;"></div>
        </div>
      </div>

    </div>

    <!-- Toast Notification -->
    <div class="toast-notif" id="toastNotif"></div>

    <script>
      const API = '../controllers/AdminController.php';

      // ─── Sidebar ───────────────────────────────────────────
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
          if (window.innerWidth <= 768 && !logoutOpen && !sidebar.contains(e.target) &&
              !toggle.contains(e.target) && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            document.body.style.overflow = '';
          }
        });
      }

      // ─── Toast ─────────────────────────────────────────────
      function showToast(msg, isError = false) {
        const el = document.getElementById('toastNotif');
        el.textContent = msg;
        el.style.background = isError ? '#dc2626' : '#1e293b';
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 3000);
      }

      // ─── Main Load ─────────────────────────────────────────
      async function loadReports() {
        const period = document.getElementById('periodFilter').value;

        // Set loading states
        ['totalAppts','completedAppts','avgWaitTime','emergencyCases','totalPatients','cancelledAppts'].forEach(id => {
          const el = document.getElementById(id);
          if (el) { el.textContent = '…'; el.parentElement?.classList.add('loading'); }
        });
        document.getElementById('dailyChart').innerHTML =
          '<div class="loading-shimmer" style="width:100%;height:200px;"></div>';

        try {
          const res  = await fetch(`${API}?action=reports&period=${period}`);
          const json = await res.json();

          if (!json.success) throw new Error(json.message || 'فشل التحميل');

          // KPIs — data is merged at root level (not nested under .data)
          const total     = parseInt(json.total_appointments     ?? 0);
          const completed = parseInt(json.completed_appointments ?? 0);
          const cancelled = parseInt(json.cancelled_appointments ?? 0);
          const emergency = parseInt(json.emergency_cases        ?? 0);
          const avgWait   = Math.round(parseFloat(json.avg_wait_time ?? 0));
          const patients  = parseInt(json.total_patients         ?? 0);

          setKPI('totalAppts',      total,     total > 0 ? `positive` : '',   `${total} في الفترة المحددة`);
          setKPI('completedAppts',  completed, 'positive', `من أصل ${total} موعد`);
          setKPI('cancelledAppts',  cancelled, cancelled > 0 ? 'warning' : 'positive', `موعد ملغى`);
          setKPI('emergencyCases',  emergency, emergency > 0 ? 'warning' : 'positive', `حالة حرجة مسجلة`);
          setKPI('totalPatients',   patients,  'positive', `مريض مسجل إجمالاً`);

          // Avg wait time — id is on <span>, closest .stat-value holds loading class
          const waitSpan = document.getElementById('avgWaitTime');
          if (waitSpan) {
            waitSpan.textContent = avgWait > 0 ? avgWait : '—';
            waitSpan.closest('.stat-value')?.classList.remove('loading');
          }
          const unitEl = document.getElementById('waitUnit');
          if (unitEl) unitEl.textContent = avgWait > 0 ? 'دقيقة' : '';
          const waitTrend = document.getElementById('trendWait');
          if (waitTrend) {
            waitTrend.className = 'stat-trend ' + (avgWait > 0 ? 'positive' : 'warning');
            waitTrend.innerHTML = avgWait > 0
              ? `<i class='bx bx-time'></i> متوسط كل استشارة`
              : `<i class='bx bx-info-circle'></i> يُحسب من وقت بدء/انتهاء الاستشارة`;
          }

          // Completion rate circle
          const pct = total > 0 ? Math.round(completed / total * 100) : 0;
          document.getElementById('completionPct').textContent = pct + '%';
          document.getElementById('completionCircle').setAttribute('stroke-dasharray', `${pct} ${100 - pct}`);
          document.getElementById('completionNote').textContent =
            `${completed} مكتمل من أصل ${total} موعد في الفترة المحددة`;

          // Charts
          renderDailyChart(json.daily_stats || []);
          renderPriorityBars(json.priorities || []);
          renderSpecialtiesBars(json.specialties || []);
          renderDoctorsBars(json.top_doctors || []);
          renderTable(json.recent_appointments || []);

        } catch (err) {
          console.error('تعذر تحميل التقارير:', err);
          showToast('تعذر تحميل التقارير: ' + err.message, true);
          document.getElementById('totalAppts').textContent    = 'خطأ';
          document.getElementById('completedAppts').textContent = 'خطأ';
          document.getElementById('dailyChart').innerHTML =
            '<div style="text-align:center;color:#ef4444;padding:2rem;width:100%"><i class="bx bx-error"></i> تعذر تحميل البيانات</div>';
        }
      }

      function setKPI(id, value, trendClass, trendText) {
        const el = document.getElementById(id);
        if (!el) return;
        // Use en-US locale to keep Western Arabic numerals (0-9), not Arabic-Indic (٠-٩)
        el.textContent = Number(value).toLocaleString('en-US');
        el.classList.remove('loading');
        const trendId = 'trend' + id.charAt(0).toUpperCase() + id.slice(1);
        const trendEl = document.getElementById(trendId);
        if (trendEl) {
          trendEl.className = 'stat-trend ' + trendClass;
          trendEl.innerHTML = `<i class='bx bx-info-circle'></i> ${trendText}`;
        }
      }

      // ─── Daily Bar Chart ───────────────────────────────────
      function renderDailyChart(stats) {
        const container = document.getElementById('dailyChart');
        if (!stats.length) {
          container.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:2rem;width:100%">لا توجد بيانات للفترة المحددة</div>';
          return;
        }
        const vals = stats.map(s => parseInt(s.count || 0));
        const max  = Math.max(...vals, 1);
        container.style.cssText = 'display:flex;align-items:flex-end;justify-content:space-around;gap:10px;padding-top:20px;border-bottom:2px solid #e2e8f0;height:250px;';
        container.innerHTML = stats.map(s => {
          const v   = parseInt(s.count || 0);
          const pct = Math.round(v / max * 100);
          const lbl = s.day_name || new Date(s.date).toLocaleDateString('ar-SA', { weekday: 'short' });
          return `<div class="chart-bar-wrap">
            <span class="chart-count">${v}</span>
            <div class="chart-bar" style="height:${pct}%;"></div>
            <span class="chart-label">${lbl}</span>
          </div>`;
        }).join('');
      }

      // ─── Priority Bars ─────────────────────────────────────
      function renderPriorityBars(items) {
        const container = document.getElementById('priorityBars');
        if (!items.length) {
          container.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:2rem">لا توجد بيانات</div>';
          return;
        }
        const total = items.reduce((s, i) => s + parseInt(i.count || 0), 0) || 1;
        const colorMap = { Critical: '#ef4444', Medium: '#f59e0b', Routine: '#10b981' };
        const labelMap = { Critical: 'حالة حرجة', Medium: 'حالة عاجلة', Routine: 'حالة مستقرة' };
        container.innerHTML = items.map(i => {
          const color = colorMap[i.priority] ?? '#6366f1';
          const label = labelMap[i.priority] ?? i.priority;
          const cnt   = parseInt(i.count || 0);
          const pct   = Math.round(cnt / total * 100);
          return `<div class="progress-bar-wrap">
            <div class="progress-row">
              <span style="font-weight:700;color:var(--surface-dark);font-size:.9rem">${label}</span>
              <span style="font-weight:800;color:${color}">${cnt} <span style="font-size:.8rem;color:#64748b;font-weight:600">(${pct}%)</span></span>
            </div>
            <div class="progress-track">
              <div class="progress-fill" style="width:${pct}%;background:${color};"></div>
            </div>
          </div>`;
        }).join('');
      }

      // ─── Specialties Bars ──────────────────────────────────
      function renderSpecialtiesBars(items) {
        const container = document.getElementById('specialtiesBars');
        if (!items.length) {
          container.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:2rem">لا توجد بيانات</div>';
          return;
        }
        const max = Math.max(...items.map(i => parseInt(i.count || 0)), 1);
        container.innerHTML = items.slice(0, 5).map(i => {
          const pct   = Math.round(parseInt(i.count) / max * 100);
          const label = i.specialization ?? i.name ?? '—';
          return `<div class="progress-bar-wrap">
            <div class="progress-row">
              <span style="font-weight:700;color:var(--surface-dark);font-size:.9rem">${label}</span>
              <span style="font-weight:800;color:#6366f1">${i.count} موعد</span>
            </div>
            <div class="progress-track">
              <div class="progress-fill" style="width:${pct}%;background:linear-gradient(90deg,#6366f1,#8b5cf6);"></div>
            </div>
          </div>`;
        }).join('');
      }

      // ─── Doctors Bars ──────────────────────────────────────
      function renderDoctorsBars(items) {
        const container = document.getElementById('doctorsBars');
        if (!items.length) {
          container.innerHTML = '<div style="text-align:center;color:#94a3b8;padding:2rem">لا توجد بيانات</div>';
          return;
        }
        const max = Math.max(...items.map(i => parseInt(i.count || 0)), 1);
        container.innerHTML = items.slice(0, 5).map(i => {
          const pct  = Math.round(parseInt(i.count) / max * 100);
          const name = i.doctor_name ?? i.name ?? '—';
          return `<div class="progress-bar-wrap">
            <div class="progress-row">
              <span style="font-weight:700;color:var(--surface-dark);font-size:.9rem">${name}</span>
              <span style="font-weight:800;color:#0d9488">${i.count} كشف</span>
            </div>
            <div class="progress-track">
              <div class="progress-fill" style="width:${pct}%;background:linear-gradient(90deg,#0d9488,#14b8a6);"></div>
            </div>
          </div>`;
        }).join('');
      }

      // ─── Table Pagination State ─────────────────────────────
      let _tableRows  = [];
      let _tablePage  = 1;
      let _tableSize  = 20;

      // ─── Recent Table ──────────────────────────────────────
      function renderTable(rows) {
        _tableRows = rows;
        _tablePage = 1;
        _tableSize = parseInt(document.getElementById('pageSizeSelect')?.value || 20);
        renderTablePage();
      }

      function changePageSize() {
        _tableSize = parseInt(document.getElementById('pageSizeSelect').value);
        _tablePage = 1;
        renderTablePage();
      }

      function goToPage(p) {
        _tablePage = p;
        renderTablePage();
      }

      function renderTablePage() {
        const tbody  = document.getElementById('reportsTable');
        const count  = document.getElementById('tableCount');
        const bar    = document.getElementById('paginationBar');
        const info   = document.getElementById('paginationInfo');
        const btns   = document.getElementById('paginationBtns');

        if (!_tableRows.length) {
          tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:2rem">لا توجد مواعيد في الفترة المحددة</td></tr>';
          if (count) count.textContent = '';
          if (bar)   bar.style.display = 'none';
          return;
        }

        const total      = _tableRows.length;
        const totalPages = Math.ceil(total / _tableSize);
        if (_tablePage > totalPages) _tablePage = totalPages;

        const start = (_tablePage - 1) * _tableSize;
        const end   = Math.min(start + _tableSize, total);
        const slice = _tableRows.slice(start, end);

        if (count) count.textContent = `إجمالي ${total} موعد في الفترة`;
        if (info)  info.textContent  = `يعرض ${start + 1}–${end} من ${total}`;

        const statusMap   = { Completed:'مكتمل', Cancelled:'ملغي', Pending:'قيد الانتظار', Confirmed:'مؤكد', Transferred:'محوَّل' };
        const priorityMap = { Critical:'حرجة', High:'عاجلة', Medium:'عاجلة', Routine:'مستقرة', Normal:'مستقرة' };

        tbody.innerHTML = slice.map((a, idx) => {
          const sClass  = a.status === 'Completed' ? 'b-success'
                        : a.status === 'Cancelled' ? 'b-danger' : 'b-warning';
          const pClass  = a.priority === 'Critical' ? 'b-danger'
                        : (a.priority === 'High' || a.priority === 'Medium') ? 'b-warning' : 'b-success';
          const sLabel  = statusMap[a.status]     ?? a.status   ?? '—';
          const pLabel  = priorityMap[a.priority] ?? a.priority ?? '—';
          const dateStr = a.appointment_date
            ? new Date(a.appointment_date).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' })
            : '—';
          return `<tr>
            <td style="color:#94a3b8;font-weight:600;">${start + idx + 1}</td>
            <td><div style="font-weight:700;color:var(--surface-dark)">
              <i class='bx bx-calendar' style="color:#94a3b8;margin-left:.3rem"></i>${dateStr}
            </div></td>
            <td style="font-weight:800;color:#1e293b">${a.patient_name ?? '—'}</td>
            <td><span style="font-weight:600">${a.doctor_name ?? '—'}</span></td>
            <td><span style="color:#64748b;font-size:.9rem">${a.specialization ?? '—'}</span></td>
            <td><span class="badge-status ${pClass}">${pLabel}</span></td>
            <td><span class="badge-status ${sClass}">${sLabel}</span></td>
          </tr>`;
        }).join('');

        // ── Pagination buttons ──
        if (bar) bar.style.display = totalPages <= 1 ? 'none' : 'flex';
        if (!btns) return;

        let html = '';

        // Prev
        html += `<button onclick="goToPage(${_tablePage - 1})" ${ _tablePage === 1 ? 'disabled' : '' }
          style="padding:.35rem .8rem;border-radius:8px;border:1.5px solid #e2e8f0;
                 background:${ _tablePage === 1 ? '#f8fafc' : 'white' };
                 color:${ _tablePage === 1 ? '#cbd5e1' : '#6366f1' };font-family:'Cairo',sans-serif;
                 font-weight:700;cursor:${ _tablePage === 1 ? 'default' : 'pointer' };font-size:.82rem;">
          <i class='bx bx-chevron-right'></i>
        </button>`;

        // Page numbers (show max 5 around current)
        const delta = 2;
        let pages = new Set([1, totalPages]);
        for (let p = Math.max(1, _tablePage - delta); p <= Math.min(totalPages, _tablePage + delta); p++) pages.add(p);
        let prev = 0;
        [...pages].sort((a,b)=>a-b).forEach(p => {
          if (prev && p - prev > 1) html += `<span style="padding:.35rem .4rem;color:#94a3b8;">…</span>`;
          html += `<button onclick="goToPage(${p})"
            style="padding:.35rem .7rem;border-radius:8px;min-width:36px;
                   border:1.5px solid ${ p === _tablePage ? '#6366f1' : '#e2e8f0' };
                   background:${ p === _tablePage ? '#6366f1' : 'white' };
                   color:${ p === _tablePage ? 'white' : '#1e293b' };
                   font-family:'Cairo',sans-serif;font-weight:700;cursor:pointer;font-size:.82rem;">
            ${p}
          </button>`;
          prev = p;
        });

        // Next
        html += `<button onclick="goToPage(${_tablePage + 1})" ${ _tablePage === totalPages ? 'disabled' : '' }
          style="padding:.35rem .8rem;border-radius:8px;border:1.5px solid #e2e8f0;
                 background:${ _tablePage === totalPages ? '#f8fafc' : 'white' };
                 color:${ _tablePage === totalPages ? '#cbd5e1' : '#6366f1' };font-family:'Cairo',sans-serif;
                 font-weight:700;cursor:${ _tablePage === totalPages ? 'default' : 'pointer' };font-size:.82rem;">
          <i class='bx bx-chevron-left'></i>
        </button>`;

        btns.innerHTML = html;
      }

      // ─── Export CSV ────────────────────────────────────────
      async function exportReport() {
        const period = document.getElementById('periodFilter').value;
        showToast('جارٍ إعداد التقرير...');
        try {
          const res  = await fetch(`${API}?action=reports&period=${period}`);
          const json = await res.json();
          if (!json.success) throw new Error(json.message);
          const rows = json.recent_appointments || [];
          if (!rows.length) { showToast('لا توجد بيانات للتصدير', true); return; }

          const header = 'التاريخ,المريض,الطبيب,التخصص,الأولوية,الحالة\n';
          const csv = header + rows.map(a =>
            `"${a.appointment_date ?? ''}","${a.patient_name ?? ''}","${a.doctor_name ?? ''}","${a.specialization ?? ''}","${a.priority ?? ''}","${a.status ?? ''}"`
          ).join('\n');

          const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
          const url  = URL.createObjectURL(blob);
          const link = document.createElement('a');
          link.href = url;
          link.download = `تقرير_شفاء_${period}_${new Date().toISOString().slice(0,10)}.csv`;
          link.click();
          URL.revokeObjectURL(url);
          showToast('✓ تم تصدير التقرير بنجاح');
        } catch (err) {
          showToast('فشل التصدير: ' + err.message, true);
        }
      }

      // ─── Logout ────────────────────────────────────────────
      function showLogoutModal(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        document.getElementById('logoutOverlay').classList.add('active');
      }
      function closeLogoutModal() { document.getElementById('logoutOverlay').classList.remove('active'); }
      function confirmLogout()    { window.location.href = '../logout.php'; }

      // ─── Init ──────────────────────────────────────────────
      window.addEventListener('DOMContentLoaded', () => {
        initSidebar();
        loadReports();

        const overlay = document.getElementById('logoutOverlay');
        if (overlay) {
          overlay.addEventListener('click', e => { if (e.target === overlay) closeLogoutModal(); });
          overlay.querySelector('.logout-modal')?.addEventListener('click', e => e.stopPropagation());
        }
      });
    </script>
  </main>

  <!-- Logout Confirmation Modal -->
  <div class="logout-overlay" id="logoutOverlay">
    <div class="logout-modal">
      <div class="logout-modal-icon"><i class='bx bx-log-out'></i></div>
      <h3>تسجيل الخروج</h3>
      <p>هل أنت متأكد من رغبتك في تسجيل الخروج من نظام شفاء+؟</p>
      <div class="logout-modal-btns">
        <button class="btn-logout-cancel" onclick="closeLogoutModal()"><i class='bx bx-x'></i> بقاء</button>
        <button class="btn-logout-confirm" onclick="confirmLogout()"><i class='bx bx-log-out'></i> تسجيل الخروج</button>
      </div>
    </div>
  </div>

</body>
</html>
