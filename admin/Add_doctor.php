<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';
require_role(ROLE_ADMIN);

$userName = htmlspecialchars($_SESSION['user_name'] ?? 'المدير', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إضافة / تعديل طبيب - نظام شفاء+</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/shared-dashboard.css?v=1.1">
  <link rel="stylesheet" href="../assets/css/admin-dashboard.css?v=1.1">
</head>

<body>

  <!-- Navbar -->
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
        
        <div class="user-menu">
          <div class="user-avatar">
            <img src="https://ui-avatars.com/api/?name=مدير+النظام&background=2563eb&color=fff&font-family=Cairo"
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

  <!-- Sidebar -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-menu">
      <a href="admin.php" class="menu-item"><i class='bx bxs-dashboard'></i><span>لوحة التحكم</span></a>
      <a href="Manage_doctors.php" class="menu-item active"><i class='bx bx-user-pin'></i><span>إدارة
          الأطباء</span></a>
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

  <!-- Main -->
  <main class="main-content">
    <div class="dashboard-wrap">

      <!-- Back Link -->
      <a href="Manage_doctors.php"
        style="display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;border-radius:12px;background:white;border:1.5px solid #e2e8f0;color:#475569;font-weight:700;font-size:.88rem;text-decoration:none;margin-bottom:1.4rem;font-family:'Cairo',sans-serif;transition:all .2s;"
        onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='white'">
        <i class='bx bx-arrow-back'></i> العودة لإدارة الأطباء
      </a>

      <!-- Hero -->
      <div class="hero-card"
        style="padding:2rem;margin-bottom:2rem;background:linear-gradient(135deg,#10b981 0%,#059669 100%);">
        <div class="hero-content">
          <div
            style="display:flex;justify-content:space-between;align-items:flex-start;width:100%;flex-wrap:wrap;gap:1rem;">
            <div>
              <h1 id="formTitle"
                style="font-size:1.7rem;margin-bottom:.3rem;display:flex;align-items:center;gap:.5rem;">
                <i class='bx bx-user-plus'></i> إضافة طبيب جديد
              </h1>
              <p class="form-subtitle" style="margin:0;font-size:.95rem;">املأ البيانات أدناه لإضافة طبيب جديد للنظام
              </p>
            </div>
            <div style="display:flex;gap:.7rem;flex-wrap:wrap;">
              <button class="btn-glass" style="background:rgba(255,255,255,.15)!important;color:white!important;"
                onclick="window.location.href='Manage_doctors.php'">
                <i class='bx bx-x'></i> إلغاء
              </button>
              <button class="btn-glass"
                style="background:rgba(255,255,255,.25)!important;color:white!important;font-weight:800;"
                onclick="saveDoctor()">
                <i class='bx bx-save'></i> <span id="saveBtnText">حفظ البيانات</span>
              </button>
            </div>
          </div>
        </div>
        <div class="hero-glow"></div>
      </div>

      <!-- Form Card -->
      <div class="content-card" style="border-radius:20px;box-shadow:0 10px 30px rgba(0,0,0,.07)!important;">
        <div class="card-body" style="padding:2rem;">

          <!-- Alerts -->
          <div class="doc-alert success" id="successAlert"><i class='bx bx-check-circle'></i><span></span></div>
          <div class="doc-alert danger" id="errorAlert"><i class='bx bx-error-circle'></i><span></span></div>

          <form id="doctorForm" novalidate>

            <!-- ① المعلومات الأساسية -->
            <div class="doc-section">
              <div class="doc-section-title"><i class='bx bx-user'></i> المعلومات الأساسية</div>
              <div class="doc-grid doc-grid-2">
                <div>
                  <label class="doc-label">الاسم الأول <span class="req">*</span></label>
                  <input class="doc-input" type="text" id="firstName" placeholder="محمد" required>
                </div>
                <div>
                  <label class="doc-label">اسم العائلة <span class="req">*</span></label>
                  <input class="doc-input" type="text" id="lastName" placeholder="العلي" required>
                </div>
                <div>
                  <label class="doc-label">رقم الترخيص <span class="req">*</span></label>
                  <input class="doc-input" type="text" id="licenseNum" placeholder="DOC-001" required>
                </div>
                <div>
                  <label class="doc-label">رقم الجوال</label>
                  <input class="doc-input" type="tel" id="phone" placeholder="05xxxxxxxx">
                </div>
                <div>
                  <label class="doc-label">البريد الإلكتروني <span class="req">*</span></label>
                  <input class="doc-input" type="email" id="email" placeholder="doctor@hospital.com" required>
                </div>
                <div id="passwordWrap">
                  <label class="doc-label">كلمة المرور <span class="req">*</span></label>
                  <input class="doc-input" type="password" id="password" placeholder="8 أحرف على الأقل"
                    autocomplete="new-password">
                </div>
              </div>
            </div>

            <!-- ② التخصصات والخبرات -->
            <div class="doc-section">
              <div class="doc-section-title"><i class='bx bx-clinic'></i> التخصصات والخبرات</div>
              <div class="doc-grid doc-grid-2">
                <div>
                  <label class="doc-label">التخصص الأساسي <span class="req">*</span></label>
                  <select class="doc-select" id="mainSpec" required>
                    <option value="">جارٍ التحميل...</option>
                  </select>
                </div>
                <div>
                  <label class="doc-label">سنوات الخبرة</label>
                  <input class="doc-input" type="number" id="experience" placeholder="5" min="0" max="60">
                </div>
                <div>
                  <label class="doc-label">المؤهل العلمي</label>
                  <select class="doc-select" id="qualification">
                    <option value="">اختر المؤهل</option>
                    <option>بكالوريوس طب وجراحة</option>
                    <option>ماجستير</option>
                    <option>دكتوراه</option>
                    <option>زمالة</option>
                    <option>استشاري</option>
                  </select>
                </div>
                <div>
                  <label class="doc-label">رسوم الاستشارة (ريال)</label>
                  <input class="doc-input" type="number" id="fee" placeholder="150" min="0">
                </div>
              </div>
              <!-- Sub specialties -->
              <div style="margin-top:1.1rem;">
                <label class="doc-label" style="margin-bottom:.6rem;">التخصصات الفرعية</label>
                <div class="doc-tags">
                  <label class="doc-tag"><input type="checkbox" id="sub1" value="أمراض مزمنة"> أمراض مزمنة</label>
                  <label class="doc-tag"><input type="checkbox" id="sub2" value="طوارئ"> طوارئ</label>
                  <label class="doc-tag"><input type="checkbox" id="sub3" value="جراحة"> جراحة</label>
                  <label class="doc-tag"><input type="checkbox" id="sub4" value="عناية مركزة"> عناية مركزة</label>
                  <label class="doc-tag"><input type="checkbox" id="sub5" value="أطفال"> أطفال</label>
                  <label class="doc-tag"><input type="checkbox" id="sub6" value="عظام"> عظام</label>
                </div>
              </div>
            </div>

            <!-- ③ أوقات العمل -->
            <div class="doc-section">
              <div class="doc-section-title"><i class='bx bx-time-five'></i> أوقات العمل</div>
              <div style="margin-bottom:1rem;">
                <label class="doc-label" style="margin-bottom:.6rem;">أيام العمل</label>
                <div class="doc-tags">
                  <label class="doc-tag"><input type="checkbox" id="day1" value="الأحد" checked> الأحد</label>
                  <label class="doc-tag"><input type="checkbox" id="day2" value="الإثنين" checked> الإثنين</label>
                  <label class="doc-tag"><input type="checkbox" id="day3" value="الثلاثاء" checked> الثلاثاء</label>
                  <label class="doc-tag"><input type="checkbox" id="day4" value="الأربعاء" checked> الأربعاء</label>
                  <label class="doc-tag"><input type="checkbox" id="day5" value="الخميس" checked> الخميس</label>
                  <label class="doc-tag"><input type="checkbox" id="day6" value="الجمعة"> الجمعة</label>
                  <label class="doc-tag"><input type="checkbox" id="day7" value="السبت"> السبت</label>
                </div>
              </div>
              <div class="doc-grid doc-grid-2">
                <div>
                  <label class="doc-label">وقت البدء</label>
                  <input class="doc-input" type="time" id="startTime" value="08:00">
                </div>
                <div>
                  <label class="doc-label">وقت الانتهاء</label>
                  <input class="doc-input" type="time" id="endTime" value="17:00">
                </div>
              </div>
            </div>

            <!-- ④ معلومات إضافية -->
            <div class="doc-section">
              <div class="doc-section-title"><i class='bx bx-detail'></i> معلومات إضافية</div>
              <div class="doc-grid">
                <div>
                  <label class="doc-label">نبذة عن الطبيب</label>
                  <textarea class="doc-textarea doc-input" id="bio"
                    placeholder="اكتب نبذة مختصرة عن الطبيب..."></textarea>
                </div>
              </div>
              <div class="doc-grid doc-grid-2" style="margin-top:1.1rem;">
                <div>
                  <label class="doc-label">الحالة</label>
                  <select class="doc-select" id="status">
                    <option value="1">نشط</option>
                    <option value="0">غير نشط</option>
                  </select>
                </div>
                <div>
                  <label class="doc-label">التقييم (من 5)</label>
                  <input class="doc-input" type="number" id="rating" min="0" max="5" step=".1" value="4.5">
                </div>
              </div>

              <!-- Image Upload -->
              <div style="margin-top:1.1rem;">
                <label class="doc-label">صورة الطبيب</label>
                <div class="doc-upload-zone" onclick="document.getElementById('imgInput').click()">
                  <i class='bx bx-image-add'></i>
                  <p><strong>اضغط لرفع صورة</strong> أو اسحب الصورة هنا</p>
                  <p style="font-size:.78rem;margin-top:.3rem;">PNG, JPG (حد أقصى 2MB)</p>
                </div>
                <input type="file" id="imgInput" accept="image/*" style="display:none" onchange="previewImage(event)">
                <div id="imgPreviewWrap">
                  <img id="imgPreview" alt="preview">
                  <button type="button" id="imgRemove" onclick="removeImage()" title="حذف الصورة"><i
                      class='bx bx-x'></i></button>
                </div>
              </div>
            </div>

          </form>
        </div>

        <!-- Footer -->
        <div class="doc-footer">
          <button type="button" class="btn-cancel" onclick="window.location.href='Manage_doctors.php'">
            <i class='bx bx-x'></i> إلغاء
          </button>
          <button type="button" class="btn-save" id="saveBtn" onclick="saveDoctor()">
            <i class='bx bx-save'></i> <span id="saveBtnText">حفظ البيانات</span>
          </button>
        </div>
      </div>

    </div>
  </main>

  <!-- Toast Container -->
  <div id="toastWrap"></div>

  <script>
    const API = '../controllers/AdminController.php';
    let editMode = false;
    let currentDoctorId = null;

    // ─── Init ────────────────────────────────────────────
    window.addEventListener('DOMContentLoaded', () => {
      loadSpecializations();

      const urlParams = new URLSearchParams(window.location.search);
      const docId = urlParams.get('id');
      if (docId) {
        editMode = true;
        currentDoctorId = parseInt(docId);
        document.getElementById('formTitle').innerHTML = "<i class='bx bx-edit'></i> تعديل بيانات الطبيب";
        document.querySelector('.form-subtitle').textContent = 'قم بتعديل البيانات أدناه';
        document.querySelectorAll('#saveBtnText').forEach(el => el.textContent = 'حفظ التعديلات');
        document.getElementById('passwordWrap').style.display = 'none';
      }

      // Sidebar toggle
      const toggle = document.getElementById('mobileToggle');
      const sidebar = document.getElementById('sidebar');
      if (toggle && sidebar) {
        toggle.addEventListener('click', () => {
          sidebar.classList.toggle('active');
          document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        });
        document.addEventListener('click', e => {
          if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggle.contains(e.target) && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            document.body.style.overflow = '';
          }
        });
      }
    });

    // ─── Load Specializations ────────────────────────────
    function loadSpecializations() {
      fetch(API + '?action=specializations')
        .then(r => r.json())
        .then(data => {
          const sel = document.getElementById('mainSpec');
          sel.innerHTML = '<option value="">اختر التخصص</option>';
          if (data.success && data.specializations) {
            data.specializations.forEach(s => {
              const opt = document.createElement('option');
              opt.value = s.id;
              opt.textContent = s.name;
              sel.appendChild(opt);
            });
          }
          if (editMode && currentDoctorId) loadDoctorData(currentDoctorId);
        })
        .catch(() => {
          document.getElementById('mainSpec').innerHTML = '<option value="">تعذر تحميل التخصصات</option>';
          if (editMode && currentDoctorId) loadDoctorData(currentDoctorId);
        });
    }

    // ─── Load doctor (edit mode) ─────────────────────────
    function loadDoctorData(id) {
      fetch(API + '?action=get_doctor&id=' + id)
        .then(r => r.json())
        .then(data => {
          if (!data.success) {
            showToast('لم يتم العثور على الطبيب', 'danger');
            setTimeout(() => location.href = 'Manage_doctors.php', 2000);
            return;
          }
          const d = data.doctor;

          // ── Basic fields ──────────────────────────────────
          document.getElementById('firstName').value = d.first_name || '';
          document.getElementById('lastName').value = d.last_name || '';
          document.getElementById('licenseNum').value = d.license_number || '';
          document.getElementById('phone').value = d.phone || '';
          document.getElementById('email').value = d.email || '';
          document.getElementById('mainSpec').value = d.specialization_id || '';
          document.getElementById('experience').value = d.experience_years || '';
          document.getElementById('status').value = d.is_active == 1 ? '1' : '0';
          document.getElementById('bio').value = d.bio || '';
          const feeEl = document.getElementById('fee');
          if (feeEl) feeEl.value = d.consultation_fee || '';

          // ── Work schedule ─────────────────────────────────
          if (d.schedule && d.schedule.length > 0) {
            // Uncheck all days first
            ['day1', 'day2', 'day3', 'day4', 'day5', 'day6', 'day7'].forEach(id => {
              const cb = document.getElementById(id);
              if (cb) cb.checked = false;
            });
            // Map day_of_week number → checkbox id (الأحد=0..السبت=6)
            const dayIdMap = { 0: 'day1', 1: 'day2', 2: 'day3', 3: 'day4', 4: 'day5', 5: 'day6', 6: 'day7' };
            let firstShift = null;
            d.schedule.forEach(s => {
              const cbId = dayIdMap[parseInt(s.day_of_week)];
              if (cbId) {
                const cb = document.getElementById(cbId);
                if (cb) cb.checked = (parseInt(s.is_available) !== 0);
              }
              if (!firstShift && s.shift_number == 1) firstShift = s;
            });
            // Set times from first shift
            if (firstShift) {
              const st = document.getElementById('startTime');
              const et = document.getElementById('endTime');
              if (st) st.value = (firstShift.start_time || '08:00:00').slice(0, 5);
              if (et) et.value = (firstShift.end_time || '17:00:00').slice(0, 5);
            }
          }

          // ── Doctor avatar ─────────────────────────────────
          if (d.avatar_path) {
            const preview = document.getElementById('imgPreview');
            const wrap = document.getElementById('imgPreviewWrap');
            if (preview && wrap) {
              preview.src = d.avatar_path;
              wrap.style.display = 'block';
            }
          }
        })
        .catch(() => showToast('خطأ في تحميل بيانات الطبيب', 'danger'));
    }

    // ─── Save Doctor ─────────────────────────────────────
    function saveDoctor() {
      const firstName = document.getElementById('firstName').value.trim();
      const lastName = document.getElementById('lastName').value.trim();
      const licenseNum = document.getElementById('licenseNum').value.trim();
      const phone = document.getElementById('phone').value.trim();
      const email = document.getElementById('email').value.trim();
      const specId = document.getElementById('mainSpec').value;
      const experience = document.getElementById('experience').value || 0;
      const fee = document.getElementById('fee').value || 0;
      const bio = document.getElementById('bio').value.trim();
      const isActive = document.getElementById('status').value;
      const password = editMode ? '' : (document.getElementById('password')?.value.trim() || '');

      // Validate
      if (!firstName || !lastName) { showToast('يرجى إدخال الاسم الأول واسم العائلة', 'danger'); return; }
      if (!email) { showToast('يرجى إدخال البريد الإلكتروني', 'danger'); return; }
      if (!specId) { showToast('يرجى اختيار التخصص الأساسي', 'danger'); return; }
      if (!licenseNum) { showToast('يرجى إدخال رقم الترخيص', 'danger'); return; }
      if (!editMode && !password) { showToast('يرجى إدخال كلمة المرور', 'danger'); return; }

      const form = new FormData();
      form.append('first_name', firstName);
      form.append('last_name', lastName);
      form.append('email', email);
      form.append('phone', phone);
      form.append('specialization_id', specId);
      form.append('license_number', licenseNum);
      form.append('experience_years', experience);
      form.append('consultation_fee', fee);
      form.append('bio', bio);
      form.append('is_active', isActive);
      if (!editMode) form.append('password', password);
      if (editMode) form.append('doctor_id', currentDoctorId);

      // Collect work schedule
      const dayMap = { 'الأحد': 0, 'الإثنين': 1, 'الثلاثاء': 2, 'الأربعاء': 3, 'الخميس': 4, 'الجمعة': 5, 'السبت': 6 };
      const startT = document.getElementById('startTime')?.value || '08:00';
      const endT = document.getElementById('endTime')?.value || '17:00';
      const scheduleDays = [];
      ['day1', 'day2', 'day3', 'day4', 'day5', 'day6', 'day7'].forEach(did => {
        const cb = document.getElementById(did);
        if (cb && dayMap[cb.value] !== undefined) {
          scheduleDays.push({ day: dayMap[cb.value], morning_start: startT, morning_end: endT, is_active: cb.checked ? 1 : 0 });
        }
      });

      // Doctor image file
      const imgFile = document.getElementById('imgInput')?.files[0];

      const action = editMode ? 'update_doctor' : 'add_doctor';
      const saveBtn = document.getElementById('saveBtn');
      document.querySelectorAll('#saveBtnText').forEach(el => el.textContent = 'جارٍ الحفظ...');
      saveBtn.disabled = true;

      // ── Async multi-step save (نفس نمط DoctorController) ────
      (async () => {
        try {
          // Step 1: Save basic doctor data
          const r1 = await fetch(API + '?action=' + action, { method: 'POST', body: form });
          const text = await r1.text();
          let data;
          try { data = JSON.parse(text); }
          catch { throw new Error('رد غير صالح: ' + text.replace(/<[^>]+>/g, '').trim().slice(0, 150)); }
          if (!data.success) throw new Error(data.message || 'فشل الحفظ');

          const doctorId = data.doctor_id || currentDoctorId;

          // Step 2: Save schedule (JSON body — مثل DoctorController::saveSchedule)
          if (scheduleDays.length > 0 && doctorId) {
            const r2 = await fetch(API + '?action=save_doctor_schedule&doctor_id=' + doctorId, {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify({ days: scheduleDays })
            });
            const d2 = await r2.json();
            if (!d2.success) showToast('خطأ في جدول العمل: ' + d2.message, 'danger');
          }

          // Step 3: Upload avatar (FormData — مثل DoctorController::uploadAvatar)
          if (imgFile && doctorId) {
            const imgFd = new FormData();
            imgFd.append('avatar', imgFile);
            const r3 = await fetch(API + '?action=upload_doctor_avatar&doctor_id=' + doctorId, {
              method: 'POST', body: imgFd
            });
            const d3 = await r3.json();
            if (!d3.success) console.warn('تحذير صورة:', d3.message);
          }

          showToast(data.message || 'تم الحفظ بنجاح!', 'success');
          setTimeout(() => location.href = 'Manage_doctors.php', 1600);

        } catch (err) {
          showToast(err.message || 'تعذر الاتصال بالخادم', 'danger');
          document.querySelectorAll('#saveBtnText').forEach(el => el.textContent = editMode ? 'حفظ التعديلات' : 'حفظ البيانات');
          saveBtn.disabled = false;
        }
      })();
    }

    // ─── Image preview ────────────────────────────────────
    function previewImage(event) {
      const file = event.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => {
        document.getElementById('imgPreview').src = e.target.result;
        document.getElementById('imgPreviewWrap').style.display = 'block';
      };
      reader.readAsDataURL(file);
    }
    function removeImage() {
      document.getElementById('imgInput').value = '';
      document.getElementById('imgPreviewWrap').style.display = 'none';
    }

    // ─── Toast ────────────────────────────────────────────
    function showToast(msg, type = 'info') {
      const icons = { success: 'bx-check-circle', danger: 'bx-error-circle', info: 'bx-info-circle' };
      const wrap = document.getElementById('toastWrap');
      const el = document.createElement('div');
      el.className = 'toast-msg ' + type;
      el.innerHTML = `<i class='bx ${icons[type] || icons.info}'></i><span>${msg}</span>`;
      wrap.appendChild(el);
      setTimeout(() => {
        el.classList.add('out');
        setTimeout(() => el.remove(), 350);
      }, 3500);
    }

    function showLogoutModal(e) {
      if (e) e.preventDefault();
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
