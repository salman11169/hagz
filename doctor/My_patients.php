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
    <title>مرضاي — شفاء+</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shared-dashboard.css">
    <link rel="stylesheet" href="../assets/css/doctor-dashboard.css?v=5">
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
            <a href="My_patients.php" class="menu-item active"><i class='bx bx-group'></i><span>المرضى</span></a>
            <a href="Doctor_reports.php" class="menu-item"><i class='bx bx-file-blank'></i><span>التقارير</span></a>
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
                    <h1><i class='bx bx-group'></i> مرضاي</h1>
                    <p>قائمة المرضى الذين راجعوك</p>
                </div>
                <div class="hero-stat" id="totalPatientsBadge">8 مرضى</div>
            </div>
        </div>

        <div class="controls-bar">
            <div class="search-box">
                <i class='bx bx-search'></i>
                <input type="text" id="searchInput" placeholder="ابحث باسم المريض أو رقم الجوال..."
                    oninput="renderPatients()">
            </div>
            <div class="filter-tabs">
                <button class="ft active" onclick="setGender('all',this)">الكل</button>
                <button class="ft" onclick="setGender('ذكر',this)">ذكور</button>
                <button class="ft" onclick="setGender('أنثى',this)">إناث</button>
                <button class="ft" onclick="setGender('chronic',this)">أمراض مزمنة</button>
            </div>
        </div>

        <div class="patients-grid" id="patientGrid"></div>

    </main>

    <!-- Slide-in sheet -->
    <div class="sheet-overlay" id="sheetOverlay" onclick="closeSheet()"></div>
    <div class="patient-sheet" id="patientSheet">
        <div class="sheet-head">
            <h2 id="sheetName">تفاصيل المريض</h2>
            <button onclick="closeSheet()"
                style="background:rgba(255,255,255,.2);border:none;color:#fff;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:1.1rem;"><i
                    class='bx bx-x'></i></button>
        </div>
        <div class="sheet-body" id="sheetBody"></div>
    </div>

    <script>
        const API = '../controllers/DoctorController.php';
        let PATIENTS = [], genderFilter = 'all';

        document.addEventListener('DOMContentLoaded', () => {
            initSidebar();
            loadPatients();
        });

        function loadPatients() {
            document.getElementById('patientGrid').innerHTML = `<div class="empty-state"><i class='bx bx-loader-alt bx-spin' style="font-size:2.5rem;opacity:1;color:var(--doc-primary)"></i></div>`;
            fetch(`${API}?action=patients`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) throw new Error(data.message);
                    // API returns data.patients directly
                    PATIENTS = (data.patients || []).map(p => ({
                        id: p.id,
                        name: `${p.first_name ?? ''} ${p.last_name ?? ''}`.trim(),
                        age: p.age ?? '—',
                        // Convert Male/Female from DB to Arabic
                        gender: p.gender === 'Male' ? 'ذكر' : p.gender === 'Female' ? 'أنثى' : '—',
                        phone: p.phone ?? '—',
                        blood: p.blood_type ?? '—',
                        chronic: [],   // API doesn't return chronic_diseases for patient list
                        visits: p.total_visits ?? 0,
                        lastVisit: p.last_visit ?? null
                    }));
                    document.getElementById('totalPatientsBadge').textContent = PATIENTS.length + ' مرضى';
                    renderPatients();
                })
                .catch(err => {
                    document.getElementById('patientGrid').innerHTML = `<div class="empty-state" style="color:#ef4444">تعذر تحميل المرضى: ${err.message}</div>`;
                });
        }

        function setGender(g, el) {
            genderFilter = g;
            document.querySelectorAll('.ft').forEach(b => b.classList.remove('active'));
            el.classList.add('active');
            renderPatients();
        }

        function renderPatients() {
            const q = document.getElementById('searchInput').value.trim().toLowerCase();
            let list = PATIENTS.slice();
            if (genderFilter === 'ذكر') list = list.filter(p => p.gender === 'ذكر');
            if (genderFilter === 'أنثى') list = list.filter(p => p.gender === 'أنثى');
            if (genderFilter === 'chronic') list = list.filter(p => p.chronic.length > 0);
            if (q) list = list.filter(p => p.name.toLowerCase().includes(q) || p.phone.includes(q));

            document.getElementById('totalPatientsBadge').textContent = list.length + ' مرضى';
            const grid = document.getElementById('patientGrid');
            if (!list.length) { grid.innerHTML = '<div class="empty-state"><i class=\'bx bx-user-x\'></i>لا يوجد مرضى مطابقون</div>'; return; }

            grid.innerHTML = list.map(p => {
                const initials = p.name.split(' ').map(w => w[0]).slice(0, 2).join('');
                const lastFmt = p.lastVisit ? new Date(p.lastVisit).toLocaleDateString('ar-SA', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
                const chronicHtml = p.chronic.length
                    ? p.chronic.map(c => `<span class="cc">${c}</span>`).join('')
                    : '<span class="cc none"><i class=\'bx bx-check\'></i> لا توجد أمراض مزمنة</span>';
                return `<div class="patient-card" onclick="openSheet(${p.id})">
          <div class="patient-card-top">
            <div class="patient-av ${p.gender === 'أنثى' ? 'female' : ''}">${initials}</div>
            <div><div class="patient-nm">${p.name}</div><div class="patient-meta">${p.age} سنة · ${p.gender} · فصيلة ${p.blood}</div></div>
          </div>
          <div class="chronic-chips">${chronicHtml}</div>
          <div class="patient-stats">
            <div class="ps-item"><div class="ps-num">${p.visits}</div><div class="ps-lbl">زيارة</div></div>
            <div class="ps-item"><div class="ps-num">${p.chronic.length}</div><div class="ps-lbl">أمراض مزمنة</div></div>
            <div class="ps-item"><div class="ps-num" style="font-size:.72rem">${lastFmt}</div><div class="ps-lbl">آخر زيارة</div></div>
          </div>
          <div class="patient-actions" onclick="event.stopPropagation()">
            <button class="pa-btn primary" onclick="openSheet(${p.id})"><i class='bx bx-show'></i> الملف</button>
            <button class="pa-btn" onclick="callPatient('${p.phone}')"><i class='bx bx-phone'></i> اتصال</button>
          </div>
        </div>`;
            }).join('');
        }

        function openSheet(id) {
            const p = PATIENTS.find(x => x.id == id);
            if (!p) return;
            document.getElementById('sheetName').textContent = p.name;
            const lastFmt = p.lastVisit ? new Date(p.lastVisit).toLocaleDateString('ar-SA', { day: 'numeric', month: 'long', year: 'numeric' }) : '—';
            const chronicHtml = p.chronic.length
                ? p.chronic.map(c => `<span class="cc">${c}</span>`).join('')
                : '<span class="cc none">لا توجد أمراض مزمنة</span>';
            document.getElementById('sheetBody').innerHTML = `
      <div class="sheet-section">
        <div class="sheet-sec-title">البيانات الشخصية</div>
        <div class="sheet-info-grid">
          <div class="si"><div class="si-lbl">العمر</div><div class="si-val">${p.age} سنة</div></div>
          <div class="si"><div class="si-lbl">الجنس</div><div class="si-val">${p.gender}</div></div>
          <div class="si"><div class="si-lbl">فصيلة الدم</div><div class="si-val">${p.blood}</div></div>
          <div class="si"><div class="si-lbl">رقم الجوال</div><div class="si-val" dir="ltr">${p.phone}</div></div>
          <div class="si"><div class="si-lbl">عدد الزيارات</div><div class="si-val">${p.visits}</div></div>
          <div class="si"><div class="si-lbl">آخر زيارة</div><div class="si-val">${lastFmt}</div></div>
        </div>
      </div>
      <div class="sheet-section">
        <div class="sheet-sec-title">الأمراض المزمنة</div>
        <div style="display:flex;flex-wrap:wrap;gap:.4rem">${chronicHtml}</div>
      </div>`;
            document.getElementById('patientSheet').classList.add('open');
            document.getElementById('sheetOverlay').classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeSheet() {
            document.getElementById('patientSheet').classList.remove('open');
            document.getElementById('sheetOverlay').classList.remove('show');
            document.body.style.overflow = '';
        }

        function callPatient(phone) { alert('الاتصال بـ: ' + phone); }

        function initSidebar() {
            const toggle = document.getElementById('mobileToggle');
            const sidebar = document.getElementById('sidebar');
            if (!toggle || !sidebar) return;
            toggle.addEventListener('click', () => { sidebar.classList.toggle('active'); document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : ''; });
            document.addEventListener('click', e => { if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggle.contains(e.target) && sidebar.classList.contains('active')) { sidebar.classList.remove('active'); document.body.style.overflow = ''; } });
        }
    </script>
    <script src="../assets/js/hagz-ui.js?v=2"></script>
    <script src="../assets/js/notif-badge.js" defer></script>
</body>

</html>
