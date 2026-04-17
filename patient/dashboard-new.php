<?php
require_once __DIR__ . '/../includes/session.php';
require_role(ROLE_PATIENT);
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'المريض', ENT_QUOTES, 'UTF-8');
$activeNav = 'dashboard';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المريض - شفاء+</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="../assets/css/patient.css?v=15.0">
</head>
<body>

    <?php include 'partials/patient-nav.php'; ?>

    <main class="p-main" id="mainContent">
        <div class="container-fluid py-4">
            
            <!-- Hero -->
            <div class="dash-hero mb-4">
                <div class="dh-text">
                    <h1 id="welcomeGreeting">كيف تشعر اليوم يا <span id="welcomeName"><?= $userName ?></span>؟</h1>
                    <p>نأمل أن تكون بصحة جيدة. تعرف على مواعيدك القادمة واطمئن على صحتك.</p>
                </div>
                <div class="dh-action d-none d-md-block">
                    <a href="booking-new.php" class="hero-btn">
                        <i class='bx bx-calendar-plus'></i>
                        حجز موعد جديد
                    </a>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="row g-4 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="dash-stat-card">
                        <div class="sc-icon sc-upcoming"><i class='bx bx-calendar-event'></i></div>
                        <div class="sc-info">
                            <div class="sc-val" id="statUpcoming">0</div>
                            <div class="sc-label">مواعيد قادمة</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="dash-stat-card">
                        <div class="sc-icon sc-history"><i class='bx bx-history'></i></div>
                        <div class="sc-info">
                            <div class="sc-val" id="statPast">0</div>
                            <div class="sc-label">زيارة سابقة</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="dash-stat-card">
                        <div class="sc-icon sc-records"><i class='bx bx-file-blank'></i></div>
                        <div class="sc-info">
                            <div class="sc-val" id="statRecords">0</div>
                            <div class="sc-label">تقارير طبية</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="dash-stat-card">
                        <div class="sc-icon sc-presc"><i class='bx bx-capsule'></i></div>
                        <div class="sc-info">
                            <div class="sc-val" id="statPresc">0</div>
                            <div class="sc-label">وصفات فعالة</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="row g-4">
                
                <!-- Upcoming Appointments (col-lg-8) -->
                <div class="col-lg-8">
                    <div class="p-card h-100">
                        <div class="px-4 pt-4 mb-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <div class="pane-icon-sm"><i class='bx bx-calendar'></i></div>
                                <h2 class="mb-0 fs-5 fw-bold text-nowrap">مواعيدك القادمة</h2>
                            </div>
                            <a href="Patient_medical_records.php" class="text-decoration-none fw-bold fs-sm text-primary text-nowrap">عرض الكل <i class='bx bx-left-arrow-alt'></i></a>
                        </div>
                        <div class="p-card-body p-4 pt-0" id="appointmentsList">
                            <div class="text-center p-5 text-muted-p">
                                <i class='bx bx-loader-alt bx-spin fs-2 mb-2'></i>
                                <p>جاري تحميل المواعيد...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Side Widgets (col-lg-4) -->
                <div class="col-lg-4 d-flex flex-column gap-4">
                    
                    <!-- Health Summary -->
                    <div class="p-card">
                        <div class="px-4 pt-4 mb-2 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <div class="pane-icon-sm"><i class='bx bx-heart-circle'></i></div>
                                <h2 class="mb-0 fs-5 fw-bold text-nowrap">الملخص الصحي</h2>
                            </div>
                        </div>
                        <div class="p-card-body p-0">
                            <div class="hm-item">
                                <div class="hm-icon"><i class='bx bxs-droplet'></i></div>
                                <div class="hm-data">
                                    <div class="hm-label">فصيلة الدم</div>
                                    <div class="hm-val" id="healthBlood">—</div>
                                </div>
                            </div>
                            <div class="hm-item">
                                <div class="hm-icon"><i class='bx bx-body'></i></div>
                                <div class="hm-data">
                                    <div class="hm-label">الوزن / الطول</div>
                                    <div class="hm-val" id="healthWeightHeight">—</div>
                                </div>
                            </div>
                            <div class="hm-item">
                                <div class="hm-icon"><i class='bx bx-error-circle'></i></div>
                                <div class="hm-data">
                                    <div class="hm-label">أمراض مزمنة</div>
                                    <div class="hm-val" id="healthChronic">—</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Prescriptions -->
                    <div class="p-card">
                        <div class="px-4 pt-4 mb-2 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <div class="pane-icon-sm"><i class='bx bx-detail'></i></div>
                                <h2 class="mb-0 fs-5 fw-bold text-nowrap">أحدث الوصفات</h2>
                            </div>
                        </div>
                        <div class="p-card-body p-0" id="recentPrescList">
                            <div class="text-center py-4 text-muted-p">
                                <i class='bx bx-loader-alt bx-spin'></i> جاري التحميل...
                            </div>
                        </div>
                    </div>

                </div>

            </div> <!-- /row -->
            
        </div>
    </main>

    <!-- UI Overlay for modals/toasts -->
    <div id="hagzUiContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/hagz-ui.js"></script>
    <script>
        const API = '../controllers/PatientController.php';

        document.addEventListener('DOMContentLoaded', () => {
            loadDashboard();
            loadProfile();
        });

        // ===== Load dashboard data =====
        function loadDashboard() {
            fetch(`${API}?action=dashboard`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        if (data.redirect) { window.location.href = data.redirect; return; }
                        throw new Error(data.message);
                    }
                    document.getElementById('statUpcoming').textContent = data.stats?.upcoming_appointments ?? 0;
                    document.getElementById('statPast').textContent = data.stats?.past_appointments ?? 0;
                    document.getElementById('statRecords').textContent = data.stats?.medical_records ?? 0;
                    document.getElementById('statPresc').textContent = data.stats?.prescriptions ?? 0;
                    renderAppointments(data.upcoming_appointments ?? []);
                })
                .catch(err => {
                    document.getElementById('appointmentsList').innerHTML =
                        `<div class="text-center p-4 text-danger">تعذر تحميل البيانات: ${err.message}</div>`;
                });
        }

        // ===== Load profile for health widget + prescriptions =====
        function loadProfile() {
            fetch(`${API}?action=profile`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const p = data.profile;
                    const diseases = data.chronic_diseases || [];

                    document.getElementById('healthBlood').textContent =
                        p.blood_type ? p.blood_type : 'غير محدد';
                    document.getElementById('healthWeightHeight').textContent =
                        (p.weight ? p.weight + ' كجم' : '—') + ' / ' + (p.height ? p.height + ' سم' : '—');
                    document.getElementById('healthChronic').textContent =
                        diseases.length ? diseases.map(d => d.disease_name).join('، ') : 'لا يوجد';
                })
                .catch(() => { });

            fetch(`${API}?action=prescriptions`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const list = (data.prescriptions || []).slice(0, 3);
                    const el = document.getElementById('recentPrescList');
                    if (!list.length) {
                        el.innerHTML = '<div class="text-center py-4 text-muted-p fw-bold fs-sm">لا توجد وصفات قريبة</div>';
                        return;
                    }
                    el.innerHTML = list.map(rx => `
                        <div class="rx-item">
                            <div class="rx-icon"><i class='bx bxs-capsule'></i></div>
                            <div class="rx-info">
                                <h4>${rx.medication_name ?? '—'} ${rx.dosage_strength ?? ''}</h4>
                                <p>${rx.frequency ?? ''} ${rx.timing ? '· ' + rx.timing : ''}</p>
                            </div>
                        </div>`).join('');
                })
                .catch(() => { });
        }

        // ===== Render appointment cards =====
        function renderAppointments(list) {
            const el = document.getElementById('appointmentsList');
            if (!list.length) {
                el.innerHTML = `
                    <div class="text-center py-5">
                        <div class="mb-3 text-muted-p fs-1"><i class='bx bx-calendar-x'></i></div>
                        <h4 class="fw-bold mb-2">لا توجد مواعيد قادمة</h4>
                        <p class="text-muted-p mb-4">ليس لديك أي حجوزات مجدولة في الوقت الحالي.</p>
                        <a href="booking-new.php" class="p-btn p-btn-primary d-inline-flex px-4 py-2">
                            <i class='bx bx-plus'></i> احجز موعداً جديداً
                        </a>
                    </div>`;
                return;
            }

            el.innerHTML = `<div class="d-flex flex-column gap-3">` + list.map(a => {
                const date = new Date(a.appointment_date);
                const dayStr = date.toLocaleDateString('ar-SA', { weekday: 'long', day: 'numeric', month: 'short' });
                const rawTime = (a.appointment_time ?? '').slice(0, 5);
                const timeStr = (() => {
                    if (!rawTime) return '—';
                    const [hh, mm] = rawTime.split(':').map(Number);
                    const isPM = hh >= 12;
                    const h12 = hh % 12 || 12;
                    return `${String(h12).padStart(2, '0')}:${String(mm || 0).padStart(2, '0')} ${isPM ? 'م' : 'ص'}`;
                })();

                const priority = a.priority || 'Routine';
                const pColor = priority === 'Critical' ? 'danger' : (priority === 'Medium' ? 'warning' : 'success');
                const pLabel = { Critical: 'حرجة', Medium: 'عاجلة', Routine: 'مستقرة' }[priority] || '';

                const statusMap = {
                    Pending: { label: 'بانتظار التأكيد', cClass: 'p-badge-warning', icon: 'bx-time-five' },
                    Confirmed: { label: 'مؤكد', cClass: 'p-badge-success', icon: 'bx-check-circle' }
                };
                const st = statusMap[a.status] || statusMap.Pending;
                
                let bookingLabel;
                if (a.booking_type === 'smart') {
                    bookingLabel = `<span class="badge rounded-pill bg-light text-dark fw-bold border p-2"><i class='bx bx-brain text-primary'></i> حجز ذكي</span>`;
                } else if (a.booking_type === 'emergency') {
                    bookingLabel = `<span class="badge rounded-pill fw-bold p-2" style="background:#fff0f0;color:#dc2626;border:1.5px solid #fca5a5;"><i class='bx bxs-ambulance'></i> طوارئ</span>`;
                } else {
                    bookingLabel = `<span class="badge rounded-pill bg-light text-dark fw-bold border p-2"><i class='bx bx-user'></i> حجز عادي</span>`;
                }

                const initial = (a.doctor_first_name?.[0] ?? 'ط');
                const docName = `${a.doctor_first_name ?? ''} ${a.doctor_last_name ?? ''}`.trim();

                return `
                <div class="appt-card border mb-3">
                     <div class="p-3">
                         
                         <!-- Doctor Info & Time -->
                         <div class="d-flex flex-column flex-md-row align-items-md-center align-items-start gap-3 w-100">
                              <div class="doc-avatar lg-avatar text-white fw-bold mx-auto mx-md-0 shadow-sm">${initial}</div>
                              <div class="w-100 text-center text-md-start">
                                  <div class="fw-bold fs-5 text-dark mb-1">د. ${docName} <span class="p-badge p-badge-${pColor} ms-1 d-inline-flex align-items-center align-middle px-2 py-1 fs-sm">${pLabel}</span></div>
                                  <div class="fs-sm fw-bold text-muted-p mb-2">${a.specialization ?? 'عناية عامة'}</div>
                                  <div class="d-flex flex-wrap justify-content-center justify-content-md-start align-items-center gap-3 fs-sm fw-bold text-muted-p">
                                      <span><i class='bx bx-calendar text-primary'></i> ${dayStr}</span>
                                      <span><i class='bx bx-time text-primary'></i> ${timeStr}</span>
                                  </div>
                              </div>
                         </div>

                         <!-- Status & Actions -->
                         <div class="appt-card-footer mt-3 pt-3">
                              <div class="row align-items-center gy-3">
                                  <!-- Badges -->
                                  <div class="col-12 col-md-7 text-center text-md-start">
                                      <div class="d-flex flex-wrap justify-content-center justify-content-md-start align-items-center gap-2">
                                          <span class="p-badge ${st.cClass} px-3 py-1 fw-bold fs-sm">
                                              <i class='bx ${st.icon}'></i> ${st.label}
                                          </span>
                                          ${bookingLabel}
                                      </div>
                                  </div>
                                  
                                  <!-- Action Button -->
                                  <div class="col-12 col-md-5 text-center text-md-end">
                                      <button onclick="cancelAppt(${a.id})" class="btn btn-sm btn-outline-danger rounded-pill fw-bold text-nowrap px-4 py-1">
                                          <i class='bx bx-x'></i> إلغاء الموعد
                                      </button>
                                  </div>
                              </div>
                         </div>
                         
                     </div>
                </div>`;
            }).join('') + `</div>`;
        }

        // ===== Cancel appointment =====
        function cancelAppt(id) {
            HagzUI.confirm({
                title: 'إلغاء الموعد',
                message: 'هل أنت متأكد من إلغاء هذا الموعد؟',
                confirmText: 'نعم، إلغاء الموعد',
                cancelText: 'تراجع',
                type: 'danger',
                onConfirm: () => {
                    fetch(`${API}?action=cancel_appointment`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ appointment_id: id })
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) { 
                                HagzUI.toast('تم إلغاء الموعد بنجاح', 'success'); 
                                loadDashboard(); 
                                // Reduce stats locally
                                const st = document.getElementById('statUpcoming');
                                if (st && parseInt(st.textContent) > 0) st.textContent = parseInt(st.textContent)-1;
                            }
                            else HagzUI.toast('خطأ: ' + data.message, 'error');
                        });
                }
            });
        }
    </script>
</body>
</html>
