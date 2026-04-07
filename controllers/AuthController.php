<?php
/**
 * AuthController — Handles Registration and Login
 * Hagz Clinic System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/session.php';

$action = $_GET['action'] ?? '';

match ($action) {
    'register'        => handleRegister(),
    'login'           => handleLogin(),
    'logout'          => logout(),
    'forgot_password' => handleForgotPassword(),
    'verify_code'     => handleVerifyCode(),
    'reset_password'  => handleResetPassword(),
    default           => json_response(false, 'طلب غير معروف.')
};

// ─────────────────────────────────────────────
// REGISTER
// ─────────────────────────────────────────────
function handleRegister(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'طلب غير صحيح.');
    }

    // 1. Collect & sanitize inputs
    $fullName    = sanitize($_POST['fullName']    ?? '');
    $phone       = sanitize($_POST['phone']       ?? '');
    $phone       = preg_replace('/[\s\-()]+/', '', $phone);
    $phone       = preg_replace('/^(\+966|00966)/', '0', $phone);
    $gender      = sanitize($_POST['gender']      ?? '');
    $age         = (int) ($_POST['age']           ?? 0);
    $bloodType   = sanitize($_POST['bloodType']   ?? '');
    $email       = sanitize($_POST['email']       ?? '');
    $password    = $_POST['password']             ?? '';
    $confirmPass = $_POST['confirmPassword']      ?? '';

    // 2. Server-side Validation
    if (empty($fullName))                      json_response(false, 'يرجى إدخال الاسم الكامل.');
    if (mb_strlen($fullName) < 3)              json_response(false, 'الاسم يجب أن يكون أكثر من 3 أحرف.');
    if (!validate_phone($phone))               json_response(false, 'رقم الجوال غير صحيح. يجب أن يبدأ بـ 05.');
    if (empty($gender))                        json_response(false, 'يرجى تحديد الجنس.');
    if ($age < 1 || $age > 120)               json_response(false, 'يرجى إدخال عمر صحيح.');
    if (!validate_email($email))               json_response(false, 'البريد الإلكتروني غير صحيح.');
    if (strlen($password) < 8)                 json_response(false, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.');
    if ($password !== $confirmPass)            json_response(false, 'كلمتا المرور غير متطابقتين.');

    // 3. Split full name
    $nameParts = explode(' ', $fullName, 2);
    $firstName = $nameParts[0];
    $lastName  = $nameParts[1] ?? '';

    // 4. Calculate date of birth from age (approximate)
    $birthYear = (int) date('Y') - $age;
    $dob       = $birthYear . '-01-01'; // approximate

    $pdo = getDB();

    // 5. Check for duplicate email or phone
    $stmt = $pdo->prepare("SELECT id FROM Users WHERE email = ? OR phone = ? LIMIT 1");
    $stmt->execute([$email, $phone]);
    if ($stmt->fetch()) {
        json_response(false, 'البريد الإلكتروني أو رقم الجوال مسجل مسبقاً.');
    }

    // 6. Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // 7. Insert into Users table (role_id = 3 for Patient)
    $stmtUser = $pdo->prepare("
        INSERT INTO Users (role_id, first_name, last_name, email, phone, password_hash)
        VALUES (3, ?, ?, ?, ?, ?)
    ");
    $stmtUser->execute([$firstName, $lastName, $email, $phone, $hashedPassword]);
    $userId = (int) $pdo->lastInsertId();

    // 8. Insert into Patients table
    $genderEn = ($gender === 'ذكر') ? 'Male' : 'Female';
    $stmtPatient = $pdo->prepare("
        INSERT INTO Patients (user_id, date_of_birth, gender, blood_type)
        VALUES (?, ?, ?, ?)
    ");
    $stmtPatient->execute([$userId, $dob, $genderEn, $bloodType ?: null]);

    json_response(true, 'تم إنشاء الحساب بنجاح! يرجى تسجيل الدخول.', ['redirect' => '/Hagz/auth/login.php']);
}

// ─────────────────────────────────────────────
// LOGIN
// ─────────────────────────────────────────────
function handleLogin(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(false, 'طلب غير صحيح.');
    }

    $email    = sanitize($_POST['email']    ?? '');
    $password = $_POST['password']          ?? '';

    if (!validate_email($email))  json_response(false, 'يرجى إدخال بريد إلكتروني صحيح.');
    if (empty($password))         json_response(false, 'يرجى إدخال كلمة المرور.');

    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT id, role_id, first_name, last_name, password_hash, is_active FROM Users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        json_response(false, 'البريد الإلكتروني أو كلمة المرور غير صحيحة.');
    }

    if (!$user['is_active']) {
        json_response(false, 'هذا الحساب موقوف. يرجى التواصل مع الإدارة.');
    }

    // Set session variables
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['role_id']    = $user['role_id'];
    $_SESSION['user_name']  = $user['first_name'] . ' ' . $user['last_name'];

    // Store role-specific ID in session
    $pdo2 = getDB();
    if ((int)$user['role_id'] === ROLE_PATIENT) {
        $ps = $pdo2->prepare("SELECT id FROM Patients WHERE user_id = ?");
        $ps->execute([$user['id']]);
        $_SESSION['patient_id'] = (int) ($ps->fetchColumn() ?: 0);
    } elseif ((int)$user['role_id'] === ROLE_DOCTOR) {
        $ds = $pdo2->prepare("SELECT id FROM Doctors WHERE user_id = ?");
        $ds->execute([$user['id']]);
        $_SESSION['doctor_id'] = (int) ($ds->fetchColumn() ?: 0);
    }


    // Redirect based on role
    $redirect = match ((int) $user['role_id']) {
        ROLE_ADMIN   => '/Hagz/admin/admin.php',
        ROLE_DOCTOR  => '/Hagz/doctor/Doctor_dashboard.php',
        ROLE_PATIENT => '/Hagz/patient/dashboard-new.php',
        default      => '/Hagz/auth/login.php'
    };

    json_response(true, 'تم تسجيل الدخول بنجاح!', [
        'redirect'  => $redirect,
        'user_name' => $_SESSION['user_name'],
        'role_id'   => $user['role_id']
    ]);
}

// ─────────────────────────────────────────────
// PASSWORD RECOVERY
// ─────────────────────────────────────────────
function handleForgotPassword(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'طلب غير صحيح.');
    $emailOrPhone = sanitize($_POST['email'] ?? '');
    if (empty($emailOrPhone)) json_response(false, 'يرجى إدخال البريد الإلكتروني أو رقم الجوال.');

    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT id, email, phone FROM Users WHERE email = ? OR phone = ? LIMIT 1");
    $stmt->execute([$emailOrPhone, $emailOrPhone]);
    $user = $stmt->fetch();

    if (!$user) {
        json_response(false, 'لم يتم العثور على حساب بهذا البريد أو الرقم.');
    }

    $otp = sprintf('%06d', mt_rand(100000, 999999));
    
    $_SESSION['reset_user_id'] = $user['id'];
    $_SESSION['reset_otp']     = $otp;
    $_SESSION['reset_expires'] = time() + 900; // 15 mins

    json_response(true, "تم إرسال رمز التحقق بطلاقة (للتجربة الرمز هو: $otp)");
}

function handleVerifyCode(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'طلب غير صحيح.');
    $code = sanitize($_POST['code'] ?? '');

    if (empty($code)) json_response(false, 'يرجى إدخال رمز التحقق.');
    
    if (!isset($_SESSION['reset_otp'], $_SESSION['reset_expires'], $_SESSION['reset_user_id'])) {
        json_response(false, 'انتهت صلاحية الجلسة، يرجى المحاولة لتسجيل الاستعادة مجدداً.');
    }

    if (time() > $_SESSION['reset_expires']) {
        unset($_SESSION['reset_otp'], $_SESSION['reset_expires'], $_SESSION['reset_user_id']);
        json_response(false, 'انتهت صلاحية الرمز. يرجى طلب رمز جديد.');
    }

    if ((string)$code !== (string)$_SESSION['reset_otp']) {
        json_response(false, 'رمز التحقق غير صحيح.');
    }

    $_SESSION['reset_verified'] = true;
    json_response(true, 'تم التحقق بنجاح! يمكنك الآن إعادة تعيين كلمة المرور الخاصه بك.');
}

function handleResetPassword(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_response(false, 'طلب غير صحيح.');
    
    $newPassword     = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (!isset($_SESSION['reset_user_id']) || empty($_SESSION['reset_verified'])) {
        json_response(false, 'عملية غير مصرح بها. يرجى إتمام التحقق أولاً.');
    }

    if (strlen($newPassword) < 8) {
        json_response(false, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.');
    }

    if ($newPassword !== $confirmPassword) {
        json_response(false, 'كلمتا المرور غير متطابقتين.');
    }

    $pdo = getDB();
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    
    $stmt = $pdo->prepare("UPDATE Users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$hashedPassword, $_SESSION['reset_user_id']]);

    unset($_SESSION['reset_otp'], $_SESSION['reset_expires'], $_SESSION['reset_user_id'], $_SESSION['reset_verified']);

    json_response(true, 'تم تغيير كلمة المرور بنجاح! نود منك تسجيل الدخول.');
}
