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
    <title>ملفي الشخصي — شفاء+</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/shared-dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/doctor-dashboard.css?v=8">
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
                    <div class="user-avatar"> <img
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
            <a href="My_patients.php" class="menu-item"><i class='bx bx-group'></i><span>المرضى</span></a>
            <a href="Doctor_reports.php" class="menu-item"><i class='bx bx-file-blank'></i><span>التقارير</span></a>
            <a href="Doctor_profile.php" class="menu-item active"><i class='bx bx-user-circle'></i><span>ملفي
                    الشخصي</span></a>
        </div>
        <div class="sidebar-bottom">
            <a href="../logout.php" class="menu-item logout" onclick="event.preventDefault(); if(window.hagzConfirmLogout) window.hagzConfirmLogout('../logout.php'); else window.location.href='../logout.php';"><i class='bx bx-log-out'></i><span>تسجيل الخروج</span></a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="main-content">
        <div class="profile-layout">

            <!-- Left: Profile card -->
            <div class="profile-card">
                <div class="profile-card-banner">
                    <div class="profile-avatar-wrap">
                        <img class="profile-avatar" id="profileAvatar"
                            src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=0d9488&color=fff&size=200&font-family=Cairo"
                            alt="Doctor">
                        <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/webp"
                            style="display:none;">
                        <div class="avatar-edit" id="avatarEditBtn" title="تغيير الصورة" style="cursor:pointer;">
                            <i class='bx bx-camera' id="avatarEditIcon"></i>
                        </div>
                    </div>
                    <div class="profile-name" id="profileName">...</div>
                    <div class="profile-spec" id="profileSpec">...</div>
                </div>
                <div class="profile-body">
                    <div class="stat-row">
                        <div class="st">
                            <div class="st-num" id="statPatients">—</div>
                            <div class="st-lbl">مرضى</div>
                        </div>
                        <div class="st">
                            <div class="st-num" id="statAppts">—</div>
                            <div class="st-lbl">موعد</div>
                        </div>
                        <div class="st">
                            <div class="st-num" id="statRating">—</div>
                            <div class="st-lbl">تقييم</div>
                        </div>
                    </div>
                    <div class="profile-info-list">
                        <div class="pil"><i class='bx bx-badge-check'></i>
                            <div>
                                <div class="pil-label">الترخيص</div>
                                <div class="pil-value" id="pInfoLicense">—</div>
                            </div>
                        </div>
                        <div class="pil"><i class='bx bx-time-five'></i>
                            <div>
                                <div class="pil-label">سنوات الخبرة</div>
                                <div class="pil-value" id="pInfoExp">—</div>
                            </div>
                        </div>
                        <div class="pil"><i class='bx bx-envelope'></i>
                            <div>
                                <div class="pil-label">البريد الإلكتروني</div>
                                <div class="pil-value" id="pInfoEmail">—</div>
                            </div>
                        </div>
                        <div class="pil"><i class='bx bx-phone'></i>
                            <div>
                                <div class="pil-label">رقم الجوال</div>
                                <div class="pil-value" dir="ltr" id="pInfoPhone">—</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Panels -->
            <div class="panels">

                <!-- Personal info -->
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class='bx bx-user'></i> البيانات الشخصية</div>
                        <button class="edit-btn" onclick="savePanelInfo()"><i class='bx bx-save'></i> حفظ</button>
                    </div>
                    <div class="form-grid">
                        <div class="fg"><label>الاسم الأول</label><input type="text" value="عبدالعزيز"></div>
                        <div class="fg"><label>اسم الأب واللقب</label><input type="text" value="خالد الصالح"></div>
                        <div class="fg"><label>التخصص</label>
                            <select>
                                <option>طب باطني</option>
                                <option>طب عام</option>
                                <option>جراحة</option>
                                <option>أطفال</option>
                                <option>طب طوارئ</option>
                                <option>نساء وولادة</option>
                            </select>
                        </div>
                        <div class="fg"><label>الدرجة العلمية</label>
                            <select>
                                <option>استشاري</option>
                                <option>أخصائي</option>
                                <option>طبيب مقيم</option>
                            </select>
                        </div>
                        <div class="fg"><label>رقم الترخيص</label><input type="text" value="SAH-2019-00471"></div>
                        <div class="fg"><label>سنوات الخبرة</label><input type="number" value="12" min="0"></div>
                        <div class="fg"><label>رقم الجوال</label><input type="tel" value="0501234567" dir="ltr"></div>
                        <div class="fg"><label>البريد الإلكتروني</label><input type="email" value="dr.alsaleh@shifa.sa"
                                dir="ltr"></div>
                        <div class="fg full"><label>نبذة شخصية</label>
                            <textarea>طبيب استشاري باطني بخبرة تزيد على 12 عاماً في تشخيص وعلاج الأمراض الداخلية. حاصل على البورد السعودي للطب الباطني. يهتم بمتابعة مرضى السكري وضغط الدم وأمراض الغدة الدرقية.</textarea>
                        </div>
                    </div>
                </div>

                <!-- Skills -->
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class='bx bx-award'></i> المهارات والتخصصات الفرعية</div>
                        <button class="edit-btn" onclick="saveSkills()"><i class='bx bx-save'></i> حفظ</button>
                    </div>
                    <!-- عرض المهارات -->
                    <div class="skill-tags" id="skillTags" style="margin-bottom:.9rem;min-height:36px;"></div>
                    <!-- إضافة مهارة جديدة -->
                    <div style="display:flex;gap:.6rem;align-items:center;">
                        <input type="text" id="skillInput" placeholder="أضف مهارة..." maxlength="60"
                            style="flex:1;padding:.6rem 1rem;border-radius:10px;border:1.5px solid var(--doc-border);font-family:'Cairo',sans-serif;font-size:.88rem;outline:none;background:#f8fafc;"
                            onkeydown="if(event.key==='Enter'){event.preventDefault();addSkill();}">
                        <button onclick="addSkill()"
                            style="padding:.6rem 1.1rem;border-radius:10px;background:var(--doc-primary);color:#fff;border:none;font-family:'Cairo',sans-serif;font-size:.88rem;font-weight:800;cursor:pointer;white-space:nowrap;">
                            <i class='bx bx-plus'></i> إضافة
                        </button>
                    </div>
                </div>

                <!-- Schedule -->
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class='bx bx-calendar-week'></i> جدول الدوام</div>
                        <button class="edit-btn" onclick="saveSchedule()"><i class='bx bx-save'></i> حفظ</button>
                    </div>

                    <!-- مدة المعاينة -->
                    <div
                        style="background:#f0fdf4;border-radius:14px;padding:1rem 1.2rem;margin-bottom:1.2rem;border:1.5px solid #bbf7d0;display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
                        <i class='bx bx-timer' style="font-size:1.4rem;color:#059669;"></i>
                        <div>
                            <div style="font-size:.8rem;font-weight:800;color:#059669;">مدة كل معاينة (دقيقة)</div>
                            <div style="font-size:.75rem;color:#6b7280;margin-top:.1rem;">يؤثّر على تواعيد الحجز المتاحة
                            </div>
                        </div>
                        <select id="consultationDuration"
                            style="padding:.55rem 1rem;border-radius:10px;border:1.5px solid #bbf7d0;font-family:'Cairo',sans-serif;font-size:.9rem;font-weight:800;color:#059669;background:#fff;outline:none;margin-right:auto;">
                            <option value="10">10 دقائق</option>
                            <option value="15">15 دقيقة</option>
                            <option value="20" selected>20 دقيقة</option>
                            <option value="30">30 دقيقة</option>
                            <option value="45">45 دقيقة</option>
                            <option value="60">ساعة كاملة</option>
                        </select>
                    </div>

                    <!-- الجدول -->
                    <div class="schedule-grid" id="scheduleGrid">
                        <!-- تعنوين -->
                        <div
                            style="display:grid;grid-template-columns:90px 44px 1fr 1fr;gap:.5rem;align-items:center;padding:.4rem 0;border-bottom:2px solid #f1f5f9;margin-bottom:.4rem;">
                            <div style="font-size:.72rem;font-weight:800;color:#94a3b8;"></div>
                            <div style="font-size:.72rem;font-weight:800;color:#94a3b8;">فعّال</div>
                            <div style="font-size:.72rem;font-weight:800;color:#0284c7;text-align:center;">صباحي 🌅
                            </div>
                            <div style="font-size:.72rem;font-weight:800;color:#7c3aed;text-align:center;">مسائي 🌙
                            </div>
                        </div>
                        <!-- أيام الأسبوع -->
                        <div class="day-row" data-day="0">
                            <div class="day-name">الأحد</div>
                            <label class="day-toggle"><input type="checkbox" checked><span
                                    class="day-toggle-slider"></span></label>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;">
                                    <input type="time" class="shift-morn-start" value="08:00">
                                    <span>إلى</span>
                                    <input type="time" class="shift-morn-end" value="14:00">
                                </div>
                            </div>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;">
                                    <input type="time" class="shift-eve-start" value="">
                                    <span>إلى</span>
                                    <input type="time" class="shift-eve-end" value="">
                                </div>
                            </div>
                        </div>
                        <div class="day-row" data-day="1">
                            <div class="day-name">الاثنين</div>
                            <label class="day-toggle"><input type="checkbox" checked><span
                                    class="day-toggle-slider"></span></label>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;"><input type="time"
                                        class="shift-morn-start" value="08:00"><span>إلى</span><input type="time"
                                        class="shift-morn-end" value="14:00"></div>
                            </div>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;"><input type="time"
                                        class="shift-eve-start" value=""><span>إلى</span><input type="time"
                                        class="shift-eve-end" value=""></div>
                            </div>
                        </div>
                        <div class="day-row" data-day="2">
                            <div class="day-name">الثلاثاء</div>
                            <label class="day-toggle"><input type="checkbox" checked><span
                                    class="day-toggle-slider"></span></label>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;"><input type="time"
                                        class="shift-morn-start" value="08:00"><span>إلى</span><input type="time"
                                        class="shift-morn-end" value="14:00"></div>
                            </div>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;"><input type="time"
                                        class="shift-eve-start" value=""><span>إلى</span><input type="time"
                                        class="shift-eve-end" value=""></div>
                            </div>
                        </div>
                        <div class="day-row" data-day="3">
                            <div class="day-name">الأربعاء</div>
                            <label class="day-toggle"><input type="checkbox" checked><span
                                    class="day-toggle-slider"></span></label>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;"><input type="time"
                                        class="shift-morn-start" value="08:00"><span>إلى</span><input type="time"
                                        class="shift-morn-end" value="14:00"></div>
                            </div>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;"><input type="time"
                                        class="shift-eve-start" value=""><span>إلى</span><input type="time"
                                        class="shift-eve-end" value=""></div>
                            </div>
                        </div>
                        <div class="day-row" data-day="4">
                            <div class="day-name">الخميس</div>
                            <label class="day-toggle"><input type="checkbox" checked><span
                                    class="day-toggle-slider"></span></label>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;"><input type="time"
                                        class="shift-morn-start" value="08:00"><span>إلى</span><input type="time"
                                        class="shift-morn-end" value="14:00"></div>
                            </div>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;"><input type="time"
                                        class="shift-eve-start" value=""><span>إلى</span><input type="time"
                                        class="shift-eve-end" value=""></div>
                            </div>
                        </div>
                        <div class="day-row" data-day="5">
                            <div class="day-name">الجمعة</div>
                            <label class="day-toggle"><input type="checkbox"><span
                                    class="day-toggle-slider"></span></label>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;"><input type="time"
                                        class="shift-morn-start" value="" disabled><span>إلى</span><input type="time"
                                        class="shift-morn-end" value="" disabled></div>
                            </div>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;"><input type="time"
                                        class="shift-eve-start" value="" disabled><span>إلى</span><input type="time"
                                        class="shift-eve-end" value="" disabled></div>
                            </div>
                        </div>
                        <div class="day-row" data-day="6">
                            <div class="day-name">السبت</div>
                            <label class="day-toggle"><input type="checkbox"><span
                                    class="day-toggle-slider"></span></label>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;"><input type="time"
                                        class="shift-morn-start" value="" disabled><span>إلى</span><input type="time"
                                        class="shift-morn-end" value="" disabled></div>
                            </div>
                            <div class="day-times" style="flex-direction:column;gap:.3rem;">
                                <div style="display:flex;align-items:center;gap:.4rem;"><input type="time"
                                        class="shift-eve-start" value="" disabled><span>إلى</span><input type="time"
                                        class="shift-eve-end" value="" disabled></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password -->
                <div class="panel">
                    <div class="panel-head">
                        <div class="panel-title"><i class='bx bx-lock-alt'></i> تغيير كلمة المرور</div>
                    </div>
                    <div class="pw-grid">
                        <div class="fg"><label>كلمة المرور الحالية</label><input type="password" placeholder="••••••••">
                        </div>
                        <div class="fg"><label>كلمة المرور الجديدة</label><input type="password" placeholder="••••••••">
                        </div>
                        <div class="fg"><label>تأكيد كلمة المرور</label><input type="password" placeholder="••••••••">
                        </div>
                        <div class="save-section">
                            <button class="save-btn-full" onclick="showToast('تم تغيير كلمة المرور ✓')">
                                <i class='bx bx-shield-quarter'></i> تحديث كلمة المرور
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Toast -->
    <div class="toast" id="toast"><i class='bx bx-check-circle'></i><span id="toastMsg"></span></div>

    <script>
        const API = '../controllers/DoctorController.php';

        // تحميل بيانات البروفايل من قاعدة البيانات
        function loadProfile() {
            fetch(`${API}?action=profile`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const p = data.profile;
                    if (!p) return;
                    const fullName = (p.first_name || '') + ' ' + (p.last_name || '');
                    // Navbar & card
                    document.querySelectorAll('.user-name').forEach(el => el.textContent = fullName);
                    document.querySelectorAll('.profile-name').forEach(el => el.textContent = 'د. ' + fullName);
                    document.querySelectorAll('.profile-spec').forEach(el => el.textContent = p.specialization || '');
                    // Avatar — لو يوجد avatar_path استخدمه، وإلا ui-avatars
                    const avatarEl = document.getElementById('profileAvatar');
                    if (p.avatar_path) {
                        avatarEl.src = p.avatar_path;
                        document.querySelectorAll('.user-avatar img').forEach(el => el.src = p.avatar_path);
                    } else {
                        const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(fullName)}&background=0d9488&color=fff&size=200&font-family=Cairo`;
                        avatarEl.src = avatarUrl;
                        document.querySelectorAll('.user-avatar img').forEach(el => el.src = avatarUrl);
                    }
                    // Profile card info via IDs
                    const pName = document.getElementById('profileName'); if (pName) pName.textContent = 'د. ' + fullName;
                    const pSpec = document.getElementById('profileSpec'); if (pSpec) pSpec.textContent = p.specialization || '';
                    const pLic = document.getElementById('pInfoLicense'); if (pLic) pLic.textContent = p.license_number || '—';
                    const pExp = document.getElementById('pInfoExp'); if (pExp) pExp.textContent = (p.experience_years || 0) + ' سنة';
                    const pMail = document.getElementById('pInfoEmail'); if (pMail) pMail.textContent = p.email || '—';
                    const pPhn = document.getElementById('pInfoPhone'); if (pPhn) pPhn.textContent = p.phone || '—';
                    // Form fields
                    const inputs = document.querySelectorAll('.fg input, .fg textarea, .fg select');
                    inputs.forEach(inp => {
                        const lbl = inp.closest('.fg')?.querySelector('label')?.textContent?.trim();
                        if (lbl === 'الاسم الأول') inp.value = p.first_name || '';
                        else if (lbl === 'اسم الأب واللقب') inp.value = p.last_name || '';
                        else if (lbl === 'رقم الترخيص') inp.value = p.license_number || '';
                        else if (lbl === 'سنوات الخبرة') inp.value = p.experience_years || '';
                        else if (lbl === 'رقم الجوال') inp.value = p.phone || '';
                        else if (lbl === 'البريد الإلكتروني') inp.value = p.email || '';
                        else if (lbl === 'نبذة شخصية') inp.value = p.bio || '';
                        // Specialization select
                        if (inp.tagName === 'SELECT' && lbl === 'التخصص') {
                            [...inp.options].forEach(o => { o.selected = o.text === p.specialization; });
                        }
                    });
                    // إحصاءات البروفايل
                    const sp = document.getElementById('statPatients');
                    const sa = document.getElementById('statAppts');
                    const sr = document.getElementById('statRating');
                    if (sp) sp.textContent = p.stat_patients ?? '—';
                    if (sa) sa.textContent = p.stat_appointments ?? '—';
                    if (sr) sr.textContent = p.stat_rating !== null ? p.stat_rating : '—';
                    // مدة كل معاينة
                    const durEl = document.getElementById('consultationDuration');
                    if (durEl && p.consultation_duration) durEl.value = String(p.consultation_duration);
                    // جدول الدوام — تعبئة من doctor_schedule
                    if (Array.isArray(p.schedule)) {
                        p.schedule.forEach(s => {
                            const row = document.querySelector(`.day-row[data-day="${s.day_of_week}"]`);
                            if (!row) return;
                            const cb = row.querySelector('input[type=checkbox]');
                            cb.checked = !!s.is_active;
                            const setVal = (cls, val) => {
                                const el = row.querySelector(cls);
                                if (el) { el.value = val || ''; el.disabled = !s.is_active; }
                            };
                            setVal('.shift-morn-start', s.morning_start);
                            setVal('.shift-morn-end', s.morning_end);
                            setVal('.shift-eve-start', s.evening_start);
                            setVal('.shift-eve-end', s.evening_end);
                        });
                    }
                })
        }

        // ===== رفع صورة البروفايل =====
        function initAvatarUpload() {
            const btn = document.getElementById('avatarEditBtn');
            const input = document.getElementById('avatarInput');
            const icon = document.getElementById('avatarEditIcon');
            if (!btn || !input) return;

            btn.addEventListener('click', () => input.click());

            input.addEventListener('change', async () => {
                const file = input.files[0];
                if (!file) return;

                // Preview فوري قبل الرفع
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('profileAvatar').src = e.target.result;
                    document.querySelectorAll('.user-avatar img').forEach(el => el.src = e.target.result);
                };
                reader.readAsDataURL(file);

                // Loading state
                icon.className = 'bx bx-loader-alt bx-spin';
                btn.style.pointerEvents = 'none';

                const fd = new FormData();
                fd.append('avatar', file);

                try {
                    const res = await fetch(`${API}?action=upload_avatar`, { method: 'POST', body: fd });
                    const data = await res.json();
                    if (data.success) {
                        showToast('تم تحديث صورة البروفايل ✓');
                        if (data.avatar_url) {
                            document.getElementById('profileAvatar').src = data.avatar_url;
                            document.querySelectorAll('.user-avatar img').forEach(el => el.src = data.avatar_url);
                        }
                    } else {
                        showToast('خطأ: ' + data.message);
                    }
                } catch {
                    showToast('تعذر رفع الصورة');
                } finally {
                    icon.className = 'bx bx-camera';
                    btn.style.pointerEvents = '';
                    input.value = '';
                }
            });
        }

        function savePanelInfo() {
            const inputs = document.querySelectorAll('.fg input, .fg textarea, .fg select');
            const fd = new FormData();
            inputs.forEach(inp => {
                const lbl = inp.closest('.fg')?.querySelector('label')?.textContent?.trim();
                if (lbl === 'الاسم الأول') fd.append('first_name', inp.value);
                else if (lbl === 'اسم الأب واللقب') fd.append('last_name', inp.value);
                else if (lbl === 'سنوات الخبرة') fd.append('experience_years', inp.value);
                else if (lbl === 'نبذة شخصية') fd.append('bio', inp.value);
                else if (lbl === 'رقم الجوال') fd.append('phone', inp.value);
                else if (lbl === 'البريد الإلكتروني') fd.append('email', inp.value);
            });
            if (!fd.has('consultation_fee')) fd.append('consultation_fee', '0');
            fetch(`${API}?action=update_profile`, { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => showToast(data.success ? 'تم حفظ البيانات بنجاح ✓' : ('خطأ: ' + data.message)))
                .catch(() => showToast('تعذر الحفظ'));
        }

        // ===== جدول الدوام =====
        function loadSchedule() {
            fetch(`${API}?action=get_schedule`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const map = data.schedule || {};
                    document.querySelectorAll('.day-row').forEach(row => {
                        const dow = parseInt(row.dataset.day);
                        if (isNaN(dow) || !map[dow]) return;
                        // أول شفت فقط للعرض الحالي
                        const shift = map[dow][0];
                        const toggle = row.querySelector('input[type=checkbox]');
                        const times = row.querySelectorAll('input[type=time]');
                        const slotIn = row.querySelector('input[data-slot]');
                        if (toggle) toggle.checked = !!parseInt(shift.is_available);
                        if (times[0]) times[0].value = shift.start_time?.slice(0, 5) || '';
                        if (times[1]) times[1].value = shift.end_time?.slice(0, 5) || '';
                        if (slotIn) slotIn.value = shift.slot_duration_min || 30;
                        times.forEach(t => t.disabled = !toggle?.checked);
                    });
                    initScheduleToggles();
                })
                .catch(() => { });
        }

        function initScheduleToggles() {
            document.querySelectorAll('.day-row').forEach(row => {
                const toggle = row.querySelector('input[type=checkbox]');
                const times = row.querySelectorAll('input[type=time]');
                if (!toggle) return;
                toggle.addEventListener('change', () => {
                    times.forEach(t => {
                        t.disabled = !toggle.checked;
                        if (!toggle.checked) t.value = '';
                        else if (!t.value) t.value = t === times[0] ? '08:00' : '16:00';
                    });
                });
            });
        }

        function saveSchedule() {
            const days = [];
            document.querySelectorAll('.day-row').forEach(row => {
                const dow = parseInt(row.dataset.day);
                if (isNaN(dow)) return;
                const active = row.querySelector('input[type=checkbox]')?.checked ? 1 : 0;
                const g = cls => row.querySelector(cls)?.value || '';
                days.push({
                    day: dow,
                    is_active: active,
                    morning_start: g('.shift-morn-start'),
                    morning_end: g('.shift-morn-end'),
                    evening_start: g('.shift-eve-start'),
                    evening_end: g('.shift-eve-end'),
                });
            });
            const duration = document.getElementById('consultationDuration')?.value || 20;
            fetch(`${API}?action=save_schedule`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ days, consultation_duration: +duration })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) HagzUI.toast('تم حفظ جدول الدوام ✓', 'success');
                    else HagzUI.toast('خطأ: ' + data.message, 'error');
                })
                .catch(() => HagzUI.toast('تعذر حفظ الجدول', 'error'));
        }

        // ===== المهارات =====
        let skillsList = [];

        function renderSkills() {
            const wrap = document.getElementById('skillTags');
            if (!wrap) return;
            if (!skillsList.length) {
                wrap.innerHTML = '<span style="font-size:.82rem;color:var(--doc-muted);">لا توجد مهارات بعد — أضف مهارتك أدناه</span>';
                return;
            }
            wrap.innerHTML = skillsList.map((s, i) =>
                `<span class="skill-tag">
                    <i class='bx bx-check'></i>${s}
                    <button onclick="removeSkill(${i})" style="background:none;border:none;padding:0 0 0 .3rem;cursor:pointer;color:inherit;font-size:.85rem;line-height:1;opacity:.7;" title="حذف">✕</button>
                </span>`
            ).join('');
        }

        function addSkill() {
            const input = document.getElementById('skillInput');
            const val = input.value.trim();
            if (!val) return;
            if (skillsList.includes(val)) { input.value = ''; return; }
            skillsList.push(val);
            renderSkills();
            input.value = '';
            input.focus();
        }

        function removeSkill(idx) {
            skillsList.splice(idx, 1);
            renderSkills();
        }

        function loadSkills() {
            fetch(`${API}?action=get_skills`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    skillsList = data.skills || [];
                    renderSkills();
                })
                .catch(() => renderSkills());
        }

        function saveSkills() {
            fetch(`${API}?action=save_skills`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ skills: skillsList })
            })
                .then(r => r.json())
                .then(data => showToast(data.success ? 'تم حفظ المهارات ✓' : ('خطأ: ' + data.message)))
                .catch(() => showToast('تعذر الحفظ'));
        }
        function showToast(msg) {
            document.getElementById('toastMsg').textContent = msg;
            const t = document.getElementById('toast');
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 3000);
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

        document.addEventListener('DOMContentLoaded', () => {
            initSidebar();
            loadProfile();
            initAvatarUpload();
            loadSchedule();
            loadSkills();

            // ── تحديث مدة المعاينة فوراً عند تغيير الـ select ──
            document.getElementById('consultationDuration')?.addEventListener('change', function () {
                const dur = parseInt(this.value) || 20;
                fetch(`${API}?action=update_slot_duration`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ slot_duration_min: dur })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) HagzUI.toast(`مدة المعاينة: ${dur} دقيقة ✓`, 'success');
                        else HagzUI.toast('تعذّر حفظ مدة المعاينة', 'error');
                    })
                    .catch(() => HagzUI.toast('خطأ في الاتصال', 'error'));
            });
        });
    </script>
    <script src="../assets/js/hagz-ui.js?v=2"></script>
    <script src="../assets/js/notif-badge.js" defer></script>
</body>

</html>
