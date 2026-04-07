/* ══════════════════════════════════
   SHIFA+ PATIENT BOOKING JS
══════════════════════════════════ */
let cur = 1;
const TOTAL = 4;

let st = {
  name: '', phone: '', age: '', gender: '', blood: '', spec: '',
  symptoms: [], symScore: 0,
  pain: 5, duration: '', durScore: 0,
  conditions: [], condScore: 0,
  priority: '', priorityKey: '',
  doctor: '', painTouched: false,
  date: '', time: '', visitType: 'حضوري'
};

const WAIT = { stable: '~45 دقيقة', urgent: '~15 دقيقة', emergency: 'فوري (دخول سريع)' };
const PAIN_DESC = {
  1: 'لا يكاد يُذكر', 2: 'خفيف جداً', 3: 'خفيف', 4: 'خفيف إلى متوسط',
  5: 'متوسط', 6: 'متوسط إلى شديد', 7: 'شديد', 8: 'شديد جداً',
  9: 'لا يُحتمل تقريباً', 10: 'ألم كامل لا يُحتمل'
};

const BOOKING_API = '../controllers/BookingController.php';
var bookingMode = 'smart';
let aiTriageDone = false;
let doctorsFromDB = [];
let r4SelectedDocId = '', r4SelectedTime = '';

// Today restrictions
document.addEventListener('DOMContentLoaded', () => {
    const _today = new Date().toISOString().split('T')[0];
    const _p4d = document.getElementById('p4Date');
    if (_p4d) _p4d.min = _today;
    const _r4d = document.getElementById('r4Date');
    if (_r4d) _r4d.min = _today;
    
    // Auto fill user data
    fetch('../controllers/PatientController.php?action=profile')
      .then(r => r.json())
      .then(data => {
        if (!data.success || !data.profile) return;
        const p = data.profile;
        const name = ((p.first_name ?? '') + ' ' + (p.last_name ?? '')).trim();
        const fill = (id, val, lock) => {
          const el = document.getElementById(id);
          if (!el || val == null || val === '') return;
          el.value = val;
          if (lock) { el.readOnly = true; el.style.background = '#f0f9ff'; }
        };
        fill('p1Name', name, true);
        fill('p1Phone', p.phone, true);
        if (p.date_of_birth) {
          const age = Math.floor((Date.now() - new Date(p.date_of_birth)) / 31557600000);
          if (age > 0) fill('p1Age', age, false);
        }
        if (p.gender) {
          const gEl = document.getElementById('p1Gender');
          if (gEl) {
            const g = String(p.gender).toLowerCase();
            gEl.value = (g === 'male' || g === 'ذكر') ? 'ذكر' : 'أنثى';
          }
        }
        if (p.blood_type) fill('p1Blood', p.blood_type, false);
        syncSummary();
      }).catch(() => { });

    let savedMode = sessionStorage.getItem('hagzBookingMode');
    let savedStep = sessionStorage.getItem('hagzCurrentStep');
    
    if (savedMode) {
        selectMode(savedMode, true);
        if (savedStep) {
            setStep(parseInt(savedStep));
        } else {
            setStep(1);
        }
    } else {
        calcPriority();
        syncSummary();
    }
});

// Sync input events
function setupLiveListeners() {
    ['p1Name', 'p1Phone', 'p1Age', 'p1Gender'].forEach(id => {
      let el = document.getElementById(id);
      if(el) el.addEventListener('input', syncSummary);
    });
}

function updatePain(v) {
  st.pain = parseInt(v);
  st.painTouched = true;
  let numEl = document.getElementById('painNum');
  let descEl = document.getElementById('painDesc');
  if(numEl) numEl.textContent = v;
  if(descEl) descEl.textContent = PAIN_DESC[v];

  let slider = document.getElementById('painRange');
  if(slider && slider.value !== v) slider.value = v;

  calcPriority(); syncSummary();
}

function selectPainBtn(btn) {
    let v = btn.dataset.val;
    let slider = document.getElementById('painRange');
    if(slider) slider.value = v;
    updatePain(v);
}

function pickSpec(el) {
  document.querySelectorAll('.spec-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  st.spec = el.dataset.spec;
  syncSummary();
  loadDoctors(st.spec);
}

function toggleSym(el) {
  el.classList.toggle('selected');
  const chips = [...document.querySelectorAll('.sym-chip.selected')];
  st.symptoms = chips.map(c => c.textContent.trim());
  st.symScore = chips.reduce((s, c) => s + parseInt(c.dataset.score || 0), 0);
  calcPriority(); syncSummary();
}

function pickDur(el) {
  document.querySelectorAll('.dur-card').forEach(d => d.classList.remove('selected'));
  el.classList.add('selected');
  st.duration = el.dataset.dur;
  st.durScore = parseInt(el.dataset.dscore || 0);
  calcPriority(); syncSummary();
}

function toggleCond(el) {
  el.classList.toggle('selected');
  if(el.querySelector('.cond-box i')) {
      el.querySelector('.cond-box i').style.display = el.classList.contains('selected') ? 'block' : 'none';
  }
  const sel = [...document.querySelectorAll('.cond-check.selected')];
  st.conditions = sel.map(c => c.querySelector('.cond-label').textContent.trim());
  st.condScore = sel.reduce((s, c) => s + parseInt(c.dataset.score || 0), 0);
  calcPriority(); syncSummary();
}

function calcPriority() {
  const total = st.symScore + st.durScore + st.condScore + (st.pain >= 8 ? 3 : st.pain >= 5 ? 1 : 0);
  let key, label;
  if (total >= 6 || st.pain >= 9 || st.symScore >= 3) {
    key = 'emergency'; label = 'حرجة';
  } else if (total >= 3 || st.pain >= 6) {
    key = 'urgent'; label = 'عاجلة';
  } else {
    key = 'normal'; label = 'مستقرة';
  }
  st.priority = label; st.priorityKey = key;
}

function syncSummary() {
  const get = id => document.getElementById(id);
  const val = id => { const el = get(id); return el ? el.value : ''; };
  const txt = (id, v) => { const el = get(id); if (el) el.textContent = v; };
  
  st.name = val('p1Name');
  st.phone = val('p1Phone');
  
  txt('sName', st.name || '—');
  txt('sPhone', st.phone || '—');
  txt('sSpec', st.spec || '—');
  txt('sSym', st.symptoms.length ? st.symptoms.join('، ') : '—');
  txt('sPain', st.painTouched ? `${st.pain}/10` : '—');
  txt('sDoc', st.doctor || '—');
  txt('sWait', st.priorityKey && bookingMode === 'smart' ? WAIT[st.priorityKey] : '—');
  
  const aiDateVal = val('aiDateVal');
  const r4DateVal = val('r4Date');
  const effectiveDate = aiDateVal || r4DateVal || '';
  txt('sDate', effectiveDate 
    ? new Date(effectiveDate).toLocaleDateString('ar-SA', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) 
    : '—');
    
  const rawTime = val('aiTimeVal') || val('r4TimeVal') || st.time || '';
  txt('sTime', rawTime ? toArabicTime(rawTime) : '—');
  
  const pEl = get('sPri');
  if (pEl) {
    pEl.innerHTML = (st.priorityKey && bookingMode === 'smart') 
      ? `<span class="pri-pill pri-1 pp-${st.priorityKey}">${st.priority}</span>` 
      : '—';
  }
  
  let done = 0;
  if (st.name && st.phone) done++;
  if (bookingMode==='regular' && st.spec) done++;
  if (bookingMode==='smart') done++; // Spec managed by AI
  if (st.symptoms.length) done++;
  if (st.painTouched) done++;
  if (st.duration) done++;
  if (effectiveDate && rawTime) done++;
  
  const pct = Math.round((done / 6) * 100);
  let pctEl = document.getElementById('progPct');
  let barEl = document.getElementById('progBar');
  if(pctEl) pctEl.textContent = pct + '%';
  if(barEl) barEl.style.width = pct + '%';
}

function goTo(n) { if (n <= cur || validate(cur)) setStep(n); }

function navigate(dir) {
  if (dir === -1 && cur === 1) { 
      sessionStorage.removeItem('hagzBookingMode');
      sessionStorage.removeItem('hagzCurrentStep');
      showModeSelector(); 
      return; 
  }
  if (dir === 1 && !validate(cur)) return;
  const next = cur + dir;
  if (next < 1 || next > TOTAL) {
    if (next > TOTAL) submitBooking();
    return;
  }
  setStep(next);
}

function showModeSelector() {
  document.getElementById('modeSelector').style.display = 'block';
  document.getElementById('wizardContainer').style.display = 'none';
}

function selectMode(mode, fromRestore = false) {
  bookingMode = mode;
  if (!fromRestore) {
      sessionStorage.setItem('hagzBookingMode', mode);
  }
  document.getElementById('modeSelector').style.display = 'none';
  document.getElementById('wizardContainer').style.display = 'block';
  setupLiveListeners();
  
  const specSection = document.getElementById('specSection');
  if (specSection) specSection.style.display = 'none';
  
  const priRow = document.getElementById('sPri')?.closest('.sum-row');
  const waitBox = document.getElementById('sumBoxWait');
  
  if (mode === 'regular') {
    if (!fromRestore) {
        st.symptoms = []; st.symScore = 0;
        st.duration = ''; st.durScore = 0;
        st.conditions = []; st.condScore = 0;
        st.pain = 5; st.painTouched = false;
        document.querySelectorAll('.sym-chip.selected, .dur-card.selected, .cond-check.selected').forEach(el => el.classList.remove('selected'));
    }
    
    if (priRow) priRow.style.display = 'none';
    if (waitBox) waitBox.style.display = 'none';
    
    aiTriageDone = true;
    let aiPane = document.getElementById('pane4-smart');
    let regPane = document.getElementById('pane4-regular');
    if(aiPane) aiPane.style.display = 'none';
    if(regPane) regPane.style.display = 'block';
  } else {
    if (priRow) priRow.style.display = '';
    if (waitBox) waitBox.style.display = '';
    let aiPane = document.getElementById('pane4-smart');
    let regPane = document.getElementById('pane4-regular');
    if(aiPane) aiPane.style.display = 'block';
    if(regPane) regPane.style.display = 'none';
  }
  
  if (!fromRestore) {
      setStep(1);
  }
}

function triggerEmergency() {
    selectMode('smart');
    if (confirm('تأكيد وضع الطوارئ؟ سيتم تخطي الأسئلة بناءً على الأولوية.')) {
        setStep(4);
        runTriage(true);
    }
}

function setStep(n) {
  sessionStorage.setItem('hagzCurrentStep', n);
  document.querySelectorAll('.wizard-pane').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.step-item').forEach(el => el.classList.remove('active'));
  
  cur = n;
  let pane = document.getElementById('pane' + cur);
  if(pane) pane.classList.add('active');
  let tab = document.getElementById('t' + cur);
  if(tab) tab.classList.add('active');
  
  for (let i = 1; i <= TOTAL; i++) {
    const tb = document.getElementById('t' + i);
    if(tb) {
        tb.classList.toggle('done', i < cur);
        tb.classList.toggle('active', i === cur);
    }
    const cn = document.getElementById('cn' + i);
    if(cn && i < TOTAL) {
        cn.classList.toggle('done', i < cur);
        cn.classList.toggle('active', i === cur);
    }
  }
  
  let nextBtn = document.getElementById('nextBtn');
  if(nextBtn) {
      nextBtn.innerHTML = cur === TOTAL ? '<i class="bx bx-calendar-check fs-5"></i> تأكيد الحجز' : 'التالي <i class="bx bx-chevron-left fs-5"></i>';
  }

  if (n === 4 && bookingMode === 'smart' && !aiTriageDone) runTriage();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validate(step) {
  if (step === 1) {
    if (!st.name || !st.phone) { showErr('يرجى تعبئة الاسم والجوال'); return false; }
  }
  if (step === 2 && !st.symptoms.length) { showErr('يرجى اختيار عرض واحد على الأقل'); return false; }
  if (step === 3 && !st.duration) { showErr('يرجى اختيار مدة التعب/الأعراض'); return false; }
  if (step === 4) {
    if (bookingMode === 'regular') {
      if (!document.getElementById('r4DoctorId').value) { showErr('يرجى اختيار طبيب'); return false; }
      if (!document.getElementById('r4Date').value) { showErr('يرجى اختيار تاريخ الموعد'); return false; }
      if (!document.getElementById('r4TimeVal').value) { showErr('يرجى اختيار وقت الموعد'); return false; }
    } else {
      if (!aiTriageDone) { showErr('يرجى الانتظار حتى يكتمل التحليل'); return false; }
    }
  }
  return true;
}

function showErr(msg) {
  if (window.HagzUI) {
      HagzUI.toast(msg, 'error');
  } else {
      alert(msg);
  }
}

// ── Regular mode APIs ──
function loadDoctors(spec) {
  r4SelectedDocId = ''; r4SelectedTime = '';
  const dId = document.getElementById('r4DoctorId');
  if(dId) dId.value = '';
  const rT = document.getElementById('r4TimeVal');
  if(rT) rT.value = '';
  
  const grid = document.getElementById('r4DocGrid');
  if(!grid) return;
  grid.innerHTML = '<div class="text-muted"><i class="bx bx-loader-alt bx-spin"></i> تحميل...</div>';
  fetch(BOOKING_API + '?action=doctors&spec=' + encodeURIComponent(spec))
    .then(r => r.json()).then(data => {
      const docs = data.doctors || [];
      if (!docs.length) { grid.innerHTML = '<div class="col-12 text-muted">لا يوجد أطباء متاحون</div>'; return; }
      grid.innerHTML = docs.map(d => `
      <div class="col-md-6">
        <div class="doctor-card" data-doc-id="${d.id}" onclick="pickRegularDoc(this)">
          <div class="doc-avatar"><i class='bx bx-user'></i></div>
          <div class="doc-info">
            <div class="doc-name">${d.name}</div>
            <div class="doc-spec"><i class="bx bx-briefcase"></i> ${d.experience_years} سنوات خبرة</div>
          </div>
        </div>
      </div>`).join('');
    }).catch(() => { grid.innerHTML = '<div class="text-danger">فشل التحميل</div>'; });
}

function pickRegularDoc(el) {
  document.querySelectorAll('#r4DocGrid .doctor-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  r4SelectedDocId = el.dataset.docId;
  document.getElementById('r4DoctorId').value = r4SelectedDocId;
  st.doctor = el.querySelector('.doc-name').innerText;
  loadRegularSlots();
}

function loadRegularSlots() {
  const date = document.getElementById('r4Date').value;
  if (!r4SelectedDocId || !date) return;
  r4SelectedTime = '';
  document.getElementById('r4TimeVal').value = '';
  const slotsEl = document.getElementById('r4SlotsEl');
  slotsEl.innerHTML = '<div class="text-muted"><i class="bx bx-loader-alt bx-spin"></i> تحميل...</div>';

  fetch(BOOKING_API + '?action=slots&doctor_id=' + r4SelectedDocId + '&date=' + date)
    .then(r => r.json()).then(data => {
      const slots = data.slots || [];
      if (!slots.length) {
        slotsEl.innerHTML = '<div class="col-12 text-muted">' + (data.message || 'لا توجد مواعيد متاحة') + '</div>'; return;
      }
      slotsEl.innerHTML = slots.map(s => {
        const arabicLabel = s.label.replace('AM', 'ص').replace('PM', 'م');
        return `<div class="slot-btn ${s.booked ? 'booked' : ''}" data-time="${s.time}" onclick="pickRegularTime(this)">
          ${arabicLabel}
        </div>`;
      }).join('');
    }).catch(() => { slotsEl.innerHTML = '<div class="text-danger">فشل التحميل</div>'; });
}

function pickRegularTime(el) {
  if (el.classList.contains('booked')) return;
  document.querySelectorAll('#r4SlotsEl .slot-btn').forEach(t => t.classList.remove('selected'));
  el.classList.add('selected');
  r4SelectedTime = el.dataset.time;
  document.getElementById('r4TimeVal').value = r4SelectedTime;
  syncSummary();
}

// ── Smart Triage API ──
function runTriage(emergency = false) {
  aiTriageDone = false;
  document.getElementById('aiLoadingState').style.display = 'block';
  document.getElementById('aiResultCard').style.display = 'none';
  document.getElementById('aiErrorState').style.display = 'none';
  const payload = {
    symptoms: emergency ? ['حالة طارئة'] : st.symptoms,
    pain_level: emergency ? 10 : (st.pain || 5),
    duration: emergency ? 'أقل من 24 ساعة' : (st.duration || ''),
    conditions: emergency ? [] : (st.conditions || []),
    notes: document.getElementById('p2Notes') ? document.getElementById('p2Notes').value : '',
    emergency,
  };
  fetch(BOOKING_API + '?action=triage', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  }).then(r => r.json()).then(data => {
    document.getElementById('aiLoadingState').style.display = 'none';
    if (data.success) showAIResult(data);
    else { 
        document.getElementById('aiErrorState').style.display = 'block'; 
        document.getElementById('aiErrorMsg').textContent = data.message || 'خطأ غير متوقع'; 
    }
  }).catch(() => {
    document.getElementById('aiLoadingState').style.display = 'none';
    document.getElementById('aiErrorState').style.display = 'block';
    document.getElementById('aiErrorMsg').textContent = 'فشل الاتصال بالخادم';
  });
}

const PRIORITY_STYLES = {
  Critical: { bg: 'linear-gradient(135deg, #ef4444, #dc2626)', label: 'حالة حرجة', key: 'emergency' },
  Medium: { bg: 'linear-gradient(135deg, #f59e0b, #d97706)', label: 'عاجلة', key: 'urgent' },
  Routine: { bg: 'linear-gradient(135deg, #10b981, #059669)', label: 'مستقرة', key: 'stable' },
};

function showAIResult(data) {
  const t = data.triage;
  const slot = data.slot || {};
  const sty = PRIORITY_STYLES[t.priority] || PRIORITY_STYLES.Routine;

  st.priority = sty.label;
  st.priorityKey = sty.key;
  if (t.specialty) st.spec = t.specialty;
  
  let pBanner = document.getElementById('aiPriorityBanner');
  if(pBanner) pBanner.style.background = sty.bg;
  document.getElementById('aiPriorityLabel').textContent = sty.label;
  
  const waitLabel = t.wait_time || (data.schedule && data.schedule.label ? data.schedule.label : '');
  document.getElementById('aiWaitTime').textContent = waitLabel;
  document.getElementById('aiConfidence').textContent = Math.round((t.confidence || 0) * 100) + '%';
  document.getElementById('aiSpecialty').textContent = t.specialty || 'طب عام';
  
  st.doctor = slot.doctor_name || 'سيتم التحديد';
  document.getElementById('aiDoctor').textContent = st.doctor;
  
  const dateLabel = slot.date
    ? new Date(slot.date + 'T00:00:00').toLocaleDateString('ar-SA', { weekday: 'long', day: 'numeric', month: 'long' })
    : (waitLabel || 'سيتم التواصل معك');
  document.getElementById('aiDate').textContent = dateLabel;
  
  const rawTime = slot.time || '';
  document.getElementById('aiTime').textContent = rawTime ? toArabicTime(rawTime) : (t.priority === 'Critical' ? 'إحضر فوراً' : '—');
  document.getElementById('aiReasoning').textContent = slot.reasoning || t.reasoning || '';

  document.getElementById('aiDoctorId').value = slot.doctor_id || '';
  document.getElementById('aiDateVal').value = slot.date || '';
  document.getElementById('aiTimeVal').value = rawTime;
  document.getElementById('aiPriorityVal').value = t.priority || 'Routine';
  document.getElementById('aiReasoningVal').value = t.reasoning || slot.reasoning || '';
  document.getElementById('aiSummaryVal').value = t.ai_summary || slot.ai_summary || '';
  document.getElementById('aiConfidenceVal').value = t.confidence || 0.5;
  document.getElementById('aiSpecialtyVal').value = t.specialty || '';
  
  document.getElementById('aiResultCard').style.display = 'block';
  aiTriageDone = true;
  syncSummary();
}

function toArabicTime(raw) {
  if (!raw) return '';
  const parts = raw.split(':').map(Number);
  const hh = parts[0], mm = parts[1] || 0;
  const isPM = hh >= 12;
  const h12 = hh % 12 || 12;
  const period = isPM ? 'م' : 'ص';
  return `${String(h12).padStart(2, '0')}:${String(mm).padStart(2, '0')} ${period}`;
}

// ── Submit ──
function submitBooking() {
  let doctor_id, date, time, visit_type, priority;
  if (bookingMode === 'regular') {
    doctor_id = parseInt(document.getElementById('r4DoctorId').value);
    date = document.getElementById('r4Date').value;
    time = document.getElementById('r4TimeVal').value;
    visit_type = document.getElementById('r4VisitType').value;
    priority = 'Routine';
  } else {
    if (!aiTriageDone) { showErr('يرجى الانتظار لحين تحميل النتيجة'); return; }
    doctor_id = parseInt(document.getElementById('aiDoctorId').value);
    date = document.getElementById('aiDateVal').value;
    time = document.getElementById('aiTimeVal').value;
    visit_type = document.getElementById('p4VisitType').value;
    priority = document.getElementById('aiPriorityVal').value;
  }
  
  const btn = document.getElementById('nextBtn');
  btn.classList.add('disabled');
  btn.innerHTML = 'جاري الحجز...';

  fetch(BOOKING_API + '?action=book', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      doctor_id, date, time, visit_type, priority,
      booking_mode: bookingMode,
      symptoms: st.symptoms || [],
      pain_level: st.pain || 5,
      duration: st.duration || '',
      conditions: st.conditions || [],
      notes: document.getElementById('p2Notes')?.value || '',
      ai_reasoning: bookingMode === 'smart' ? document.getElementById('aiReasoningVal').value : '',
      ai_summary: bookingMode === 'smart' ? document.getElementById('aiSummaryVal').value : '',
      ai_confidence: bookingMode === 'smart' ? parseFloat(document.getElementById('aiConfidenceVal').value) : 0,
      ai_specialty: bookingMode === 'smart' ? document.getElementById('aiSpecialtyVal').value : '',
    }),
  }).then(r => r.json()).then(data => {
    btn.classList.remove('disabled'); btn.innerHTML = 'تأكيد الحجز';
    if (data.success) {
      sessionStorage.removeItem('hagzBookingMode');
      sessionStorage.removeItem('hagzCurrentStep');
      document.getElementById('sbRef').textContent = data.ref;
      document.getElementById('successOvl').classList.add('show');
    } else {
      showErr(data.message || 'فشل الحجز');
    }
  }).catch(() => {
    btn.classList.remove('disabled'); btn.innerHTML = 'تأكيد الحجز';
    showErr('خطأ في الاتصال');
  });
}
