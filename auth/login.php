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
  <title>تسجيل الدخول - شفاء+</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <link href="../assets/css/auth.css?v=2" rel="stylesheet">
</head>

<body>

  <div class="auth-container">
    <div class="auth-card">
      <div class="logo-section">
        <div class="logo-icon"><i class='bx bx-plus-medical'></i></div>
        <h1 class="logo-title">شفاء<span style="color:var(--primary)">+</span></h1>
        <p class="logo-subtitle">نظام إدارة العيادات الذكي — سجّل دخولك للمتابعة</p>
      </div>

      <div class="alert alert-danger" id="alertBox"></div>

      <form id="loginForm" onsubmit="submitLogin(event)">
        <div class="form-group">
          <label class="form-label" for="email">البريد الإلكتروني</label>
          <div class="input-wrap">
            <i class='bx bx-envelope icon-prefix'></i>
            <input type="email" class="form-control" id="email" name="email" placeholder="example@email.com" required
              autocomplete="email">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">كلمة المرور</label>
          <div class="input-wrap">
            <i class='bx bx-lock-alt icon-prefix'></i>
            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required
              autocomplete="current-password">
            <button type="button" class="toggle-pass" onclick="togglePassword()">
              <i class='bx bx-hide' id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <div class="remember-row">
          <label class="remember-check">
            <input type="checkbox" id="rememberMe"> تذكرني
          </label>
          <a href="Reset_password.php" class="forgot-link">نسيت كلمة المرور؟</a>
        </div>

        <button type="submit" class="btn-login" id="loginBtn">
          <i class='bx bx-log-in'></i> تسجيل الدخول
        </button>
      </form>

      <div class="divider">أو</div>

      <p class="auth-prompt">
        ليس لديك حساب؟ <a href="signup.php" class="auth-link">إنشاء حساب جديد</a>
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
    function togglePassword() {
      const pw = document.getElementById('password');
      const eye = document.getElementById('eyeIcon');
      if (pw.type === 'password') {
        pw.type = 'text';
        eye.className = 'bx bx-show';
      } else {
        pw.type = 'password';
        eye.className = 'bx bx-hide';
      }
    }

    function showAlert(msg, type = 'danger') {
      const box = document.getElementById('alertBox');
      box.innerHTML = `<i class='bx bx-${type === 'danger' ? 'error-circle' : 'check-circle'}'></i> ${msg}`;
      box.className = `alert alert-${type} show`;
    }

    function submitLogin(e) {
      e.preventDefault();
      const btn = document.getElementById('loginBtn');
      const email = document.getElementById('email').value.trim();
      const pass = document.getElementById('password').value;

      if (!email || !email.includes('@')) { showAlert('يرجى إدخال بريد إلكتروني صحيح.'); return; }
      if (pass.length < 6) { showAlert('يرجى إدخال كلمة المرور.'); return; }

      btn.disabled = true;
      btn.innerHTML = '<i class="bx bx-loader bx-spin"></i> جاري التحقق...';

      const formData = new FormData();
      formData.append('email', email);
      formData.append('password', pass);

      fetch('/Hagz/controllers/AuthController.php?action=login', {
        method: 'POST',
        body: formData
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            if (window.HagzUI) HagzUI.toast('مرحباً بك! جاري التوجيه...', 'success');
            else showAlert('✓ ' + data.message, 'success');
            setTimeout(() => window.location.href = data.redirect, 1200);
          } else {
            if (window.HagzUI) HagzUI.toast(data.message, 'error');
            else showAlert(data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bx bx-log-in"></i> تسجيل الدخول';
          }
        })
        .catch(() => {
          if (window.HagzUI) HagzUI.toast('خطأ في الاتصال بالخادم. يرجى المحاولة مجدداً.', 'error');
          else showAlert('خطأ في الاتصال بالخادم. يرجى المحاولة مجدداً.');
          btn.disabled = false;
          btn.innerHTML = '<i class="bx bx-log-in"></i> تسجيل الدخول';
        });
    }
  </script>
  <script src="/Hagz/assets/js/hagz-ui.js"></script>
</body>

</html>