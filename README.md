<p align="center">
  <a href="./README_EN.md">🌐 <strong>English Version</strong></a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2"/>
  <img src="https://img.shields.io/badge/MariaDB-10.4-003545?style=for-the-badge&logo=mariadb&logoColor=white" alt="MariaDB"/>
  <img src="https://img.shields.io/badge/AI-Groq%20%7C%20Gemini%20%7C%20OpenAI-FF6F00?style=for-the-badge&logo=openai&logoColor=white" alt="AI"/>
  <img src="https://img.shields.io/badge/الترخيص-أكاديمي-blue?style=for-the-badge" alt="License"/>
</p>

<h1 align="center">🏥 شفاء+ (Shifa+)</h1>
<h3 align="center">نظام إدارة العيادات الذكي بالذكاء الاصطناعي</h3>

<p align="center">
  <em>أول نظام حجز طبي عربي يُعطي الأولوية للمواعيد بناءً على الحالة الصحية بدلاً من أسبقية الحضور.</em>
</p>

> ⚠️ **تنبيه**: هذا المشروع لأغراض أكاديمية وتعليمية. لم يخضع لتدقيق أمني سريري ولا يجب استخدامه في بيئة إنتاج حقيقية بدون مراجعة متخصصة.

---

## 📋 فهرس المحتويات

- [نظرة عامة](#نظرة-عامة)
- [المميزات الرئيسية](#المميزات-الرئيسية)
- [التقنيات المستخدمة](#التقنيات-المستخدمة)
- [التثبيت والتشغيل](#التثبيت-والتشغيل)
- [الاستخدام](#الاستخدام)
- [هيكل المشروع](#هيكل-المشروع)
- [قاعدة البيانات](#قاعدة-البيانات)
- [التوثيق التفصيلي](#التوثيق-التفصيلي)
- [المساهمة](#المساهمة)
- [الترخيص](#الترخيص)

---

## نظرة عامة

**شفاء+** نظام إدارة عيادات متكامل مبني بـ PHP 8.2 بدون إطار عمل، مُصمم للبيئات الصحية العربية. يتميز بـ **طبقة فرز ذكية** تصنّف المرضى حسب الخطورة الطبية عبر الذكاء الاصطناعي (Groq/Gemini/OpenAI)، مع محرك احتياطي محلي يعمل بدون اتصال.

| النظام التقليدي | شفاء+ |
|---|---|
| من يأتي أولاً يُخدم أولاً | جدولة حسب الأولوية الطبية |
| اختيار التخصص يدوياً | توجيه تلقائي بالذكاء الاصطناعي |
| حجز مواعيد ثابت | إزاحة تسلسلية (Cascade) للحالات الطارئة |

---

## المميزات الرئيسية

- 🤖 **فرز ذكي بالذكاء الاصطناعي** — تصنيف الأعراض لثلاث أولويات (حرجة/عاجلة/مستقرة) مع توجيه تخصصي تلقائي
- 🔄 **إزاحة تسلسلية** — إعادة جدولة تلقائية للمواعيد الأقل أولوية عند وصول حالة حرجة
- 👨‍⚕️ **لوحات تحكم متعددة** — بوابات منفصلة للمشرف والطبيب والمريض
- 🗓️ **حجز ذكي + عادي** — وضعان: AI-driven أو اختيار يدوي تقليدي
- 🚨 **نداء الطوارئ** — حجز طوارئ فوري مع إشعارات للأطباء المداومين
- 📋 **سجلات طبية كاملة** — تشخيص، وصفات، فحوصات مخبرية، أمراض مزمنة
- 🔀 **تحويلات بين الأطباء** — نظام إحالة مع ملخص سريري وأولوية
- 🔔 **نظام تنبيهات مزدوج** — إشعارات للمرضى + نداءات عاجلة بين الأطباء
- 🎯 **إدارة جداول متقدمة** — شفتات صباحية/مسائية مع مدة معاينة قابلة للتخصيص

---

## التقنيات المستخدمة

| الطبقة | التقنية |
|---|---|
| **الخادم** | PHP 8.2 (Vanilla — بدون إطار عمل) |
| **قاعدة البيانات** | MariaDB 10.4 — PDO مع UTF-8 mb4 |
| **خادم الويب** | Apache + `mod_rewrite` |
| **الذكاء الاصطناعي** | Groq (افتراضي)، Google Gemini، OpenAI |
| **النموذج الافتراضي** | `llama-3.3-70b-versatile` مع fallback تلقائي |
| **الواجهة** | HTML5 + CSS3 + Vanilla JS + Bootstrap 5.3 RTL |
| **المصادقة** | PHP Sessions + `password_hash()` (bcrypt) |

---

## التثبيت والتشغيل

### المتطلبات

- PHP ≥ 8.2 (`pdo_mysql`, `curl`, `json`, `mbstring`)
- MariaDB ≥ 10.4 / MySQL ≥ 8.0
- Apache مع `mod_rewrite`
- مفتاح API (Groq أو Gemini أو OpenAI)

### خطوات سريعة

```bash
# 1. استنساخ المستودع
git clone https://github.com/salman11169/hagz.git

# 2. إنشاء قاعدة البيانات واستيراد المخطط
mysql -u root -p -e "CREATE DATABASE hagz_clinic_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p hagz_clinic_ai < hagz_clinic_ai.sql

# 3. إعداد الاتصال بقاعدة البيانات
#    تعديل config/database.php (DB_HOST, DB_NAME, DB_USER, DB_PASS)

# 4. إعداد مفتاح الذكاء الاصطناعي
#    تعديل config/ai.php واستبدال YOUR_API_KEY_HERE بمفتاحك
```

### الوصول للنظام

| الصفحة | الرابط |
|---|---|
| الصفحة الرئيسية | `http://localhost/hagz/public/` |
| تسجيل الدخول | `http://localhost/hagz/auth/login.php` |
| إنشاء حساب | `http://localhost/hagz/auth/signup.php` |

### بيانات الدخول التجريبية

| الدور | البريد | كلمة المرور |
|---|---|---|
| مشرف | `admin@hagz.com` | `password` |
| طبيب | `abdulaziz@hagz.sa` | `password` |

---

## الاستخدام

### الأدوار والبوابات

| الدور | الصلاحيات |
|---|---|
| **مشرف** | إدارة كاملة: أطباء، مرضى، تقارير، إعدادات، صلاحيات |
| **طبيب** | قائمة المرضى، علاج، تحويلات، جدول، تقارير، نداء أطباء |
| **مريض** | حجز ذكي/عادي، سجلات، وصفات، إشعارات، ملف شخصي |

### إنشاء المستخدمين

| الدور | طريقة الإنشاء |
|---|---|
| **مشرف** | مبذور تلقائياً عند استيراد `hagz_clinic_ai.sql` |
| **طبيب** | يُضيفه المشرف من لوحة التحكم ← إدارة الأطباء ← إضافة طبيب |
| **مريض** | يسجّل بنفسه من صفحة [إنشاء حساب](http://localhost/hagz/auth/signup.php) |

### سير عمل الحجز الذكي

```
المريض يختار أعراضه → AI يصنّف (حرجة/عاجلة/مستقرة)
    → يحدد التخصص المناسب → يختار أفضل طبيب وموعد
    → إزاحة تسلسلية إن لزم → إشعار المرضى المتأثرين
```

---

## هيكل المشروع

```
hagz/
├── .htaccess                    # توجيه Apache (RewriteBase /hagz/)
├── .gitignore                   # استثناءات Git
├── hagz_clinic_ai.sql           # مخطط قاعدة البيانات + بيانات أولية
├── logout.php                   # تسجيل الخروج
├── package.json                 # أدوات تطوير (mermaid-cli)
│
├── config/
│   ├── database.php             # اتصال PDO (define constants)
│   └── ai.php                   # إعدادات AI (provider, models, timeout)
│
├── includes/
│   ├── session.php              # إدارة الجلسات + حراسة RBAC
│   └── functions.php            # أدوات: sanitize, redirect, json_response, validate
│
├── services/
│   └── TriageAI.php             # محرك الفرز (AI + fallback محلي)
│
├── controllers/                 # 6 وحدات تحكم JSON API
│   ├── AuthController.php       # تسجيل دخول/جديد، استعادة كلمة المرور
│   ├── BookingController.php    # حجز ذكي/عادي/طوارئ
│   ├── PatientController.php    # لوحة المريض + ملف + سجلات
│   ├── DoctorController.php     # 27 action (أكبر controller)
│   ├── AdminController.php      # إدارة + تقارير + إعدادات
│   └── NotificationController.php # إشعارات + عداد
│
├── public/                      # صفحات عامة (نقطة الدخول الرئيسية)
│   ├── index.php                # الصفحة الرئيسية (Landing Page)
│   └── 404.html                 # صفحة خطأ
│
├── auth/                        # صفحات المصادقة
│   ├── login.php
│   ├── signup.php
│   └── Reset_password.php
│
├── admin/                       # 7 صفحات إدارة
├── doctor/                      # 8 صفحات طبيب
├── patient/                     # 6 صفحات + partials/
│
├── assets/
│   ├── css/                     # 18 ملف CSS
│   ├── js/                      # 3 ملفات JS
│   └── img/avatars/             # صور المستخدمين
│
└── uploads/doctors/             # صور الأطباء
```

---

## قاعدة البيانات

**20 جدولاً علائقياً** في `hagz_clinic_ai` (MariaDB 10.4، UTF-8 mb4):

| الجدول | الوصف |
|---|---|
| `users` | جميع المستخدمين (أطباء، مرضى، مشرفون) |
| `roles` | الأدوار: Admin(1), Doctor(2), Patient(3), Receptionist(4) |
| `patients` | بيانات المرضى (ميلاد، جنس، فصيلة دم، وزن، طول) |
| `doctors` | ملفات الأطباء (تخصص، ترخيص، رسوم، سيرة) |
| `specializations` | 8 تخصصات طبية مع أيقونات |
| `doctor_schedules` | جدول أسبوعي (شفت صباحي/مسائي، مدة معاينة) |
| `doctor_skills` | مهارات وشهادات الأطباء |
| `doctor_alerts` | نداءات عاجلة بين الأطباء (summon/emergency) |
| `appointments` | المواعيد (smart/regular/emergency) مع دورة حياة كاملة |
| `triage_logs` | سجلات الفرز الذكي (أعراض JSON، أولوية، ملخص AI) |
| `medical_records` | السجلات السريرية (تشخيص، متابعة) |
| `prescriptions` | وصفات الأدوية |
| `record_lab_tests` | فحوصات مخبرية |
| `record_symptoms` | أعراض مسجّلة مع مستوى ألم |
| `chronic_diseases` | أمراض مزمنة لتقييم المخاطر |
| `notifications` | إشعارات المرضى |
| `billing` | فواتير المواعيد |
| `bill_items` | بنود الفاتورة التفصيلية |
| `referrals` | تحويلات بين الأطباء (Pending→Accepted→Completed) |
| `system_settings` | إعدادات النظام العامة |

---

## التوثيق التفصيلي

> 📖 لتوثيق API المفصّل، هيكلية قاعدة البيانات (ERD)، ومحرك الفرز الذكي، راجع مجلد [`docs/`](./docs/).

| المستند | الوصف |
|---|---|
| [`docs/API.md`](./docs/API.md) | جميع نقاط النهاية (67 action) مع أمثلة |
| [`docs/TRIAGE.md`](./docs/TRIAGE.md) | محرك الفرز: AI pipeline + fallback + خوارزمية الإزاحة |
| [`docs/DATABASE.md`](./docs/DATABASE.md) | مخطط قاعدة البيانات (20 جدول) مع العلاقات والفهارس |

---

## المساهمة

هذا المشروع مبادرة أكاديمية. للمساهمة:

1. Fork المستودع
2. أنشئ branch: `git checkout -b feature/your-feature`
3. التزم بالكود الحالي (PHP، تعليقات عربية، RTL)
4. اختبر المحرك الاحتياطي عند تعديل `TriageAI.php`
5. قدّم Pull Request

---

## المراجع

استُلهمت منهجية الفرز من أنظمة معتمدة عالمياً:

- **ESI** — مؤشر خطورة الطوارئ (AHRQ)
- **NHS 111** — الفرز الرقمي البريطاني
- **Ada Health** — تقييم الأعراض بالذكاء الاصطناعي
- **Symptomate** — مطابقة الأعراض مع التخصصات

---

## الترخيص

مشروع أكاديمي. تواصل مع مالك المستودع للاستفسارات.

---

<p align="center">
  <strong>شفاء+</strong> — لأن صحتك لا تنتظر 🏥
</p>
