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
  <title>تعديل الموعد — شفاء+</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/shared-dashboard.css">
  <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
  <link rel="stylesheet" href="../assets/css/doctor-dashboard.css">
  <style>
    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: .5rem;
      color: var(--doc-primary);
      font-weight: 800;
      font-size: .92rem;
      text-decoration: none;
      margin-bottom: 1.5rem;
      padding: .6rem 1.2rem;
      border-radius: 12px;
      background: var(--doc-primary-l);
      transition: var(--doc-transition);
    }

    .back-btn:hover {
      background: var(--doc-primary);
      color: #fff;
      transform: translateX(4px);
    }

    /* Form hero */
    .form-hero {
      background: var(--doc-gp);
      border-radius: 24px;
      padding: 2rem 2.5rem;
      margin-bottom: 1.5rem;
      position: relative;
      overflow: hidden;
      box-shadow: 0 20px 55px var(--doc-shadow);
    }

    .form-hero::before {
      content: '';
      position: absolute;
      top: -80px;
      left: -80px;
      width: 280px;
      height: 280px;
      border-radius: 50%;
      background: rgba(255, 255, 255, .06);
    }

    .form-hero-inner {
      position: relative;
      z-index: 2;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 1rem;
    }

    .form-hero h1 {
      font-size: 1.7rem;
      font-weight: 900;
      color: #fff;
      display: flex;
      align-items: center;
      gap: .6rem;
    }

    .form-hero p {
      color: rgba(255, 255, 255, .8);
      font-size: .9rem;
      font-weight: 600;
      margin-top: .3rem;
    }

    .id-badge {
      background: rgba(255, 255, 255, .2);
      color: #fff;
      padding: .5rem 1.3rem;
      border-radius: 50px;
      font-weight: 800;
      font-size: .82rem;
      border: 1.5px solid rgba(255, 255, 255, .35);
    }

    /* Alerts */
    .alert-box {
      display: none;
      align-items: center;
      gap: .8rem;
      padding: 1rem 1.3rem;
      border-radius: 14px;
      font-weight: 700;
      font-size: .9rem;
      margin-bottom: 1rem;
    }

    .alert-box.show {
      display: flex;
    }

    .alert-success {
      background: rgba(16, 185, 129, .1);
      color: #065f46;
      border: 1.5px solid rgba(16, 185, 129, .25);
    }

    .alert-danger {
      background: rgba(239, 68, 68, .1);
      color: #991b1b;
      border: 1.5px solid rgba(239, 68, 68, .25);
    }

    /* Form layout */
    .form-layout {
      display: grid;
      grid-template-columns: 1fr 300px;
      gap: 1.5rem;
      align-items: start;
    }

    .form-main {
      display: flex;
      flex-direction: column;
      gap: 1.3rem;
    }

    .form-card {
      background: #fff;
      border-radius: 20px;
      padding: 1.8rem;
      border: 1.5px solid var(--doc-border);
      box-shadow: 0 6px 24px rgba(15, 23, 42, .05);
    }

    .form-sec-head {
      display: flex;
      align-items: center;
      gap: .7rem;
      font-size: 1.05rem;
      font-weight: 900;
      color: var(--doc-dark);
      margin-bottom: 1.3rem;
      padding-bottom: .9rem;
      border-bottom: 2px solid #f1f5f9;
    }

    .form-sec-head i {
      font-size: 1.3rem;
      color: var(--doc-primary);
    }

    .fields-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }

    .field-group {
      display: flex;
      flex-direction: column;
      gap: .4rem;
    }

    .field-label {
      font-size: .82rem;
      font-weight: 800;
      color: var(--doc-dark);
    }

    .field-label .req {
      color: var(--doc-danger);
      margin-right: .2rem;
    }

    .field-input,
    .field-select,
    .field-textarea {
      width: 100%;
      padding: .8rem 1rem;
      border-radius: 12px;
      border: 1.5px solid var(--doc-border);
      font-family: 'Cairo', sans-serif;
      font-size: .9rem;
      color: var(--doc-dark);
      background: #f8fafc;
      transition: var(--doc-transition);
      outline: none;
    }

    .field-input:focus,
    .field-select:focus,
    .field-textarea:focus {
      border-color: var(--doc-primary);
      background: #fff;
      box-shadow: 0 0 0 4px var(--doc-primary-l);
    }

    .field-input:disabled {
      color: var(--doc-muted);
      cursor: not-allowed;
    }

    .field-textarea {
      resize: vertical;
      min-height: 90px;
    }

    /* Time slots */
    .time-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
      gap: .6rem;
      margin-top: .5rem;
    }

    .ts {
      padding: .55rem;
      border-radius: 10px;
      text-align: center;
      font-size: .82rem;
      font-weight: 700;
      cursor: pointer;
      border: 1.5px solid var(--doc-border);
      background: #f8fafc;
      color: var(--doc-dark);
      transition: var(--doc-transition);
    }

    .ts:hover {
      border-color: var(--doc-primary);
      color: var(--doc-primary);
      background: var(--doc-primary-l);
    }

    .ts.active {
      background: var(--doc-gp);
      color: #fff;
      border-color: var(--doc-primary);
      box-shadow: 0 4px 12px var(--doc-shadow);
    }

    /* Priority selector */
    .priority-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: .9rem;
    }

    .pri-opt {
      padding: 1.2rem 1rem;
      border-radius: 16px;
      text-align: center;
      cursor: pointer;
      border: 2px solid var(--doc-border);
      background: #f8fafc;
      transition: var(--doc-transition);
    }

    .pri-opt:hover {
      transform: translateY(-3px);
    }

    .pri-opt.selected.normal {
      background: rgba(16, 185, 129, .1);
      border-color: #10b981;
    }

    .pri-opt.selected.urgent {
      background: rgba(245, 158, 11, .1);
      border-color: #f59e0b;
    }

    .pri-opt.selected.emergency {
      background: rgba(239, 68, 68, .1);
      border-color: #ef4444;
    }

    .pri-opt-icon {
      font-size: 1.8rem;
      margin-bottom: .5rem;
    }

    .pri-opt-label {
      font-size: .84rem;
      font-weight: 800;
      color: var(--doc-dark);
    }

    /* Sidebar actions */
    .sidebar-card {
      background: #fff;
      border-radius: 20px;
      padding: 1.6rem;
      border: 1.5px solid var(--doc-border);
      box-shadow: 0 6px 24px rgba(15, 23, 42, .05);
      position: sticky;
      top: 1.5rem;
      display: flex;
      flex-direction: column;
      gap: .8rem;
    }

    .save-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: .6rem;
      width: 100%;
      padding: 1rem;
      border-radius: 14px;
      border: none;
      font-family: 'Cairo', sans-serif;
      font-weight: 900;
      font-size: .95rem;
      cursor: pointer;
      transition: var(--doc-transition);
    }

    .save-btn.primary {
      background: var(--doc-gp);
      color: #fff;
      box-shadow: 0 8px 24px var(--doc-shadow);
    }

    .save-btn.primary:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 32px var(--doc-shadow);
    }

    .save-btn.secondary {
      background: #f8fafc;
      color: var(--doc-dark);
      border: 1.5px solid var(--doc-border);
    }

    .save-btn.secondary:hover {
      background: var(--doc-primary-l);
      color: var(--doc-primary);
      border-color: var(--doc-primary);
    }

    /* Responsive */
    @media (max-width: 1024px) {
      .form-layout {
        grid-template-columns: 1fr;
      }

      .sidebar-card {
        position: static;
      }
    }

    @media (max-width: 768px) {
      .form-hero {
        padding: 1.5rem;
      }

      .form-hero h1 {
        font-size: 1.4rem;
      }

      .fields-row {
        grid-template-columns: 1fr;
      }

      .priority-row {
        grid-template-columns: 1fr 1fr 1fr;
      }
    }

    @media (max-width: 480px) {
      .priority-row {
        grid-template-columns: 1fr 1fr;
      }
    }
  </style>
</head>

<body>

  <!-- NAVBAR -->
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-brand">
        <button class="icon-btn mobile-toggle" id="mobileToggle">
          <i class='bx bx-menu'></i>
        </button>
        <div class="brand-icon"><i class='bx bx-plus-medical'></i></div>
        <div class="brand-text">شفاء<span>+</span></div>
      </div>
      <div class="nav-actions">
        <button class="icon-btn notif-btn">
          <i class='bx bx-bell'></i>
          <span class="badge" id="notifBadge" style="display:none;">0</span>
        </button>
        <div class="user-menu">
          <div class="user-avatar">
            <img src="https://ui-avatars.com/api/?name=عبدالعزيز+الصالح&background=0d9488&color=fff&font-family=Cairo"
              alt="Doctor">
          </div>
          <div class="user-info">
            <span class="user-greeting">مرحباً دكتور،</span>
            <span class="user-name">عبدالعزيز الصالح</span>
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
      <a href="My_appointments.php" class="menu-item active"><i class='bx bx-calendar-check'></i><span>مواعيدي</span></a>
      <a href="My_patients.php" class="menu-item"><i class='bx bx-group'></i><span>المرضى</span></a>
      <a href="Doctor_reports.php" class="menu-item"><i class='bx bx-file-blank'></i><span>التقارير الطبية</span></a>
      <a href="Doctor_referrals.php" class="menu-item"><i class='bx bx-transfer-alt'></i><span>التحويلات</span></a>
      <a href="Doctor_profile.php" class="menu-item"><i class='bx bx-user-circle'></i><span>ملفي الشخصي</span></a>
    </div>
    <div class="sidebar-bottom">
      <a href="../logout.php" class="menu-item logout" onclick="event.preventDefault(); if(window.hagzConfirmLogout) window.hagzConfirmLogout('../logout.php'); else window.location.href='../logout.php';"><i class='bx bx-log-out'></i><span>تسجيل الخروج</span></a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main-content">

    <a href="My_appointments.php" class="back-btn">
      <i class='bx bx-arrow-back'></i> العودة للوحة التحكم
    </a>

    <!-- Hero -->
    <div class="form-hero">
      <div class="form-hero-inner">
        <div>
          <h1><i class='bx bx-edit-alt'></i> تعديل الموعد</h1>
          <p>قم بتعديل بيانات الموعد أدناه وحفظ التغييرات</p>
        </div>
        <div class="id-badge" id="apptIdBadge">—</div>
      </div>
    </div>

    <!-- Alerts -->
    <div class="alert-box alert-success" id="successAlert">
      <i class='bx bx-check-circle' style="font-size:1.4rem;"></i>
      تم حفظ التعديلات بنجاح! جاري التوجيه...
    </div>
    <div class="alert-box alert-danger" id="errorAlert">
      <i class='bx bx-error-circle' style="font-size:1.4rem;"></i>
      <span id="errorMsg"></span>
    </div>

    <!-- Form layout -->
    <div class="form-layout">
      <div class="form-main">

        <!-- Patient info (read-only) -->
        <div class="form-card">
          <div class="form-sec-head"><i class='bx bx-user'></i> معلومات المريض</div>
          <div class="fields-row">
            <div class="field-group">
              <label class="field-label">اسم المريض</label>
              <input type="text" class="field-input" id="patientName" disabled>
            </div>
            <div class="field-group">
              <label class="field-label">رقم الجوال</label>
              <input type="tel" class="field-input" id="patientPhone" disabled>
            </div>
          </div>
        </div>

        <!-- Appointment details -->
        <div class="form-card">
          <div class="form-sec-head"><i class='bx bx-calendar'></i> تفاصيل الموعد</div>
          <div class="fields-row" style="margin-bottom:1rem;">
            <div class="field-group">
              <label class="field-label">التخصص <span class="req">*</span></label>
              <select class="field-select" id="specialty" onchange="loadDoctors()">
                <option value="">اختر التخصص</option>
                <option>طب عام</option>
                <option>طب طوارئ</option>
                <option>طب باطني</option>
                <option>جراحة</option>
                <option>أطفال</option>
                <option>عظام</option>
                <option>أعصاب</option>
                <option>نساء وولادة</option>
                <option>طب الأسنان</option>
              </select>
            </div>
            <div class="field-group">
              <label class="field-label">الطبيب <span class="req">*</span></label>
              <select class="field-select" id="doctor">
                <option value="">اختر الطبيب</option>
              </select>
            </div>
          </div>
          <div class="fields-row">
            <div class="field-group">
              <label class="field-label">التاريخ <span class="req">*</span></label>
              <input type="date" class="field-input" id="appointmentDate">
            </div>
            <div class="field-group">
              <label class="field-label">نوع الزيارة</label>
              <select class="field-select" id="visitType">
                <option>حضوري</option>
                <option>عن بُعد</option>
              </select>
            </div>
          </div>

          <div class="field-group" style="margin-top:1rem;">
            <label class="field-label">الوقت <span class="req">*</span></label>
            <div class="time-grid" id="timeSlots"></div>
          </div>
        </div>

        <!-- Priority -->
        <div class="form-card">
          <div class="form-sec-head"><i class='bx bx-flag'></i> الأولوية الطبية</div>
          <div class="priority-row">
            <div class="pri-opt normal" onclick="selectPriority('normal', this)">
              <div class="pri-opt-icon"><i class='bx bx-check-circle' style="color:#10b981;"></i></div>
              <div class="pri-opt-label">مستقرة</div>
            </div>
            <div class="pri-opt urgent" onclick="selectPriority('urgent', this)">
              <div class="pri-opt-icon"><i class='bx bx-time-five' style="color:#f59e0b;"></i></div>
              <div class="pri-opt-label">عاجلة</div>
            </div>
            <div class="pri-opt emergency" onclick="selectPriority('emergency', this)">
              <div class="pri-opt-icon"><i class='bx bxs-error-circle' style="color:#ef4444;"></i></div>
              <div class="pri-opt-label">حرجة</div>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="form-card">
          <div class="form-sec-head"><i class='bx bx-note'></i> ملاحظات إضافية</div>
          <div class="field-group">
            <label class="field-label">ملاحظات الطبيب</label>
            <textarea class="field-textarea" id="notes" placeholder="أي ملاحظات إضافية للموعد..."></textarea>
          </div>
        </div>

      </div>

      <!-- Sticky save -->
      <div>
        <div class="sidebar-card">
          <button class="save-btn primary" onclick="saveChanges()">
            <i class='bx bx-save'></i> حفظ التعديلات
          </button>
          <button class="save-btn secondary" onclick="window.history.back()">
            <i class='bx bx-x'></i> إلغاء
          </button>
        </div>
      </div>
    </div>

  </main>

  <script>
    let appointmentId = null, currentAppt = null;
    let selectedTime = null, selectedPriority = 'normal';

    const DOCTORS = [
      { name: 'د. عبدالعزيز الصالح', spec: 'طب عام' },
      { name: 'د. نورة العتيبي', spec: 'طب باطني' },
      { name: 'د. فيصل الحربي', spec: 'جراحة' },
      { name: 'د. منى الزهراني', spec: 'أطفال' },
      { name: 'د. خالد الدوسري', spec: 'طب طوارئ' },
    ];
    const TIMES = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
      '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'];
    const PRI_LABEL = { normal: 'مستقرة', urgent: 'عاجلة', emergency: 'حرجة' };

    document.addEventListener('DOMContentLoaded', () => {
      initSidebar();
      const urlParams = new URLSearchParams(window.location.search);
      appointmentId = urlParams.get('id');
      loadAppointment();
    });

    function loadAppointment() {
      const data = JSON.parse(localStorage.getItem('appointments') || '[]');
      let a = data.find(x => x.id === appointmentId);
      if (!a) {
        a = {
          id: 'APT-PREVIEW', name: 'مريض تجريبي', phone: '0501234567',
          spec: 'طب عام', doctor: 'د. عبدالعزيز الصالح',
          date: new Date().toISOString().split('T')[0], time: '10:00',
          visitType: 'حضوري', priorityKey: 'normal', notes: ''
        };
      }
      currentAppt = a;
      document.getElementById('apptIdBadge').textContent = a.id;
      document.getElementById('patientName').value = a.name || '';
      document.getElementById('patientPhone').value = a.phone || '';
      document.getElementById('specialty').value = a.spec || '';
      document.getElementById('appointmentDate').value = typeof a.date === 'string' && a.date.includes('T') ? a.date.split('T')[0] : a.date || '';
      document.getElementById('visitType').value = a.visitType || 'حضوري';
      document.getElementById('notes').value = a.notes || '';
      selectedTime = a.time || null;
      selectedPriority = a.priorityKey || 'normal';
      loadDoctors(a.doctor);
      renderTimeSlots();
      renderPriority();
    }

    function loadDoctors(selected) {
      const spec = document.getElementById('specialty').value;
      const sel = document.getElementById('doctor');
      const list = spec ? DOCTORS.filter(d => d.spec === spec) : DOCTORS;
      sel.innerHTML = '<option value="">اختر الطبيب</option>' +
        list.map(d => `<option value="${d.name}">${d.name}</option>`).join('');
      if (selected || currentAppt?.doctor) sel.value = selected || currentAppt.doctor;
    }

    function renderTimeSlots() {
      document.getElementById('timeSlots').innerHTML = TIMES.map(t =>
        `<div class="ts ${t === selectedTime ? 'active' : ''}" onclick="selectTime('${t}',this)">${t}</div>`
      ).join('');
    }

    function selectTime(t, el) {
      selectedTime = t;
      document.querySelectorAll('.ts').forEach(x => x.classList.remove('active'));
      el.classList.add('active');
    }

    function renderPriority() {
      document.querySelectorAll('.pri-opt').forEach(o => o.classList.remove('selected'));
      document.querySelector(`.pri-opt.${selectedPriority}`)?.classList.add('selected');
    }

    function selectPriority(p, el) {
      selectedPriority = p;
      document.querySelectorAll('.pri-opt').forEach(o => o.classList.remove('selected'));
      el.classList.add('selected');
    }

    function saveChanges() {
      const spec = document.getElementById('specialty').value;
      const doc = document.getElementById('doctor').value;
      const date = document.getElementById('appointmentDate').value;
      if (!spec || !doc || !date || !selectedTime) {
        showError('يرجى تعبئة جميع الحقول المطلوبة (التخصص، الطبيب، التاريخ، الوقت)');
        return;
      }
      const data = JSON.parse(localStorage.getItem('appointments') || '[]');
      const idx = data.findIndex(a => a.id === appointmentId);
      const update = {
        spec, doctor: doc, date, time: selectedTime,
        visitType: document.getElementById('visitType').value,
        priorityKey: selectedPriority, priority: PRI_LABEL[selectedPriority],
        notes: document.getElementById('notes').value
      };
      if (idx > -1) { Object.assign(data[idx], update); localStorage.setItem('appointments', JSON.stringify(data)); }
      document.getElementById('successAlert').classList.add('show');
      setTimeout(() => window.location.href = 'Appointment_details.php?id=' + appointmentId, 1500);
    }

    function showError(msg) {
      document.getElementById('errorMsg').textContent = msg;
      const el = document.getElementById('errorAlert');
      el.classList.add('show');
      setTimeout(() => el.classList.remove('show'), 4000);
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
