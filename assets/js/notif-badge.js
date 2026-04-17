/**
 * notif-badge.js — تحديث ديناميكي لعداد الإشعارات في هيدر جميع صفحات الدكتور
 * يُجلب الحالات الحرجة وينبه الطبيب للنداءات العاجلة (Summon/Emergency)
 */
(function () {
    const API = '../controllers/DoctorController.php';
    const badge = document.getElementById('notifBadge');
    const notifBtn = document.querySelector('.notif-btn');
    
    let isAlertActive = false; // Prevent overlapping alerts
    let dropdownOpen = false;

    // Build the Notification Dropdown UI globally (since header is shared)
    function buildNotifDropdown() {
        if (!notifBtn) return;
        
        const wrap = document.createElement('div');
        wrap.id = 'notifDropdownWrap';
        wrap.style.cssText = `
            position: absolute; top: calc(100% + 10px); left: 0; width: 340px;
            background: #fff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: 1px solid var(--doc-border, #e2e8f0); z-index: 1000;
            display: none; flex-direction: column; overflow: hidden;
            font-family: 'Cairo', sans-serif;
        `;

        const header = document.createElement('div');
        header.style.cssText = `
            padding: 1rem; border-bottom: 1px solid var(--doc-border, #e2e8f0);
            font-weight: 700; font-size: 1rem; color: #1e293b;
            display: flex; justify-content: space-between; align-items: center;
        `;
        header.innerHTML = `<span><i class='bx bx-bell'></i> الإشعارات والنداءات</span>
                            <button id="markAllReadBtn" style="font-size:0.8rem; background:none; border:none; color:var(--doc-primary, #0d9488); cursor:pointer; font-family:'Cairo', sans-serif;">تأكيد الكل</button>`;

        const list = document.createElement('div');
        list.id = 'notifDropdownList';
        list.style.cssText = `
            max-height: 350px; overflow-y: auto; display: flex; flex-direction: column;
        `;
        list.innerHTML = `<div style="padding:1.5rem;text-align:center;color:#64748b;font-size:0.9rem;">جاري التحميل...</div>`;

        wrap.appendChild(header);
        wrap.appendChild(list);
        
        // Ensure nav-actions has position relative; if not, wrap will go out of place
        if(notifBtn.parentElement && window.getComputedStyle(notifBtn.parentElement).position === 'static') {
            notifBtn.parentElement.style.position = 'relative';
        }
        notifBtn.parentElement.appendChild(wrap);

        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownOpen = !dropdownOpen;
            wrap.style.display = dropdownOpen ? 'flex' : 'none';
            if (dropdownOpen) fetchAllAlerts();
        });

        document.addEventListener('click', (e) => {
            if (dropdownOpen && !wrap.contains(e.target) && !notifBtn.contains(e.target)) {
                dropdownOpen = false;
                wrap.style.display = 'none';
            }
        });

        const markAllBtn = document.getElementById('markAllReadBtn');
        if(markAllBtn) {
            markAllBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                fetch(`${API}?action=mark_all_read`, { method: 'POST' }).then(() => {
                    fetchAllAlerts();
                    checkQueue();
                });
            });
        }
    }

    function fetchAllAlerts() {
        const list = document.getElementById('notifDropdownList');
        fetch(`${API}?action=get_all_alerts`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    list.innerHTML = `<div style="padding:1.5rem;text-align:center;color:#ef4444;font-size:0.9rem;">فشل التحميل</div>`;
                    return;
                }
                const alerts = data.alerts || [];
                if (alerts.length === 0) {
                    list.innerHTML = `<div style="padding:1.5rem;text-align:center;color:#64748b;font-size:0.9rem;"><i class='bx bx-check-shield' style='font-size:2rem;display:block;margin-bottom:0.5rem;color:#cbd5e1'></i> لا توجد إشعارات حالياً</div>`;
                    return;
                }
                list.innerHTML = alerts.map(a => {
                    const bg = parseInt(a.is_read) ? '#fff' : '#fef2f2';
                    const iconColor = parseInt(a.is_read) ? '#94a3b8' : '#dc2626';
                    return `
                    <div style="padding: 1rem; border-bottom: 1px solid #f1f5f9; background: ${bg}; cursor:pointer; transition: background 0.2s;"
                         onclick="window.location.href='/Hagz/doctor/Appointment_details.php?id=${a.appointment_id}'">
                        <div style="display:flex; gap: 0.75rem;">
                            <div style="color:${iconColor}; font-size:1.5rem; margin-top:0.2rem;"><i class='bx bx-alarm-exclamation'></i></div>
                            <div>
                                <div style="font-size: 0.9rem; font-weight: 700; color: #1e293b; margin-bottom: 0.2rem;">${a.message}</div>
                                <div style="font-size: 0.75rem; color: #64748b;"><i class='bx bx-time'></i> ${a.created_at}</div>
                            </div>
                        </div>
                    </div>`;
                }).join('');
            }).catch(() => {
                list.innerHTML = `<div style="padding:1.5rem;text-align:center;color:#64748b;font-size:0.9rem;">خطأ في الاتصال</div>`;
            });
    }

    function checkQueue() {
        if (!badge) return;
        fetch(`${API}?action=today_queue`)
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.queue) return;
                const critical = data.queue.filter(p =>
                    p.priority === 'Critical' && !['Completed', 'Cancelled'].includes(p.status)
                ).length;
                
                // We could show unread summons + critical
                // But for now, badge is just critical. Or we can combine them!
                fetch(`${API}?action=check_alerts`)
                .then(rr => rr.json())
                .then(dAlt => {
                    const unreadSummons = (dAlt.success && dAlt.alerts) ? dAlt.alerts.length : 0;
                    const totalBadge = critical + unreadSummons;
                    if (totalBadge > 0) {
                        badge.textContent = totalBadge;
                        badge.style.display = '';
                        badge.style.background = '#dc2626';
                    } else {
                        badge.style.display = 'none';
                    }
                    
                    if (unreadSummons > 0 && dAlt.alerts.length > 0) {
                        showUrgentAlertOverlay(dAlt.alerts[0]);
                    }
                });
            })
            .catch(() => { /* صامت */ });
    }

    function showUrgentAlertOverlay(alertObj) {
        if (isAlertActive) return;
        isAlertActive = true;

        const overlayId = 'urgentAlertOverlaySystem';
        let overlay = document.getElementById(overlayId);
        
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.id = overlayId;
            overlay.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(220, 38, 38, 0.95); z-index: 999999;
                display: flex; align-items: center; justify-content: center;
                backdrop-filter: blur(8px); flex-direction: column; text-align: center;
                padding: 2rem; color: #fff; font-family: 'Cairo', sans-serif;
                animation: flashBg 1s infinite alternate;
            `;
            
            const style = document.createElement('style');
            style.innerHTML = `
                @keyframes flashBg { 0% { background: rgba(220, 38, 38, 0.95); } 100% { background: rgba(185, 28, 28, 0.98); } }
                @keyframes popUp { 0% { transform: scale(0.8); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
            `;
            document.head.appendChild(style);

            overlay.innerHTML = `
                <div style="background:#fff; color:#1e293b; padding:3rem; border-radius:24px; max-width:500px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5); animation: popUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                    <i class='bx bx-alarm-exclamation bx-tada' style='font-size:5rem; color:#dc2626; margin-bottom:1rem;'></i>
                    <h1 style="font-size:1.8rem; font-weight:900; margin-bottom:1rem; color:#dc2626;">نداء استدعاء عاجل!</h1>
                    <p id="urgentAlertMessage" style="font-size:1.2rem; font-weight:600; margin-bottom:2rem; line-height:1.6;"></p>
                    <div style="display:flex; gap:1rem; justify-content:center;">
                        <button id="btnReadAlert" style="background:#dc2626; color:#fff; padding:1rem 2rem; border:none; border-radius:12px; font-family:'Cairo',sans-serif; font-weight:800; font-size:1.1rem; cursor:pointer; width:100%; display:flex; align-items:center; justify-content:center; gap:0.5rem; transition:transform 0.2s;">
                            <i class='bx bx-check-double'></i> تلقيت وتأكدت
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            document.getElementById('btnReadAlert').addEventListener('click', () => {
                const alertId = overlay.getAttribute('data-alert-id');
                const btnRead = document.getElementById('btnReadAlert');
                btnRead.innerHTML = "<i class='bx bx-loader-alt bx-spin'></i> جاري التأكيد...";
                btnRead.disabled = true;
                
                fetch(`${API}?action=mark_alert_read`, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({alert_id: alertId})
                }).then(() => {
                    overlay.style.display = 'none';
                    isAlertActive = false;
                    btnRead.innerHTML = "<i class='bx bx-check-double'></i> تلقيت وتأكدت";
                    btnRead.disabled = false;
                    
                    // Refresh queue and dropdown
                    checkQueue();
                    if(dropdownOpen) fetchAllAlerts();
                    
                    // IF it's an appointment, redirect to it
                    const apptId = overlay.getAttribute('data-appt-id');
                    if(apptId && apptId !== 'null' && apptId !== '0') {
                        window.location.href = `/Hagz/doctor/Appointment_details.php?id=${apptId}`;
                    }
                }).catch(() => {
                    overlay.style.display = 'none';
                    isAlertActive = false;
                    btnRead.disabled = false;
                });
            });
        }

        const msgBox = document.getElementById('urgentAlertMessage');
        msgBox.textContent = alertObj.message || "طلب استدعاء عام للتدخل الطبي.";
        overlay.setAttribute('data-alert-id', alertObj.id);
        overlay.setAttribute('data-appt-id', alertObj.appointment_id);
        
        overlay.style.display = 'flex';
    }

    // Build the bell dropdown UI globally
    buildNotifDropdown();

    // Run Once
    checkQueue();

    // Poll every 8 seconds
    setInterval(() => {
        checkQueue();
    }, 8000);

})();
