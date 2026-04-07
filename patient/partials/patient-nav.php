<?php
/**
 * SHIFA+ · Patient Shared Navbar + Sidebar
 *
 * Variables expected before include:
 *   $userName   (string) - patient display name
 *   $activeNav  (string) - active menu key, e.g. 'booking', 'dashboard'
 *
 * Usage:
 *   <?php $activeNav = 'booking'; include 'partials/patient-nav.php'; ?>
 */

// Unread notification count helper (safe fallback)
$notifCount = 0;
if (function_exists('get_unread_notifications_count')) {
    $notifCount = (int) get_unread_notifications_count();
}

// Avatar URL
$avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($userName ?? 'م') . '&background=4f46e5&color=fff&size=64';

// Menu items  [key, href, icon, label]
$navItems = [
    ['dashboard',   'dashboard-new.php',             'bxs-dashboard',     'الرئيسية'],
    ['booking',     'booking-new.php',               'bx-calendar-plus',  'حجز موعد'],
    ['records',     'records-new.php',               'bx-folder-open',    'سجلي الطبي'],
    ['presc',       'prescriptions-new.php',         'bx-receipt',        'الوصفات والفواتير'],
    ['notif',       'notifications-new.php',         'bx-bell',           'الإشعارات'],
    ['profile',     'profile-new.php',               'bx-user-circle',    'الملف الشخصي'],
];
?>

<!-- ════════════════ NAVBAR ════════════════ -->
<nav class="p-navbar">

  <!-- Mobile toggle -->
  <button class="nav-icon-btn p-mobile-toggle d-lg-none me-2" id="sidebarToggle" aria-label="القائمة">
    <i class="bx bx-menu fs-3"></i>
  </button>

  <!-- Brand -->
  <a href="dashboard-new.php" class="brand">
    <div class="brand-icon"><i class="bx bx-plus-medical"></i></div>
    <span class="brand-text">شفاء<span>+</span></span>
  </a>

  <!-- Right actions -->
  <div class="nav-end">

    <!-- Notifications bell -->
    <a href="notifications-new.php" class="nav-icon-btn" title="الإشعارات">
      <i class="bx bx-bell"></i>
      <?php if ($notifCount > 0): ?>
        <span class="notif-dot"><?= $notifCount ?></span>
      <?php endif; ?>
    </a>

    <!-- User pill -->
    <a href="profile-new.php" class="user-pill">
      <img src="<?= $avatarUrl ?>" alt="<?= htmlspecialchars($userName ?? 'المريض') ?>" id="navAvatarImg">
      <span class="uname"><?= htmlspecialchars($userName ?? 'المريض') ?></span>
      <i class="bx bx-chevron-down text-muted small"></i>
    </a>

  </div>
</nav>

<!-- ════════════════ SIDEBAR OVERLAY (mobile) ════════════════ -->
<div class="p-sidebar-overlay" id="sidebarOverlay"></div>

<!-- ════════════════ SIDEBAR ════════════════ -->
<aside class="p-sidebar" id="appSidebar">
  <!-- Mobile Logo & Close Button -->
  <div class="sidebar-logo-sm">
    <a href="dashboard-new.php" class="brand">
      <div class="brand-icon"><i class="bx bx-plus-medical"></i></div>
      <span class="brand-text">شفاء<span>+</span></span>
    </a>
    <button class="nav-icon-btn p-mobile-close d-lg-none" onclick="document.getElementById('appSidebar').classList.remove('open'); document.getElementById('sidebarOverlay').classList.remove('show');">
      <i class='bx bx-x'></i>
    </button>
  </div>

  <!-- Menu items -->
  <nav class="sidebar-menu">
    <?php foreach ($navItems as [$key, $href, $icon, $label]): ?>
      <a href="<?= $href ?>"
         class="p-nav-link <?= ($activeNav ?? '') === $key ? 'active' : '' ?>">
        <i class="bx <?= $icon ?>"></i>
        <span><?= $label ?></span>
        <?php if ($key === 'notif' && $notifCount > 0): ?>
          <span class="badge bg-danger ms-auto rounded-pill fs-xs">
            <?= $notifCount ?>
          </span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- Bottom logout -->
  <div class="sidebar-bottom">
    <a href="../logout.php" class="p-nav-link logout">
      <i class="bx bx-log-out"></i>
      <span>تسجيل الخروج</span>
    </a>
  </div>

</aside>

<!-- ════════════════ SIDEBAR TOGGLE SCRIPT ════════════════ -->
<script>
  (function () {
    const toggle   = document.getElementById('sidebarToggle');
    const sidebar  = document.getElementById('appSidebar');
    const overlay  = document.getElementById('sidebarOverlay');

    function openSidebar()  { sidebar.classList.add('open');  overlay.classList.add('show'); }
    function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('show'); }

    if (toggle)  toggle.addEventListener('click',   openSidebar);
    if (overlay) overlay.addEventListener('click',  closeSidebar);
  })();
</script>
