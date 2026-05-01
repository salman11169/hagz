# 📖 Shifa+ — التوثيق التفصيلي

> [🌐 English](./README_EN_DOCS.md) — هذا المجلد يحتوي على التوثيق التقني التفصيلي لمشروع شفاء+.

---

## 📂 الفهرس

| المستند | الوصف |
|---|---|
| [API.md](./API.md) | جميع نقاط النهاية (70+ action) مع أمثلة الطلب والاستجابة |
| [TRIAGE.md](./TRIAGE.md) | محرك الفرز الذكي: AI pipeline + fallback + خوارزمية الإزاحة |
| [DATABASE.md](./DATABASE.md) | مخطط قاعدة البيانات (20 جدول) مع العلاقات |

---

## 🏗️ نظرة معمارية سريعة

```
┌──────────────┐     ┌──────────────────────┐     ┌────────────────────┐
│  المتصفح      │────▶│  Apache + .htaccess  │────▶│  Controllers (6)   │
│  (HTML/JS)   │◀────│  RewriteBase /hagz/  │◀────│  JSON API          │
└──────────────┘     └──────────────────────┘     └────────┬───────────┘
                                                           │
                    ┌──────────────────────────────────────┤
                    ▼                                      ▼
           ┌──────────────┐                      ┌──────────────────┐
           │  TriageAI    │                      │  MariaDB 10.4    │
           │  (Groq/      │                      │  hagz_clinic_ai  │
           │  Gemini/     │                      │  20 جدول         │
           │  OpenAI)     │                      └──────────────────┘
           └──────────────┘
```

### طبقات النظام

| الطبقة | الملفات | الوظيفة |
|---|---|---|
| **العرض (View)** | `public/`, `auth/`, `admin/`, `doctor/`, `patient/` | صفحات PHP/HTML مع Bootstrap 5.3 RTL |
| **التحكم (Controller)** | `controllers/*.php` | 6 وحدات JSON API |
| **الخدمات (Service)** | `services/TriageAI.php` | محرك AI + fallback |
| **الإعداد (Config)** | `config/database.php`, `config/ai.php` | إعدادات PDO و AI |
| **المساعدات (Helpers)** | `includes/session.php`, `includes/functions.php` | RBAC + أدوات مشتركة |
| **واجهة JS** | `assets/js/*.js` | 3 ملفات: حجز، UI system، إشعارات |

---

## 📊 الأدوار والصلاحيات (RBAC)

```
┌─────────────────────────────────────────────────────┐
│                    النظام                            │
├──────────┬──────────┬──────────┬────────────────────┤
│ Admin(1) │ Doctor(2)│Patient(3)│ Receptionist(4)*   │
├──────────┼──────────┼──────────┼────────────────────┤
│ إدارة    │ قائمة   │ حجز     │ (محجوز             │
│ كاملة    │ المرضى  │ ذكي/    │  لتطوير            │
│ + تقارير │ + علاج  │ عادي    │  مستقبلي)          │
│ + إعدادات│ + تحويل │ + سجلات │                    │
│ + صلاحيات│ + نداء  │ + وصفات │                    │
│          │ أطباء   │ + إشعارات│                    │
└──────────┴──────────┴──────────┴────────────────────┘
```
> *دور Receptionist(4) معرّف في الأدوار لكن لا يوجد له controller أو واجهة حالياً.

---

## 📁 خريطة الملفات الكاملة

### صفحات HTML/PHP

| المسار | الحجم | الوصف |
|---|---|---|
| `public/index.php` | 22 KB | الصفحة الرئيسية (Landing) |
| `public/404.html` | 6 KB | صفحة الخطأ |
| `auth/login.php` | 6 KB | تسجيل الدخول |
| `auth/signup.php` | 16 KB | إنشاء حساب جديد |
| `auth/Reset_password.php` | 11 KB | استعادة كلمة المرور (OTP) |
| `admin/admin.php` | 22 KB | لوحة تحكم المشرف |
| `admin/Manage_doctors.php` | 20 KB | إدارة الأطباء |
| `admin/Add_doctor.php` | 29 KB | إضافة طبيب جديد |
| `admin/Manage_patients.php` | 21 KB | إدارة المرضى |
| `admin/Reports.php` | 39 KB | التقارير والإحصائيات |
| `admin/System_settings.php` | 34 KB | إعدادات النظام |
| `admin/User_permissions.php` | 42 KB | إدارة الصلاحيات |
| `doctor/Doctor_dashboard.php` | 24 KB | لوحة تحكم الطبيب |
| `doctor/My_appointments.php` | 21 KB | مواعيد الطبيب |
| `doctor/Appointment_details.php` | 62 KB | تفاصيل الموعد + علاج (أكبر صفحة) |
| `doctor/Doctor_profile.php` | 41 KB | الملف الشخصي + جدول + مهارات |
| `doctor/My_patients.php` | 13 KB | قائمة المرضى |
| `doctor/Doctor_reports.php` | 23 KB | تقارير الطبيب |
| `doctor/Doctor_referrals.php` | 10 KB | التحويلات |
| `doctor/Edit_appointment.php` | 23 KB | تعديل الموعد |
| `patient/dashboard-new.php` | 20 KB | لوحة تحكم المريض |
| `patient/booking-new.php` | 44 KB | حجز المواعيد (ذكي + عادي) |
| `patient/records-new.php` | 16 KB | السجلات الطبية |
| `patient/prescriptions-new.php` | 21 KB | الوصفات الطبية |
| `patient/profile-new.php` | 17 KB | الملف الشخصي |
| `patient/notifications-new.php` | 12 KB | الإشعارات |
| `patient/partials/patient-nav.php` | 5 KB | شريط التنقل المشترك |

### JavaScript

| الملف | الحجم | الوظيفة |
|---|---|---|
| `assets/js/patient-booking.js` | 25 KB (609 سطر) | معالج الحجز الذكي + العادي + الطوارئ |
| `assets/js/hagz-ui.js` | 13 KB (312 سطر) | نظام Toast + Confirm Modal + Logout |
| `assets/js/notif-badge.js` | 12 KB (238 سطر) | عداد إشعارات الطبيب + نداء عاجل (polling 8s) |

### CSS (18 ملف)

| الملف | الحجم | الوظيفة |
|---|---|---|
| `doctor-dashboard.css` | 79 KB | أكبر ملف — يغطي جميع صفحات الطبيب |
| `patient.css` | 47 KB | واجهة المريض |
| `admin-dashboard.css` | 32 KB | واجهة المشرف |
| `index.css` | 20 KB | الصفحة الرئيسية |
| `help-center.css` | 16 KB | مركز المساعدة |
| `auth.css` | 10 KB | صفحات الدخول والتسجيل |
| `user_permissions.css` | 11 KB | صفحة الصلاحيات |
| وملفات أخرى... | — | about, contact, faq, privacy, terms, etc. |
