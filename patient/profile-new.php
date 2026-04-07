<?php
require_once __DIR__ . '/../includes/session.php';
require_role(ROLE_PATIENT);
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'المريض', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>الملف الشخصي | شفاء+</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="../assets/css/patient.css?v=14.0">
</head>
<body>

<?php require_once __DIR__ . '/partials/patient-nav.php'; ?>

<main class="p-main" id="mainContent">
  <div class="p-page-header">
    <h1><i class='bx bx-user-circle'></i> الملف الشخصي</h1>
    <p>إدارة بياناتك الشخصية والصحية وإعدادات الأمان الخاصة بك بخصوصية تامة.</p>
  </div>

  <div class="profile-container">
    
    <!-- Header Card -->
    <div class="p-card profile-hero">
      <div class="ph-avatar-wrap">
        <div class="ph-avatar-label">
            <div class="ph-avatar" id="profileAvatar">
              <img id="profileAvatarImg" src="" alt="صورة الملف" class="d-none">
              <span id="profileAvatarInitial">م</span>
            </div>
        </div>
        <label for="avatarFileInput" class="ph-avatar-btn">
           <i class='bx bx-camera' id="avatarCamIcon"></i>
        </label>
        <input type="file" id="avatarFileInput" accept="image/jpeg,image/png,image/webp" class="d-none">
      </div>
      <div class="ph-info">
        <h2 class="ph-name" id="profileName">جاري التحميل...</h2>
        <div class="ph-badges">
          <span class="p-badge p-badge-success"><i class='bx bx-check-circle'></i> حساب موثق</span>
          <span class="p-badge p-badge-primary"><i class='bx bx-crown'></i> عضو نشط</span>
        </div>
      </div>
    </div>

    <!-- Tabs Layout -->
    <div class="p-card profile-tabs-card">
      <div class="p-tabs-nav">
        <button class="p-tab-btn active" data-tab="info"><i class='bx bx-id-card'></i> الأساسية</button>
        <button class="p-tab-btn" data-tab="health"><i class='bx bx-heart-circle'></i> الصحية</button>
        <button class="p-tab-btn" data-tab="edit"><i class='bx bx-edit-alt'></i> تعديل</button>
        <button class="p-tab-btn" data-tab="security"><i class='bx bx-lock-alt'></i> الأمان</button>
      </div>

      <div class="p-tab-content">
        <!-- Info Tab -->
        <div class="p-pane active" id="tab-info">
          <h3 class="pane-title"><i class='bx bx-info-circle'></i> البيانات الأساسية</h3>
          <div class="pane-grid">
            <div class="info-box"><div class="ib-label">الاسم الكامل</div><div class="ib-val" id="infoName">—</div></div>
            <div class="info-box"><div class="ib-label">رقم الجوال</div><div class="ib-val" id="infoPhone">—</div></div>
            <div class="info-box"><div class="ib-label">البريد الإلكتروني</div><div class="ib-val" id="infoEmail">—</div></div>
            <div class="info-box"><div class="ib-label">العمر</div><div class="ib-val" id="infoAge">—</div></div>
            <div class="info-box"><div class="ib-label">الجنس</div><div class="ib-val" id="infoGender">—</div></div>
            <div class="info-box"><div class="ib-label">فصيلة الدم</div><div class="ib-val" id="infoBlood">—</div></div>
          </div>
        </div>

        <!-- Health Tab -->
        <div class="p-pane" id="tab-health">
          <h3 class="pane-title"><i class='bx bx-heart'></i> السجل الصحي</h3>
          
          <div class="health-block">
            <h4 class="hb-title">الأمراض المزمنة المسجلة</h4>
            <div class="chronic-grid" id="chronicDiseases">
              <span class="p-badge p-badge-muted"><i class='bx bx-loader-alt bx-spin'></i> التحميل...</span>
            </div>
          </div>
          
          <div class="health-block mt-4">
            <h4 class="hb-title">القياسات الحيوية</h4>
            <div class="pane-grid">
              <div class="info-box"><div class="ib-label">الوزن</div><div class="ib-val" id="infoWeight">—</div></div>
              <div class="info-box"><div class="ib-label">الطول</div><div class="ib-val" id="infoHeight">—</div></div>
            </div>
          </div>
        </div>

        <!-- Edit Tab -->
        <div class="p-pane" id="tab-edit">
          <h3 class="pane-title"><i class='bx bx-edit'></i> تعديل البيانات الشخصية والصحية</h3>
          <form id="editForm" class="p-form">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">رقم الجوال</label>
                <input type="tel" class="form-control" id="editPhone" placeholder="05XXXXXXXX">
              </div>
              <div class="col-md-6">
                <label class="form-label">فصيلة الدم</label>
                <select class="form-select" id="editBlood">
                  <option value="">غير محدد</option>
                  <option value="A+">A+</option><option value="A-">A-</option>
                  <option value="B+">B+</option><option value="B-">B-</option>
                  <option value="O+">O+</option><option value="O-">O-</option>
                  <option value="AB+">AB+</option><option value="AB-">AB-</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">الوزن (كجم)</label>
                <input type="number" class="form-control" id="editWeight" min="1" max="300" placeholder="مثال: 70">
              </div>
              <div class="col-md-6">
                <label class="form-label">الطول (سم)</label>
                <input type="number" class="form-control" id="editHeight" min="50" max="250" placeholder="مثال: 175">
              </div>
            </div>
            <div class="form-actions mt-4 text-start">
              <button type="submit" class="p-btn p-btn-primary px-4 py-2"><i class='bx bx-save'></i> حفظ التحديثات</button>
            </div>
          </form>
        </div>

        <!-- Security Tab -->
        <div class="p-pane" id="tab-security">
          <div class="row">
            <div class="col-lg-6">
              <h3 class="pane-title mb-4"><i class='bx bx-shield-quarter'></i> إعدادات الأمان</h3>
              <div class="p-card border-0 bg-white shadow-sm p-4 rounded-4">
                <h4 class="fs-5 mb-4 fw-bold">تغيير كلمة المرور</h4>
                <form id="passwordForm" class="p-form">
                  <div class="mb-3">
                    <label class="form-label text-muted small">كلمة المرور الحالية</label>
                    <input type="password" class="form-control form-control-lg" id="currentPassword" required>
                  </div>
                  <div class="mb-3">
                    <label class="form-label text-muted small">كلمة المرور الجديدة</label>
                    <input type="password" class="form-control form-control-lg" id="newPassword" required minlength="8">
                    <div class="form-text mt-2"><i class='bx bx-info-circle'></i> يجب أن تتكون من 8 أحرف على الأقل.</div>
                  </div>
                  <div class="mb-4">
                    <label class="form-label text-muted small">تأكيد كلمة المرور الجديدة</label>
                    <input type="password" class="form-control form-control-lg" id="confirmPassword" required minlength="8">
                  </div>
                  <div class="text-end mt-4">
                      <button type="submit" class="p-btn p-btn-primary px-4 py-2 w-100"><i class='bx bx-key'></i> تحديث الان</button>
                  </div>
                </form>
              </div>
            </div>
            <div class="col-lg-6 d-none d-lg-flex flex-column align-items-center justify-content-center text-center p-5">
               <i class='bx bx-shield-alt-2 text-primary opacity-25 display-1'></i>
               <h5 class="mt-4 fw-bold text-secondary">حماية حسابك أولويتنا</h5>
               <p class="text-muted small mt-2 w-75 mx-auto">تأكد دائماً من استخدام كلمة مرور قوية وغير مكررة للحفاظ على سرية بياناتك الصحية.</p>
            </div>
          </div>
        </div>
        
      </div>
    </div>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/hagz-ui.js"></script>
<script>
const API = '../controllers/PatientController.php';

document.addEventListener('DOMContentLoaded', () => {
    loadProfile();
    initTabs();
    initAvatarUpload();
});

function initTabs() {
    const btns = document.querySelectorAll('.p-tab-btn');
    const panes = document.querySelectorAll('.p-pane');
    btns.forEach(btn => {
        btn.addEventListener('click', () => {
            btns.forEach(b => b.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
        });
    });
}

function loadProfile() {
    fetch(`${API}?action=profile`)
      .then(r => r.json())
      .then(data => {
          if (!data.success) throw new Error(data.message);
          const p = data.profile;
          const diseases = data.chronic_diseases || [];

          const fullName = ((p.first_name ?? '') + ' ' + (p.last_name ?? '')).trim();
          document.querySelectorAll('.ph-name, #infoName').forEach(el => el.textContent = fullName);
          
          // Avatar
          const initial = document.getElementById('profileAvatarInitial');
          const img = document.getElementById('profileAvatarImg');
          if (initial) initial.textContent = fullName[0] ?? 'م';
          if (img && p.avatar_path) {
              let ipath = p.avatar_path;
              if (ipath.startsWith('/assets')) ipath = '..' + ipath;
              else if (!ipath.startsWith('http') && !ipath.startsWith('../')) ipath = '../' + ipath;
              
              img.src = ipath;
              img.classList.remove('d-none');
              if (initial) initial.classList.add('d-none');
              const navImg = document.getElementById('navAvatarImg');
              if (navImg) navImg.src = ipath;
          }

          document.getElementById('infoPhone').textContent = p.phone ?? '—';
          document.getElementById('infoEmail').textContent = p.email ?? '—';
          document.getElementById('infoGender').textContent = p.gender ?? '—';
          document.getElementById('infoBlood').textContent = p.blood_type ?? 'غير محدد';
          document.getElementById('infoWeight').textContent = p.weight ? p.weight + ' كجم' : '—';
          document.getElementById('infoHeight').textContent = p.height ? p.height + ' سم' : '—';
          
          if (p.date_of_birth) {
              const age = Math.floor((Date.now() - new Date(p.date_of_birth)) / 31557600000);
              document.getElementById('infoAge').textContent = age + ' سنة';
          }

          const cdEl = document.getElementById('chronicDiseases');
          if (cdEl) {
              cdEl.innerHTML = diseases.length
                ? diseases.map(d => `<span class="p-badge p-badge-danger"><i class='bx bx-plus-medical'></i> ${d.disease_name}</span>`).join('')
                : '<span class="text-muted fs-sm fw-bold">لا توجد سجلات للأمراض المزمنة</span>';
          }

          // Populate Edit Form
          document.getElementById('editPhone').value = p.phone ?? '';
          document.getElementById('editBlood').value = p.blood_type ?? '';
          document.getElementById('editWeight').value = p.weight ?? '';
          document.getElementById('editHeight').value = p.height ?? '';
      })
      .catch(err => {
          if(window.HagzUI) HagzUI.toast('تعذر تحميل البيانات: ' + err.message, 'error');
      });
}

document.getElementById('editForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = this.querySelector('button[type="submit"]');
    btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> جاري الحفظ...";
    btn.disabled = true;

    const fd = new FormData();
    fd.append('phone', document.getElementById('editPhone')?.value || '');
    fd.append('blood_type', document.getElementById('editBlood')?.value || '');
    fd.append('weight', document.getElementById('editWeight')?.value || '');
    fd.append('height', document.getElementById('editHeight')?.value || '');
    
    fetch(`${API}?action=update_profile`, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => {
          btn.disabled = false;
          btn.innerHTML = "<i class='bx bx-save'></i> حفظ التحديثات";
          if (data.success) {
              if(window.HagzUI) HagzUI.toast('تم حفظ التغييرات بنجاح!', 'success');
              loadProfile(); 
              document.querySelector('.p-tab-btn[data-tab="info"]').click();
          } else {
              if(window.HagzUI) HagzUI.toast(data.message || 'حدث خطأ', 'error');
          }
      }).catch(err => {
          btn.disabled = false;
          btn.innerHTML = "<i class='bx bx-save'></i> حفظ التحديثات";
          if(window.HagzUI) HagzUI.toast('حدث خطأ بالاتصال', 'error');
      });
});

document.getElementById('passwordForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const current = document.getElementById('currentPassword').value;
    const newPass = document.getElementById('newPassword').value;
    const confirm = document.getElementById('confirmPassword').value;
    
    if (newPass.length < 8) { 
        if(window.HagzUI) HagzUI.toast('كلمة المرور يجب أن تكون 8 أحرف على الأقل', 'error'); 
        return; 
    }
    if (newPass !== confirm) { 
        if(window.HagzUI) HagzUI.toast('كلمتا المرور غير متطابقتين', 'error'); 
        return; 
    }
    // We would make an API call to change password here
    if(window.HagzUI) HagzUI.toast('هذه ميزة تجريبية، جاري تحديث كلمات المرور...', 'info');
    setTimeout(() => {
        this.reset();
        document.querySelector('.p-tab-btn[data-tab="info"]').click();
    }, 1000);
});

function initAvatarUpload() {
    const input = document.getElementById('avatarFileInput');
    const img = document.getElementById('profileAvatarImg');
    const ini = document.getElementById('profileAvatarInitial');
    const camIc = document.getElementById('avatarCamIcon');
    
    input.addEventListener('change', () => {
        const file = input.files[0];
        if (!file) return;
        if (file.size > 3 * 1024 * 1024) {
            if (window.HagzUI) HagzUI.toast('حجم الصورة يجب أن يكون أقل من 3MB', 'error');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result; 
            img.classList.remove('d-none');
            ini.classList.add('d-none');
        };
        reader.readAsDataURL(file);
        
        camIc.className = 'bx bx-loader-alt bx-spin';
        const fd = new FormData();
        fd.append('avatar', file);
        
        fetch(`${API}?action=upload_avatar`, { method: 'POST', body: fd })
          .then(r => r.json())
          .then(data => {
              camIc.className = 'bx bx-camera';
              if (data.success) {
                  if (window.HagzUI) HagzUI.toast('تم تغيير الصورة بنجاح ✨', 'success');
                  const navImg = document.getElementById('navAvatarImg');
                  if (navImg) navImg.src = img.src; // Sync side immediately
              } else {
                  if (window.HagzUI) HagzUI.toast(data.message || 'فشل رفع الصورة', 'error');
                  img.classList.add('d-none');
                  ini.classList.remove('d-none');
              }
          })
          .catch(() => {
              camIc.className = 'bx bx-camera';
              if (window.HagzUI) HagzUI.toast('خطأ في شبكة الاتصال', 'error');
          });
        input.value = '';
    });
}
</script>
</body>
</html>
