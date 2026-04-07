<?php
require_once __DIR__ . '/../includes/session.php';
// If already logged in, redirect to appropriate dashboard
if (is_logged_in()) {
  $role = $_SESSION['role_id'] ?? 0;
  if ($role === ROLE_ADMIN) {
    header('Location: /Hagz/admin/admin.php');
    exit();
  }
  if ($role === ROLE_DOCTOR) {
    header('Location: /Hagz/doctor/Doctor_dashboard.php');
    exit();
  }
  if ($role === ROLE_PATIENT) {
    header('Location: /Hagz/patient/dashboard-new.php');
    exit();
  }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>إنشاء حساب جديد</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <link href="../assets/css/auth.css?v=2" rel="stylesheet">
</head>

<body>

  <div class="auth-container signup-container">
    <div class="auth-card">
      <div class="logo-section">
        <div class="logo-icon"><i class='bx bx-user-plus'></i></div>
        <h1 class="logo-title">إنشاء حساب جديد</h1>
        <p class="logo-subtitle">انضم إلى نظام فرز المواعيد الذكي</p>
      </div>

      <div class="steps-wrap">
        <div class="step active" id="step1">
          <div class="step-circle">1</div>
          <span class="step-label">البيانات</span>
        </div>
        <div class="step-line" id="line1"></div>
        <div class="step" id="step2">
          <div class="step-circle">2</div>
          <span class="step-label">الحساب</span>
        </div>
        <div class="step-line" id="line2"></div>
        <div class="step" id="step3">
          <div class="step-circle">3</div>
          <span class="step-label">التأكيد</span>
        </div>
      </div>

      <div class="alert alert-danger" id="alert"></div>

      <form id="signupForm">

        <div class="form-step active" id="pane1">
          <div class="form-group">
            <label class="form-label">الاسم الكامل <span style="color:var(--danger)">*</span></label>
            <div class="input-wrap">
              <i class='bx bx-user'></i>
              <input type="text" class="form-control" id="fullName" placeholder="محمد أحمد العلي" required>
            </div>
          </div>

          <div style="display:flex; gap:1rem; margin-bottom:1.3rem;">
            <div style="flex:1;">
              <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">العمر <span style="color:var(--danger)">*</span></label>
                <input type="number" class="form-control" id="age" placeholder="25" min="1" max="120" required>
              </div>
            </div>
            <div style="flex:1;">
              <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">فصيلة الدم</label>
                <select class="form-select" id="bloodType">
                  <option value="">غير معروف</option>
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
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">الجنس <span style="color:var(--danger)">*</span></label>
            <div class="gender-opts">
              <div class="gender-opt" data-gender="ذكر" onclick="selectGender(this)">
                <i class='bx bx-male-sign'></i> ذكر
              </div>
              <div class="gender-opt" data-gender="أنثى" onclick="selectGender(this)">
                <i class='bx bx-female-sign'></i> أنثى
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">رقم الجوال <span style="color:var(--danger)">*</span></label>
            <div class="input-wrap">
              <i class='bx bx-phone'></i>
              <input type="tel" class="form-control" id="phone" placeholder="05xxxxxxxx" required>
            </div>
          </div>
        </div>

        <div class="form-step" id="pane2">
          <div class="form-group">
            <label class="form-label">البريد الإلكتروني <span style="color:var(--danger)">*</span></label>
            <div class="input-wrap">
              <i class='bx bx-envelope'></i>
              <input type="email" class="form-control" id="email" placeholder="example@email.com" required>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">كلمة المرور <span style="color:var(--danger)">*</span></label>
            <div class="input-wrap">
              <i class='bx bx-lock-alt'></i>
              <input type="password" class="form-control" id="password" placeholder="••••••••" required>
            </div>
            <small style="color:var(--muted);font-size:.75rem;">يجب أن تكون 8 أحرف على الأقل</small>
          </div>

          <div class="form-group">
            <label class="form-label">تأكيد كلمة المرور <span style="color:var(--danger)">*</span></label>
            <div class="input-wrap">
              <i class='bx bx-lock-alt'></i>
              <input type="password" class="form-control" id="confirmPassword" placeholder="••••••••" required>
            </div>
          </div>
        </div>

        <div class="form-step" id="pane3">
          <div style="text-align:center;margin-bottom:1.5rem;">
            <div style="width:70px;height:70px;border-radius:50%;background:rgba(16,185,129,.12);
                      display:flex;align-items:center;justify-content:center;
                      margin:0 auto 1rem;font-size:2.5rem;color:var(--success);">
              <i class='bx bx-check-circle'></i>
            </div>
            <h3 style="font-weight:800;color:var(--dark);font-size:1.3rem;margin-bottom:.5rem;">
              تأكيد البيانات
            </h3>
            <p style="color:var(--muted);font-size:.88rem;">
              يرجى مراجعة بياناتك قبل إنشاء الحساب
            </p>
          </div>

          <div style="background:#f8fafc;border-radius:16px;padding:1.3rem;">
            <div style="display:grid;gap:.8rem;">
              <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #e8f0fe;">
                <span style="font-size:.82rem;color:var(--muted);font-weight:600;">الاسم</span>
                <span style="font-size:.85rem;font-weight:800;color:var(--dark);" id="summaryName">—</span>
              </div>
              <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #e8f0fe;">
                <span style="font-size:.82rem;color:var(--muted);font-weight:600;">العمر / الجنس</span>
                <span style="font-size:.85rem;font-weight:800;color:var(--dark);" id="summaryAge">—</span>
              </div>
              <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid #e8f0fe;">
                <span style="font-size:.82rem;color:var(--muted);font-weight:600;">الجوال</span>
                <span style="font-size:.85rem;font-weight:800;color:var(--dark);" id="summaryPhone">—</span>
              </div>
              <div style="display:flex;justify-content:space-between;padding:.5rem 0;">
                <span style="font-size:.82rem;color:var(--muted);font-weight:600;">البريد</span>
                <span style="font-size:.85rem;font-weight:800;color:var(--dark);" id="summaryEmail">—</span>
              </div>
            </div>
          </div>

          <div class="terms">
            <input type="checkbox" id="termsCheck" required>
            <label for="termsCheck">
              أوافق على <a href="#">شروط الخدمة</a> و <a href="#">سياسة الخصوصية</a>
            </label>
          </div>
        </div>

        <div class="btn-group">
          <button type="button" class="btn btn-prev" id="prevBtn" onclick="prevStep()" style="display:none;">
            <i class='bx bx-chevron-right'></i> السابق
          </button>
          <button type="button" class="btn btn-next" id="nextBtn" onclick="nextStep()">
            التالي <i class='bx bx-chevron-left'></i>
          </button>
        </div>
      </form>

      <p class="auth-prompt">
        لديك حساب بالفعل؟ <a href="login.php" class="auth-link">تسجيل الدخول</a>
      </p>
    </div>

    <div class="back-home">
      <a href="../public/index.php">
        <i class='bx bx-arrow-back'></i>
        العودة للصفحة الرئيسية
      </a>
    </div>
  </div>

  <script>
    let currentStep = 1;
    const totalSteps = 3;
    let selectedGender = '';

    function selectGender(el) {
      document.querySelectorAll('.gender-opt').forEach(o => o.classList.remove('selected'));
      el.classList.add('selected');
      selectedGender = el.dataset.gender;
    }

    function updateSteps() {
      document.querySelectorAll('.form-step').forEach(step => step.classList.remove('active'));
      document.getElementById(`pane${currentStep}`).classList.add('active');

      document.querySelectorAll('.step').forEach((step, index) => {
        if (index + 1 === currentStep) {
          step.classList.add('active');
          step.classList.remove('done');
        } else if (index + 1 < currentStep) {
          step.classList.add('done');
          step.classList.remove('active');
        } else {
          step.classList.remove('active', 'done');
        }
      });

      document.querySelectorAll('.step-line').forEach((line, index) => {
        if (index + 1 < currentStep) line.classList.add('done', 'active');
        else if (index + 1 === currentStep) {
          line.classList.add('active');
          line.classList.remove('done');
        } else line.classList.remove('active', 'done');
      });

      document.getElementById('prevBtn').style.display = currentStep > 1 ? 'flex' : 'none';
      if (currentStep === totalSteps) {
        document.getElementById('nextBtn').innerHTML = "<i class='bx bx-check'></i> إنشاء الحساب";
        updateSummary();
      } else {
        document.getElementById('nextBtn').innerHTML = "التالي <i class='bx bx-chevron-left'></i>";
      }
    }

    function updateSummary() {
      document.getElementById('summaryName').innerText = document.getElementById('fullName').value || '—';
      const age = document.getElementById('age').value || '—';
      document.getElementById('summaryAge').innerText = `${age} سنة / ${selectedGender || '—'}`;
      document.getElementById('summaryPhone').innerText = document.getElementById('phone').value || '—';
      document.getElementById('summaryEmail').innerText = document.getElementById('email').value || '—';
    }

    function validateStep(step) {
      if (step === 1) {
        const name = document.getElementById('fullName').value.trim();
        const age = document.getElementById('age').value;
        const phone = document.getElementById('phone').value.trim();
        if (!name) { showAlert('يرجى إدخال الاسم الكامل', 'danger'); return false; }
        if (!age || age < 1) { showAlert('يرجى إدخال العمر', 'danger'); return false; }
        if (!selectedGender) { showAlert('يرجى اختيار الجنس', 'danger'); return false; }
        if (!phone || phone.length < 10) { showAlert('يرجى إدخال رقم جوال صحيح (05XXXXXXXX)', 'danger'); return false; }
      }
      if (step === 2) {
        const email = document.getElementById('email').value.trim();
        const pass = document.getElementById('password').value;
        const confirm = document.getElementById('confirmPassword').value;
        if (!email || !email.includes('@')) { showAlert('يرجى إدخال بريد إلكتروني صحيح', 'danger'); return false; }
        if (pass.length < 8) { showAlert('كلمة المرور يجب أن تكون 8 أحرف على الأقل', 'danger'); return false; }
        if (pass !== confirm) { showAlert('كلمتا المرور غير متطابقتين', 'danger'); return false; }
      }
      if (step === 3) {
        if (!document.getElementById('termsCheck').checked) {
          showAlert('يجب الموافقة على الشروط والأحكام', 'danger');
          return false;
        }
      }
      return true;
    }

    function nextStep() {
      if (!validateStep(currentStep)) return;
      if (currentStep < totalSteps) {
        currentStep++;
        updateSteps();
      } else {
        submitForm();
      }
    }

    function prevStep() {
      if (currentStep > 1) {
        currentStep--;
        updateSteps();
      }
    }

    function submitForm() {
      const btn = document.getElementById('nextBtn');
      btn.disabled = true;
      btn.innerHTML = '<i class="bx bx-loader bx-spin"></i> جاري الإنشاء...';

      const formData = new FormData();
      formData.append('fullName', document.getElementById('fullName').value);
      formData.append('age', document.getElementById('age').value);
      formData.append('gender', selectedGender);
      formData.append('bloodType', document.getElementById('bloodType').value);
      formData.append('phone', document.getElementById('phone').value);
      formData.append('email', document.getElementById('email').value);
      formData.append('password', document.getElementById('password').value);
      formData.append('confirmPassword', document.getElementById('confirmPassword').value);

      fetch('../controllers/AuthController.php?action=register', {
        method: 'POST',
        body: formData
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            showAlert('✓ ' + data.message, 'success');
            setTimeout(() => window.location.href = data.redirect || 'login.php', 1800);
          } else {
            showAlert(data.message, 'danger');
            btn.disabled = false;
            btn.innerHTML = '<i class="bx bx-check"></i> إنشاء الحساب';
          }
        })
        .catch(() => {
          showAlert('حدث خطأ في الاتصال. يرجى المحاولة مجدداً.', 'danger');
          btn.disabled = false;
          btn.innerHTML = '<i class="bx bx-check"></i> إنشاء الحساب';
        });
    }

    function showAlert(msg, type) {
      const alert = document.getElementById('alert');
      alert.textContent = msg;
      alert.className = `alert alert-${type} show`;
      setTimeout(() => alert.classList.remove('show'), 5000);
    }
  </script>
</body>

</html>