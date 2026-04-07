<?php
require_once __DIR__ . '/../includes/session.php';
// If already logged in, redirect to appropriate dashboard
if (is_logged_in()) {
    $role = $_SESSION['role_id'] ?? 0;
    if ($role === ROLE_ADMIN)   { header('Location: /Hagz/admin/admin.php'); exit(); }
    if ($role === ROLE_DOCTOR)  { header('Location: /Hagz/doctor/Doctor_dashboard.php'); exit(); }
    if ($role === ROLE_PATIENT) { header('Location: /Hagz/patient/dashboard-new.php'); exit(); }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>إعادة تعيين كلمة المرور - نظام فرز المواعيد الذكي</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <link href="../assets/css/auth.css?v=2" rel="stylesheet">
</head>

<body class="reset-page">

  <div class="auth-container reset-container">
    <div class="auth-card reset-card">
      <div class="logo-section">
        <div class="logo-icon"><i class='bx bx-key'></i></div>
        <h1 class="logo-title">إعادة تعيين كلمة المرور</h1>
        <p class="logo-subtitle" id="mainSubtitle">أدخل بريدك الإلكتروني لإرسال رابط إعادة التعيين</p>
      </div>

      <div class="steps">
        <div class="step active" id="step1">1</div>
        <div class="step" id="step2">2</div>
        <div class="step" id="step3">3</div>
      </div>

      <div class="alert alert-danger" id="alert"></div>

      <div class="form-section active" id="section1">
        <form onsubmit="sendCode(event)">
          <div class="form-group">
            <label class="form-label">البريد الإلكتروني أو رقم الجوال</label>
            <div class="input-wrap">
              <i class='bx bx-envelope'></i>
              <input type="text" class="form-control" id="email" placeholder="example@email.com أو 05xxxxxxxx" required>
            </div>
          </div>
          <button type="submit" class="btn-submit">
            <i class='bx bx-send'></i>
            <span>إرسال رمز التحقق</span>
          </button>
        </form>
      </div>

      <div class="form-section" id="section2">
        <form onsubmit="verifyCode(event)">
          <div class="form-group">
            <label class="form-label">رمز التحقق</label>
            <div class="input-wrap">
              <i class='bx bx-lock-alt'></i>
              <input type="text" class="form-control" id="code" placeholder="أدخل الرمز المكون من 6 أرقام" maxlength="6"
                required>
            </div>
            <small style="color:var(--muted);font-size:.78rem;display:block;margin-top:.5rem;">
              تم إرسال رمز التحقق إلى بريدك الإلكتروني
            </small>
          </div>
          <button type="submit" class="btn-submit">
            <i class='bx bx-check'></i>
            <span>تحقق من الرمز</span>
          </button>
        </form>
      </div>

      <div class="form-section" id="section3">
        <form onsubmit="resetPassword(event)">
          <div class="form-group">
            <label class="form-label">كلمة المرور الجديدة</label>
            <div class="input-wrap">
              <i class='bx bx-lock-alt'></i>
              <input type="password" class="form-control" id="newPassword" placeholder="••••••••" required>
            </div>
            <small style="color:var(--muted);font-size:.78rem;display:block;margin-top:.5rem;">
              يجب أن تكون 8 أحرف على الأقل
            </small>
          </div>
          <div class="form-group">
            <label class="form-label">تأكيد كلمة المرور</label>
            <div class="input-wrap">
              <i class='bx bx-lock-alt'></i>
              <input type="password" class="form-control" id="confirmPassword" placeholder="••••••••" required>
            </div>
          </div>
          <button type="submit" class="btn-submit">
            <i class='bx bx-save'></i>
            <span>حفظ كلمة المرور الجديدة</span>
          </button>
        </form>
      </div>

      <div class="form-section" id="section4">
        <div class="success-icon">
          <i class='bx bx-check'></i>
        </div>
        <h3 style="text-align:center;font-weight:900;color:var(--dark);margin-bottom:.8rem;">
          تم بنجاح! ✓
        </h3>
        <p style="text-align:center;color:var(--muted);margin-bottom:2rem;">
          تم تغيير كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول بكلمة المرور الجديدة.
        </p>
        <a href="login.php" class="btn-submit" style="text-decoration:none;">
          <i class='bx bx-log-in'></i>
          <span>تسجيل الدخول</span>
        </a>
      </div>

      <p class="auth-prompt">
        تذكرت كلمة المرور؟ <a href="login.php" class="auth-link">تسجيل الدخول</a>
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

    function sendCode(e) {
      e.preventDefault();
      const email = document.getElementById('email').value.trim();
      const btn = e.target.querySelector('.btn-submit');

      if (!email) {
        showAlert('يرجى إدخال البريد الإلكتروني أو رقم الجوال', 'danger');
        return;
      }

      btn.disabled = true;
      btn.innerHTML = '<i class="bx bx-loader bx-spin"></i> <span>جاري الإرسال...</span>';

      const formData = new FormData();
      formData.append('email', email);

      fetch('../controllers/AuthController.php?action=forgot_password', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          showAlert(data.message, 'success');
          setTimeout(() => nextStep(), 2500); // give enough time to read OTP
        } else {
          showAlert(data.message, 'danger');
          btn.disabled = false;
          btn.innerHTML = "<i class='bx bx-send'></i> <span>إرسال رمز التحقق</span>";
        }
      })
      .catch(() => {
        showAlert('حدث خطأ في الاتصال. يرجى المحاولة مجدداً.', 'danger');
        btn.disabled = false;
        btn.innerHTML = "<i class='bx bx-send'></i> <span>إرسال رمز التحقق</span>";
      });
    }

    function verifyCode(e) {
      e.preventDefault();
      const code = document.getElementById('code').value.trim();
      const btn = e.target.querySelector('.btn-submit');

      if (code.length !== 6) {
        showAlert('يرجى إدخال رمز مكون من 6 أرقام', 'danger');
        return;
      }

      btn.disabled = true;
      btn.innerHTML = '<i class="bx bx-loader bx-spin"></i> <span>جاري التحقق...</span>';

      const formData = new FormData();
      formData.append('code', code);

      fetch('../controllers/AuthController.php?action=verify_code', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          showAlert(data.message, 'success');
          setTimeout(() => nextStep(), 1500);
        } else {
          showAlert(data.message, 'danger');
          btn.disabled = false;
          btn.innerHTML = "<i class='bx bx-check'></i> <span>تحقق من الرمز</span>";
        }
      })
      .catch(() => {
        showAlert('حدث خطأ في الاتصال. يرجى المحاولة مجدداً.', 'danger');
        btn.disabled = false;
        btn.innerHTML = "<i class='bx bx-check'></i> <span>تحقق من الرمز</span>";
      });
    }

    function resetPassword(e) {
      e.preventDefault();
      const newPass = document.getElementById('newPassword').value;
      const confirm = document.getElementById('confirmPassword').value;
      const btn = e.target.querySelector('.btn-submit');

      if (newPass.length < 8) {
        showAlert('كلمة المرور يجب أن تكون 8 أحرف على الأقل', 'danger');
        return;
      }

      if (newPass !== confirm) {
        showAlert('كلمتا المرور غير متطابقتين', 'danger');
        return;
      }

      btn.disabled = true;
      btn.innerHTML = '<i class="bx bx-loader bx-spin"></i> <span>جاري الحفظ...</span>';

      const formData = new FormData();
      formData.append('newPassword', newPass);
      formData.append('confirmPassword', confirm);

      fetch('../controllers/AuthController.php?action=reset_password', {
        method: 'POST',
        body: formData
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          showAlert(data.message, 'success');
          setTimeout(() => nextStep(), 1500);
        } else {
          showAlert(data.message, 'danger');
          btn.disabled = false;
          btn.innerHTML = "<i class='bx bx-save'></i> <span>حفظ كلمة المرور الجديدة</span>";
        }
      })
      .catch(() => {
        showAlert('حدث خطأ في الاتصال. يرجى المحاولة مجدداً.', 'danger');
        btn.disabled = false;
        btn.innerHTML = "<i class='bx bx-save'></i> <span>حفظ كلمة المرور الجديدة</span>";
      });
    }

    function nextStep() {
      document.getElementById('section' + currentStep).classList.remove('active');
      document.getElementById('step' + currentStep).classList.remove('active');
      document.getElementById('step' + currentStep).classList.add('done');

      currentStep++;

      if (currentStep <= 4) {
        document.getElementById('section' + currentStep).classList.add('active');
        if (currentStep <= 3) {
          document.getElementById('step' + currentStep).classList.add('active');
        }
      }

      const subtitles = {
        1: 'أدخل بريدك الإلكتروني لإرسال رابط إعادة التعيين',
        2: 'أدخل رمز التحقق المرسل إلى بريدك',
        3: 'أدخل كلمة المرور الجديدة',
        4: 'نجحت المهمة!'
      };
      
      const st = document.getElementById('mainSubtitle');
      if (st && subtitles[currentStep]) {
          st.textContent = subtitles[currentStep];
      }
    }

    function showAlert(msg, type) {
      const alert = document.getElementById('alert');
      alert.textContent = msg;
      alert.className = `alert alert-${type} show`;
      setTimeout(() => alert.classList.remove('show'), 6000); // 6s duration
    }
  </script>
</body>

</html>
