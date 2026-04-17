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
  <title>إدارة المرضى - نظام فرز المواعيد الذكي</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
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
      <a href="Manage_doctors.php" class="menu-item"><i class='bx bx-user-pin'></i><span>إدارة الأطباء</span></a>
      <a href="Manage_patients.php" class="menu-item active"><i class='bx bx-group'></i><span>إدارة المرضى</span></a>
      <a href="Reports.php" class="menu-item"><i class='bx bx-chart'></i><span>التقارير</span></a>
      <a href="System_settings.php" class="menu-item"><i class='bx bx-cog'></i><span>إعدادات النظام</span></a>
      <a href="User_permissions.php" class="menu-item"><i class='bx bx-shield-quarter'></i><span>صلاحيات</span></a>
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
      <div class="hero-card"
        style="padding: 2rem; margin-bottom: 2rem; background: linear-gradient(135deg, #0ea5e9, #2563eb);">
        <div class="hero-content">
          <h1 style="font-size: 1.8rem; margin-bottom: 0;"><i class='bx bx-group'></i> إدارة المرضى</h1>
          <p style="margin-top: 0.5rem; font-size: 1rem;">الوصول السريع إلى الملفات الطبية والسجلات الصحية</p>
        </div>
        <div class="hero-actions">
          <button class="btn-glass primary" onclick="exportPatients()">
            <i class='bx bx-download'></i> تحميل السجلات
          </button>
        </div>
        <div class="hero-glow"
          style="background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 70%);"></div>
      </div>

      <!-- Stats Grid -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon s-blue"><i class='bx bx-user'></i></div>
          <div class="stat-info">
            <h3>إجمالي المرضى</h3>
            <div class="stat-value" id="totalPatients">0</div>
            <div class="stat-trend positive"><i class='bx bx-trending-up'></i> مسجلين بالنظام</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon s-green"><i class='bx bx-user-plus'></i></div>
          <div class="stat-info">
            <h3>مرضى جدد</h3>
            <div class="stat-value" id="newPatients">0</div>
            <div class="stat-trend positive"><i class='bx bx-calendar-plus'></i> هذا الشهر</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon s-orange"><i class='bx bx-calendar-check'></i></div>
          <div class="stat-info">
            <h3>نشطون مؤخراً</h3>
            <div class="stat-value" id="activePatients">0</div>
            <div class="stat-trend positive"><i class='bx bx-check'></i> زاروا العيادة</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon s-purple" style="background: linear-gradient(135deg, #ef4444, #b91c1c);"><i
              class='bx bx-error-circle'></i></div>
          <div class="stat-info">
            <h3>حالات مزمنة</h3>
            <div class="stat-value" id="chronicPatients">0</div>
            <div class="stat-trend warning"><i class='bx bx-pulse'></i> تحت المتابعة المستمرة</div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="content-grid" style="grid-template-columns: 1fr;">

        <!-- Filters -->
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
                  بالاسم أو الجوال</label>
                <input type="text" id="searchPatient" placeholder="ابحث..." onkeyup="filterPatients()"
                  style="width: 100%; padding: 0.8rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-family: 'Cairo'; background: #f8fafc; transition: border 0.3s; outline: none;">
              </div>
              <div>
                <label
                  style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; display: block;">فصيلة
                  الدم</label>
                <select id="filterBlood" onchange="filterPatients()"
                  style="width: 100%; padding: 0.8rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-family: 'Cairo'; background: #f8fafc; outline: none;">
                  <option value="">الكل</option>
                  <option>A+</option>
                  <option>A-</option>
                  <option>B+</option>
                  <option>B-</option>
                  <option>O+</option>
                  <option>O-</option>
                  <option>AB+</option>
                  <option>AB-</option>
                </select>
              </div>
              <div>
                <label
                  style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; display: block;">الحالة
                  الطبية</label>
                <select id="filterStatus" onchange="filterPatients()"
                  style="width: 100%; padding: 0.8rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px; font-family: 'Cairo'; background: #f8fafc; outline: none;">
                  <option value="">الكل</option>
                  <option value="active">مستقر</option>
                  <option value="chronic">تاريخ مرضي / مزمن</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Patients Table -->
        <div class="content-card">
          <div class="card-header border-bottom">
            <h2><i class='bx bx-list-ul'></i> سجل المرضى (<span id="patientsCount">0</span>)</h2>
          </div>

          <div class="table-responsive" style="padding-top: 1rem;">
            <table class="premium-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>المريض</th>
                  <th>العمر / الجنس</th>
                  <th>فصيلة الدم</th>
                  <th>التواصل</th>
                  <th>آخر زيارة</th>
                  <th>الحالة</th>
                  <th>إجراءات</th>
                </tr>
              </thead>
              <tbody id="patientsBody">
                <!-- Rendered by JS -->
              </tbody>
            </table>

            <div class="pagination" id="pagination"
              style="display: flex; justify-content: center; gap: 0.5rem; padding: 1.5rem 0;"></div>

            <!-- Empty state -->
            <div id="emptyState" style="display:none; text-align: center; padding: 4rem 2rem;">
              <i class='bx bx-user-x' style="font-size: 4rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
              <h3 style="color: #475569;">لا توجد سجلات</h3>
              <p style="color: #64748b;">لم يتم العثور على مرضى مطابقين لما تبحث عنه</p>
            </div>
          </div>
        </div>

      </div> <!-- /content-grid -->

    </div> <!-- /dashboard-wrap -->

    <script>
      const API = '../controllers/AdminController.php';
      let patients = [];
      let filteredPatients = [];
      let currentPage = 1;
      const perPage = 10;

      document.addEventListener('DOMContentLoaded', () => {
        initSidebar();
        loadPatients();
      });

      function loadPatients() {
        document.getElementById('patientsBody').innerHTML =
          `<tr><td colspan="8" style="text-align:center;padding:3rem"><i class='bx bx-loader-alt bx-spin' style="font-size:2rem;color:#2563eb"></i></td></tr>`;

        fetch(`${API}?action=patients`)
          .then(r => r.json())
          .then(data => {
            if (!data.success) throw new Error(data.message);
            patients = data.patients || [];
            filteredPatients = [...patients];
            updateStats();
            renderTable();
          })
          .catch(err => {
            document.getElementById('patientsBody').innerHTML =
              `<tr><td colspan="8" style="text-align:center;color:#ef4444;padding:2rem">تعذر تحميل البيانات: ${err.message}</td></tr>`;
          });
      }

      function updateStats() {
        document.getElementById('totalPatients').textContent = patients.length;
        const thirtyDaysAgo = new Date(); thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        document.getElementById('newPatients').textContent = patients.filter(p => p.created_at && new Date(p.created_at) >= thirtyDaysAgo).length;
        document.getElementById('activePatients').textContent = patients.filter(p => p.last_visit).length;
        document.getElementById('chronicPatients').textContent = patients.filter(p => p.has_chronic).length;
      }

      function renderTable() {
        const tbody = document.getElementById('patientsBody');
        const start = (currentPage - 1) * perPage;
        const page = filteredPatients.slice(start, start + perPage);
        document.getElementById('patientsCount').textContent = filteredPatients.length;

        if (!page.length) {
          tbody.closest('table').style.display = 'none';
          document.getElementById('emptyState').style.display = 'block';
          document.getElementById('pagination').style.display = 'none';
          return;
        }

        tbody.closest('table').style.display = 'table';
        document.getElementById('emptyState').style.display = 'none';
        const bgs = ['linear-gradient(135deg,#3b82f6,#60a5fa)', 'linear-gradient(135deg,#8b5cf6,#a78bfa)',
          'linear-gradient(135deg,#f59e0b,#fbbf24)', 'linear-gradient(135deg,#10b981,#34d399)'];

        tbody.innerHTML = page.map((p, i) => {
          const isChronic = !!p.has_chronic;
          const fullName = `${p.first_name ?? ''} ${p.last_name ?? ''}`.trim();
          const lastV = p.last_visit ? formatDate(p.last_visit) : 'لم يزر بعد';
          return `<tr>
            <td style="font-weight:700;color:#64748b">${start + i + 1}</td>
            <td><div style="display:flex;align-items:center;gap:.8rem">
              <div style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;background:${bgs[i % 4]};flex-shrink:0">${(fullName[0] ?? 'م')}</div>
              <div><div style="font-weight:700">${fullName}</div><div style="font-size:.8rem;color:#94a3b8">مريض #${p.id}</div></div>
            </div></td>
            <td><span style="font-weight:700;color:#475569">${p.age ?? '—'} سنة</span><br><span style="color:#64748b;font-size:.8rem">${p.gender ?? '—'}</span></td>
            <td><span style="font-weight:800;color:#ef4444">${p.blood_type ?? '—'}</span></td>
            <td><span style="font-size:.85rem;color:#475569">${p.phone ?? '—'}</span></td>
            <td><span style="font-size:.85rem;color:#64748b">${lastV}</span></td>
            <td><span style="padding:.3rem .7rem;border-radius:30px;font-size:.75rem;font-weight:700;${isChronic ? 'background:rgba(239,68,68,.1);color:#ef4444' : 'background:rgba(16,185,129,.1);color:#10b981'}">${isChronic ? 'مزمن' : 'مستقر'}</span></td>
            <td><div style="display:flex;gap:.3rem">
              <button style="width:32px;height:32px;border-radius:6px;border:none;background:#f8fafc;color:var(--admin-primary);cursor:pointer" onclick="viewPatient(${p.id})" title="عرض الملف"><i class='bx bx-folder-open'></i></button>
            </div></td>
          </tr>`;
        }).join('');
        renderPagination();
      }

      function renderPagination() {
        const total = Math.ceil(filteredPatients.length / perPage);
        const el = document.getElementById('pagination');
        if (total <= 1) { el.style.display = 'none'; return; }
        el.style.display = 'flex';
        el.innerHTML = '';
        const prev = document.createElement('button'); prev.className = 'btn-glass';
        prev.innerHTML = '<i class="bx bx-chevron-right" style="font-size:1.5rem;color:var(--admin-primary)"></i>';
        prev.disabled = currentPage === 1; if (prev.disabled) prev.style.opacity = '.5';
        prev.onclick = () => { currentPage--; renderTable(); }; el.appendChild(prev);
        for (let i = 1; i <= total; i++) {
          const btn = document.createElement('button'); btn.className = 'btn-glass';
          if (i === currentPage) btn.classList.add('primary'); else { btn.style.color = 'var(--surface-dark)'; btn.style.background = 'white'; btn.style.borderColor = '#e2e8f0'; }
          btn.textContent = i; btn.onclick = () => { currentPage = i; renderTable(); }; el.appendChild(btn);
        }
        const next = document.createElement('button'); next.className = 'btn-glass';
        next.innerHTML = '<i class="bx bx-chevron-left" style="font-size:1.5rem;color:var(--admin-primary)"></i>';
        next.disabled = currentPage === total; if (next.disabled) next.style.opacity = '.5';
        next.onclick = () => { currentPage++; renderTable(); }; el.appendChild(next);
      }

      function filterPatients() {
        const search = document.getElementById('searchPatient').value.toLowerCase();
        const blood = document.getElementById('filterBlood').value;
        const status = document.getElementById('filterStatus').value;
        filteredPatients = patients.filter(p => {
          const name = `${p.first_name ?? ''} ${p.last_name ?? ''}`.toLowerCase();
          const matchSearch = !search || name.includes(search) || (p.phone ?? '').includes(search);
          const matchBlood = !blood || p.blood_type === blood;
          const matchStatus = !status ||
            (status === 'chronic' && p.has_chronic) ||
            (status === 'active' && !p.has_chronic);
          return matchSearch && matchBlood && matchStatus;
        });
        currentPage = 1; renderTable();
      }

      function resetFilters() {
        document.getElementById('searchPatient').value = '';
        document.getElementById('filterBlood').value = '';
        document.getElementById('filterStatus').value = '';
        filteredPatients = [...patients]; currentPage = 1; renderTable();
      }

      function formatDate(d) {
        if (!d) return '—';
        return new Date(d).toLocaleDateString('ar-SA', { day: 'numeric', month: 'short', year: 'numeric' });
      }

      function viewPatient(id) {
        alert('عرض ملف المريض #' + id);
      }

      function exportPatients() {
        const rows = ['الاسم,الجوال,فصيلة الدم,آخر زيارة'];
        patients.forEach(p => rows.push(`"${p.first_name} ${p.last_name}",${p.phone ?? ''},${p.blood_type ?? ''},${formatDate(p.last_visit)}`));
        const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'patients_' + new Date().toISOString().slice(0, 10) + '.csv';
        a.click();
      }

      function initSidebar() {
        const toggle = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');
        if (!toggle || !sidebar) return;
        toggle.addEventListener('click', () => { sidebar.classList.toggle('active'); document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : ''; });
        document.addEventListener('click', e => { const logoutOpen = document.getElementById('logoutOverlay')?.classList.contains('active'); if (window.innerWidth <= 768 && !logoutOpen && !sidebar.contains(e.target) && !toggle.contains(e.target) && sidebar.classList.contains('active')) { sidebar.classList.remove('active'); document.body.style.overflow = ''; } });
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
