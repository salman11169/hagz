/**
 * hagz-ui.js — Shared UI System (Toast Notifications + Confirm Modals)
 * Hagz Clinic System — شفاء+
 * Usage: include this file in any page, then call:
 *   HagzUI.toast('message', 'success' | 'error' | 'warning' | 'info')
 *   HagzUI.confirm({ title, message, confirmText, onConfirm })
 *   HagzUI.confirmLogout(logoutUrl)
 */

(function () {
    'use strict';

    // ─── Inject styles ─────────────────────────────────────────────────────────
    const CSS = `
    /* ── Toast Container ── */
    #hagz-toast-container {
        position: fixed;
        top: 1.2rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 99999;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .6rem;
        pointer-events: none;
        width: max-content;
        max-width: calc(100vw - 2rem);
    }
    .hagz-toast {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .85rem 1.4rem;
        border-radius: 16px;
        font-family: 'Cairo', sans-serif;
        font-size: .92rem;
        font-weight: 700;
        color: #fff;
        box-shadow: 0 8px 32px rgba(0,0,0,.18);
        pointer-events: all;
        animation: hagz-toast-in .35s cubic-bezier(.34,1.56,.64,1) forwards;
        min-width: 240px;
        max-width: 420px;
        text-align: right;
        direction: rtl;
        position: relative;
        overflow: hidden;
    }
    .hagz-toast::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 3px;
        background: rgba(255,255,255,.4);
        animation: hagz-toast-progress 3.5s linear forwards;
    }
    .hagz-toast.out { animation: hagz-toast-out .3s ease forwards; }
    .hagz-toast.success { background: linear-gradient(135deg, #059669, #10b981); }
    .hagz-toast.error   { background: linear-gradient(135deg, #dc2626, #ef4444); }
    .hagz-toast.warning { background: linear-gradient(135deg, #d97706, #f59e0b); }
    .hagz-toast.info    { background: linear-gradient(135deg, #2563eb, #3b82f6); }
    .hagz-toast i { font-size: 1.3rem; flex-shrink: 0; }
    .hagz-toast-close {
        margin-right: auto;
        margin-left: -.2rem;
        background: none;
        border: none;
        color: rgba(255,255,255,.7);
        cursor: pointer;
        font-size: 1rem;
        padding: 0 .2rem;
        line-height: 1;
        transition: color .2s;
    }
    .hagz-toast-close:hover { color: #fff; }

    @keyframes hagz-toast-in  { from { opacity:0; transform:translateY(-20px) scale(.9); } to { opacity:1; transform:translateY(0) scale(1); } }
    @keyframes hagz-toast-out { from { opacity:1; transform:scale(1); } to { opacity:0; transform:scale(.85); } }
    @keyframes hagz-toast-progress { from { width:100%; } to { width:0%; } }

    /* ── Confirm Modal ── */
    .hagz-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15,23,42,.55);
        backdrop-filter: blur(4px);
        z-index: 99998;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        animation: hagz-fade-in .2s ease;
    }
    .hagz-modal {
        background: #fff;
        border-radius: 24px;
        padding: 2rem 2rem 1.5rem;
        max-width: 420px;
        width: 100%;
        box-shadow: 0 30px 80px rgba(0,0,0,.25);
        direction: rtl;
        font-family: 'Cairo', sans-serif;
        animation: hagz-modal-in .35s cubic-bezier(.34,1.56,.64,1);
        position: relative;
    }
    .hagz-modal-icon {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin: 0 auto 1.2rem;
    }
    .hagz-modal-icon.danger  { background: rgba(239,68,68,.12);  color: #dc2626; }
    .hagz-modal-icon.warning { background: rgba(245,158,11,.12); color: #d97706; }
    .hagz-modal-icon.info    { background: rgba(37,99,235,.12);  color: #2563eb; }
    .hagz-modal-title {
        font-size: 1.15rem;
        font-weight: 900;
        color: #0f172a;
        text-align: center;
        margin-bottom: .5rem;
    }
    .hagz-modal-body {
        font-size: .9rem;
        font-weight: 600;
        color: #64748b;
        text-align: center;
        margin-bottom: 1.6rem;
        line-height: 1.6;
    }
    .hagz-modal-actions {
        display: flex;
        gap: .75rem;
        justify-content: center;
    }
    .hagz-btn {
        padding: .7rem 1.6rem;
        border-radius: 12px;
        border: none;
        font-family: 'Cairo', sans-serif;
        font-size: .9rem;
        font-weight: 800;
        cursor: pointer;
        transition: all .25s;
        min-width: 100px;
    }
    .hagz-btn-cancel {
        background: #f1f5f9;
        color: #475569;
    }
    .hagz-btn-cancel:hover { background: #e2e8f0; }
    .hagz-btn-confirm {
        color: #fff;
    }
    .hagz-btn-confirm.danger  { background: linear-gradient(135deg,#dc2626,#ef4444); box-shadow: 0 4px 14px rgba(220,38,38,.35); }
    .hagz-btn-confirm.warning { background: linear-gradient(135deg,#d97706,#f59e0b); box-shadow: 0 4px 14px rgba(217,119,6,.35); }
    .hagz-btn-confirm.info    { background: linear-gradient(135deg,#2563eb,#3b82f6); box-shadow: 0 4px 14px rgba(37,99,235,.35); }
    .hagz-btn-confirm:hover { transform:translateY(-2px); filter:brightness(1.05); }

    @keyframes hagz-fade-in  { from { opacity:0; } to { opacity:1; } }
    @keyframes hagz-modal-in { from { opacity:0; transform:scale(.85) translateY(20px); } to { opacity:1; transform:scale(1) translateY(0); } }
    `;

    const style = document.createElement('style');
    style.textContent = CSS;
    document.head.appendChild(style);

    // ─── Toast container ────────────────────────────────────────────────────────
    let toastContainer = null;
    function initToastContainer() {
        if (!document.body) return null;
        toastContainer = document.getElementById('hagz-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'hagz-toast-container';
            document.body.appendChild(toastContainer);
        }
        return toastContainer;
    }

    // ─── Icons map ──────────────────────────────────────────────────────────────
    const ICONS = {
        success: "bx bx-check-circle",
        error:   "bx bx-error-circle",
        warning: "bx bx-error",
        info:    "bx bx-info-circle"
    };
    const MODAL_ICONS = {
        danger:  { icon: "bx bx-trash",       cls: "danger" },
        warning: { icon: "bx bx-error",        cls: "warning" },
        info:    { icon: "bx bx-info-circle",  cls: "info" },
        logout:  { icon: "bx bx-log-out",      cls: "danger" }
    };

    // ─── Toast ──────────────────────────────────────────────────────────────────
    function toast(message, type = 'info', duration = 3500) {
        const container = initToastContainer();
        if (!container) return; // Fail safe if body is somehow missing

        const el = document.createElement('div');
        el.className = `hagz-toast ${type}`;
        el.innerHTML = `
            <i class="${ICONS[type] || ICONS.info}"></i>
            <span>${message}</span>
            <button class="hagz-toast-close" onclick="this.parentElement.remove()">✕</button>
        `;
        container.appendChild(el);

        setTimeout(() => {
            el.classList.add('out');
            setTimeout(() => el.remove(), 300);
        }, duration);
    }

    // ─── Confirm Modal ──────────────────────────────────────────────────────────
    function confirm({ title = 'تأكيد', message = '', confirmText = 'تأكيد', cancelText = 'إلغاء', type = 'danger', onConfirm = null, onCancel = null }) {
        // Remove existing modal
        document.querySelectorAll('.hagz-overlay').forEach(el => el.remove());

        const iconData = MODAL_ICONS[type] || MODAL_ICONS.danger;
        const overlay  = document.createElement('div');
        overlay.className = 'hagz-overlay';
        overlay.innerHTML = `
            <div class="hagz-modal" role="dialog" aria-modal="true">
                <div class="hagz-modal-icon ${iconData.cls}">
                    <i class="${iconData.icon}"></i>
                </div>
                <div class="hagz-modal-title">${title}</div>
                <div class="hagz-modal-body">${message}</div>
                <div class="hagz-modal-actions">
                    <button class="hagz-btn hagz-btn-cancel" id="hagzCancelBtn">${cancelText}</button>
                    <button class="hagz-btn hagz-btn-confirm ${iconData.cls}" id="hagzConfirmBtn">${confirmText}</button>
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        document.getElementById('hagzConfirmBtn').focus();

        function close(confirmed) {
            overlay.style.animation = 'hagz-fade-in .2s ease reverse';
            setTimeout(() => overlay.remove(), 180);
            if (confirmed && typeof onConfirm === 'function') onConfirm();
            if (!confirmed && typeof onCancel  === 'function') onCancel();
        }

        document.getElementById('hagzConfirmBtn').addEventListener('click', () => close(true));
        document.getElementById('hagzCancelBtn').addEventListener('click',  () => close(false));
        overlay.addEventListener('click', e => { if (e.target === overlay) close(false); });
        document.addEventListener('keydown', function esc(e) {
            if (e.key === 'Escape') { close(false); document.removeEventListener('keydown', esc); }
        });
    }

    // ─── Logout confirm ─────────────────────────────────────────────────────────
    function confirmLogout(logoutUrl = '/logout.php') {
        confirm({
            title:       'تسجيل الخروج',
            message:     'هل أنت متأكد أنك تريد تسجيل الخروج من حسابك؟',
            confirmText: 'نعم، تسجيل الخروج',
            cancelText:  'إلغاء',
            type:        'logout',
            onConfirm:   () => { window.location.href = logoutUrl; }
        });
    }

    // ─── Auto-bind all logout links ─────────────────────────────────────────────
    function bindLogoutLinks() {
        document.addEventListener('click', function(e) {
            const el = e.target.closest('a[href*="logout"], .logout');
            if (el) {
                const href = el.getAttribute('href');
                if (href && href.includes('logout')) {
                    e.preventDefault();
                    confirmLogout(href);
                }
            }
        });
    }

    // ─── Flash message from URL params ──────────────────────────────────────────
    function checkFlashParams() {
        const params = new URLSearchParams(window.location.search);
        const msg    = params.get('flash_msg');
        const type   = params.get('flash_type') || 'info';
        if (msg) {
            setTimeout(() => toast(decodeURIComponent(msg), type), 400);
            // Clean URL
            const clean = window.location.pathname + (window.location.hash || '');
            window.history.replaceState({}, '', clean);
        }
    }

    // ─── Public API ─────────────────────────────────────────────────────────────
    window.HagzUI = { toast, confirm, confirmLogout };

    // Init
    document.addEventListener('DOMContentLoaded', () => {
        bindLogoutLinks();
        checkFlashParams();
    });

    // Expose helpers for inline onclick usage
    window.hagzConfirmLogout = confirmLogout;
    window.hagzToast = toast;

})();
