<?php
require_once __DIR__ . '/../includes/session.php';
require_role(ROLE_PATIENT);
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'المريض', ENT_QUOTES, 'UTF-8');
$activeNav = 'presc';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الوصفات والفواتير — شفاء+</title>
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Bootstrap 5 RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <!-- Patient Styles (Zero Inline CSS Required) -->
    <link rel="stylesheet" href="../assets/css/patient.css?v=20.0">
</head>

<body>

    <?php include 'partials/patient-nav.php'; ?>

    <main class="p-main" id="mainContent">
        <div class="container-fluid py-4">

            <!-- Title & Hero -->
            <div class="dash-hero mb-4">
                <div class="dh-text">
                    <h1 class="h3 mb-2"><i class='bx bx-receipt ms-2'></i>الوصفات والفواتير</h1>
                    <p class="mb-0 text-white-50">إدارة الوصفات الطبية وسجل الدفع</p>
                </div>
            </div>

            <!-- Stats Mobile Compact Grid 2x2 -->
            <div class="row g-2 g-md-4 mb-4">
                <div class="col-6 col-md-6 col-xl-3">
                    <div class="dash-stat-card h-100 py-2 px-1 d-flex flex-column align-items-center justify-content-center text-center border-0 shadow-sm">
                        <div class="pane-icon-sm bg-primary text-white rounded-circle shadow-sm mb-1 fs-5"><i class='bx bx-file-blank'></i></div>
                        <div class="fs-4 fw-black text-dark lh-1" id="statRx">—</div>
                        <div class="text-muted-p fs-xs mt-1 fw-bold text-nowrap">وصفات طبية</div>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-xl-3">
                    <div class="dash-stat-card h-100 py-2 px-1 d-flex flex-column align-items-center justify-content-center text-center border-0 shadow-sm">
                        <div class="pane-icon-sm bg-success text-white rounded-circle shadow-sm mb-1 fs-5"><i class='bx bx-check-circle'></i></div>
                        <div class="fs-4 fw-black text-dark lh-1" id="statPaid">—</div>
                        <div class="text-muted-p fs-xs mt-1 fw-bold text-nowrap">فواتير سُددت</div>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-xl-3">
                    <div class="dash-stat-card h-100 py-2 px-1 d-flex flex-column align-items-center justify-content-center text-center border-0 shadow-sm">
                        <div class="pane-icon-sm bg-warning text-dark rounded-circle shadow-sm mb-1 fs-5"><i class='bx bx-time'></i></div>
                        <div class="fs-4 fw-black text-dark lh-1" id="statPending">—</div>
                        <div class="text-muted-p fs-xs mt-1 fw-bold text-nowrap">بانتظار السداد</div>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-xl-3">
                    <div class="dash-stat-card h-100 py-2 px-1 d-flex flex-column align-items-center justify-content-center text-center border-0 shadow-sm">
                        <div class="pane-icon-sm bg-info text-white rounded-circle shadow-sm mb-1 fs-5"><i class='bx bxs-wallet'></i></div>
                        <div class="fs-4 fw-black text-dark lh-1" id="statTotal">—</div>
                        <div class="text-muted-p fs-xs mt-1 fw-bold text-nowrap">إجمالي الدفع</div>
                    </div>
                </div>
            </div>

            <!-- Tab Toggles (Prescriptions vs Bills) -->
            <div class="d-flex w-100 gap-2 mb-4 p-2 bg-white rounded-4 shadow-sm align-items-stretch">
                <button class="flex-grow-1 p-filter-pill active ft border-0 py-3 fw-bold rounded-pill text-nowrap fs-sm" id="btn-prescriptions" onclick="switchTab('prescriptions')"><i class='bx bx-file-medical fs-5 align-middle'></i> الوصفات</button>
                <button class="flex-grow-1 p-filter-pill ft border-0 py-3 fw-bold rounded-pill text-nowrap fs-sm" id="btn-bills" onclick="switchTab('bills')"><i class='bx bx-credit-card fs-5 align-middle'></i> الفواتير</button>
            </div>

            <!-- Content Area -->
            <div>

                <!-- Prescriptions View -->
                <div id="view-prescriptions" class="tab-pane-view">
                    <div id="rxList" class="my-4">
                        <div class="text-center p-5">
                            <i class='bx bx-loader-alt bx-spin fs-1 text-primary'></i>
                        </div>
                    </div>
                </div>

                <!-- Bills View -->
                <div id="view-bills" class="tab-pane-view d-none">
                    <div id="billList" class="my-4">
                        <div class="text-center p-5">
                            <i class='bx bx-loader-alt bx-spin fs-1 text-primary'></i>
                        </div>
                    </div>
                </div>

            </div>

        </div> <!-- /container -->
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API = '../controllers/PatientController.php';

        document.addEventListener('DOMContentLoaded', () => {
            loadPrescriptions();
            loadBills();
        });

        // Safe switch tab using bootstrap classes strictly
        function switchTab(viewId) {
            document.querySelectorAll('.p-filter-pill').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-pane-view').forEach(p => p.classList.add('d-none'));

            document.getElementById('btn-' + viewId).classList.add('active');
            document.getElementById('view-' + viewId).classList.remove('d-none');
        }

        function loadPrescriptions() {
            fetch(`${API}?action=prescriptions`)
                .then(r => r.json())
                .then(data => {
                    const list = data.prescriptions || [];
                    const el = document.getElementById('rxList');
                    const stat = document.getElementById('statRx');
                    if (stat) stat.textContent = list.length;

                    if (!list.length) {
                        el.innerHTML = `
                        <div class="text-center p-5 bg-white rounded-4 shadow-sm opacity-75">
                            <i class='bx bx-file-blank text-muted opacity-50 display-1 mb-3'></i>
                            <h4 class="fw-bold text-muted">لا توجد وصفات طبية مسجلة</h4>
                            <p class="text-muted-p fs-sm">سيتم ظهور وصفاتك هنا بمجرد كتابتها من قبل الطبيب المعالج.</p>
                        </div>`;
                        return;
                    }

                    const groups = {};
                    list.forEach(rx => {
                        const key = rx.appointment_id ?? rx.rx_id;
                        if (!groups[key]) groups[key] = { rx, meds: [] };
                        groups[key].meds.push(rx);
                    });

                    let htmlBuffer = '';
                    Object.values(groups).forEach(g => {
                        const rx = g.rx;
                        const date = rx.appointment_date ? new Date(rx.appointment_date).toLocaleDateString('ar-SA', { day: 'numeric', month: 'long', year: 'numeric' }) : '—';
                        const rows = g.meds.map(m => `
                            <tr>
                                <td class="py-3 px-1 px-md-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 p-2"><i class='bx bx-capsule'></i></div>
                                        <div class="fw-bold text-dark text-wrap">${m.medication_name ?? '—'}</div>
                                    </div>
                                </td>
                                <td class="py-3 px-1 text-center text-md-start"><span class="badge bg-light text-dark border rounded-pill px-3 py-2 text-wrap d-inline-block">${m.dosage_strength || '—'}</span></td>
                                <td class="py-3 px-1 text-center text-md-start"><span class="badge bg-light text-dark border rounded-pill px-3 py-2 text-wrap d-inline-block">${m.frequency || '—'}</span></td>
                                <td class="py-3 px-1 text-center text-md-start"><span class="badge bg-light text-dark border rounded-pill px-3 py-2 text-wrap d-inline-block">${m.timing || '—'}</span></td>
                            </tr>`).join('');

                        htmlBuffer += `
                        <div class="record-card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-4 bg-white border-start border-primary border-4">
                            <!-- Header -->
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between pb-3 mb-4 border-bottom gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 flex-shrink-0">
                                        <i class='bx bx-file-medical fs-2'></i>
                                    </div>
                                    <div>
                                        <h4 class="fw-bold text-dark mb-1 fs-5">وصفة طبية — APT-${rx.appointment_id ?? rx.rx_id}</h4>
                                        <div class="text-muted-p fs-sm fw-bold">
                                            <i class='bx bx-user text-primary me-1'></i>${rx.doctor_name ? 'د. ' + rx.doctor_name : '—'} <span class="mx-1 opacity-50">|</span> ${rx.specialization ?? ''}
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 mt-sm-0 text-start text-sm-end">
                                    <span class="badge bg-light text-dark px-3 py-2 fs-sm fw-bold rounded-pill border d-inline-flex align-items-center gap-1">
                                        <i class='bx bx-calendar fs-6'></i> ${date}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Table inside pure responsive bootstrap structure -->
                            <div class="table-responsive mb-4 rounded-3 border">
                                <table class="table table-borderless table-striped align-middle mb-0">
                                    <thead class="bg-light text-muted-p fs-sm">
                                        <tr>
                                            <th class="py-3 px-1 px-md-3 text-nowrap">الدواء</th>
                                            <th class="py-3 px-1 text-center text-md-start text-nowrap">الجرعة</th>
                                            <th class="py-3 px-1 text-center text-md-start text-nowrap">التكرار</th>
                                            <th class="py-3 px-1 text-center text-md-start text-nowrap">التوقيت</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fs-sm fw-bold text-dark border-top">
                                        ${rows}
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-end">
                                <button class="btn btn-primary px-4 py-2 rounded-3 text-white fw-bold d-inline-flex align-items-center gap-2" onclick="window.print()">
                                    <i class='bx bx-printer'></i> طباعة الوصفة
                                </button>
                            </div>
                        </div>`;
                    });

                    el.innerHTML = htmlBuffer;
                })
                .catch(err => {
                    document.getElementById('rxList').innerHTML = `<div class="alert alert-danger fw-bold border-0 shadow-sm rounded-4"><i class="bx bx-error-circle"></i> تعذر التحميل: ${err.message}</div>`;
                });
        }

        function loadBills() {
            fetch(`${API}?action=bills`)
                .then(r => r.json())
                .then(data => {
                    const list = data.bills || [];
                    const el = document.getElementById('billList');

                    const paid = list.filter(b => b.payment_status === 'Paid').length;
                    const pending = list.filter(b => b.payment_status === 'Pending').length;
                    const total = list.reduce((s, b) => s + parseFloat(b.net_amount ?? b.total_amount ?? 0), 0);
                    
                    if (document.getElementById('statPaid')) document.getElementById('statPaid').textContent = paid;
                    if (document.getElementById('statPending')) document.getElementById('statPending').textContent = pending;
                    if (document.getElementById('statTotal')) document.getElementById('statTotal').textContent = total.toFixed(0);

                    if (!list.length) {
                        el.innerHTML = `
                        <div class="text-center p-5 bg-white rounded-4 shadow-sm opacity-75">
                            <i class='bx bx-receipt text-muted opacity-50 display-1 mb-3'></i>
                            <h4 class="fw-bold text-muted">لا توجد فواتير مسجلة</h4>
                            <p class="text-muted-p fs-sm">رصيدك نظيف. ليس لديك أي فواتير مستحقة أو سابقة في السجل.</p>
                        </div>`;
                        return;
                    }

                    let htmlBuffer = '';
                    list.forEach(b => {
                        const date = b.appointment_date ? new Date(b.appointment_date).toLocaleDateString('ar-SA', { day: 'numeric', month: 'long', year: 'numeric' }) : '—';
                        
                        let stBg = 'bg-danger bg-opacity-10', stText = 'text-danger', stBorder = 'border-danger border-opacity-25', stBorderSide = 'border-danger', stLbl = 'بانتظار السداد', icon = 'bx-time-five';
                        if (b.payment_status === 'Paid') {
                            stBg = 'bg-success bg-opacity-10'; stText = 'text-success'; stBorder = 'border-success border-opacity-25'; stBorderSide = 'border-success'; stLbl = 'مسددة'; icon = 'bx-check-double';
                        } else if (b.payment_status === 'Partial') {
                            stBg = 'bg-warning bg-opacity-10'; stText = 'text-warning'; stBorder = 'border-warning border-opacity-25'; stBorderSide = 'border-warning'; stLbl = 'سداد جزئي'; icon = 'bx-pie-chart-alt';
                        }

                        const items = (b.items || []).map(i => `
                            <div class="d-flex justify-content-between align-items-center mb-2 fs-sm fw-bold text-secondary">
                                <span>${i.item_name}</span>
                                <span>${parseFloat(i.amount).toFixed(0)} ر.س</span>
                            </div>`).join('');
                            
                        const discount = b.insurance_discount > 0 ? `
                            <div class="d-flex justify-content-between align-items-center mb-2 fs-sm fw-bold text-success">
                                <span>خصم التأمين <i class="bx bxs-check-shield mx-1"></i></span>
                                <span dir="ltr">-${parseFloat(b.insurance_discount).toFixed(0)} ر.س</span>
                            </div>` : '';

                        htmlBuffer += `
                        <div class="record-card border-0 shadow-sm p-4 mb-4 rounded-4 bg-white border-start ${stBorderSide} border-4">
                            <!-- Header -->
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between pb-3 mb-4 border-bottom gap-3">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <h4 class="fw-bold text-dark mb-0 fs-5"><i class='bx bx-receipt text-primary ms-1'></i> فاتورة #INV-${b.id}</h4>
                                        <span class="badge ${stBg} ${stText} px-2 py-1 fs-xs fw-bold rounded-pill border ${stBorder} d-inline-flex align-items-center gap-1">
                                            <i class='bx ${icon} fs-6'></i> ${stLbl}
                                        </span>
                                    </div>
                                    <div class="text-muted-p fs-sm fw-bold">
                                        <i class='bx bx-calendar text-primary me-1'></i> ${date} <span class="mx-1 opacity-50">|</span> ${b.doctor_name ? 'د. ' + b.doctor_name : ''}
                                    </div>
                                </div>
                                <div class="mt-2 mt-sm-0 text-start text-sm-end">
                                    <div class="fs-4 fw-black text-dark lh-1">${parseFloat(b.net_amount ?? b.total_amount).toFixed(0)} <span class="fs-6 opacity-75">ر.س</span></div>
                                </div>
                            </div>
                            
                            <!-- Items -->
                            <div class="bg-light rounded-4 p-4 mb-4">
                                ${items}
                                ${discount}
                                <div class="d-flex justify-content-between align-items-center pt-3 mt-3 border-top fw-black text-dark fs-5">
                                    <span>الصافي للدفع</span>
                                    <span>${parseFloat(b.net_amount ?? b.total_amount).toFixed(0)} ر.س</span>
                                </div>
                            </div>
                            <div class="d-flex gap-2 justify-content-end align-items-center">
                                <button class="btn btn-light border px-4 py-2 rounded-3 text-dark fw-bold d-inline-flex align-items-center gap-2" onclick="window.print()">
                                    <i class='bx bx-printer'></i> طباعة الفاتورة
                                </button>
                                ${b.payment_status !== 'Paid' ? `
                                <button class="btn btn-primary px-4 py-2 rounded-3 text-white fw-bold d-inline-flex align-items-center gap-2 shadow-sm" onclick="alert('جاري نقلك لبوابة الدفع الآمنة...')">
                                    <i class='bx bx-credit-card'></i> دفع الآن
                                </button>` : ''}
                            </div>
                        </div>`;
                    });

                    el.innerHTML = htmlBuffer;
                })
                .catch(err => {
                    document.getElementById('billList').innerHTML = `<div class="alert alert-danger fw-bold border-0 shadow-sm rounded-4"><i class="bx bx-error-circle"></i> تعذر التحميل: ${err.message}</div>`;
                });
        }
    </script>
</body>

</html>
