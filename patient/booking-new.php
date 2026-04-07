<?php
require_once __DIR__ . '/../includes/session.php';
require_role(ROLE_PATIENT);
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'المريض', ENT_QUOTES, 'UTF-8');
$patient_id = $_SESSION['patient_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>حجز موعد - شفاء+</title>
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="../assets/css/patient.css?v=9.0">
</head>
<body>

<?php 
$activeNav = 'booking';
include __DIR__ . '/partials/patient-nav.php'; 
?>

<main class="p-main">
  <!-- Page Header -->
  <div class="p-page-header">
    <h1>حجز موعد جديد</h1>
    <p>بوابتك لحجز المواعيد الطبية بسهولة ومرونة</p>
  </div>

  <!-- BOOKING MODE SELECTOR -->
  <div class="booking-mode-wrap" id="modeSelector">
    <div class="mode-heading">
      <h2>كيف تريد الحجز؟</h2>
      <p>اختر الطريقة المناسبة لك للمتابعة</p>
    </div>
    
    <div class="row g-4">
      <!-- Smart UI -->
      <div class="col-md-6">
        <div class="booking-mode-card" onclick="selectMode('smart')">
          <div class="mode-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);">
             <i class='bx bx-brain'></i>
          </div>
          <div class="mode-title">حجز ذكي (مُوصى به)</div>
          <div class="mode-desc">يحلل الذكاء الاصطناعي أعراضك ويعيّن أفضل طبيب وموعد تلقائياً حسب أولوية حالتك.</div>
          <span class="mode-badge" style="background:#eef2ff; color:#4f46e5;">سريع ودقيق</span>
        </div>
      </div>
      <!-- Regular UI -->
      <div class="col-md-6">
        <div class="booking-mode-card" onclick="selectMode('regular')">
          <div class="mode-icon" style="background:linear-gradient(135deg,#0ea5e9,#0284c7);">
             <i class='bx bx-calendar-plus'></i>
          </div>
          <div class="mode-title">حجز يَدَوي (عادي)</div>
          <div class="mode-desc">أنت تختار التخصص، والطبيب، واليوم، والوقت المناسب لك بشكل مباشر وكامل التحكّم.</div>
          <span class="mode-badge" style="background:#f0f9ff; color:#0284c7;">تحكم كامل</span>
        </div>
      </div>
    </div>

    <!-- Emergency Strip -->
    <div class="emergency-strip" onclick="triggerEmergency()">
      <div class="em-icon"><i class='bx bxs-error-circle'></i></div>
      <div>
        <div class="em-title">هل لديك حالة طارئة؟ اضغط هنا للتدخل السريع</div>
        <div class="em-sub">تجاوز الأسئلة التقليدية للوصول لأقرب طبيب طوارئ فوراً</div>
      </div>
      <i class='bx bx-chevron-left' style="margin-inline-start:auto; font-size:1.5rem;"></i>
    </div>
  </div>

  <!-- MAIN WIZARD CONTAINER -->
  <div id="wizardContainer" style="display:none;">
    <div class="row g-4">
      
      <!-- ════ FORM COLUMN ════ -->
      <div class="col-lg-8">
        <div class="p-card">
          <!-- Wizard Steps Track -->
          <div class="wizard-steps">
            <div class="steps-track">
              <div class="step-item active" id="t1" onclick="goTo(1)">
                <div class="step-bubble"><i class='bx bx-user'></i></div>
                <div class="step-label">بياناتك</div>
              </div>
              <div class="step-connector" id="cn1"></div>
              <div class="step-item" id="t2" onclick="goTo(2)">
                <div class="step-bubble"><i class='bx bx-heart-circle'></i></div>
                <div class="step-label">الأعراض</div>
              </div>
              <div class="step-connector" id="cn2"></div>
              <div class="step-item" id="t3" onclick="goTo(3)">
                <div class="step-bubble"><i class='bx bx-pulse'></i></div>
                <div class="step-label">التاريخ المرضي</div>
              </div>
              <div class="step-connector" id="cn3"></div>
              <div class="step-item" id="t4" onclick="goTo(4)">
                <div class="step-bubble"><i class='bx bx-calendar-check'></i></div>
                <div class="step-label">الموعد</div>
              </div>
            </div>
          </div>

          <!-- Wizard Body Panels -->
          <div class="wizard-body">
            
            <!-- PANE 1: Personal Info -->
            <div class="wizard-pane active" id="pane1">
              <div class="pane-heading">
                <div class="pane-icon"><i class='bx bx-user'></i></div>
                <div>
                  <h3>البيانات الشخصية</h3>
                  <p>تأكيد بيانات المريض للبدء في إجراءات الموعد</p>
                </div>
              </div>

              <div class="row g-3 mb-4">
                <div class="col-md-6">
                  <label class="p-form-label">الاسم الكامل <span class="text-danger">*</span></label>
                  <div class="p-icon-input">
                    <i class='bx bx-user'></i>
                    <input type="text" class="p-form-control" id="p1Name" placeholder="محمد أحمد العلي">
                  </div>
                </div>
                <div class="col-md-6">
                  <label class="p-form-label">رقم الجوال <span class="text-danger">*</span></label>
                  <div class="p-icon-input">
                    <i class='bx bx-phone'></i>
                    <input type="tel" class="p-form-control" id="p1Phone" placeholder="05xxxxxxxx">
                  </div>
                </div>
                <div class="col-md-4">
                  <label class="p-form-label">العمر <span class="text-danger">*</span></label>
                  <input type="number" class="p-form-control" id="p1Age" placeholder="25" min="1" max="120">
                </div>
                <div class="col-md-4">
                  <label class="p-form-label">الجنس <span class="text-danger">*</span></label>
                  <select class="p-form-select" id="p1Gender">
                    <option value="">اختر</option>
                    <option>ذكر</option>
                    <option>أنثى</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="p-form-label">فصيلة الدم</label>
                  <select class="p-form-select" id="p1Blood">
                    <option value="">غير معروف</option>
                    <option>A+</option><option>A-</option>
                    <option>B+</option><option>B-</option>
                    <option>O+</option><option>O-</option>
                    <option>AB+</option><option>AB-</option>
                  </select>
                </div>
              </div>

              <!-- Specialty (Only visible if regular mode and we need it early? We hid it initially via JS) -->
              <div id="specSection" style="display:none;">
                <label class="p-form-label mb-3">اختر التخصص الطبي <span class="text-danger">*</span></label>
                <div class="spec-grid">
                  <div class="spec-card" data-spec="طب عام" onclick="pickSpec(this)">
                    <div class="check-badge"><i class='bx bx-check'></i></div>
                    <div class="spec-icon" style="background:#4f46e5;"><i class='bx bx-plus-medical'></i></div>
                    <div class="spec-name">طب عام</div>
                  </div>
                  <div class="spec-card" data-spec="طب طوارئ" onclick="pickSpec(this)">
                    <div class="check-badge"><i class='bx bx-check'></i></div>
                    <div class="spec-icon" style="background:#ef4444;"><i class='bx bx-first-aid'></i></div>
                    <div class="spec-name">طوارئ</div>
                  </div>
                  <div class="spec-card" data-spec="طب باطني" onclick="pickSpec(this)">
                    <div class="check-badge"><i class='bx bx-check'></i></div>
                    <div class="spec-icon" style="background:#0ea5e9;"><i class='bx bx-heart'></i></div>
                    <div class="spec-name">باطني</div>
                  </div>
                  <div class="spec-card" data-spec="أطفال" onclick="pickSpec(this)">
                    <div class="check-badge"><i class='bx bx-check'></i></div>
                    <div class="spec-icon" style="background:#f59e0b;"><i class='bx bx-child'></i></div>
                    <div class="spec-name">أطفال</div>
                  </div>
                  <div class="spec-card" data-spec="عظام" onclick="pickSpec(this)">
                    <div class="check-badge"><i class='bx bx-check'></i></div>
                    <div class="spec-icon" style="background:#8b5cf6;"><i class='bx bx-body'></i></div>
                    <div class="spec-name">عظام</div>
                  </div>
                  <div class="spec-card" data-spec="أسنان" onclick="pickSpec(this)">
                    <div class="check-badge"><i class='bx bx-check'></i></div>
                    <div class="spec-icon" style="background:#14b8a6;"><i class='bx bx-smile'></i></div>
                    <div class="spec-name">أسنان</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- PANE 2: Symptoms -->
            <div class="wizard-pane" id="pane2">
               <div class="pane-heading">
                <div class="pane-icon" style="background: linear-gradient(135deg,#ec4899,#be185d);"><i class='bx bx-heart-circle'></i></div>
                <div>
                  <h3>الأعراض الحالية</h3>
                  <p>اختر ما يصف حالتك بدقة — يمكنك اختيار أكثر من عرض</p>
                </div>
              </div>
              
              <div class="symptom-grid mb-4">
                <!-- Emergency symptoms (score 3 each) -->
                <div class="sym-chip" data-score="3" onclick="toggleSym(this)"><i class='bx bx-heart-circle'></i>ألم شديد في الصدر</div>
                <div class="sym-chip" data-score="3" onclick="toggleSym(this)"><i class='bx bx-dizzy'></i>صعوبة في التنفس</div>
                <div class="sym-chip" data-score="3" onclick="toggleSym(this)"><i class='bx bx-confused'></i>فقدان الوعي / إغماء</div>
                <div class="sym-chip" data-score="3" onclick="toggleSym(this)"><i class='bx bx-droplet'></i>نزيف غير متوقف</div>
                <div class="sym-chip" data-score="3" onclick="toggleSym(this)"><i class='bx bx-walk'></i>شلل أو تخدر مفاجئ</div>
                <!-- Urgent symptoms (score 2 each) -->
                <div class="sym-chip" data-score="2" onclick="toggleSym(this)"><i class='bx bx-trending-up'></i>ارتفاع حرارة شديد</div>
                <div class="sym-chip" data-score="2" onclick="toggleSym(this)"><i class='bx bx-dizzy'></i>دوخة وغثيان</div>
                <div class="sym-chip" data-score="2" onclick="toggleSym(this)"><i class='bx bx-body'></i>ألم شديد في البطن</div>
                <div class="sym-chip" data-score="2" onclick="toggleSym(this)"><i class='bx bx-run'></i>ألم في الظهر والعمود</div>
                <div class="sym-chip" data-score="2" onclick="toggleSym(this)"><i class='bx bx-smile-beam'></i>تشنج في الفك أو الوجه</div>
                <!-- Normal symptoms (score 1 each) -->
                <div class="sym-chip" data-score="1" onclick="toggleSym(this)"><i class='bx bx-cloud'></i>سعال</div>
                <div class="sym-chip" data-score="1" onclick="toggleSym(this)"><i class='bx bx-cloud-rain'></i>زكام ورشح</div>
                <div class="sym-chip" data-score="1" onclick="toggleSym(this)"><i class='bx bx-face'></i>صداع خفيف</div>
                <div class="sym-chip" data-score="1" onclick="toggleSym(this)"><i class='bx bx-tired'></i>إرهاق وتعب عام</div>
                <div class="sym-chip" data-score="1" onclick="toggleSym(this)"><i class='bx bx-droplet-half'></i>جفاف وعطش</div>
                <div class="sym-chip" data-score="1" onclick="toggleSym(this)"><i class='bx bx-cookie'></i>غثيان خفيف</div>
              </div>

              <label class="p-form-label">تفاصيل إضافية (اختياري)</label>
              <textarea class="p-form-control" id="p2Notes" rows="3" placeholder="اشرح تفاصيل أخرى بكلماتك..."></textarea>
            </div>

            <!-- PANE 3: History & Severity -->
            <div class="wizard-pane" id="pane3">
              <div class="pane-heading">
                <div class="pane-icon" style="background: linear-gradient(135deg,#f59e0b,#b45309);"><i class='bx bx-pulse'></i></div>
                <div>
                  <h3>درجة الألم والتاريخ المرضي</h3>
                  <p>تساعدنا هذه التفاصيل في تقييم مدى استعجال الحالة بدقة</p>
                </div>
              </div>

              <!-- Pain Scale -->
              <div class="p-card p-4 mb-4 text-center">
                <label class="p-form-label mb-3">حدد مستوى الألم (1 الى 10)</label>
                <div class="fs-1 fw-bold text-primary mb-1" id="painNum">5</div>
                <div class="text-muted fw-bold mb-3" id="painDesc">متوسط</div>
                <input type="range" class="form-range" min="1" max="10" value="5" id="painRange" oninput="updatePain(this.value)">
                <div class="d-flex justify-content-between text-muted small mt-2 px-1 fw-bold">
                  <span>1 — خفيف</span>
                  <span>5 — متوسط</span>
                  <span>10 — ألم لا يُحتمل</span>
                </div>
              </div>

              <label class="p-form-label mb-2">منذ متى بدأت الأعراض؟ <span class="text-danger">*</span></label>
              <div class="duration-grid mb-4">
                <div class="dur-card" data-dur="أقل من 24 ساعة" data-dscore="3" onclick="pickDur(this)">
                  <div class="dur-icon"><i class='bx bx-time-five' style='font-size:1.8rem;'></i></div>
                  <div class="dur-label">أقل من 24 ساعة</div>
                  <div class="dur-sub">حادة ومفاجئة</div>
                </div>
                <div class="dur-card" data-dur="1–3 أيام" data-dscore="2" onclick="pickDur(this)">
                  <div class="dur-icon"><i class='bx bx-calendar' style='font-size:1.8rem;'></i></div>
                  <div class="dur-label">1–3 أيام</div>
                  <div class="dur-sub">حديثة نسبياً</div>
                </div>
                <div class="dur-card" data-dur="أكثر من أسبوع" data-dscore="1" onclick="pickDur(this)">
                  <div class="dur-icon"><i class='bx bx-calendar-week' style='font-size:1.8rem;'></i></div>
                  <div class="dur-label">أسبوع فأكثر</div>
                  <div class="dur-sub">مزمنة نوعاً ما</div>
                </div>
                <div class="dur-card" data-dur="أكثر من شهر" data-dscore="0" onclick="pickDur(this)">
                  <div class="dur-icon"><i class='bx bx-calendar-check' style='font-size:1.8rem;'></i></div>
                  <div class="dur-label">شهر فأكثر</div>
                  <div class="dur-sub">حالة مستمرة</div>
                </div>
              </div>

              <label class="p-form-label mb-2">هل لديك أي من هذه الحالات؟ (اختياري)</label>
              <div class="condition-grid">
                <div class="cond-check" data-score="1" onclick="toggleCond(this)">
                  <span class="cond-icon"><i class='bx bx-heart' style='font-size:1.4rem;color:#ef4444;'></i></span>
                  <div class="cond-box"><i class='bx bx-check' style="font-size:.8rem;"></i></div>
                  <span class="cond-label">أمراض قلب</span>
                </div>
                <div class="cond-check" data-score="1" onclick="toggleCond(this)">
                  <span class="cond-icon"><i class='bx bx-droplet' style='font-size:1.4rem;color:#ef4444;'></i></span>
                  <div class="cond-box"><i class='bx bx-check' style="font-size:.8rem;"></i></div>
                  <span class="cond-label">سكري</span>
                </div>
                <div class="cond-check" data-score="1" onclick="toggleCond(this)">
                  <span class="cond-icon"><i class='bx bx-pulse' style='font-size:1.4rem;color:#f59e0b;'></i></span>
                  <div class="cond-box"><i class='bx bx-check' style="font-size:.8rem;"></i></div>
                  <span class="cond-label">ضغط الدم</span>
                </div>
                <div class="cond-check" data-score="1" onclick="toggleCond(this)">
                  <span class="cond-icon"><i class='bx bx-brain' style='font-size:1.4rem;color:#8b5cf6;'></i></span>
                  <div class="cond-box"><i class='bx bx-check' style="font-size:.8rem;"></i></div>
                  <span class="cond-label">أمراض عصبية</span>
                </div>
                <div class="cond-check" data-score="1" onclick="toggleCond(this)">
                  <span class="cond-icon"><i class='bx bx-wind' style='font-size:1.4rem;color:#06b6d4;'></i></span>
                  <div class="cond-box"><i class='bx bx-check' style="font-size:.8rem;"></i></div>
                  <span class="cond-label">الربو / تنفسية</span>
                </div>
                <div class="cond-check" data-score="1" onclick="toggleCond(this)">
                  <span class="cond-icon"><i class='bx bx-filter-alt' style='font-size:1.4rem;color:#f97316;'></i></span>
                  <div class="cond-box"><i class='bx bx-check' style="font-size:.8rem;"></i></div>
                  <span class="cond-label">أمراض كلى</span>
                </div>
                <div class="cond-check" data-score="1" onclick="toggleCond(this)">
                  <span class="cond-icon"><i class='bx bx-body' style='font-size:1.4rem;color:#db2777;'></i></span>
                  <div class="cond-box"><i class='bx bx-check' style="font-size:.8rem;"></i></div>
                  <span class="cond-label">حمل ورضاعة</span>
                </div>
              </div>
            </div>

            <!-- PANE 4: Appointment (Smart or Regular) -->
            <div class="wizard-pane" id="pane4">
              <!-- Sub-pane: AI Smart Result -->
              <div id="pane4-smart">
                <div class="pane-heading">
                  <div class="pane-icon" style="background: linear-gradient(135deg,#10b981,#059669);"><i class='bx bx-brain'></i></div>
                  <div>
                    <h3>نتيجة التقييم الذكي</h3>
                    <p>قام النظام باختيار التخصص والطبيب بناءً على حالتك</p>
                  </div>
                </div>

                <!-- Loaders -->
                <div id="aiLoadingState" class="text-center p-5" style="display:none;">
                  <i class='bx bx-brain bx-spin fs-1 text-primary mb-3'></i>
                  <h5 class="fw-bold">جاري مراجعة وتحليل مدخلاتك...</h5>
                  <p class="text-muted">نستخدم تقنيات AI لتحديد الأولويات والتوجيه الصحيح</p>
                </div>
                <div id="aiErrorState" class="text-center p-5" style="display:none;">
                  <i class='bx bx-error-circle fs-1 text-danger mb-3'></i>
                  <h5 id="aiErrorMsg" class="fw-bold text-danger">حدث خطأ أثناء التقييم</h5>
                  <button class="p-btn p-btn-outline mt-3" onclick="runTriage()">حاول مرة أخرى</button>
                </div>

                <!-- Result Content -->
                <div id="aiResultCard" style="display:none;">
                  <div id="aiPriorityBanner" class="p-card p-3 mb-3 d-flex align-items-center gap-3" style="background:#f0f4ff; border:none; color:#fff;">
                    <div class="fs-1"><i class='bx bx-shield-plus'></i></div>
                    <div>
                      <div class="small fw-bold opacity-75">أولوية الحالة الطبية</div>
                      <div id="aiPriorityLabel" class="fs-4 fw-bold"></div>
                      <div id="aiWaitTime" class="small mt-1 opacity-75"></div>
                    </div>
                    <div class="ms-auto text-end">
                      <div class="small fw-bold opacity-75">الثقة</div>
                      <div id="aiConfidence" class="fs-5 fw-bold"></div>
                    </div>
                  </div>

                  <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                      <div class="p-card bg-light p-3 border-0">
                        <div class="small fw-bold text-muted mb-1"><i class='bx bx-clinic text-primary'></i> التخصص الموصى به</div>
                        <div id="aiSpecialty" class="fw-bold">...</div>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="p-card bg-light p-3 border-0">
                        <div class="small fw-bold text-muted mb-1"><i class='bx bx-user-pin text-primary'></i> الطبيب المتاح</div>
                        <div id="aiDoctor" class="fw-bold">...</div>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="p-card bg-light p-3 border-0">
                        <div class="small fw-bold text-muted mb-1"><i class='bx bx-calendar text-primary'></i> التاريخ الأقرب</div>
                        <div id="aiDate" class="fw-bold text-success">...</div>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="p-card bg-light p-3 border-0">
                        <div class="small fw-bold text-muted mb-1"><i class='bx bx-time text-primary'></i> الوقت</div>
                        <div id="aiTime" class="fw-bold text-success">...</div>
                      </div>
                    </div>
                  </div>

                  <div class="p-card p-3 mb-4" style="background:#fffbeb; border-color:#fef08a;">
                    <div class="small fw-bold text-warning-emphasis mb-1"><i class='bx bx-bulb'></i> تقرير المساعد الذكي</div>
                    <div id="aiReasoning" class="text-warning-emphasis small fw-semibold">...</div>
                  </div>

                  <label class="p-form-label">آلية الزيارة</label>
                  <select class="p-form-select" id="p4VisitType">
                    <option value="حضوري">عيادة وحضور فعلي</option>
                    <option value="عن بُعد">لقاء افتراضي (عن بُعد)</option>
                  </select>

                  <input type="hidden" id="aiDoctorId">
                  <input type="hidden" id="aiDateVal">
                  <input type="hidden" id="aiTimeVal">
                  <input type="hidden" id="aiPriorityVal">
                  <input type="hidden" id="aiReasoningVal">
                  <input type="hidden" id="aiSummaryVal">
                  <input type="hidden" id="aiConfidenceVal">
                  <input type="hidden" id="aiSpecialtyVal">
                </div>
              </div>

              <!-- Sub-pane: Regular Manual Selection -->
              <div id="pane4-regular" style="display:none;">
                <div class="pane-heading">
                  <div class="pane-icon" style="background: linear-gradient(135deg,#0ea5e9,#0284c7);"><i class='bx bx-calendar'></i></div>
                  <div>
                    <h3>اختيار الطبيب والموعد</h3>
                    <p>ابحث عن التخصص لتظهر لك قائمة الأطباء المتاحين وجداولهم</p>
                  </div>
                </div>

                <label class="p-form-label mb-2">التخصص <span class="text-danger">*</span></label>
                <select class="p-form-select mb-3" id="r4Spec" onchange="loadDoctors(this.value)">
                  <option value="">-- اختر --</option>
                  <option>طب عام</option>
                  <option>طب طوارئ</option>
                  <option>طب باطني</option>
                  <option>أطفال</option>
                  <option>عظام</option>
                  <option>أسنان</option>
                </select>

                <label class="p-form-label mb-2">الأطباء المتاحين <span class="text-danger">*</span></label>
                <div class="row g-2 mb-3" id="r4DocGrid">
                  <div class="col-12 text-muted small">اختر تخصّصاً لعرض قائمة الأطباء</div>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="p-form-label">يوم الموعد <span class="text-danger">*</span></label>
                    <div class="p-icon-input">
                       <i class='bx bx-calendar'></i>
                       <input type="date" class="p-form-control" id="r4Date" onchange="loadRegularSlots()">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <label class="p-form-label">نوع الزيارة</label>
                    <select class="p-form-select" id="r4VisitType">
                      <option value="حضوري">زيارة للعيادة</option>
                      <option value="عن بُعد">استشارة عن بُعد</option>
                    </select>
                  </div>
                </div>

                <label class="p-form-label mb-2">الأوقات <span class="text-danger">*</span></label>
                <div class="slot-grid mb-2" id="r4SlotsEl">
                </div>
                <div class="text-muted small"><i class='bx bxs-circle text-primary'></i> الخيارات الباهتة تعني غير متاح</div>

                <input type="hidden" id="r4DoctorId">
                <input type="hidden" id="r4TimeVal">
              </div>

            </div>
            
            <!-- Wizard Footer Buttons -->
            <div class="wizard-footer pb-0 bg-transparent border-0 mt-3 px-0">
              <button class="p-btn p-btn-ghost" id="prevBtn" onclick="navigate(-1)">
                 السابق
              </button>
              <span class="step-info" id="stepCounter">1 / 4</span>
              <button class="p-btn p-btn-primary" id="nextBtn" onclick="navigate(1)">
                التالي <i class='bx bx-chevron-left ms-1'></i>
              </button>
            </div>

          </div>
        </div>
      </div>
      
      <!-- ════ SUMMARY COLUMN ════ -->
      <div class="col-lg-4">
        <div class="booking-summary sticky-top" style="top:calc(var(--p-navbar-h) + 1.5rem);">
          <div class="sum-header"><i class='bx bx-receipt fs-5'></i> ملخص بياناتك</div>
          <div class="sum-body">
            <div class="sum-row">
              <span class="sum-key"><i class='bx bx-user'></i> المريض</span>
              <span class="sum-val" id="sName">—</span>
            </div>
            <div class="sum-row">
              <span class="sum-key"><i class='bx bx-phone'></i> الجوال</span>
              <span class="sum-val" id="sPhone">—</span>
            </div>
            <div class="sum-row">
              <span class="sum-key"><i class='bx bx-clinic'></i> التخصص</span>
              <span class="sum-val" id="sSpec">—</span>
            </div>
            <div class="sum-row">
              <span class="sum-key"><i class='bx bx-heart-circle'></i> أعراض بارزة</span>
              <span class="sum-val" id="sSym" style="font-size: .75rem;">—</span>
            </div>
            <div class="sum-row">
              <span class="sum-key"><i class='bx bx-pulse'></i> الوجع</span>
              <span class="sum-val" id="sPain">—</span>
            </div>
            <div class="sum-row">
              <span class="sum-key"><i class='bx bx-shield-plus'></i> الأولوية</span>
              <span class="sum-val" id="sPri">—</span>
            </div>
            <div class="sum-row">
              <span class="sum-key"><i class='bx bx-user-pin'></i> الطبيب</span>
              <span class="sum-val" id="sDoc">—</span>
            </div>
            <div class="sum-row">
              <span class="sum-key"><i class='bx bx-calendar'></i> تاريخ الموعد</span>
              <span class="sum-val" id="sDate" style="font-size:.75rem;">—</span>
            </div>
            <div class="sum-row">
              <span class="sum-key"><i class='bx bx-time'></i> الوقت</span>
              <span class="sum-val" id="sTime">—</span>
            </div>

            <div class="wait-box shadow-sm" id="sumBoxWait">
               <div class="wait-lbl">وقت الانتظار المتوقع للعيادة</div>
               <div class="wait-val mt-1" id="sWait">—</div>
            </div>
          </div>
          
          <div class="sum-progress">
            <div class="prog-label"><span>اكتمال المعلومات</span> <span id="progPct">0%</span></div>
            <div class="progress-bar-wrap">
              <div class="progress-bar-fill" id="progBar" style="width:0%;"></div>
            </div>
          </div>
        </div>
      </div>
      
    </div>
  </div>

  <!-- SUCCESS OVERLAY -->
  <div class="success-overlay" id="successOvl">
    <div class="success-box">
      <div class="success-icon"><i class='bx bx-check-double'></i></div>
      <h2 class="fw-bold mb-2">تم تثبيت الموعد بنجاح!</h2>
      <p class="text-muted mb-3">رقم المرجع لتأكيد الحضور</p>
      <div class="booking-ref" id="sbRef">—</div>
      <p class="text-muted small mb-4">تم إرسال رسالة نصية قصيرة SMS تحوي تفاصيل الحجز للموبايل المسجل.</p>
      
      <div class="d-flex justify-content-center gap-2">
        <button class="p-btn p-btn-primary" onclick="window.location.href='dashboard-new.php'">عودة للرئيسية</button>
        <button class="p-btn p-btn-outline" onclick="window.location.href='records-new.php'">سجلي</button>
      </div>
    </div>
  </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/hagz-ui.js"></script>
<script src="../assets/js/patient-booking.js?v=5.0"></script>
</body>
</html>
