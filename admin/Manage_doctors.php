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
  <title>إدارة الأطباء - نظام فرز المواعيد الذكي</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
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
        <button class="icon-btn notif-btn">
          <i class='bx bx-bell'></i>
          <span class="badge">0</span>
        </button>
        <div class="user-menu">
          <div class="user-avatar">
            <img
              src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=2563eb&color=fff&font-family=Cairo"
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
      <a href="admin.php" class="menu-item"><i class='bx bxs-dashboard'></i><span>لوحة التحكم</span></a>
      <a href="Manage_doctors.php" class="menu-item active"><i class='bx bx-user-pin'></i><span>إدارة الأطباء</span></a>
      <a href="Manage_patients.php" class="menu-item"><i class='bx bx-group'></i><span>إدارة المرضى</span></a>
      <a href="Reports.php" class="menu-item"><i class='bx bx-chart'></i><span>التقارير والإحصائيات</span></a>
      <a href="System_settings.php" class="menu-item"><i class='bx bx-cog'></i><span>إعدادات النظام</span></a>
      <a href="User_permissions.php" class="menu-item"><i class='bx bx-shield-quarter'></i><span>صلاحيات
          المستخدمين</span></a>
    </div>
    <div class="sidebar-bottom">
      <a href="#" onclick="showLogoutModal(event)" class="menu-item logout"><i class='bx bx-log-out'></i><span>تسجيل
          الخروج</span></a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">

    <div class="dashboard-wrap">

      <!-- Page Header -->
      <div class="hero-card" style="padding: 2rem; margin-bottom: 2rem;">
        <div class="hero-content">
          <h1 style="font-size: 1.8rem; margin-bottom: 0;"><i class='bx bx-user-voice'></i> إدارة الأطباء</h1>
          <p style="margin-top: 0.5rem; font-size: 1rem;">التحكم الكامل في سجلات وبيانات الطاقم الطبي</p>
        </div>
        <div class="hero-actions">
          <button class="btn-glass" onclick="window.location.href='Add_doctor.php'">
            <i class='bx bx-plus'></i> إضافة طبيب
          </button>
          <button class="btn-glass primary" onclick="exportDoctors()">
            <i class='bx bx-download'></i> تصدير البيانات
          </button>
        </div>
        <div class="hero-glow"></div>
      </div>

      <!-- Stats Grid -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon s-blue"><i class='bx bx-group'></i></div>
          <div class="stat-info">
            <h3>إجمالي الأطباء</h3>
            <div class="stat-value" id="totalDoctors">0</div>
            <div class="stat-trend positive"><i class='bx bx-up-arrow-alt'></i> متاحون بالخدمة</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon s-green"><i class='bx bx-check-circle'></i></div>
          <div class="stat-info">
            <h3>أطباء نشطون</h3>
            <div class="stat-value" id="activeDoctors">0</div>
            <div class="stat-trend positive"><i class='bx bx-check'></i> على رأس العمل</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon s-orange"><i class='bx bx-time'></i></div>
          <div class="stat-info">
            <h3>مشغولون الآن</h3>
            <div class="stat-value" id="busyDoctors">0</div>
            <div class="stat-trend warning"><i class='bx bx-pulse'></i> يعالجون مرضى</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon s-purple"><i class='bx bx-calendar-check'></i></div>
          <div class="stat-info">
            <h3>مواعيد اليوم</h3>
            <div class="stat-value" id="todayAppts">0</div>
            <div class="stat-trend positive"><i class='bx bx-calendar-event'></i> مجدولة</div>
          </div>
        </div>
      </div>

      <!-- Main Content (Filters and Grid) -->
      <div class="content-grid" style="grid-template-columns: 1fr;">

        <!-- Filters (Glassmorphism layout) -->
        <div class="content-card" style="margin-bottom: 2rem;">
          <div class="card-header border-bottom">
            <h2><i class='bx bx-filter-alt'></i> خيارات التصفية</h2>
            <button class="btn-text" onclick="resetFilters()">
              <i class='bx bx-refresh'></i> إعادة تعيين
            </button>
          </div>
          <div class="card-body" style="padding: 1.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
              <div>
                <label
                  style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; display: block;">بحث
                  بالاسم</label>
                <input type="text" id="searchName" placeholder="ابحث عن طبيب..." onkeyup="filterDoctors()"
                  style="width: 100%; padding: 0.8rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-family: 'Cairo'; background: #f8fafc; transition: border 0.3s; outline: none;">
              </div>
              <div>
                <label
                  style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; display: block;">التخصص</label>
                <select id="filterSpec" onchange="filterDoctors()"
                  style="width: 100%; padding: 0.8rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-family: 'Cairo'; background: #f8fafc; outline: none;">
                  <option value="">جميع التخصصات</option>
                  <option>طب عام</option>
                  <option>طب طوارئ</option>
                  <option>طب باطني</option>
                  <option>جراحة</option>
                  <option>أطفال</option>
                  <option>عظام</option>
                </select>
              </div>
              <div>
                <label
                  style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; display: block;">الحالة</label>
                <select id="filterStatus" onchange="filterDoctors()"
                  style="width: 100%; padding: 0.8rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-family: 'Cairo'; background: #f8fafc; outline: none;">
                  <option value="">جميع الحالات</option>
                  <option value="active">نشط</option>
                  <option value="busy">مشغول</option>
                  <option value="inactive">غير نشط</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Doctors Table -->
        <div class="content-card">
          <div class="card-header border-bottom">
            <h2><i class='bx bx-list-ul'></i> قائمة الأطباء</h2>
          </div>

          <div class="table-responsive" style="padding-top: 1rem;">
            <table class="premium-table">
              <thead>
                <tr>
                  <th>الطبيب</th>
                  <th>التخصص</th>
                  <th>معلومات الاتصال</th>
                  <th>مواعيد اليوم</th>
                  <th>ترخيص / خبرة</th>
                  <th>الحالة</th>
                  <th>إجراءات</th>
                </tr>
              </thead>
              <tbody id="doctorsTableBody">
                <!-- Rendered by JS -->
              </tbody>
            </table>

            <!-- Empty state shown via JS -->
            <div id="emptyState" style="display:none; text-align: center; padding: 4rem 2rem;">
              <i class='bx bx-user-x' style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
              <h3 style="color: #475569;">لا توجد نتائج</h3>
              <p style="color: #64748b;">لم يتم العثور على أطباء مطابقين للبحث</p>
            </div>
          </div>
        </div>

      </div> <!-- /content-grid -->

    </div> <!-- /dashboard-wrap -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      const API = '../controllers/AdminController.php';
      let allDoctors = [];
      let filteredDoctors = [];

      // ===== Load specializations for filter =====
      function loadSpecializations() {
        fetch(API + '?action=specializations')
          .then(r => r.json())
          .then(data => {
            if (!data.success) return;
            const sel = document.getElementById('filterSpec');
            sel.innerHTML = '<option value="">' + 'جميع التخصصات' + '</option>';
            data.specializations.forEach(function (s) {
              const o = document.createElement('option');
              o.value = s.name; o.textContent = s.name;
              sel.appendChild(o);
            });
          });
      }

      // ===== Bootstrap: load on page ready =====
      document.addEventListener('DOMContentLoaded', () => {
        initSidebar();
        loadDoctors();
        loadSpecializations();
      });

      // ===== Fetch doctors from backend =====
      function loadDoctors() {
        var tbody = document.getElementById('doctorsTableBody');
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:2rem"><i class="bx bx-loader-alt bx-spin" style="font-size:2rem;color:#2563eb"></i></td></tr>';

        fetch(API + '?action=doctors')
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (!data.success) throw new Error(data.message);
            allDoctors = data.doctors || [];
            filteredDoctors = allDoctors.slice();
            updateStats();
            renderDoctors();
          })
          .catch(function (err) {
            document.getElementById('doctorsTableBody').innerHTML =
              '<tr><td colspan="7" style="text-align:center;color:#ef4444;padding:2rem">تعذر تحميل بيانات الأطباء: ' + err.message + '</td></tr>';
          });
      }

      // ===== Stats =====
      function updateStats() {
        document.getElementById('totalDoctors').textContent = allDoctors.length;
        document.getElementById('activeDoctors').textContent = allDoctors.filter(d => d.is_active == 1).length;
        document.getElementById('busyDoctors').textContent = 0; // no busy field yet
        document.getElementById('todayAppts').textContent = allDoctors.reduce((s, d) => s + (parseInt(d.today_appointments) || 0), 0);
      }

      // ===== Render table rows =====
      function renderDoctors() {
        const tbody = document.getElementById('doctorsTableBody');
        const empty = document.getElementById('emptyState');
        const table = document.querySelector('.premium-table');

        if (!filteredDoctors.length) {
          table.style.display = 'none';
          empty.style.display = 'block';
          return;
        }
        table.style.display = 'table';
        empty.style.display = 'none';
        tbody.innerHTML = filteredDoctors.map(createDoctorRow).join('');
      }

      function createDoctorRow(d) {
        var isActive = d.is_active == 1;
        var statusLabel = isActive ? 'نشط' : 'غير نشط';
        var statusClass = isActive ? 'b-success' : 'b-danger';
        var fullName = d.first_name + ' ' + d.last_name;
        var initial = d.first_name ? d.first_name[0] : 'ط';
        var license = d.license_number ? '<i class="bx bx-id-card"></i> ' + d.license_number : '—';
        var exp = d.experience_years ? d.experience_years + ' سنوات' : '';
        var toggleIcon = isActive ? 'bx-block' : 'bx-check-circle';
        var newStatus = isActive ? 0 : 1;

        return '<tr>' +
          '<td><div class="user-block"><div class="u-avatar">' + initial + '</div>' +
          '<div><div style="font-weight:700;color:var(--surface-dark)">د. ' + fullName + '</div>' +
          '<div style="font-size:.8rem;color:#64748b">' + (d.email || '') + '</div></div></div></td>' +
          '<td><span style="font-weight:600;color:#475569">' + (d.specialization || '—') + '</span></td>' +
          '<td><div style="font-size:.85rem;color:#64748b"><i class="bx bx-phone"></i> ' + (d.phone || '—') + '</div>' +
          '<div style="font-size:.85rem;color:#64748b"><i class="bx bx-envelope"></i> ' + (d.email || '—') + '</div></td>' +
          '<td><span style="font-weight:800;color:var(--admin-primary)">' + (d.today_appointments || 0) + '</span> موعد</td>' +
          '<td><div style="font-size:.83rem;font-weight:700;color:#4f46e5">' + license + '</div>' +
          '<div style="font-size:.78rem;color:#94a3b8">' + exp + '</div></td>' +
          '<td><span class="badge-status ' + statusClass + '">' + statusLabel + '</span></td>' +
          '<td><div style="display:flex;gap:.4rem;flex-wrap:wrap">' +
          '<button class="edit-btn" onclick="location.href=\'Add_doctor.php?id=' + d.doctor_id + '\'" title="تعديل"><i class="bx bx-edit-alt"></i></button>' +
          '<button class="action-btn" title="تفعيل/تعطيل" onclick="toggleDoctor(' + d.doctor_id + ',\'' + fullName + '\',' + newStatus + ')">' +
          '<i class="bx ' + toggleIcon + '"></i></button></div></td></tr>';
      }

      // ===== Toggle doctor active status =====
      function toggleDoctor(doctorId, name, newStatus) {
        const action = newStatus ? 'تفعيل' : 'تعطيل';
        if (!confirm(`هل تريد ${action} حساب د. ${name}؟`)) return;

        fetch(`${API}?action=toggle_doctor`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ doctor_id: doctorId, is_active: newStatus })
        })
          .then(r => r.json())
          .then(data => {
            if (data.success) { loadDoctors(); }
            else { alert('خطأ: ' + data.message); }
          })
          .catch(() => alert('حدث خطأ أثناء التعديل'));
      }

      // ===== Filter =====
      function filterDoctors() {
        const q = document.getElementById('searchName').value.trim().toLowerCase();
        const spec = document.getElementById('filterSpec').value.toLowerCase();
        const status = document.getElementById('filterStatus').value;

        filteredDoctors = allDoctors.filter(d => {
          const name = `${d.first_name} ${d.last_name}`.toLowerCase();
          const matchName = !q || name.includes(q);
          const matchSpec = !spec || (d.specialization ?? '').toLowerCase().includes(spec);
          const matchStatus = !status || (status === 'active' ? d.is_active == 1 : d.is_active == 0);
          return matchName && matchSpec && matchStatus;
        });
        renderDoctors();
      }

      function resetFilters() {
        document.getElementById('searchName').value = '';
        document.getElementById('filterSpec').value = '';
        document.getElementById('filterStatus').value = '';
        filteredDoctors = [...allDoctors];
        renderDoctors();
      }

      // ===== Export =====
      function exportDoctors() {
        const rows = allDoctors.map(d => `${d.first_name} ${d.last_name},${d.specialization ?? ''},${d.phone ?? ''},${d.email ?? ''},${d.is_active ? 'نشط' : 'غير نشط'}`);
        const csv = 'الاسم,التخصص,الجوال,البريد,الحالة\n' + rows.join('\n');
        const a = document.createElement('a');
        a.href = 'data:text/csv;charset=utf-8,\uFEFF' + encodeURIComponent(csv);
        a.download = 'doctors_' + new Date().toISOString().slice(0, 10) + '.csv';
        a.click();
      }

      // ===== Mobile sidebar =====
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
