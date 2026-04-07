<?php
require_once __DIR__ . '/../includes/session.php';
require_role(ROLE_PATIENT);
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'المريض', ENT_QUOTES, 'UTF-8');
$activeNav = 'records';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>سجلي الطبي - شفاء+</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="../assets/css/patient.css?v=16.0">
</head>

<body>

    <?php include 'partials/patient-nav.php'; ?>

    <main class="p-main" id="mainContent">
        <div class="container-fluid py-4">

            <!-- Hero -->
            <div class="dash-hero mb-4">
                <div class="dh-text">
                    <h1><i class='bx bx-folder-open'></i> سجلي الطبي</h1>
                    <p>تاريخ زياراتك الطبية الكاملة ونتائج تشخيصك السابقة.</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="row g-2 g-md-4 mb-3">
                <div class="col-6 col-md-6 col-xl-3">
                    <div class="dash-stat-card h-100 py-2 px-1 d-flex flex-column align-items-center justify-content-center text-center border-0 shadow-sm">
                        <div class="pane-icon-sm bg-primary text-white rounded-circle shadow-sm mb-1 fs-5"><i class='bx bx-calendar'></i></div>
                        <div class="fs-4 fw-black text-dark lh-1" id="totalVisits">0</div>
                        <div class="text-muted-p fs-xs mt-1 fw-bold text-nowrap">إجمالي الزيارات</div>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-xl-3">
                    <div class="dash-stat-card h-100 py-2 px-1 d-flex flex-column align-items-center justify-content-center text-center border-0 shadow-sm">
                        <div class="pane-icon-sm bg-success text-white rounded-circle shadow-sm mb-1 fs-5"><i class='bx bx-check-circle'></i></div>
                        <div class="fs-4 fw-black text-dark lh-1" id="doneVisits">0</div>
                        <div class="text-muted-p fs-xs mt-1 fw-bold text-nowrap">مكتملة</div>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-xl-3">
                    <div class="dash-stat-card h-100 py-2 px-1 d-flex flex-column align-items-center justify-content-center text-center border-0 shadow-sm">
                        <div class="pane-icon-sm bg-info text-white rounded-circle shadow-sm mb-1 fs-5"><i class='bx bx-test-tube'></i></div>
                        <div class="fs-4 fw-black text-dark lh-1" id="labCount">0</div>
                        <div class="text-muted-p fs-xs mt-1 fw-bold text-nowrap">فحوصات</div>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-xl-3">
                    <div class="dash-stat-card h-100 py-2 px-1 d-flex flex-column align-items-center justify-content-center text-center border-0 shadow-sm">
                        <div class="pane-icon-sm bg-warning text-dark rounded-circle shadow-sm mb-1 fs-5"><i class='bx bx-capsule'></i></div>
                        <div class="fs-4 fw-black text-dark lh-1" id="medCount">0</div>
                        <div class="text-muted-p fs-xs mt-1 fw-bold text-nowrap">أدوية</div>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="p-card border-0 shadow-sm p-4 mb-4 rounded-4 bg-white">
                <div class="row gy-3 align-items-center">
                    <div class="col-lg-6 col-12">
                        <div class="p-search-wrapper position-relative">
                            <i class='bx bx-search position-absolute top-50 translate-middle-y text-muted fs-5 ms-3 start-0'></i>
                            <input type="text" id="searchQ" class="form-control rounded-pill bg-light border-0 py-2 pe-5"
                                placeholder="ابحث بالتاريخ، الطبيب، التخصص..." oninput="renderRecords()">
                        </div>
                    </div>
                    <div class="col-lg-6 col-12">
                        <!-- Filters as equal width buttons in one row using classes -->
                        <div class="d-flex w-100 gap-1 gap-sm-2 justify-content-between text-center align-items-stretch">
                            <button class="flex-grow-1 p-filter-pill active ft border-0 py-2 fw-bold rounded-pill text-nowrap fs-sm px-1 px-sm-3" onclick="setFilter('all',this)">الكل</button>
                            <button class="flex-grow-1 p-filter-pill ft border-0 py-2 fw-bold rounded-pill text-nowrap fs-sm px-1 px-sm-3" onclick="setFilter('done',this)">مكتملة</button>
                            <button class="flex-grow-1 p-filter-pill ft border-0 py-2 fw-bold rounded-pill text-nowrap fs-sm px-1 px-sm-3" onclick="setFilter('labs',this)">بفحوصات</button>
                            <button class="flex-grow-1 p-filter-pill ft border-0 py-2 fw-bold rounded-pill text-nowrap fs-sm px-1 px-sm-3" onclick="setFilter('meds',this)">بأدوية</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Records List -->
            <div id="recordsList" class="d-flex flex-column gap-3">
                <div class="text-center p-5 text-muted-p">
                    <i class='bx bx-loader-alt bx-spin fs-2 mb-2'></i>
                    <p>جاري تحميل السجلات الطبية...</p>
                </div>
            </div>

        </div>
    </main>

    <div id="hagzUiContainer"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/hagz-ui.js"></script>
    <script>
        const API = '../controllers/PatientController.php';
        let RECORDS = [];
        let currentFilter = 'all';

        document.addEventListener('DOMContentLoaded', () => {
            loadRecords();
        });

        function loadRecords() {
            document.getElementById('recordsList').innerHTML =
                `<div class="text-center p-5 text-muted-p"><i class='bx bx-loader-alt bx-spin fs-1 mb-2'></i><p>جاري تحميل السجلات...</p></div>`;

            fetch(`${API}?action=medical_records`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        if (data.redirect) { window.location.href = data.redirect; return; }
                        throw new Error(data.message);
                    }
                    RECORDS = (data.records || []).map(r => ({
                        id: '#APT-' + (r.appointment_id ?? r.id ?? '—'),
                        date: r.appointment_date ?? '',
                        spec: r.specialization ?? 'عناية عامة',
                        doctor: r.doctor_name ?? 'طبيب عام',
                        rawStatus: r.status,
                        status: {
                            Completed: 'مكتمل', Referred: 'محوّل', Transferred: 'محوّل',
                            Confirmed: 'مؤكد', Cancelled: 'ملغي', Pending: 'قيد الانتظار'
                        }[r.status] ?? (r.status ?? 'مكتمل'),
                        symptoms: Array.isArray(r.symptoms) ? r.symptoms.map(s => s.symptom_name ?? s) : [],
                        labs: Array.isArray(r.labs) ? r.labs.map(l => l.test_name ?? l) : [],
                        meds: Array.isArray(r.medications) ? r.medications.map(m => {
                            const name = m.medication_name ?? m;
                            const parts = [m.dosage_strength, m.frequency, m.timing].filter(Boolean);
                            return parts.length ? `${name} (${parts.join(' — ')})` : name;
                        }) : [],
                        notes: r.doctor_notes ?? ''
                    }));
                    renderRecords();
                })
                .catch(err => {
                    document.getElementById('recordsList').innerHTML =
                        `<div class="text-center p-5 text-danger bg-white rounded border"><i class='bx bx-error-circle fs-1 mb-2'></i><p>تعذر تحميل السجلات: ${err.message}</p></div>`;
                });
        }

        function setFilter(f, el) {
            currentFilter = f;
            document.querySelectorAll('.ft').forEach(b => b.classList.remove('active'));
            el.classList.add('active');
            renderRecords();
        }

        function renderRecords() {
            const q = document.getElementById('searchQ').value.trim().toLowerCase();
            let list = RECORDS.slice();

            if (currentFilter === 'done') list = list.filter(r => r.status === 'مكتمل' || r.rawStatus === 'Completed');
            if (currentFilter === 'labs') list = list.filter(r => r.labs.length > 0);
            if (currentFilter === 'meds') list = list.filter(r => r.meds.length > 0);
            if (q) list = list.filter(r =>
                r.spec.includes(q) || r.doctor.toLowerCase().includes(q) || r.date.includes(q) || r.id.toLowerCase().includes(q)
            );

            document.getElementById('totalVisits').textContent = RECORDS.length;
            document.getElementById('doneVisits').textContent = RECORDS.filter(r => r.status === 'مكتمل' || r.rawStatus === 'Completed').length;
            document.getElementById('labCount').textContent = RECORDS.reduce((s, r) => s + r.labs.length, 0);
            document.getElementById('medCount').textContent = RECORDS.reduce((s, r) => s + r.meds.length, 0);

            const el = document.getElementById('recordsList');
            if (!list.length) {
                el.innerHTML = `
                <div class="text-center bg-white p-5 border rounded">
                    <div class="mb-3 text-muted-p fs-1"><i class='bx bx-folder'></i></div>
                    <h4 class="fw-bold mb-2">لا يوجد سجلات مطابقة</h4>
                    <p class="text-muted-p mb-0">لم نعثر على أي نتائج تتطابق مع بحثك أو الفلتر المحدد.</p>
                </div>`;
                return;
            }

            el.innerHTML = list.map(r => {
                const d = new Date(r.date);
                const day = isNaN(d) ? '—' : d.getDate();
                const mon = isNaN(d) ? '' : d.toLocaleDateString('ar-SA', { month: 'short' });

                let bClass = 'p-badge-muted';
                let bIcon = 'bx-time-five';
                if (r.status === 'مكتمل' || r.rawStatus === 'Completed') {
                    bClass = 'p-badge-success'; bIcon = 'bx-check-double';
                } else if (r.status === 'محوّل' || r.rawStatus === 'Referred' || r.rawStatus === 'Transferred') {
                    bClass = 'p-badge-info'; bIcon = 'bx-transfer-alt';
                } else if (r.status === 'ملغي' || r.rawStatus === 'Cancelled') {
                    bClass = 'p-badge-danger'; bIcon = 'bx-x-circle';
                }

                const labHtml = r.labs.length ? r.labs.map(l => `<span class="badge bg-info bg-opacity-10 text-info px-2 py-1 rounded-pill fw-bold border border-info border-opacity-25 fs-xs d-inline-flex align-items-center gap-1"><i class='bx bx-test-tube'></i> ${l}</span>`).join('') : '<span class="text-muted fs-sm">لا يوجد</span>';
                const medHtml = r.meds.length ? r.meds.map(m => `<span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill fw-bold border border-warning border-opacity-25 fs-xs d-inline-flex align-items-center gap-1"><i class='bx bx-capsule'></i> ${m}</span>`).join('') : '<span class="text-muted fs-sm">لا يوجد</span>';
                const symHtml = r.symptoms.length ? r.symptoms.map(s => `<span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill fw-bold border border-danger border-opacity-25 fs-xs d-inline-flex align-items-center gap-1"><i class='bx bx-error-circle'></i> ${s}</span>`).join('') : '<span class="text-muted fs-sm">لا يوجد</span>';

                return `
                <div class="record-card border-0 shadow-sm p-4 mb-4 rounded-4 bg-white border-start border-primary border-4">
                    <!-- Record Header -->
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between pb-3 mb-4 border-bottom gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-primary bg-opacity-10 text-primary text-center rounded-3 p-2 flex-shrink-0 px-3">
                                <div class="fs-4 fw-black lh-1">${day}</div>
                                <div class="fs-sm fw-bold">${mon}</div>
                            </div>
                            <div>
                                <h4 class="fw-bold text-dark mb-1 fs-5">د. ${r.doctor}</h4>
                                <div class="text-muted-p fs-sm fw-bold">
                                    <i class='bx bx-clinic text-primary me-1'></i>${r.spec} <span class="mx-1 opacity-50">|</span> <span dir="ltr">${r.id}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 mt-sm-0 text-start text-sm-end">
                            <span class="p-badge ${bClass} px-3 py-2 fs-sm fw-bold rounded-pill border d-inline-flex align-items-center gap-1">
                                <i class='bx ${bIcon} fs-6'></i> ${r.status}
                            </span>
                        </div>
                    </div>

                    <!-- Record Body Content -->
                    <div class="row gy-3">
                        <div class="col-12 col-md-4">
                            <div class="text-muted-p fw-bold fs-sm mb-2">الأعراض:</div>
                            <div class="d-flex flex-wrap gap-1">${symHtml}</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="text-muted-p fw-bold fs-sm mb-2">الفحوصات المطلوبة:</div>
                            <div class="d-flex flex-wrap gap-1">${labHtml}</div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="text-muted-p fw-bold fs-sm mb-2">الأدوية الموصوفة:</div>
                            <div class="d-flex flex-wrap gap-1">${medHtml}</div>
                        </div>
                    </div>

                    <!-- Record Footer Notes -->
                    ${r.notes ? `
                    <div class="mt-4 p-3 rounded-3 bg-light text-dark fs-sm fw-bold border-start border-primary border-4">
                        <i class='bx bxs-quote-alt-right opacity-50 ms-1 text-primary'></i> ${r.notes}
                    </div>` : ''}
                </div>`;
            }).join('');
        }
    </script>
</body>

</html>