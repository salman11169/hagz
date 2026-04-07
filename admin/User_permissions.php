<?php
require_once __DIR__ . '/../includes/session.php';
require_role(ROLE_ADMIN);
require_once __DIR__ . '/../config/database.php';

$userName = htmlspecialchars($_SESSION['user_name'] ?? 'المدير', ENT_QUOTES, 'UTF-8');

// ── Fetch role counts from DB ──────────────────────────────
$pdo = getDB();

$roleCounts = $pdo->query("
    SELECT r.id, r.name, COUNT(u.id) AS user_count
    FROM roles r
    LEFT JOIN users u ON u.role_id = r.id
    GROUP BY r.id
    ORDER BY r.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

$roleMap = [];
foreach ($roleCounts as $rc) {
    $roleMap[$rc['name']] = ['id' => (int)$rc['id'], 'count' => (int)$rc['user_count']];
}

$adminCount        = $roleMap['Admin']['count']        ?? 0;
$doctorCount       = $roleMap['Doctor']['count']       ?? 0;
$patientCount      = $roleMap['Patient']['count']      ?? 0;
$receptionistCount = $roleMap['Receptionist']['count'] ?? 0;
$totalUsers        = array_sum(array_column($roleCounts, 'user_count'));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إدارة الصلاحيات - نظام فرز المواعيد الذكي</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/shared-dashboard.css?v=1.1">
  <link rel="stylesheet" href="../assets/css/admin-dashboard.css?v=1.1">
  <style>
    /* ── Role card top accent ── */
    .role-accent { position: absolute; top:0; left:0; width:100%; height:4px; }
    /* ── Toggle switch ── */
    .toggle { position:relative; display:inline-block; width:46px; height:24px; flex-shrink:0; }
    .toggle input { opacity:0; width:0; height:0; }
    .toggle-slider {
      position:absolute; cursor:pointer; inset:0;
      background:#cbd5e1; border-radius:24px;
      transition: background .3s;
    }
    .toggle-slider::before {
      content:''; position:absolute;
      width:18px; height:18px; left:3px; bottom:3px;
      background:white; border-radius:50%;
      transition: transform .3s;
    }
    .toggle input:checked + .toggle-slider { background:#10b981; }
    .toggle input:checked + .toggle-slider::before { transform:translateX(22px); }
    /* ── Permission row ── */
    .perm-row {
      display:flex; justify-content:space-between; align-items:center;
      padding:.8rem; background:#f8fafc; border-radius:12px;
    }
    .perm-label {
      font-weight:700; color:var(--surface-dark);
      display:flex; align-items:center; gap:.5rem;
    }
    /* ── Users table filter pills ── */
    .filter-pill {
      display:inline-flex; align-items:center; gap:.4rem;
      padding:.4rem 1rem; border-radius:20px; border:2px solid #e2e8f0;
      background:white; color:#64748b; font-weight:700; font-size:.85rem;
      cursor:pointer; transition:all .2s; font-family:'Cairo',sans-serif;
    }
    .filter-pill:hover { border-color:#6366f1; color:#6366f1; }
    .filter-pill.active { background:#6366f1; border-color:#6366f1; color:white; }
    /* ── Status badge ── */
    .status-dot {
      width:8px; height:8px; border-radius:50%; display:inline-block; margin-left:.4rem;
    }
    .status-dot.active   { background:#10b981; }
    .status-dot.inactive { background:#ef4444; }
    /* ── User row actions ── */
    .user-action-btn {
      padding:.35rem .8rem; border-radius:8px; border:1px solid;
      font-family:'Cairo',sans-serif; font-size:.82rem; font-weight:700;
      cursor:pointer; transition:all .2s; white-space:nowrap;
    }
    .btn-activate   { background:#f0fdf4; border-color:#bbf7d0; color:#16a34a; }
    .btn-activate:hover { background:#dcfce7; }
    .btn-deactivate { background:#fef2f2; border-color:#fecaca; color:#dc2626; }
    .btn-deactivate:hover { background:#fee2e2; }
    /* ── Role select ── */
    .role-select {
      border:1.5px solid #e2e8f0; border-radius:8px; padding:.3rem .6rem;
      font-family:'Cairo',sans-serif; font-size:.82rem; font-weight:700;
      color:#1e293b; background:white; cursor:pointer; outline:none;
      transition:border-color .2s;
    }
    .role-select:focus { border-color:#6366f1; }
    /* ── Loading shimmer ── */
    .shimmer {
      background: linear-gradient(90deg,#f1f5f9 25%,#e2e8f0 50%,#f1f5f9 75%);
      background-size:200% 100%; animation:shimmer 1.5s infinite;
      border-radius:8px; height:48px; margin-bottom:.8rem;
    }
    @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
    /* ── Toast ── */
    .toast-notif {
      position:fixed; bottom:2rem; left:50%;
      transform:translateX(-50%) translateY(120px);
      background:#1e293b; color:white; padding:.8rem 2rem;
      border-radius:12px; font-family:'Cairo',sans-serif;
      font-weight:700; z-index:9999;
      transition:transform .3s ease;
      box-shadow:0 8px 30px rgba(0,0,0,.3);
    }
    .toast-notif.show { transform:translateX(-50%) translateY(0); }
    /* ── Empty state ── */
    .empty-state { text-align:center; padding:3rem; color:#94a3b8; }
    .empty-state i { font-size:3rem; margin-bottom:1rem; display:block; }
    /* ── Role badge ── */
    .role-badge {
      display:inline-flex; align-items:center; gap:.3rem;
      font-size:.78rem; font-weight:700; padding:.2rem .6rem;
      border-radius:8px;
    }
    .role-admin       { background:#fef2f2; color:#dc2626; }
    .role-doctor      { background:#f0fdf4; color:#16a34a; }
    .role-patient     { background:#eff6ff; color:#2563eb; }
    .role-receptionist{ background:#faf5ff; color:#7c3aed; }
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
        <div class="brand-icon"><i class='bx bx-plus-medical'></i></div>
        <div class="brand-text">شفاء<span>+</span></div>
      </div>
      <div class="nav-actions">
        <button class="icon-btn notif-btn">
          <i class='bx bx-bell'></i>
          <span class="badge"><?= $totalUsers ?></span>
        </button>
        <div class="user-menu">
          <div class="user-avatar">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=2563eb&color=fff&font-family=Cairo" alt="User">
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

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-menu">
      <a href="admin.php"          class="menu-item"><i class='bx bxs-dashboard'></i><span>لوحة التحكم</span></a>
      <a href="Manage_doctors.php" class="menu-item"><i class='bx bx-user-pin'></i><span>إدارة الأطباء</span></a>
      <a href="Manage_patients.php"class="menu-item"><i class='bx bx-group'></i><span>إدارة المرضى</span></a>
      <a href="Reports.php"        class="menu-item"><i class='bx bx-chart'></i><span>التقارير والإحصائيات</span></a>
      <a href="System_settings.php"class="menu-item"><i class='bx bx-cog'></i><span>إعدادات النظام</span></a>
      <a href="User_permissions.php" class="menu-item active"><i class='bx bx-shield-quarter'></i><span>صلاحيات المستخدمين</span></a>
    </div>
    <div class="sidebar-bottom">
      <a href="#" onclick="showLogoutModal(event)" class="menu-item logout">
        <i class='bx bx-log-out'></i><span>تسجيل الخروج</span>
      </a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <div class="dashboard-wrap">

      <!-- Page Header -->
      <div class="hero-card" style="padding:2rem;margin-bottom:2rem;background:linear-gradient(135deg,#4338ca 0%,#312e81 100%);">
        <div class="hero-content" style="width:100%;max-width:100%;">
          <div style="display:flex;justify-content:space-between;align-items:start;width:100%;flex-wrap:wrap;gap:1rem;">
            <div>
              <h1 style="font-size:1.8rem;margin-bottom:0;">
                <i class='bx bx-user-check'></i> إدارة صلاحيات النظام
              </h1>
              <p style="margin-top:.5rem;font-size:1rem;opacity:.85;">
                تخصيص مستويات الوصول للمستخدمين لضمان الأمان والكفاءة
                &mdash; إجمالي <strong><?= $totalUsers ?></strong> مستخدم
              </p>
            </div>
            <div style="display:flex;gap:.8rem;flex-wrap:wrap;align-items:center;">
              <button class="btn-glass primary" onclick="loadUsers()"
                      style="background:rgba(255,255,255,.2)!important;">
                <i class='bx bx-refresh'></i> تحديث
              </button>
              <button class="btn-glass" onclick="exportUsers()"
                      style="background:rgba(255,255,255,.1)!important;color:white!important;">
                <i class='bx bx-export'></i> تصدير CSV
              </button>
            </div>
          </div>
        </div>
        <div class="hero-glow"></div>
      </div>

      <!-- ── Role Cards (Informational — Counts from DB) ── -->
      <div class="content-grid" style="grid-template-columns:repeat(auto-fit,minmax(320px,1fr));">

        <!-- Admin Role -->
        <div class="content-card" style="position:relative;overflow:hidden;">
          <div class="role-accent" style="background:linear-gradient(90deg,#ef4444,#dc2626);"></div>
          <div class="card-header border-bottom" style="padding:1.5rem;border-color:#f1f5f9;">
            <div style="display:flex;align-items:center;gap:1rem;">
              <div class="u-avatar" style="background:linear-gradient(135deg,#ef4444,#dc2626);color:white;width:48px;height:48px;font-size:1.5rem;">
                <i class='bx bx-crown'></i>
              </div>
              <div>
                <h3 style="margin:0;font-size:1.2rem;font-weight:800;color:var(--surface-dark);">المدير العام</h3>
                <span style="font-size:.85rem;color:#64748b;font-weight:600;">
                  <i class='bx bx-user'></i>
                  <span id="cnt-admin"><?= $adminCount ?></span> مستخدم
                </span>
              </div>
            </div>
          </div>
          <div class="card-body" style="padding:1.5rem;">
            <div style="display:flex;flex-direction:column;gap:1rem;">
              <div class="perm-row">
                <span class="perm-label"><i class='bx bx-check-circle' style="color:#10b981;font-size:1.2rem;"></i> إدارة كاملة للنظام</span>
                <label class="toggle"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
              </div>
              <div class="perm-row">
                <span class="perm-label"><i class='bx bx-group' style="color:#3b82f6;font-size:1.2rem;"></i> إدارة المستخدمين</span>
                <label class="toggle"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
              </div>
              <div class="perm-row">
                <span class="perm-label"><i class='bx bx-cog' style="color:#8b5cf6;font-size:1.2rem;"></i> إعدادات النظام</span>
                <label class="toggle"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
              </div>
              <div class="perm-row">
                <span class="perm-label"><i class='bx bx-bar-chart' style="color:#f59e0b;font-size:1.2rem;"></i> عرض التقارير</span>
                <label class="toggle"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
              </div>
            </div>
            <div style="display:flex;gap:.5rem;margin-top:1.5rem;padding-top:1.5rem;border-top:1px dashed #e2e8f0;">
              <button class="btn-glass" onclick="filterByRole('Admin')"
                style="flex:1;justify-content:center;color:var(--admin-primary);border-color:#cbd5e1;">
                <i class='bx bx-list-ul'></i> عرض المستخدمين
              </button>
              <button class="btn-glass" disabled
                style="flex:1;justify-content:center;opacity:.5;color:#94a3b8;background:#f1f5f9;border-color:transparent;">
                <i class='bx bx-lock'></i> محمي
              </button>
            </div>
          </div>
        </div>

        <!-- Doctor Role -->
        <div class="content-card" style="position:relative;overflow:hidden;">
          <div class="role-accent" style="background:linear-gradient(90deg,#10b981,#059669);"></div>
          <div class="card-header border-bottom" style="padding:1.5rem;border-color:#f1f5f9;">
            <div style="display:flex;align-items:center;gap:1rem;">
              <div class="u-avatar" style="background:linear-gradient(135deg,#10b981,#059669);color:white;width:48px;height:48px;font-size:1.5rem;">
                <i class='bx bx-user-pin'></i>
              </div>
              <div>
                <h3 style="margin:0;font-size:1.2rem;font-weight:800;color:var(--surface-dark);">طبيب</h3>
                <span style="font-size:.85rem;color:#64748b;font-weight:600;">
                  <i class='bx bx-user'></i>
                  <span id="cnt-doctor"><?= $doctorCount ?></span> مستخدم
                </span>
              </div>
            </div>
          </div>
          <div class="card-body" style="padding:1.5rem;">
            <div style="display:flex;flex-direction:column;gap:1rem;">
              <div class="perm-row">
                <span class="perm-label"><i class='bx bx-calendar' style="color:#10b981;font-size:1.2rem;"></i> إدارة المواعيد</span>
                <label class="toggle"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
              </div>
              <div class="perm-row">
                <span class="perm-label"><i class='bx bx-file' style="color:#3b82f6;font-size:1.2rem;"></i> السجلات الطبية</span>
                <label class="toggle"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
              </div>
              <div class="perm-row">
                <span class="perm-label"><i class='bx bx-edit' style="color:#8b5cf6;font-size:1.2rem;"></i> تعديل الحالات</span>
                <label class="toggle"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
              </div>
              <div class="perm-row">
                <span class="perm-label"><i class='bx bx-trash' style="color:#ef4444;font-size:1.2rem;"></i> حذف السجلات</span>
                <label class="toggle"><input type="checkbox" disabled><span class="toggle-slider"></span></label>
              </div>
            </div>
            <div style="display:flex;gap:.5rem;margin-top:1.5rem;padding-top:1.5rem;border-top:1px dashed #e2e8f0;">
              <button class="btn-glass" onclick="filterByRole('Doctor')"
                style="flex:1;justify-content:center;color:var(--admin-primary);border-color:#cbd5e1;">
                <i class='bx bx-list-ul'></i> عرض المستخدمين
              </button>
              <button class="btn-glass" onclick="filterByRole('Doctor')"
                style="flex:1;justify-content:center;color:#ef4444;border-color:#fecaca;background:#fef2f2;">
                <i class='bx bx-shield-x'></i> إيقاف دور
              </button>
            </div>
          </div>
        </div>

        <!-- Patient Role -->
        <div class="content-card" style="position:relative;overflow:hidden;">
          <div class="role-accent" style="background:linear-gradient(90deg,#6366f1,#06b6d4);"></div>
          <div class="card-header border-bottom" style="padding:1.5rem;border-color:#f1f5f9;">
            <div style="display:flex;align-items:center;gap:1rem;">
              <div class="u-avatar" style="background:linear-gradient(135deg,#6366f1,#06b6d4);color:white;width:48px;height:48px;font-size:1.5rem;">
                <i class='bx bx-user'></i>
              </div>
              <div>
                <h3 style="margin:0;font-size:1.2rem;font-weight:800;color:var(--surface-dark);">مريض</h3>
                <span style="font-size:.85rem;color:#64748b;font-weight:600;">
                  <i class='bx bx-user'></i>
                  <span id="cnt-patient"><?= $patientCount ?></span> مستخدم
                </span>
              </div>
            </div>
          </div>
          <div class="card-body" style="padding:1.5rem;">
            <div style="display:flex;flex-direction:column;gap:1rem;">
              <div class="perm-row">
                <span class="perm-label"><i class='bx bx-calendar-plus' style="color:#10b981;font-size:1.2rem;"></i> طلب موعد</span>
                <label class="toggle"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
              </div>
              <div class="perm-row">
                <span class="perm-label"><i class='bx bx-show' style="color:#3b82f6;font-size:1.2rem;"></i> عرض تقريره</span>
                <label class="toggle"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
              </div>
              <div class="perm-row">
                <span class="perm-label"><i class='bx bx-edit-alt' style="color:#8b5cf6;font-size:1.2rem;"></i> تحديث ملفه</span>
                <label class="toggle"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
              </div>
              <div class="perm-row">
                <span class="perm-label"><i class='bx bx-x-circle' style="color:#f59e0b;font-size:1.2rem;"></i> إلغاء الحجز</span>
                <label class="toggle"><input type="checkbox" checked disabled><span class="toggle-slider"></span></label>
              </div>
            </div>
            <div style="display:flex;gap:.5rem;margin-top:1.5rem;padding-top:1.5rem;border-top:1px dashed #e2e8f0;">
              <button class="btn-glass" onclick="filterByRole('Patient')"
                style="flex:1;justify-content:center;color:var(--admin-primary);border-color:#cbd5e1;">
                <i class='bx bx-list-ul'></i> عرض المستخدمين
              </button>
              <button class="btn-glass" disabled
                style="flex:1;justify-content:center;opacity:.5;color:#94a3b8;background:#f1f5f9;border-color:transparent;">
                <i class='bx bx-lock'></i> محمي
              </button>
            </div>
          </div>
        </div>

      </div>

      <!-- ── Users Management Table ── -->
      <div class="content-card" style="margin-top:2rem;">
        <div class="card-header border-bottom">
          <h2><i class='bx bx-table'></i> إدارة المستخدمين والأدوار</h2>
          <span id="usersCount" style="font-size:.85rem;color:#64748b;font-weight:600;"></span>
        </div>

        <!-- Filter Pills -->
        <div style="padding:1rem 1.5rem;display:flex;gap:.6rem;flex-wrap:wrap;border-bottom:1px solid #f1f5f9;">
          <button class="filter-pill active" id="pill-all"          onclick="filterByRole('all')">
            <i class='bx bx-grid-alt'></i> الكل
            <span id="badge-all" style="background:#6366f1;color:white;border-radius:10px;padding:0 6px;font-size:.75rem;"><?= $totalUsers ?></span>
          </button>
          <button class="filter-pill" id="pill-Admin"        onclick="filterByRole('Admin')">
            <i class='bx bx-crown'></i> مدير
            <span id="badge-admin" style="background:#ef4444;color:white;border-radius:10px;padding:0 6px;font-size:.75rem;"><?= $adminCount ?></span>
          </button>
          <button class="filter-pill" id="pill-Doctor"       onclick="filterByRole('Doctor')">
            <i class='bx bx-user-pin'></i> أطباء
            <span id="badge-doctor" style="background:#10b981;color:white;border-radius:10px;padding:0 6px;font-size:.75rem;"><?= $doctorCount ?></span>
          </button>
          <button class="filter-pill" id="pill-Patient"      onclick="filterByRole('Patient')">
            <i class='bx bx-group'></i> مرضى
            <span id="badge-patient" style="background:#2563eb;color:white;border-radius:10px;padding:0 6px;font-size:.75rem;"><?= $patientCount ?></span>
          </button>
          <?php if ($receptionistCount > 0): ?>
          <button class="filter-pill" id="pill-Receptionist" onclick="filterByRole('Receptionist')">
            <i class='bx bx-headphone'></i> موظف استقبال
            <span id="badge-receptionist" style="background:#7c3aed;color:white;border-radius:10px;padding:0 6px;font-size:.75rem;"><?= $receptionistCount ?></span>
          </button>
          <?php endif; ?>
          <!-- Search -->
          <div style="margin-right:auto;">
            <input type="text" id="userSearch" placeholder="🔍 بحث بالاسم أو البريد..."
              oninput="filterTable()"
              style="border:1.5px solid #e2e8f0;border-radius:20px;padding:.4rem 1rem;
                     font-family:'Cairo',sans-serif;font-size:.85rem;font-weight:600;
                     outline:none;color:#1e293b;width:220px;transition:border-color .2s;"
              onfocus="this.style.borderColor='#6366f1'"
              onblur="this.style.borderColor='#e2e8f0'">
          </div>
        </div>

        <div class="table-responsive">
          <table class="premium-table" id="usersTable">
            <thead>
              <tr>
                <th>#</th>
                <th>المستخدم</th>
                <th>البريد الإلكتروني</th>
                <th>الدور الحالي</th>
                <th>الحالة</th>
                <th>تغيير الدور</th>
                <th>الإجراءات</th>
              </tr>
            </thead>
            <tbody id="usersBody">
              <tr>
                <td colspan="7" style="text-align:center;padding:2.5rem;">
                  <i class='bx bx-loader-alt bx-spin' style="font-size:2rem;color:#cbd5e1;"></i>
                  <div style="color:#94a3b8;font-weight:600;margin-top:.5rem;">جارٍ تحميل البيانات...</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- end dashboard-wrap -->

    <!-- Toast -->
    <div class="toast-notif" id="toastNotif"></div>

    <!-- Confirm Modal -->
    <div id="confirmOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
         z-index:10000;align-items:center;justify-content:center;">
      <div style="background:white;border-radius:20px;padding:2rem;max-width:400px;width:90%;text-align:center;
                  box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="font-size:3rem;margin-bottom:1rem;" id="confirmIcon">⚠️</div>
        <h3 style="color:#1e293b;font-weight:900;margin-bottom:.5rem;" id="confirmTitle">تأكيد</h3>
        <p style="color:#64748b;margin-bottom:2rem;" id="confirmMsg"></p>
        <div style="display:flex;gap:1rem;justify-content:center;">
          <button onclick="closeConfirm()" style="padding:.6rem 1.5rem;border-radius:10px;
                  border:2px solid #e2e8f0;background:white;font-family:'Cairo',sans-serif;
                  font-weight:700;cursor:pointer;color:#64748b;">إلغاء</button>
          <button id="confirmBtn" style="padding:.6rem 1.5rem;border-radius:10px;border:none;
                  font-family:'Cairo',sans-serif;font-weight:700;cursor:pointer;
                  background:#ef4444;color:white;">تأكيد</button>
        </div>
      </div>
    </div>

    <script>
      const API = '../controllers/AdminController.php';
      let allUsers      = [];
      let currentFilter = 'all';
      let pendingAction = null;

      // ── Roles meta ─────────────────────────────────────────
      const ROLES = {
        'Admin':        { id: 1, label: 'مدير',     cls: 'role-admin',        icon: 'bx-crown'    },
        'Doctor':       { id: 2, label: 'طبيب',     cls: 'role-doctor',       icon: 'bx-user-pin' },
        'Patient':      { id: 3, label: 'مريض',     cls: 'role-patient',      icon: 'bx-user'     },
        'Receptionist': { id: 4, label: 'استقبال',  cls: 'role-receptionist', icon: 'bx-headphone'}
      };

      // ── Sidebar ─────────────────────────────────────────────
      document.addEventListener('DOMContentLoaded', () => {
        const toggle  = document.getElementById('mobileToggle');
        const sidebar = document.getElementById('sidebar');
        if (toggle && sidebar) {
          toggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
          });
          document.addEventListener('click', e => {
            const logoutOpen = document.getElementById('logoutOverlay')?.classList.contains('active');
            if (window.innerWidth <= 768 && !logoutOpen
                && !sidebar.contains(e.target) && !toggle.contains(e.target)
                && sidebar.classList.contains('active')) {
              sidebar.classList.remove('active');
              document.body.style.overflow = '';
            }
          });
        }

        const overlay = document.getElementById('logoutOverlay');
        if (overlay) {
          overlay.addEventListener('click', e => { if (e.target === overlay) closeLogoutModal(); });
          overlay.querySelector('.logout-modal')?.addEventListener('click', e => e.stopPropagation());
        }

        loadUsers();
      });

      // ── Toast ────────────────────────────────────────────────
      function showToast(msg, isError = false) {
        const el = document.getElementById('toastNotif');
        el.textContent  = msg;
        el.style.background = isError ? '#dc2626' : '#1e293b';
        el.classList.add('show');
        setTimeout(() => el.classList.remove('show'), 3500);
      }

      // ── Load Users from API ──────────────────────────────────
      async function loadUsers() {
        document.getElementById('usersBody').innerHTML = `
          <tr><td colspan="7" style="text-align:center;padding:2.5rem;">
            <i class='bx bx-loader-alt bx-spin' style="font-size:2rem;color:#cbd5e1;"></i>
            <div style="color:#94a3b8;font-weight:600;margin-top:.5rem;">جارٍ تحميل البيانات...</div>
          </td></tr>`;

        try {
          const res  = await fetch(`${API}?action=user_permissions`);
          const json = await res.json();

          if (!json.success) throw new Error(json.message || 'فشل التحميل');

          allUsers = json.users || [];
          renderUsers();

        } catch (err) {
          console.error(err);
          document.getElementById('usersBody').innerHTML = `
            <tr><td colspan="7" class="empty-state">
              <i class='bx bx-error-circle' style="color:#ef4444;"></i>
              تعذر تحميل البيانات: ${err.message}
            </td></tr>`;
          showToast('تعذر تحميل بيانات المستخدمين', true);
        }
      }

      // ── Render Table ─────────────────────────────────────────
      function renderUsers() {
        const search  = (document.getElementById('userSearch')?.value || '').trim().toLowerCase();
        const tbody   = document.getElementById('usersBody');
        const countEl = document.getElementById('usersCount');

        let filtered = allUsers.filter(u => {
          const roleMatch  = currentFilter === 'all' || u.role === currentFilter;
          const searchMatch = !search
            || (u.first_name + ' ' + u.last_name).toLowerCase().includes(search)
            || (u.email || '').toLowerCase().includes(search);
          return roleMatch && searchMatch;
        });

        if (countEl) countEl.textContent = `${filtered.length} مستخدم`;

        if (!filtered.length) {
          tbody.innerHTML = `<tr><td colspan="7">
            <div class="empty-state">
              <i class='bx bx-user-x'></i>
              <strong>لا يوجد مستخدمون في هذه الفئة</strong>
            </div></td></tr>`;
          return;
        }

        const currentUserId = <?= (int)($_SESSION['user_id'] ?? 0) ?>;

        tbody.innerHTML = filtered.map((u, idx) => {
          const roleMeta   = ROLES[u.role] ?? { id: 0, label: u.role, cls: '', icon: 'bx-user' };
          const isActive   = parseInt(u.is_active) === 1;
          const isSelf     = parseInt(u.id) === currentUserId;
          const fullName   = `${u.first_name} ${u.last_name}`;
          const initials   = (u.first_name?.charAt(0) ?? '') + (u.last_name?.charAt(0) ?? '');

          // Role options
          const roleOptions = Object.entries(ROLES).map(([key, val]) =>
            `<option value="${val.id}" ${u.role === key ? 'selected' : ''}>${val.label}</option>`
          ).join('');

          return `<tr id="row-${u.id}" class="${isActive ? '' : 'opacity-60'}" style="${isActive ? '' : 'opacity:.6;'}">
            <td style="color:#94a3b8;font-weight:600;">${idx + 1}</td>
            <td>
              <div style="display:flex;align-items:center;gap:.8rem;">
                <div style="width:36px;height:36px;border-radius:50%;
                            background:linear-gradient(135deg,#6366f1,#8b5cf6);
                            color:white;display:flex;align-items:center;justify-content:center;
                            font-weight:700;font-size:.85rem;flex-shrink:0;">
                  ${initials}
                </div>
                <div>
                  <div style="font-weight:800;color:#1e293b;">${fullName}</div>
                  ${isSelf ? '<span style="font-size:.75rem;color:#6366f1;font-weight:700;">أنت</span>' : ''}
                </div>
              </div>
            </td>
            <td style="color:#64748b;font-size:.9rem;">${u.email ?? '—'}</td>
            <td>
              <span class="role-badge ${roleMeta.cls}">
                <i class='bx ${roleMeta.icon}'></i> ${roleMeta.label}
              </span>
            </td>
            <td>
              <span style="display:inline-flex;align-items:center;gap:.3rem;font-weight:700;font-size:.85rem;
                           color:${isActive ? '#16a34a' : '#dc2626'};">
                <span class="status-dot ${isActive ? 'active' : 'inactive'}"></span>
                ${isActive ? 'نشط' : 'موقوف'}
              </span>
            </td>
            <td>
              <select class="role-select"
                      onchange="confirmRoleChange(${u.id}, this.value, '${fullName}', this)"
                      ${isSelf ? 'disabled title="لا يمكنك تغيير دورك"' : ''}>
                ${roleOptions}
              </select>
            </td>
            <td>
              ${isSelf
                ? `<span style="color:#94a3b8;font-size:.8rem;font-weight:600;">حسابك الحالي</span>`
                : (isActive
                  ? `<button class="user-action-btn btn-deactivate"
                             onclick="confirmToggle(${u.id}, 0, '${fullName}')">
                       <i class='bx bx-user-x'></i> إيقاف
                     </button>`
                  : `<button class="user-action-btn btn-activate"
                             onclick="confirmToggle(${u.id}, 1, '${fullName}')">
                       <i class='bx bx-user-check'></i> تفعيل
                     </button>`)
              }
            </td>
          </tr>`;
        }).join('');
      }

      // ── Filter by Role ────────────────────────────────────────
      function filterByRole(role) {
        currentFilter = role;
        document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
        const pill = document.getElementById('pill-' + role) || document.getElementById('pill-all');
        if (pill) pill.classList.add('active');
        renderUsers();

        // Scroll to table
        document.querySelector('.content-card:last-child')
          ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      }

      // ── Search ────────────────────────────────────────────────
      function filterTable() { renderUsers(); }

      // ── Confirm Toggle Status ─────────────────────────────────
      function confirmToggle(userId, newStatus, name) {
        const isActivate = newStatus === 1;
        document.getElementById('confirmIcon').textContent  = isActivate ? '✅' : '⚠️';
        document.getElementById('confirmTitle').textContent = isActivate ? 'تفعيل المستخدم' : 'إيقاف المستخدم';
        document.getElementById('confirmMsg').textContent   =
          isActivate
            ? `هل تريد تفعيل حساب "${name}"؟ سيتمكن من تسجيل الدخول مجدداً.`
            : `هل تريد إيقاف حساب "${name}"؟ لن يتمكن من تسجيل الدخول.`;
        const btn = document.getElementById('confirmBtn');
        btn.style.background = isActivate ? '#16a34a' : '#ef4444';
        pendingAction = () => toggleStatus(userId, newStatus);
        btn.onclick = () => { closeConfirm(); pendingAction && pendingAction(); };
        const ov = document.getElementById('confirmOverlay');
        ov.style.display = 'flex';
      }

      // ── Toggle Status (via API) ───────────────────────────────
      async function toggleStatus(userId, newStatus) {
        try {
          const res  = await fetch(`${API}?action=toggle_doctor`, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ user_id: userId, is_active: newStatus })
          });
          const json = await res.json();

          if (!json.success) throw new Error(json.message);

          showToast(newStatus === 1 ? '✓ تم تفعيل المستخدم بنجاح' : '✓ تم إيقاف المستخدم بنجاح');

          // Update local data and re-render
          const user = allUsers.find(u => parseInt(u.id) === parseInt(userId));
          if (user) user.is_active = newStatus;
          renderUsers();

        } catch (err) {
          console.error(err);
          showToast('فشل تحديث الحالة: ' + err.message, true);
        }
      }

      // ── Confirm Role Change ───────────────────────────────────
      function confirmRoleChange(userId, newRoleId, name, selectEl) {
        const roleLabel = Object.values(ROLES).find(r => r.id == newRoleId)?.label ?? newRoleId;
        const oldRole   = allUsers.find(u => parseInt(u.id) === parseInt(userId))?.role;
        const oldLabel  = ROLES[oldRole]?.label ?? oldRole;

        document.getElementById('confirmIcon').textContent  = '🔄';
        document.getElementById('confirmTitle').textContent = 'تغيير صلاحية المستخدم';
        document.getElementById('confirmMsg').textContent   =
          `تغيير دور "${name}" من "${oldLabel}" إلى "${roleLabel}". هل أنت متأكد؟`;
        const btn = document.getElementById('confirmBtn');
        btn.style.background = '#6366f1';
        pendingAction = () => changeRole(userId, newRoleId, oldRole, selectEl);
        btn.onclick = () => { closeConfirm(); pendingAction && pendingAction(); };
        document.getElementById('confirmOverlay').style.display = 'flex';
      }

      // ── Change Role (via API) ─────────────────────────────────
      async function changeRole(userId, newRoleId, oldRole, selectEl) {
        try {
          const fd = new FormData();
          fd.append('user_id', userId);
          fd.append('role_id', newRoleId);

          const res  = await fetch(`${API}?action=update_permission`, { method: 'POST', body: fd });
          const json = await res.json();

          if (!json.success) throw new Error(json.message);

          showToast('✓ تم تحديث صلاحية المستخدم بنجاح');

          // Update local data
          const user = allUsers.find(u => parseInt(u.id) === parseInt(userId));
          if (user) {
            const newRoleName = Object.keys(ROLES).find(k => ROLES[k].id == newRoleId);
            if (newRoleName) user.role = newRoleName;
          }
          renderUsers();

        } catch (err) {
          console.error(err);
          showToast('فشل تغيير الدور: ' + err.message, true);
          // Revert select
          if (selectEl && oldRole) selectEl.value = ROLES[oldRole]?.id ?? '';
        }
      }

      // ── Confirm Modal Helpers ─────────────────────────────────
      function closeConfirm() {
        document.getElementById('confirmOverlay').style.display = 'none';
        pendingAction = null;
      }
      document.getElementById('confirmOverlay')
        ?.addEventListener('click', e => { if (e.target.id === 'confirmOverlay') closeConfirm(); });

      // ── Export CSV ────────────────────────────────────────────
      function exportUsers() {
        if (!allUsers.length) { showToast('لا توجد بيانات للتصدير', true); return; }

        const source = currentFilter === 'all'
          ? allUsers
          : allUsers.filter(u => u.role === currentFilter);

        const header = 'الاسم,البريد الإلكتروني,الدور,الحالة\n';
        const csv = header + source.map(u =>
          `"${u.first_name} ${u.last_name}","${u.email ?? ''}","${u.role ?? ''}","${parseInt(u.is_active) ? 'نشط' : 'موقوف'}"`
        ).join('\n');

        const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
        const url  = URL.createObjectURL(blob);
        const lnk  = document.createElement('a');
        lnk.href     = url;
        lnk.download = `صلاحيات_المستخدمين_${new Date().toISOString().slice(0,10)}.csv`;
        lnk.click();
        URL.revokeObjectURL(url);
        showToast('✓ تم تصدير القائمة بنجاح');
      }

      // ── Logout ────────────────────────────────────────────────
      function showLogoutModal(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        document.getElementById('logoutOverlay').classList.add('active');
      }
      function closeLogoutModal() { document.getElementById('logoutOverlay').classList.remove('active'); }
      function confirmLogout()    { window.location.href = '../logout.php'; }
    </script>
  </main>

  <!-- Logout Modal -->
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
