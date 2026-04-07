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
  <title>تفاصيل الموعد — شفاء+</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/shared-dashboard.css">
  <link rel="stylesheet" href="../assets/css/doctor-dashboard.css?v=3">
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
            <img
              src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=0d9488&color=fff&font-family=Cairo"
              alt="Doctor">
          </div>
          <div class="user-info">
            <span class="user-greeting">مرحباً دكتور،</span>
            <span class="user-name">
              <?= $userName ?>
            </span>
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
      <a href="My_appointments.php" class="menu-item active"><i
          class='bx bx-calendar-check'></i><span>مواعيدي</span></a>
      <a href="My_patients.php" class="menu-item"><i class='bx bx-group'></i><span>المرضى</span></a>
      <a href="Doctor_reports.php" class="menu-item"><i class='bx bx-file-blank'></i><span>التقارير</span></a>
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
      <i class='bx bx-arrow-back'></i> العودة للمواعيد
    </a>

    <!-- Hero -->
    <div class="appt-hero">
      <div class="appt-hero-inner">
        <div>
          <div class="appt-hero-id" id="apptId">رقم الحجز: —</div>
          <div class="appt-hero-title" id="apptTitle">موعد طبي</div>
          <div class="status-pill" id="statusPill">
            <i class='bx bx-time'></i> قيد الانتظار
          </div>
        </div>
        <div style="display:flex;gap:.8rem;flex-wrap:wrap;align-items:center;">
          <button class="btn-doc btn-doc-light" onclick="window.print()">
            <i class='bx bx-printer'></i> طباعة
          </button>
        </div>
      </div>
    </div>

    <!-- Grid -->
    <div class="detail-grid">

      <!-- Left: details -->
      <div class="detail-left">

        <!-- Appointment info -->
        <div class="sec-card">
          <div class="sec-head"><i class='bx bx-info-circle'></i> معلومات الموعد</div>
          <div class="info-grid">
            <div class="info-item">
              <div class="info-label">التاريخ</div>
              <div class="info-value" id="apptDate">—</div>
            </div>
            <div class="info-item">
              <div class="info-label">الوقت</div>
              <div class="info-value" id="apptTime">—</div>
            </div>
            <div class="info-item">
              <div class="info-label">نوع الزيارة</div>
              <div class="info-value" id="visitType">—</div>
            </div>
            <div class="info-item">
              <div class="info-label">الأولوية</div>
              <div class="info-value" id="priorityVal">—</div>
            </div>
          </div>
        </div>

        <!-- Patient Info -->
        <div class="sec-card">
          <div class="sec-head"><i class='bx bx-user-circle'></i> بيانات المريض</div>
          <div class="info-grid" style="margin-bottom:1rem;">
            <div class="info-item">
              <div class="info-label">الاسم</div>
              <div class="info-value" id="patientName">—</div>
            </div>
            <div class="info-item">
              <div class="info-label">الجنس</div>
              <div class="info-value" id="patientGender">—</div>
            </div>
            <div class="info-item">
              <div class="info-label">العمر</div>
              <div class="info-value" id="patientAge">—</div>
            </div>
            <div class="info-item">
              <div class="info-label">رقم الجوال</div>
              <div class="info-value" id="patientPhone">—</div>
            </div>
          </div>
          <div id="chronicWrap" style="display:none;">
            <div style="font-size:.82rem;font-weight:700;color:var(--doc-muted);margin-bottom:.6rem;">الأمراض المزمنة
            </div>
            <div class="sym-wrap" id="chronicList"></div>
          </div>
        </div>

        <!-- Doctor -->
        <div class="sec-card">
          <div class="sec-head"><i class='bx bxs-stethoscope'></i> الطبيب المعالج</div>
          <div class="doc-info-card">
            <div class="doc-av" id="docAv">د</div>
            <div>
              <div class="doc-nm" id="docName">—</div>
              <div class="doc-sp" id="docSpec">—</div>
            </div>
          </div>
        </div>

        <!-- AI Triage Card (يظهر فقط للحجوزات الذكية) -->
        <div class="ai-triage-card" id="aiTriageCard" style="display:none;">
          <div class="ai-triage-header">
            <div class="ai-brain-icon"><i class='bx bxs-brain'></i></div>
            <div>
              <div class="ai-triage-title">تقرير الذكاء الاصطناعي</div>
              <div class="ai-triage-sub">تحليل سري — للطبيب فقط</div>
            </div>
            <span class="ai-conf-badge" id="aiConfBadge">—</span>
          </div>
          <div class="ai-summary-box" id="aiSummaryBox">—</div>
          <div class="ai-reasoning-box" id="aiReasoningBox" style="display:none;"></div>
          <div class="ai-pri-row">
            <span class="ai-badge" id="aiPriBadge">—</span>
          </div>
        </div>

        <!-- Symptoms -->
        <div class="sec-card">
          <div class="sec-head"><i class='bx bx-heart-circle'></i> الأعراض والشكوى</div>
          <div class="sym-wrap" id="symList"></div>

          <div style="margin-top:1.3rem;">
            <div style="display:flex;justify-content:space-between;margin-bottom:.4rem;">
              <span style="font-size:.82rem;font-weight:700;color:var(--doc-muted);">شدة الألم</span>
              <span style="font-size:.82rem;font-weight:900;color:var(--doc-dark);" id="painTxt">—</span>
            </div>
            <div class="pain-bar-wrap">
              <div class="pain-bar-bg">
                <div class="pain-bar-fill" id="painBar" style="width:0%;background:var(--doc-gp);"></div>
              </div>
            </div>
          </div>

          <div class="info-grid" style="margin-top:1rem;">
            <div class="info-item">
              <div class="info-label">مدة الأعراض</div>
              <div class="info-value" id="duration">—</div>
            </div>
            <div class="info-item">
              <div class="info-label">الحالة الصحية</div>
              <div class="info-value" id="condition">—</div>
            </div>
          </div>
        </div>

        <!-- ملاحظات المريض (للقراءة فقط) -->
        <div id="patientNotesWrap" class="sec-card"
          style="display:none;border-right:3px solid var(--doc-primary);background:rgba(13,148,136,.04);">
          <div class="sec-head" style="color:var(--doc-primary);"><i class='bx bx-message-rounded-dots'></i> ملاحظات
            المريض</div>
          <p id="patientNotesTxt" style="font-size:.92rem;color:var(--doc-text);line-height:1.8;margin:0;"></p>
        </div>

        <!-- Lab Tests -->
        <div class="sec-card">
          <div class="sec-head"><i class='bx bx-test-tube'></i> الفحوصات المخبرية والتصويرية</div>

          <!-- Category tabs -->
          <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.1rem;" id="labTabs">
            <button class="lab-tab active" onclick="showLabCat('heme',this)">دم</button>
            <button class="lab-tab" onclick="showLabCat('chem',this)">كيمياء</button>
            <button class="lab-tab" onclick="showLabCat('micro',this)">ميكروب</button>
            <button class="lab-tab" onclick="showLabCat('endo',this)">هرمونات</button>
            <button class="lab-tab" onclick="showLabCat('radio',this)">أشعة</button>
            <button class="lab-tab" onclick="showLabCat('cardio',this)">قلب</button>
          </div>

          <!-- Category panels -->
          <div class="lab-panel" id="cat-heme">
            <div class="lab-checks">
              <label class="lc"><input type="checkbox" value="CBC - صورة دم كاملة"> CBC - صورة دم كاملة</label>
              <label class="lc"><input type="checkbox" value="ESR - سرعة الترسيب"> ESR - سرعة الترسيب</label>
              <label class="lc"><input type="checkbox" value="PT/INR - تخثر الدم"> PT/INR - تخثر الدم</label>
              <label class="lc"><input type="checkbox" value="Blood Group - فصيلة الدم"> Blood Group - فصيلة
                الدم</label>
              <label class="lc"><input type="checkbox" value="Reticulocyte - شبكيات الكريات"> Reticulocyte - شبكيات
                الكريات</label>
            </div>
          </div>
          <div class="lab-panel" id="cat-chem" style="display:none;">
            <div class="lab-checks">
              <label class="lc"><input type="checkbox" value="FBS - سكر الصيام"> FBS - سكر الصيام</label>
              <label class="lc"><input type="checkbox" value="HbA1c - سكر تراكمي"> HbA1c - سكر تراكمي</label>
              <label class="lc"><input type="checkbox" value="Creatinine - وظائف كلى"> Creatinine - وظائف كلى</label>
              <label class="lc"><input type="checkbox" value="BUN - نيتروجين اليوريا"> BUN - نيتروجين اليوريا</label>
              <label class="lc"><input type="checkbox" value="LFTs - وظائف كبد"> LFTs - وظائف كبد</label>
              <label class="lc"><input type="checkbox" value="Lipid Profile - دهون الدم"> Lipid Profile - دهون
                الدم</label>
              <label class="lc"><input type="checkbox" value="Uric Acid - حمض اليوريك"> Uric Acid - حمض اليوريك</label>
              <label class="lc"><input type="checkbox" value="CRP - بروتين C التفاعلي"> CRP - بروتين C التفاعلي</label>
            </div>
          </div>
          <div class="lab-panel" id="cat-micro" style="display:none;">
            <div class="lab-checks">
              <label class="lc"><input type="checkbox" value="Urine Culture - زراعة بول"> Urine Culture - زراعة
                بول</label>
              <label class="lc"><input type="checkbox" value="Blood Culture - زراعة دم"> Blood Culture - زراعة
                دم</label>
              <label class="lc"><input type="checkbox" value="Stool Analysis - تحليل براز"> Stool Analysis - تحليل
                براز</label>
              <label class="lc"><input type="checkbox" value="Swab Culture - مسحة زراعة"> Swab Culture - مسحة
                زراعة</label>
              <label class="lc"><input type="checkbox" value="Hepatitis B/C - التهاب الكبد"> Hepatitis B/C - التهاب
                الكبد</label>
            </div>
          </div>
          <div class="lab-panel" id="cat-endo" style="display:none;">
            <div class="lab-checks">
              <label class="lc"><input type="checkbox" value="TSH - الغدة الدرقية"> TSH - الغدة الدرقية</label>
              <label class="lc"><input type="checkbox" value="T3/T4 - هرمونات الدرقية"> T3/T4 - هرمونات الدرقية</label>
              <label class="lc"><input type="checkbox" value="Cortisol - الكورتيزول"> Cortisol - الكورتيزول</label>
              <label class="lc"><input type="checkbox" value="Insulin - الأنسولين"> Insulin - الأنسولين</label>
              <label class="lc"><input type="checkbox" value="Vitamin D - فيتامين د"> Vitamin D - فيتامين د</label>
              <label class="lc"><input type="checkbox" value="Vitamin B12 - فيتامين ب12"> Vitamin B12 - فيتامين
                ب12</label>
            </div>
          </div>
          <div class="lab-panel" id="cat-radio" style="display:none;">
            <div class="lab-checks">
              <label class="lc"><input type="checkbox" value="Chest X-Ray - أشعة صدر"> Chest X-Ray - أشعة صدر</label>
              <label class="lc"><input type="checkbox" value="Abdominal Ultrasound - سونار بطن"> Abdominal Ultrasound -
                سونار بطن</label>
              <label class="lc"><input type="checkbox" value="CT Chest - مقطعي صدر"> CT Chest - مقطعي صدر</label>
              <label class="lc"><input type="checkbox" value="MRI Brain - رنين دماغ"> MRI Brain - رنين دماغ</label>
              <label class="lc"><input type="checkbox" value="Bone Scan - مسح عظام"> Bone Scan - مسح عظام</label>
            </div>
          </div>
          <div class="lab-panel" id="cat-cardio" style="display:none;">
            <div class="lab-checks">
              <label class="lc"><input type="checkbox" value="ECG - تخطيط قلب"> ECG - تخطيط قلب</label>
              <label class="lc"><input type="checkbox" value="Echo - صدى قلب"> Echo - صدى قلب</label>
              <label class="lc"><input type="checkbox" value="Troponin - تروبونين"> Troponin - تروبونين</label>
              <label class="lc"><input type="checkbox" value="BNP - فشل قلب"> BNP - فشل قلب</label>
            </div>
          </div>

          <!-- Selected labs display -->
          <div id="labList" style="display:flex;flex-direction:column;gap:.6rem;margin-top:1rem;"></div>
          <button onclick="addSelectedLabs()"
            style="margin-top:.8rem;padding:.65rem 1.4rem;border-radius:10px;background:var(--doc-gp);color:#fff;border:none;font-weight:800;cursor:pointer;font-family:'Cairo',sans-serif;font-size:.88rem;">
            <i class='bx bx-check'></i> إضافة المحدد
          </button>
        </div>

        <!-- Medications -->
        <div class="sec-card">
          <div class="sec-head"><i class='bx bx-capsule'></i> الوصفة الطبية</div>
          <div id="medList" style="display:flex;flex-direction:column;gap:.7rem;margin-bottom:1rem;"></div>
          <div id="medRows" style="display:flex;flex-direction:column;gap:.8rem;margin-bottom:.9rem;"></div>
          <div style="display:flex;gap:.7rem;">
            <button onclick="addMedRow()"
              style="flex:1;padding:.65rem;border-radius:10px;background:#f8fafc;border:1.5px dashed var(--doc-border);color:var(--doc-primary);font-weight:800;cursor:pointer;font-family:'Cairo',sans-serif;font-size:.88rem;display:flex;align-items:center;justify-content:center;gap:.4rem;">
              <i class='bx bx-plus-circle'></i> إضافة دواء
            </button>
            <button onclick="confirmMeds()"
              style="padding:.65rem 1.3rem;border-radius:10px;background:var(--doc-gp);color:#fff;border:none;font-weight:800;cursor:pointer;font-family:'Cairo',sans-serif;font-size:.88rem;display:flex;align-items:center;gap:.4rem;">
              <i class='bx bx-check'></i> تأكيد
            </button>
          </div>
        </div>

        <!-- ملاحظات الطبيب -->
        <div class="sec-card">
          <div class="sec-head"><i class='bx bx-note'></i> ملاحظات الطبيب والتشخيص</div>
          <textarea id="docNotes" placeholder="أدخل التشخيص والملاحظات الطبية..."
            style="width:100%;padding:.9rem 1rem;border-radius:12px;border:1.5px solid var(--doc-border);font-family:'Cairo',sans-serif;font-size:.9rem;resize:vertical;min-height:100px;outline:none;"></textarea>
        </div>

        <!-- Timeline -->
        <div class="sec-card">
          <div class="sec-head"><i class='bx bx-time-five'></i> سجل الموعد</div>
          <div class="timeline">
            <div class="tl-item done">
              <div class="tl-time" id="createdTime">—</div>
              <div class="tl-title">تم إنشاء الحجز</div>
            </div>
            <div class="tl-item" id="tlConfirm" style="display:none;">
              <div class="tl-time" id="confirmTime">—</div>
              <div class="tl-title">تم تأكيد الموعد / بدء الفحص</div>
            </div>
            <div class="tl-item" id="tlComplete" style="display:none;">
              <div class="tl-time" id="completeTime">—</div>
              <div class="tl-title">اكتمل الموعد بنجاح</div>
            </div>
            <div class="tl-item" id="tlTransfer" style="display:none;">
              <div class="tl-time" id="transferTime">—</div>
              <div class="tl-title"><i class='bx bx-transfer-alt'></i> تم تحويل الحالة</div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right: actions -->
      <div>
        <div class="action-card">
          <div class="sec-head" style="margin-bottom:1.2rem;"><i class='bx bx-zap'></i> الإجراءات الطبية</div>

          <button class="action-btn ab-primary" onclick="saveTreatment()">
            <i class='bx bx-save'></i> حفظ المعالجة
          </button>
          <button class="action-btn ab-outline"
            style="background:rgba(245,158,11,.08);color:#d97706;border-color:rgba(245,158,11,.3);"
            onclick="openTransferModal()">
            <i class='bx bx-transfer-alt'></i> تحويل إلى استشاري
          </button>
          <button class="action-btn ab-primary" id="confirmBtn" onclick="confirmAppt()"
            style="background:linear-gradient(135deg,#0d9488,#0891b2);display:none;">
            <i class='bx bx-check-double'></i> قبول الموعد
          </button>
          <button class="action-btn ab-outline" onclick="window.print()">
            <i class='bx bx-printer'></i> طباعة التقرير
          </button>
          <button class="action-btn ab-outline" id="completeBtn" onclick="completeAppt()">
            <i class='bx bx-check-circle'></i> إنهاء الفحص
          </button>
          <button class="action-btn ab-danger" id="cancelBtn" onclick="cancelAppt()">
            <i class='bx bx-x-circle'></i> إلغاء الموعد
          </button>

          <div style="margin-top:1.2rem;padding-top:1.2rem;border-top:2px solid #f1f5f9;">
            <a href="My_appointments.php" class="action-btn ab-outline" style="text-decoration:none;">
              <i class='bx bx-arrow-back'></i> العودة للمواعيد
            </a>
          </div>
        </div>
      </div>

    </div>

  </main>

  <!-- Transfer Modal -->
  <div class="modal-overlay" id="transferModal">
    <div class="modal-box">
      <div class="modal-title"><i class='bx bx-transfer-alt'></i> تحويل الحالة إلى استشاري</div>
      <div class="modal-field">
        <label>الطبيب الاستشاري</label>
        <select id="transferDocSel">
          <option value="">— جاري التحميل... —</option>
        </select>
      </div>
      <div class="modal-field">
        <label>سبب التحويل <span style="color:#ef4444">*</span></label>
        <textarea id="transferReason" rows="2" placeholder="اكتب سبب التحويل..."></textarea>
      </div>
      <div class="modal-field">
        <label>الملخص السريري (اختياري)</label>
        <textarea id="transferSummary" rows="3" placeholder="وصف الحالة للاستشاري..."></textarea>
      </div>
      <div class="modal-field">
        <label>أولوية التحويل</label>
        <select id="transferPriority">
          <option value="Routine">روتيني</option>
          <option value="Urgent">عاجل</option>
          <option value="Emergency">طارئ</option>
        </select>
      </div>
      <div class="modal-actions">
        <button class="modal-cancel" onclick="closeTransferModal()">إلغاء</button>
        <button class="modal-send" onclick="submitTransfer()"><i class='bx bx-send'></i> إرسال التحويل</button>
      </div>
    </div>
  </div>

  <script>
    const API = '../controllers/DoctorController.php';
    let currentApptId = null;

    document.addEventListener('DOMContentLoaded', () => {
      initSidebar();
      loadAppointment();
    });

    function loadAppointment() {
      const apptId = new URLSearchParams(window.location.search).get('id');
      if (!apptId) {
        document.querySelector('.main-content').innerHTML = '<div style="padding:3rem;text-align:center;color:#ef4444">رقم الموعد غير محدد</div>';
        return;
      }
      fetch(`${API}?action=appointment_detail&id=${apptId}`)
        .then(r => r.json())
        .then(data => {
          if (!data.success) throw new Error(data.message);
          currentApptId = apptId;
          renderAppt(data.appointment, data.medical_record, data.triage);
        })
        .catch(err => {
          document.querySelector('.appt-hero-title').textContent = 'تعذر تحميل الموعد: ' + err.message;
        });
    }

    /* متغيرات مشتركة — Lab و Med تحتاجها renderAppt و addSelectedLabs كليهما */
    let labItems = [], medItems = [];

    function renderAppt(a, rec, triage) {
      document.getElementById('apptId').textContent = 'رقم الحجز: #' + a.id;
      document.getElementById('apptTitle').textContent = a.patient_name || 'موعد طبي';

      const status = a.status;
      const isDone = status === 'Completed', isCancelled = status === 'Cancelled';
      const pill = document.getElementById('statusPill');
      if (isDone) {
        pill.innerHTML = "<i class='bx bx-check-circle'></i> مكتمل";
        pill.style.background = 'rgba(16,185,129,.25)';
      } else if (isCancelled) {
        pill.innerHTML = "<i class='bx bx-x-circle'></i> ملغي";
        pill.style.background = 'rgba(239,68,68,.25)';
      }

      document.getElementById('apptDate').textContent = fmtDate(a.appointment_date);
      document.getElementById('apptTime').textContent = a.appointment_time ? a.appointment_time.slice(0, 5) : '—';
      document.getElementById('visitType').textContent = a.visit_type === 'Telehealth' ? 'عن بُعد' : 'حضوري';

      // Priority
      const priMap = { Critical: { key: 'emergency', label: 'حرجة' }, Medium: { key: 'urgent', label: 'متوسطة' }, Routine: { key: 'normal', label: 'مستقرة' } };
      const pri = priMap[a.priority] || priMap.Routine;
      document.getElementById('priorityVal').innerHTML = `<span class="pri-dot ${pri.key}"></span>${pri.label}`;

      // Patient
      document.getElementById('patientName').textContent = a.patient_name || '—';
      document.getElementById('patientPhone').textContent = a.phone || a.patient_phone || '—';
      document.getElementById('patientGender').textContent = a.gender === 'Male' ? 'ذكر' : a.gender === 'Female' ? 'أنثى' : (a.gender || '—');
      document.getElementById('patientAge').textContent = a.age ? a.age + ' سنة' : '—';

      if (a.chronic_diseases && a.chronic_diseases.length) {
        document.getElementById('chronicWrap').style.display = 'block';
        document.getElementById('chronicList').innerHTML =
          a.chronic_diseases.map(d => {
            const name = typeof d === 'object' ? (d.disease_name || JSON.stringify(d)) : d;
            return `<span class="sym-chip" style="background:rgba(239,68,68,.1);color:#991b1b;border-color:rgba(239,68,68,.2);"><i class='bx bx-error-alt'></i>${name}</span>`;
          }).join('');
      }

      // Doctor section (current session doctor)
      document.getElementById('docName').textContent = document.querySelector('.user-name')?.textContent?.trim() || '—';
      document.getElementById('docSpec').textContent = 'طبيب معالج';

      // === AI Triage Card ===
      renderTriageCard(triage);

      // === الأعراض والشكوى ===
      // الأولوية: 1) rec.symptoms (بعد فحص الطبيب) 2) triage.raw_symptoms_input (وقت الحجز) 3) لا شيء
      const isSmart = a.booking_type === 'smart';

      if (rec && rec.symptoms && rec.symptoms.length) {
        // ── بيانات من الملف الطبي (بعد الفحص) ─────────────────────────
        document.getElementById('duration').textContent = rec.symptoms[0].duration || '—';
        document.getElementById('symList').innerHTML = rec.symptoms.map(sym =>
          `<span class="sym-chip"><i class='bx bx-pulse'></i>${sym.symptom_name}</span>`).join('');
        const maxPain = Math.max(...rec.symptoms.map(s => parseInt(s.pain_level) || 0));
        renderPainBar(maxPain);
        // الحالة الصحية: للحجز الذكي فقط
        if (isSmart) {
          const ct = rec.symptoms[0].condition_type;
          document.getElementById('condition').textContent = ct === 'Chronic' ? 'مزمن' : 'حاد';
        } else {
          document.getElementById('condition').textContent = '—';
          document.getElementById('condition').closest('.info-item').style.display = 'none';
        }

      } else if (triage && triage.raw_symptoms_input) {
        // ── بيانات من triage_logs (وقت الحجز — ذكي أو عادي) ─────────
        let triageObj = null;
        try { triageObj = JSON.parse(triage.raw_symptoms_input); } catch (_) { }

        if (triageObj && typeof triageObj === 'object' && !Array.isArray(triageObj)) {
          // صيغة JSON: { symptoms, pain_level, duration, notes, chronic }
          const symsArr = triageObj.symptoms || [];
          const pain = parseInt(triageObj.pain_level) || 0;
          const dur = triageObj.duration || '—';
          const notes = triageObj.notes || '';
          const chronic = triageObj.chronic || [];

          document.getElementById('duration').textContent = dur;
          renderPainBar(pain);

          document.getElementById('symList').innerHTML = symsArr.length
            ? symsArr.map(s => {
              const name = typeof s === 'object' ? (s.name || s.symptom || JSON.stringify(s)) : s;
              return `<span class="sym-chip"><i class='bx bx-pulse'></i>${name}</span>`;
            }).join('')
            : `<span class="sym-chip"><i class='bx bx-info-circle'></i>لا توجد أعراض مسجلة</span>`;

          // الحالة الصحية: للحجز الذكي فقط
          if (isSmart) {
            document.getElementById('condition').textContent = pain >= 7 ? 'حاد' : 'متوسط';
          } else {
            document.getElementById('condition').textContent = '—';
            document.getElementById('condition').closest('.info-item').style.display = 'none';
          }

          // الأمراض المزمنة من Triage
          if (chronic.length && !a.chronic_diseases?.length) {
            document.getElementById('chronicWrap').style.display = 'block';
            document.getElementById('chronicList').innerHTML =
              chronic.map(d => `<span class="sym-chip" style="background:rgba(239,68,68,.1);color:#991b1b;border-color:rgba(239,68,68,.2);"><i class='bx bx-error-alt'></i>${d}</span>`).join('');
          }

          // ملاحظات المريض
          if (notes) {
            const el = document.getElementById('patientNotesTxt');
            if (el) el.textContent = notes;
            const wrap = document.getElementById('patientNotesWrap');
            if (wrap) wrap.style.display = 'block';
          }

        } else if (Array.isArray(triageObj)) {
          // صيغة array قديمة
          document.getElementById('symList').innerHTML = triageObj.map(s => {
            const name = typeof s === 'object' ? (s.name || s.symptom || JSON.stringify(s)) : s;
            return `<span class="sym-chip"><i class='bx bx-pulse'></i>${name}</span>`;
          }).join('');
          renderPainBar(0);
          if (!isSmart) document.getElementById('condition').closest('.info-item').style.display = 'none';

        } else {
          // نص قديم عادي
          const rawText = triage.raw_symptoms_input;
          const durMatch = rawText.match(/مدة:\s*(.+?)(?:\n|$)/u);
          if (durMatch) document.getElementById('duration').textContent = durMatch[1].trim();

          const notesMatch = rawText.match(/ملاحظات:\s*(.+?)(?:\n|$)/u);
          if (notesMatch) {
            const el = document.getElementById('patientNotesTxt');
            if (el) el.textContent = notesMatch[1].trim();
            const wrap = document.getElementById('patientNotesWrap');
            if (wrap) wrap.style.display = 'block';
          }

          const cleanText = rawText.replace(/\nملاحظات:[^\n]*/gu, '').replace(/\nمدة:[^\n]*/gu, '');
          const parts = cleanText.includes('، ')
            ? cleanText.split('، ').map(s => s.trim()).filter(Boolean)
            : cleanText.split('\n').map(s => s.trim()).filter(Boolean);

          document.getElementById('symList').innerHTML = parts.length
            ? parts.map(s => `<span class="sym-chip"><i class='bx bx-pulse'></i>${s}</span>`).join('')
            : `<span class="sym-chip"><i class='bx bx-info-circle'></i>لا توجد أعراض مسجلة</span>`;

          if (isSmart) {
            const priPain = { Critical: 9, Medium: 6, Routine: 3 };
            renderPainBar(priPain[a.priority] ?? 0);
            const condMap = { Critical: 'حاد (حرج)', Medium: 'حاد', Routine: 'مستقر' };
            document.getElementById('condition').textContent = condMap[a.priority] ?? 'حاد';
          } else {
            renderPainBar(0);
            document.getElementById('condition').closest('.info-item').style.display = 'none';
          }
        }

        // تقييم AI — للحجوزات الذكية فقط
        if (isSmart && triage.ai_predicted_priority && triage.ai_predicted_priority !== a.priority) {
          const priTriage = { Critical: 'حرجة', Medium: 'متوسطة', Routine: 'مستقرة' };
          const lbl = priTriage[triage.ai_predicted_priority];
          if (lbl) {
            document.getElementById('priorityVal').insertAdjacentHTML('afterend',
              `<div style="font-size:.7rem;color:var(--doc-muted);margin-top:2px;">تقييم AI: ${lbl}</div>`);
          }
        }

      } else {
        // ── لا أعراض أصلاً ─────────────────────────────────────────────
        document.getElementById('symList').innerHTML =
          `<div style="text-align:center;padding:1.5rem 1rem;color:var(--doc-muted);width:100%;">
             <i class='bx bx-calendar-plus' style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.5;"></i>
             <small>لا توجد بيانات أعراض — الطبيب يُكمل التفاصيل أثناء الفحص</small>
           </div>`;
        document.getElementById('duration').textContent = '—';
        document.getElementById('condition').closest('.info-item').style.display = 'none';
        renderPainBar(0);
      }

      // محتوى Medical_Record (ملاحظات + مختبريات + أدوية)
      if (rec) {
        if (rec.doctor_notes) document.getElementById('docNotes').value = rec.doctor_notes;
        if (rec.labs && rec.labs.length) {
          rec.labs.forEach(l => {
            if (!labItems.includes(l.test_name)) {
              labItems.push(l.test_name);  // أعد تعبئة الـ array من DB
            }
            addItemRow('labList', l.test_name, 'lab');
          });
        }
        if (rec.medications && rec.medications.length) {
          rec.medications.forEach(m => {
            const details = [m.dosage_strength, m.frequency, m.timing].filter(Boolean).join(' — ');
            const label = m.medication_name + (details ? ' (' + details + ')' : '');
            medItems.push({ 
                name: m.medication_name, 
                strength: m.dosage_strength || '',
                freq: m.frequency || '',
                timing: m.timing || ''
            });  // أعد تعبئة array بشكل صحيح
            addItemRow('medList', label, 'med');
          });
        }
      }
      // ===== سجل الموعد (timeline) =====

      const createdEl = document.getElementById('createdTime');
      if (createdEl) {
        // created_at من البيانات، fallback على appointment_date
        const createdVal = a.created_at || a.appointment_date;
        createdEl.textContent = safeDate(createdVal, true);
      }

      const isConfirmed = ['Confirmed', 'Completed', 'Transferred'].includes(a.status);
      const tlConfirm = document.getElementById('tlConfirm');
      const tlComplete = document.getElementById('tlComplete');
      const confirmBtn = document.getElementById('confirmBtn');
      const completeBtn = document.getElementById('completeBtn');
      const cancelBtn = document.getElementById('cancelBtn');

      if (isConfirmed && tlConfirm) {
        tlConfirm.style.display = 'block';
        tlConfirm.classList.add('done');
        const confTime = document.getElementById('confirmTime');
        if (confTime) confTime.textContent =
          safeDate(a.confirmed_at || a.consultation_start_time || a.appointment_date);
      }
      if (isDone && tlComplete) {
        tlComplete.style.display = 'block';
        tlComplete.classList.add('done');
        const compTime = document.getElementById('completeTime');
        if (compTime) compTime.textContent =
          safeDate(a.completed_at || a.consultation_end_time);
      }
      if (a.status === 'Transferred') {
        const tlTransfer = document.getElementById('tlTransfer');
        if (tlTransfer) {
          tlTransfer.style.display = 'block';
          tlTransfer.classList.add('done');
          const tTime = document.getElementById('transferTime');
          if (tTime) tTime.textContent = safeDate(a.appointment_date);
        }
      }
      // إظهار/إخفاء الأزرار حسب الحالة
      if (a.status === 'Pending') {
        if (confirmBtn) confirmBtn.style.display = '';
        if (completeBtn) completeBtn.style.display = 'none';
      }
      if (isDone || isCancelled) {
        if (cancelBtn) cancelBtn.style.display = 'none';
        if (completeBtn) completeBtn.style.display = 'none';
        if (confirmBtn) confirmBtn.style.display = 'none';
      }

    } // end renderAppt

    function fmtDate(d) {
      if (!d) return '—';
      const dt = new Date(String(d).replace(' ', 'T'));
      if (isNaN(dt)) return d;
      return dt.toLocaleDateString('ar-SA', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }
    function fmtDateTime(d) {
      if (!d) return '—';
      const dt = new Date(String(d).replace(' ', 'T'));
      if (isNaN(dt)) return d;
      return dt.toLocaleString('ar-SA', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    // تحويل timestamp MySQL → نص عربي مقروء
    function safeDate(d, withTime = false) {
      if (!d) return '—';
      const dt = new Date(String(d).replace(' ', 'T'));
      if (isNaN(dt)) return String(d);
      return withTime
        ? dt.toLocaleString('ar-SA', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
        : dt.toLocaleDateString('ar-SA', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }

    function renderPainBar(level) {
      const n = parseInt(level) || 0;
      document.getElementById('painTxt').textContent = n ? n + ' / 10' : 'غير محدد';
      const pColor = n >= 8 ? '#ef4444' : n >= 5 ? '#f59e0b' : '#10b981';
      document.getElementById('painBar').style.cssText = `width:${n * 10}%;background:${pColor};transition:width .8s cubic-bezier(.4,0,.2,1);`;
    }

    function confirmAppt() {
      if (!confirm('قبول هذا الموعد وتأكيده للمريض؟')) return;
      updateStatus('Confirmed');
    }

    function completeAppt() {
      if (!confirm('تحديد هذا الموعد كمكتمل؟')) return;
      updateStatus('Completed');
    }

    function cancelAppt() {
      if (!confirm('إلغاء هذا الموعد؟')) return;
      updateStatus('Cancelled');
    }

    function updateStatus(status) {
      const fd = new FormData();
      fd.append('appointment_id', currentApptId);
      fd.append('status', status);
      fetch(`${API}?action=update_status`, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (data.success) location.reload();
          else HagzUI.toast(data.message || 'فشل تحديث الحالة', 'error');
        });
    }

    /* ══ LAB TESTS ══ */

    function showLabCat(cat, btn) {
      document.querySelectorAll('.lab-panel').forEach(p => p.style.display = 'none');
      document.getElementById('cat-' + cat).style.display = 'block';
      document.querySelectorAll('.lab-tab').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
    }

    function addSelectedLabs() {
      const checked = document.querySelectorAll('.lab-panel input[type=checkbox]:checked');
      if (!checked.length) {
        if (window.HagzUI) HagzUI.toast('يرجى تحديد فحص واحد على الأقل من القائمة أعلاه', 'warning');
        else alert('يرجى تحديد فحص واحد على الأقل');
        return;
      }
      checked.forEach(cb => {
        if (!labItems.includes(cb.value)) {
          labItems.push(cb.value);
          addItemRow('labList', cb.value, 'lab');
        }
        cb.checked = false;
      });
      if (window.HagzUI) HagzUI.toast('تمت إضافة الفحوصات — لا تنسَ الحفظ', 'success');
    }

    /* ══ MEDICATIONS ══ */
    const MED_DB = {
      pain: ['Paracetamol', 'Ibuprofen', 'Diclofenac', 'Tramadol', 'Codeine', 'Aspirin', 'Naproxen', 'Ketorolac'],
      abx: ['Amoxicillin', 'Amoxiclav', 'Azithromycin', 'Ciprofloxacin', 'Metronidazole', 'Cefuroxime', 'Doxycycline', 'Clindamycin'],
      bp: ['Amlodipine', 'Losartan', 'Enalapril', 'Bisoprolol', 'Hydrochlorothiazide', 'Ramipril', 'Valsartan', 'Atenolol'],
      diabetes: ['Metformin', 'Glibenclamide', 'Gliclazide', 'Sitagliptin', 'Empagliflozin', 'Insulin Glargine', 'Insulin Aspart', 'Pioglitazone'],
      gastro: ['Omeprazole', 'Pantoprazole', 'Domperidone', 'Metoclopramide', 'Ondansetron', 'Lactulose', 'Loperamide', 'Ranitidine'],
      antihistamine: ['Cetirizine', 'Loratadine', 'Fexofenadine', 'Chlorpheniramine', 'Diphenhydramine', 'Ebastine', 'Desloratadine'],
      vitamins: ['Vitamin C 500mg', 'Vitamin D3 1000IU', 'Vitamin B12', 'Folic Acid', 'Iron Supplement', 'Calcium + D3', 'Zinc', 'Omega-3']
    };

    /* ══ MEDICATIONS — Dynamic rows ══ */
    let medRowCount = 0;

    function addMedRow() {
      const id = ++medRowCount;
      const row = document.createElement('div');
      row.id = 'medrow-' + id;
      row.style.cssText = 'background:#f8fafc;border:1.5px solid var(--doc-border);border-radius:12px;padding:.9rem;display:flex;flex-direction:column;gap:.6rem;';
      row.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.2rem;">
          <span style="font-size:.8rem;font-weight:800;color:var(--doc-muted);">دواء ${id}</span>
          <button onclick="document.getElementById('medrow-${id}').remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:1rem;padding:0;"><i class='bx bx-trash'></i></button>
        </div>
        <input type="text" placeholder="اسم الدواء (يمكن الكتابة)" data-field="name"
          style="width:100%;padding:.6rem .8rem;border-radius:8px;border:1.5px solid var(--doc-border);font-family:'Cairo',sans-serif;font-size:.85rem;outline:none;box-sizing:border-box;">
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:.5rem;">
          <select data-field="strength" style="padding:.55rem .6rem;border-radius:8px;border:1.5px solid var(--doc-border);font-family:'Cairo',sans-serif;font-size:.82rem;outline:none;background:#fff;">
            <option value="">الجرعة</option>
            <option>2.5mg</option><option>5mg</option><option>10mg</option><option>25mg</option>
            <option>50mg</option><option>100mg</option><option>125mg</option><option>200mg</option>
            <option>250mg</option><option>400mg</option><option>500mg</option><option>1g</option>
            <option>2g</option><option>5ml</option><option>10ml</option>
          </select>
          <select data-field="freq" style="padding:.55rem .6rem;border-radius:8px;border:1.5px solid var(--doc-border);font-family:'Cairo',sans-serif;font-size:.82rem;outline:none;background:#fff;">
            <option value="">التكرار</option>
            <option>مرة يومياً</option><option>مرتين يومياً</option>
            <option>3 مرات يومياً</option><option>4 مرات يومياً</option>
            <option>كل 6 ساعات</option><option>كل 8 ساعات</option><option>كل 12 ساعة</option>
            <option>أسبوعياً</option><option>عند الحاجة</option>
          </select>
          <select data-field="timing" style="padding:.55rem .6rem;border-radius:8px;border:1.5px solid var(--doc-border);font-family:'Cairo',sans-serif;font-size:.82rem;outline:none;background:#fff;">
            <option value="">التوقيت</option>
            <option>قبل الأكل</option><option>بعد الأكل</option>
            <option>مع الأكل</option><option>قبل النوم</option><option>صباحاً</option>
          </select>
        </div>`;
      document.getElementById('medRows').appendChild(row);
    }

    function confirmMeds() {
      const rows = document.querySelectorAll('#medRows > div');
      rows.forEach(row => {
        const name = row.querySelector('[data-field="name"]')?.value.trim();
        const strength = row.querySelector('[data-field="strength"]')?.value || '';
        const freq = row.querySelector('[data-field="freq"]')?.value || '';
        const timing = row.querySelector('[data-field="timing"]')?.value || '';
        if (!name) return;
        const details = [strength, freq, timing].filter(Boolean).join(' — ');
        const label = `${name}${details ? ' (' + details + ')' : ''}`;
        medItems.push({ name, strength, freq, timing });
        addItemRow('medList', label, 'med');
        row.remove();
      });
    }

    function addItemRow(containerId, text, type) {
      const wrap = document.getElementById(containerId);
      const row = document.createElement('div');
      row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:.6rem .9rem;border-radius:10px;background:#f8fafc;border:1.5px solid var(--doc-border);font-size:.88rem;font-weight:700;';
      const icon = type === 'lab'
        ? "<i class='bx bx-test-tube' style='color:var(--doc-primary);margin-left:.5rem;'></i>"
        : "<i class='bx bx-capsule' style='color:#10b981;margin-left:.5rem;'></i>";
      row.innerHTML = `<span>${icon}${text}</span><button onclick="this.closest('div').remove()" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:1.1rem;"><i class='bx bx-x'></i></button>`;
      wrap.appendChild(row);
    }

    function saveTreatment() {
      // Auto-confirm any open med rows before saving
      const openRows = document.querySelectorAll('#medRows > div');
      openRows.forEach(row => {
        const name = row.querySelector('[data-field="name"]')?.value.trim();
        const strength = row.querySelector('[data-field="strength"]')?.value || '';
        const freq = row.querySelector('[data-field="freq"]')?.value || '';
        const timing = row.querySelector('[data-field="timing"]')?.value || '';
        if (!name) return;
        const details = [strength, freq, timing].filter(Boolean).join(' — ');
        const label = `${name}${details ? ' (' + details + ')' : ''}`;
        // حفظ كل حقل منفصل لتجنب خلط القيم عند السplit
        medItems.push({ name, strength, freq, timing });
        addItemRow('medList', label, 'med');
        row.remove();
      });

      // Collect labs
      const labs = labItems.map(name => ({ name }));
      // Collect meds — الحقول محفوظة منفصلة في medItems
      const medsPayload = medItems.map(m => ({
        name: m.name,
        strength: m.strength || '',
        freq: m.freq || '',
        timing: m.timing || ''
      }));
      // Collect notes
      const notes = document.getElementById('docNotes').value;

      if (!labs.length && !medsPayload.length && !notes.trim()) {
        HagzUI.toast('الرجاء إدخال ملاحظات أو وصفة أو فحوصات قبل الحفظ', 'warning');
        return;
      }

      const btn = document.querySelector('.ab-primary');
      btn.disabled = true;
      btn.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> جاري الحفظ...";

      const fd = new FormData();
      fd.append('appointment_id', currentApptId);
      fd.append('notes', notes);
      fd.append('labs', JSON.stringify(labs));
      fd.append('meds', JSON.stringify(medsPayload));
      fd.append('symptoms', JSON.stringify([]));

      fetch(`${API}?action=save_treatment`, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          btn.disabled = false;
          if (data.success) {
            btn.innerHTML = "<i class='bx bx-check-circle'></i> تم الحفظ بنجاح";
            btn.style.background = '#10b981';
            setTimeout(() => {
              btn.innerHTML = "<i class='bx bx-save'></i> حفظ المعالجة";
              btn.style.background = '';
            }, 2500);
          } else {
            btn.innerHTML = "<i class='bx bx-save'></i> حفظ المعالجة";
            HagzUI.toast('خطأ في الحفظ: ' + data.message, 'error');
          }
        })
        .catch(() => {
          btn.disabled = false;
          btn.innerHTML = "<i class='bx bx-save'></i> حفظ المعالجة";
          HagzUI.toast('تعذّر الحفظ — تحقّق من الاتصال بالخادم', 'error');
        });
    }

    // ══ TRANSFER MODAL ══
    async function openTransferModal() {
      // تحميل قائمة الأطباء
      const sel = document.getElementById('transferDocSel');
      sel.innerHTML = '<option value="">— جاري التحميل... —</option>';
      const data = await fetch('/controllers/BookingController.php?action=doctors').then(r => r.json()).catch(() => null);
      if (data?.success && data.doctors?.length) {
        sel.innerHTML = '<option value="">— اختر الطبيب —</option>'
          + data.doctors.map(d => `<option value="${d.id}">${esc(d.name)} — ${esc(d.specialization)}</option>`).join('');
      } else {
        sel.innerHTML = '<option value="">لا توجد أطباء متاحون</option>';
      }
      document.getElementById('transferReason').value = '';
      document.getElementById('transferSummary').value = document.getElementById('docNotes')?.value || '';
      document.getElementById('transferModal').classList.add('open');
    }

    function closeTransferModal() {
      document.getElementById('transferModal').classList.remove('open');
    }

    async function submitTransfer() {
      const toDoc = document.getElementById('transferDocSel').value;
      const reason = document.getElementById('transferReason').value.trim();
      const summary = document.getElementById('transferSummary').value.trim();
      const priority = document.getElementById('transferPriority').value;
      if (!toDoc || !reason) {
        HagzUI.toast('الرجاء اختيار الطبيب وكتابة سبب التحويل', 'warning');
        return;
      }

      const btn = document.querySelector('.modal-send');
      btn.disabled = true; btn.innerHTML = `<i class='bx bx-loader-alt bx-spin'></i> جاري الإرسال...`;

      const data = await fetch(`${API}?action=create_referral`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ appointment_id: +currentApptId, to_doctor_id: +toDoc, reason, clinical_summary: summary, priority })
      }).then(r => r.json()).catch(() => null);

      btn.disabled = false; btn.innerHTML = `<i class='bx bx-send'></i> إرسال التحويل`;

      if (data?.success) {
        // تحديث حالة الموعد إلى Transferred
        const fd = new FormData();
        fd.append('appointment_id', currentApptId);
        fd.append('status', 'Transferred');
        await fetch(`${API}?action=update_status`, { method: 'POST', body: fd });
        closeTransferModal();
        location.reload();
      } else {
        HagzUI.toast(data?.message || 'فشل إرسال التحويل', 'error');
      }
    }

    function renderTriageCard(triage) {
      if (!triage || !triage.ai_summary) return;
      const card = document.getElementById('aiTriageCard');
      if (!card) return;
      card.style.display = 'block';

      const conf = triage.algorithm_confidence_score;
      document.getElementById('aiConfBadge').textContent =
        conf != null ? `دقة: ${Math.round(conf * 100)}%` : 'AI Analysis';

      document.getElementById('aiSummaryBox').textContent = triage.ai_summary;

      if (triage.ai_reasoning) {
        const rBox = document.getElementById('aiReasoningBox');
        rBox.style.display = 'block';
        rBox.textContent = triage.ai_reasoning;
      }

      const priMap = {
        Critical: { cls: 'critical', label: '🔴 حرجة — تدخل عاجل' },
        Medium: { cls: 'medium', label: '🟡 متوسطة' },
        Routine: { cls: 'routine', label: '🟢 مستقرة' }
      };
      const p = priMap[triage.ai_predicted_priority] || priMap.Routine;
      const badge = document.getElementById('aiPriBadge');
      badge.className = `ai-badge ${p.cls}`;
      badge.textContent = p.label;

      if (triage.scheduled_date) {
        const s = document.createElement('span');
        s.className = 'ai-badge';
        s.style.background = 'rgba(255,255,255,.15)';
        s.textContent = `📅 مقترح AI: ${triage.scheduled_date}${triage.scheduled_time ? ' — ' + triage.scheduled_time.slice(0, 5) : ''}`;
        badge.closest('.ai-pri-row').appendChild(s);
      }
    }

    function esc(s) {
      return String(s ?? '').replace(/[&<>"']/g, c =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
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
          sidebar.classList.remove('active');
          document.body.style.overflow = '';
        }
      });
    }
  </script>
  <script src="../assets/js/hagz-ui.js?v=2"></script>
  <script src="../assets/js/notif-badge.js" defer></script>
</body>

</html>
