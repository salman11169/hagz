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
  <title>لوحة تحكم الطبيب — شفاء+</title>
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/shared-dashboard.css">
  <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
  <link rel="stylesheet" href="../assets/css/doctor-dashboard.css?v=9">
</head>

<body>

  <!-- ═══ NAVBAR ═══ -->
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-brand">
        <button class="icon-btn mobile-toggle" id="mobileToggle" aria-label="Toggle Sidebar">
          <i class='bx bx-menu'></i>
        </button>
        <div class="brand-icon"><i class='bx bx-plus-medical'></i></div>
        <div class="brand-text">شفاء<span>+</span></div>
      </div>
      <div class="nav-actions">
        <button class="icon-btn notif-btn" id="notifToggle" title="الإشعارات">
          <i class='bx bx-bell'></i>
          <span class="badge" id="notifBadge" style="display:none">0</span>
        </button>
        <div class="user-menu" id="userMenuWrap">
          <div class="user-avatar" id="userMenuTrigger" style="cursor:pointer;">
            <img id="navAvatar"
              src="https://ui-avatars.com/api/?name=<?= urlencode($userName) ?>&background=0d9488&color=fff&font-family=Cairo"
              alt="Doctor"
              style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.35);">
          </div>
          <div class="user-info" id="userMenuTrigger2" style="cursor:pointer;">
            <span class="user-greeting">مرحباً دكتور،</span>
            <span class="user-name" id="navName"><?= $userName ?></span>
          </div>
          <i class='bx bx-chevron-down dropdown-icon' id="userMenuChevron"></i>
          <div class="nav-dropdown" id="navDropdown">
            <a href="/index.php" class="nav-dd-item"><i class='bx bx-home-alt'></i> الموقع الرئيسي</a>
            <div class="nav-dd-divider"></div>
            <a href="../logout.php" class="nav-dd-item nav-dd-logout"
              onclick="event.preventDefault(); window.hagzConfirmLogout ? window.hagzConfirmLogout('../logout.php') : window.location.href='../logout.php'">
              <i class='bx bx-log-out'></i> تسجيل الخروج
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <!-- ═══ SIDEBAR ═══ -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-menu">
      <a href="Doctor_dashboard.php" class="menu-item active"><i class='bx bxs-dashboard'></i><span>الرئيسية</span></a>
      <a href="My_appointments.php" class="menu-item"><i class='bx bx-calendar-check'></i><span>مواعيدي</span></a>
      <a href="My_patients.php" class="menu-item"><i class='bx bx-group'></i><span>المرضى</span></a>
      <a href="Doctor_reports.php" class="menu-item"><i class='bx bx-file-blank'></i><span>التقارير</span></a>
      <a href="Doctor_referrals.php" class="menu-item"><i class='bx bx-transfer-alt'></i><span>التحويلات</span></a>
      <a href="Doctor_profile.php" class="menu-item"><i class='bx bx-user-circle'></i><span>ملفي الشخصي</span></a>
    </div>
    <div class="sidebar-bottom">
      <a href="../logout.php" class="menu-item logout" onclick="event.preventDefault(); if(window.hagzConfirmLogout) window.hagzConfirmLogout('../logout.php'); else window.location.href='../logout.php';">
        <i class='bx bx-log-out'></i><span>تسجيل الخروج</span>
      </a>
    </div>
  </aside>

  <!-- ═══ MAIN CONTENT ═══ -->
  <main class="main-content">

    <div class="emergency-banner" id="emergencyBanner"
      style="position:relative;top:auto;z-index:10;border-radius:16px;margin-bottom:1.5rem;display:none">
      <div class="emergency-content">
        <div class="emergency-text">
          <div class="emergency-icon"><i class='bx bxs-error'></i></div>
          <span id="emergencyMsg">0 حالة حرجة تحتاج تدخل فوري!</span>
        </div>
        <button class="btn-view-emergency" onclick="scrollToEmergencies()">
          <i class='bx bx-show'></i> عرض الحالات
        </button>
      </div>
    </div>

    <div class="doc-header">
      <div class="doc-header-inner">
        <div class="doc-header-title">
          <h1><i class='bx bxs-stethoscope' style="vertical-align:middle;margin-left:.4rem"></i>لوحة تحكم الطبيب</h1>
          <p id="headerDate"></p>
        </div>
        <div class="doc-header-actions">
          <button class="btn-doc btn-doc-light" id="refreshBtn" onclick="refreshData()">
            <i class='bx bx-refresh'></i> تحديث
          </button>
          <a href="Doctor_profile.php" class="btn-doc btn-doc-white">
            <i class='bx bx-calendar'></i> جدولي
          </a>
        </div>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card emergency">
        <div class="stat-icon si-emergency"><i class='bx bxs-error-circle'></i></div>
        <div class="stat-value" id="statCritical">—</div>
        <div class="stat-label">حالات حرجة</div>
      </div>
      <div class="stat-card urgent">
        <div class="stat-icon si-urgent"><i class='bx bx-time-five'></i></div>
        <div class="stat-value" id="statMedium">—</div>
        <div class="stat-label">حالات متوسطة</div>
      </div>
      <div class="stat-card stable">
        <div class="stat-icon si-stable"><i class='bx bx-check-shield'></i></div>
        <div class="stat-value" id="statRoutine">—</div>
        <div class="stat-label">حالات مستقرة</div>
      </div>
      <div class="stat-card today">
        <div class="stat-icon si-today"><i class='bx bx-calendar-check'></i></div>
        <div class="stat-value" id="statTotal">—</div>
        <div class="stat-label">إجمالي اليوم</div>
      </div>
    </div>

    <div class="doc-cols">
      <div class="doc-card" id="queueSection">
        <div class="card-head">
          <div class="card-title"><i class='bx bx-list-ul'></i> قائمة مرضى اليوم</div>
          <div class="filters-bar">
            <button class="flt-btn active" data-filter="all" onclick="filterQueue('all',this)">الكل</button>
            <button class="flt-btn" data-filter="Critical" onclick="filterQueue('Critical',this)"><i class='bx bxs-error-circle'></i> حرجة</button>
            <button class="flt-btn" data-filter="Medium" onclick="filterQueue('Medium',this)"><i class='bx bx-time-five'></i> متوسطة</button>
            <button class="flt-btn" data-filter="Routine" onclick="filterQueue('Routine',this)"><i class='bx bx-check-circle'></i> مستقرة</button>
          </div>
        </div>
        <div class="patients-list" id="patientsQueue">
          <div class="loading-state"><i class='bx bx-loader-alt bx-spin'></i></div>
        </div>
      </div>

      <div class="right-col">
        <div class="doc-card">
          <div class="card-title"><i class='bx bx-time-five'></i> جدول الدوام</div>
          <div class="schedule-days" id="scheduleDays">
            <div class="loading-state"><i class='bx bx-loader-alt bx-spin'></i></div>
          </div>
        </div>
        <div class="doc-card">
          <div class="card-title"><i class='bx bx-zap'></i> إجراءات سريعة</div>
          <div class="qa-grid">
            <a href="Doctor_reports.php" class="qa-btn"><i class='bx bx-file-blank qa-icon' style="color:#0d9488"></i>التقارير</a>
            <a href="My_appointments.php" class="qa-btn"><i class='bx bx-calendar-plus qa-icon' style="color:#2563eb"></i>مواعيدي</a>
            <a href="My_patients.php" class="qa-btn"><i class='bx bx-group qa-icon' style="color:#10b981"></i>المرضى</a>
            <a href="Doctor_referrals.php" class="qa-btn"><i class='bx bx-transfer-alt qa-icon' style="color:#f59e0b"></i>التحويلات</a>
          </div>
        </div>
        <div class="doc-card">
          <div class="card-title"><i class='bx bx-calendar-event'></i> المواعيد القادمة</div>
          <div id="upcomingList">
            <div class="loading-state"><i class='bx bx-loader-alt bx-spin'></i></div>
          </div>
        </div>
      </div>
    </div>

  </main>

  <!-- ═══ Transfer Modal ═══ -->
  <div class="modal-overlay" id="transferModal">
    <div class="modal-box">
      <div class="modal-title"><i class='bx bx-transfer-alt'></i> تحويل الحالة إلى استشاري</div>
      <input type="hidden" id="transferApptId">
      <div class="modal-field">
        <label>الطبيب الاستشاري</label>
        <select id="transferDoctorSel"><option value="">— اختر الطبيب —</option></select>
      </div>
      <div class="modal-field">
        <label>سبب التحويل</label>
        <textarea id="transferReason" rows="2" placeholder="سبب التحويل..."></textarea>
      </div>
      <div class="modal-field">
        <label>الملخص السريري (اختياري)</label>
        <textarea id="transferSummary" rows="3" placeholder="وصف الحالة للاستشاري..."></textarea>
      </div>
      <div class="modal-actions">
        <button class="modal-cancel" onclick="closeTransferModal()">إلغاء</button>
        <button class="modal-send" onclick="submitTransfer()"><i class='bx bx-send'></i> إرسال التحويل</button>
      </div>
    </div>
  </div>

  <script>
    const API = '../controllers/DoctorController.php';
    const DAY_NAMES = ['الأحد','الاثنين','الثلاثاء','الأربعاء','الخميس','الجمعة','السبت'];
    let allQueue = [], currentFilter = 'all';

    async function apiFetch(url, opts={}) { const r=await fetch(url,opts); return r.json(); }

    async function loadDashboard() {
      const data = await apiFetch(`${API}?action=dashboard`).catch(()=>null);
      if (!data?.success) return;
      const s = data.stats||{};
      animateCount('statCritical', s.critical_today||0);
      animateCount('statMedium',   s.medium_today||0);
      animateCount('statRoutine',  s.routine_today||0);
      animateCount('statTotal',    s.today_total||0);
      renderUpcoming(data.upcoming||[]);
      const critCount = parseInt(s.critical_today)||0;
      const banner = document.getElementById('emergencyBanner');
      if (critCount>0) { document.getElementById('emergencyMsg').textContent=`${critCount} حالة حرجة تحتاج تدخل فوري!`; banner.style.display=''; }
    }

    function renderUpcoming(list) {
      const el=document.getElementById('upcomingList');
      if (!list.length) { el.innerHTML=emptyState('bx-calendar-x','لا توجد مواعيد قادمة'); return; }
      el.innerHTML=list.map(a=>`
    <div class="notif-item" style="cursor:pointer" onclick="viewAppt(${a.id})">
      <div class="notif-dot ${priorityCls(a.priority)}"></div>
      <div>
        <div class="notif-text">${esc(a.patient_name)} — ${fmtDate(a.appointment_date)}</div>
        <div class="notif-time">${fmtTime(a.appointment_time)} ${typeBadge(a.booking_type)}</div>
      </div>
    </div>`).join('');
    }

    async function loadQueue() {
      document.getElementById('patientsQueue').innerHTML=`<div class="loading-state"><i class='bx bx-loader-alt bx-spin'></i></div>`;
      const data=await apiFetch(`${API}?action=today_queue`).catch(()=>null);
      if (!data?.success) { document.getElementById('patientsQueue').innerHTML=`<div class="error-state">تعذر تحميل قائمة المرضى</div>`; return; }
      allQueue=data.queue||[]; updateBadge(); renderQueue(); checkEmergencies();
    }

    function renderQueue() {
      const list=document.getElementById('patientsQueue');
      const filtered=currentFilter==='all'?allQueue:allQueue.filter(p=>p.priority===currentFilter);
      if (!filtered.length) { list.innerHTML=emptyState('bx-folder-open','لا توجد حالات في هذه الفئة'); return; }
      list.innerHTML=filtered.map(p=>{
        const priKey=p.priority==='Critical'?'emergency':p.priority==='Medium'?'urgent':'normal';
        const priLabel=p.priority==='Critical'?'حرجة':p.priority==='Medium'?'متوسطة':'مستقرة';
        const aiRow=p.booking_type==='smart'&&p.ai_summary?`<div class="ai-summary-chip"><i class='bx bxs-brain'></i><span>${esc(p.ai_summary)}</span></div>`:'';
        return `<div class="patient-card ${priKey}">
        <div class="pc-row">
          <div class="pc-info">
            <div class="pc-avatar">${(p.patient_name||'م')[0]}</div>
            <div>
              <div class="pc-name">${esc(p.patient_name||'غير محدد')}</div>
              <div class="pc-id">موعد #${p.id} · ${p.age?p.age+' سنة':''} · ${genderAr(p.gender)} &nbsp;${typeBadge(p.booking_type)}</div>
            </div>
          </div>
          <span class="pri-pill pp-${priKey}">${priorityIcon(priKey)} ${priLabel}</span>
        </div>
        ${aiRow}
        <div class="pc-meta">
          <div class="meta-item"><i class='bx bx-time'></i> ${fmtTime(p.appointment_time)}</div>
          <div class="meta-item"><i class='bx bx-calendar'></i> ${fmtDate(p.appointment_date)}</div>
          <div class="meta-item"><i class='bx bx-run'></i> ${p.visit_type==='Telehealth'?'عن بُعد':'حضوري'}</div>
          <div class="meta-item"><i class='bx bx-body'></i> ${genderAr(p.gender)}</div>
        </div>
        <div class="pc-actions">
          <button class="pc-btn pc-btn-view" onclick="viewAppt(${p.id})"><i class='bx bx-show'></i> عرض</button>
          <button class="pc-btn pc-btn-transfer" onclick="openTransferModal(${p.id})"><i class='bx bx-transfer'></i> تحويل</button>
          <button class="pc-btn pc-btn-done" onclick="completeVisit(${p.id},this)"><i class='bx bx-check-circle'></i> إكمال</button>
        </div>
      </div>`;
      }).join('');
    }

    function filterQueue(val,btn) { currentFilter=val; document.querySelectorAll('.flt-btn').forEach(b=>b.classList.remove('active')); if(btn)btn.classList.add('active'); renderQueue(); }

    async function loadSchedule() {
      const data=await apiFetch(`${API}?action=get_schedule`).catch(()=>null);
      const wrap=document.getElementById('scheduleDays');
      if (!data?.success) { wrap.innerHTML=emptyState('bx-calendar-x','تعذر تحميل الجدول'); return; }
      const map=data.schedule||{};
      wrap.innerHTML=Array.from({length:7},(_,i)=>{
        const shifts=map[i]||[], isOn=shifts.some(s=>parseInt(s.is_available));
        const hours=isOn?shifts.filter(s=>parseInt(s.is_available)).map(s=>`${fmtTime(s.start_time)}–${fmtTime(s.end_time)}`).join(' / '):'إجازة';
        return `<div class="day-chip ${isOn?'on':''}"><div class="day-name">${DAY_NAMES[i]}</div><div class="day-hours">${hours}</div></div>`;
      }).join('');
    }

    function viewAppt(id) { window.location.href='Appointment_details.php?id='+id; }

    async function completeVisit(id,btn) {
      if (!confirm('تحديد هذا الموعد كمكتمل؟')) return;
      btn.disabled=true; btn.innerHTML=`<i class='bx bx-loader-alt bx-spin'></i>`;
      const fd=new FormData(); fd.append('appointment_id',id); fd.append('status','Completed');
      const data=await apiFetch(`${API}?action=update_status`,{method:'POST',body:fd}).catch(()=>null);
      if (data?.success) loadQueue();
      else { alert(data?.message||'حدث خطأ'); btn.disabled=false; btn.innerHTML=`<i class='bx bx-check-circle'></i> إكمال`; }
    }

    async function loadDoctorsList() {
      const data=await apiFetch('../controllers/DoctorController.php?action=doctors').catch(()=>null);
      const sel=document.getElementById('transferDoctorSel');
      if (!data?.success||!data.doctors?.length) return;
      sel.innerHTML='<option value="">— اختر الطبيب —</option>'+data.doctors.map(d=>`<option value="${d.id}">${esc(d.name)} — ${esc(d.specialization)}</option>`).join('');
    }

    function openTransferModal(apptId) {
      document.getElementById('transferApptId').value=apptId;
      document.getElementById('transferReason').value='';
      document.getElementById('transferSummary').value='';
      document.getElementById('transferModal').classList.add('open');
    }
    function closeTransferModal() { document.getElementById('transferModal').classList.remove('open'); }

    async function submitTransfer() {
      const apptId=document.getElementById('transferApptId').value;
      const toDoc=document.getElementById('transferDoctorSel').value;
      const reason=document.getElementById('transferReason').value.trim();
      const summary=document.getElementById('transferSummary').value.trim();
      if (!toDoc||!reason) { alert('الرجاء اختيار الطبيب وكتابة سبب التحويل.'); return; }
      const data=await apiFetch(`${API}?action=create_referral`,{
        method:'POST', headers:{'Content-Type':'application/json'},
        body:JSON.stringify({appointment_id:+apptId,to_doctor_id:+toDoc,reason,clinical_summary:summary})
      }).catch(()=>null);
      if (data?.success) {
        const fd=new FormData(); fd.append('appointment_id',apptId); fd.append('status','Transferred');
        await apiFetch(`${API}?action=update_status`,{method:'POST',body:fd});
        closeTransferModal(); loadQueue();
      } else alert(data?.message||'فشل إرسال التحويل');
    }

    function checkEmergencies() {
      const count=allQueue.filter(p=>p.priority==='Critical'&&!['Completed','Cancelled'].includes(p.status)).length;
      const banner=document.getElementById('emergencyBanner');
      if (count>0) { document.getElementById('emergencyMsg').textContent=`${count} حالة حرجة تحتاج تدخل فوري!`; banner.style.display=''; }
      else banner.style.display='none';
    }
    function updateBadge() {
      const count=allQueue.filter(p=>p.priority==='Critical'&&!['Completed','Cancelled'].includes(p.status)).length;
      const badge=document.getElementById('notifBadge'); badge.textContent=count; badge.style.display=count>0?'':'none';
    }
    function scrollToEmergencies() { filterQueue('Critical',document.querySelector('.flt-btn[data-filter="Critical"]')); document.getElementById('queueSection').scrollIntoView({behavior:'smooth'}); }
    function refreshData() {
      const btn=document.getElementById('refreshBtn');
      btn.innerHTML=`<i class='bx bx-loader-alt bx-spin'></i> جاري التحديث...`; btn.disabled=true;
      Promise.all([loadDashboard(),loadQueue(),loadSchedule()]).finally(()=>{ setTimeout(()=>{ btn.innerHTML=`<i class='bx bx-refresh'></i> تحديث`; btn.disabled=false; },800); });
    }
    function animateCount(id,target) {
      const el=document.getElementById(id); if(!el)return;
      let cur=0; const step=Math.max(1,Math.ceil(target/20));
      const t=setInterval(()=>{ cur=Math.min(cur+step,target); el.textContent=cur; if(cur>=target)clearInterval(t); },40);
    }
    function typeBadge(type) {
      if(type==='smart') return `<span class="type-badge type-smart"><i class='bx bx-bot'></i> ذكي</span>`;
      if(type==='regular') return `<span class="type-badge type-regular"><i class='bx bx-calendar'></i> عادي</span>`;
      return '';
    }
    function priorityCls(p) { return p==='Critical'?'emergency':p==='Medium'?'warning':'info'; }
    function priorityIcon(key) {
      if(key==='emergency') return `<i class='bx bxs-error-circle'></i>`;
      if(key==='urgent') return `<i class='bx bx-time-five'></i>`;
      return `<i class='bx bx-check-circle'></i>`;
    }
    function genderAr(g) { return g==='Male'?'ذكر':g==='Female'?'أنثى':'—'; }
    function fmtTime(t) { if(!t)return'—'; const[h,m]=t.split(':').map(Number); const ampm=h>=12?'م':'ص'; return `${h%12||12}${m?':'+String(m).padStart(2,'0'):''} ${ampm}`; }
    function fmtDate(d) { if(!d)return'—'; return new Date(d).toLocaleDateString('ar-SA',{day:'numeric',month:'short'}); }
    function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
    function emptyState(icon,msg) { return `<div class="empty-state"><i class='bx ${icon}'></i><p>${msg}</p></div>`; }

    function initSidebar() {
      const toggle=document.getElementById('mobileToggle'), sidebar=document.getElementById('sidebar');
      toggle.addEventListener('click',()=>{ sidebar.classList.toggle('active'); document.body.style.overflow=sidebar.classList.contains('active')?'hidden':''; });
      document.addEventListener('click',e=>{ if(window.innerWidth<=768&&!sidebar.contains(e.target)&&!toggle.contains(e.target)&&sidebar.classList.contains('active')){ sidebar.classList.remove('active'); document.body.style.overflow=''; } });
    }

    document.addEventListener('DOMContentLoaded',()=>{
      document.getElementById('headerDate').textContent=new Date().toLocaleDateString('ar-SA',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
      initSidebar();
      Promise.all([loadDashboard(),loadQueue(),loadSchedule(),loadDoctorsList()]);
    });
  </script>
  <script src="../assets/js/hagz-ui.js?v=2"></script>
  <script>
    (function(){
      const wrap=document.getElementById('userMenuWrap'), dd=document.getElementById('navDropdown');
      const t1=document.getElementById('userMenuTrigger'), t2=document.getElementById('userMenuTrigger2');
      const chevron=document.getElementById('userMenuChevron');
      function toggle(e){e.stopPropagation();wrap.classList.toggle('open');dd.classList.toggle('open');}
      function close(){wrap.classList.remove('open');dd.classList.remove('open');}
      [t1,t2,chevron].forEach(el=>el?.addEventListener('click',toggle));
      document.addEventListener('click',e=>{if(!wrap.contains(e.target))close();});
      fetch('../controllers/DoctorController.php?action=profile').then(r=>r.json()).then(data=>{
        const p=data?.profile;
        if(p?.avatar_path){document.getElementById('navAvatar').src=p.avatar_path;}
        else if(p?.first_name){const name=encodeURIComponent((p.first_name+' '+p.last_name).trim()); document.getElementById('navAvatar').src=`https://ui-avatars.com/api/?name=${name}&background=0d9488&color=fff&font-family=Cairo`;}
      }).catch(()=>{});
    })();
  </script>
  <script src="../assets/js/notif-badge.js" defer></script>
</body>

</html>
