# 🔌 Shifa+ — توثيق API

> جميع الـ Controllers ترجع JSON بالصيغة: `{ "success": true/false, "message": "...", ...data }`
>
> المصادقة عبر PHP Sessions — يجب تسجيل الدخول أولاً.

---

## 📋 فهرس سريع

| Controller | عدد Actions | الوصول |
|---|---|---|
| [AuthController](#authcontroller) | 6 | عام |
| [BookingController](#bookingcontroller) | 6 | مريض فقط |
| [PatientController](#patientcontroller) | 9 | مريض فقط |
| [DoctorController](#doctorcontroller) | 27 | طبيب فقط |
| [AdminController](#admincontroller) | 16 | مشرف فقط |
| [NotificationController](#notificationcontroller) | 3 | مريض فقط |
| **المجموع** | **67 action** | |

---

## AuthController

**المسار:** `controllers/AuthController.php?action=...`

| Action | Method | المعاملات | الوصف |
|---|---|---|---|
| `register` | POST | fullName, phone, gender, age, bloodType, email, password, confirmPassword | تسجيل مريض جديد (role_id=3) |
| `login` | POST | email, password | تسجيل دخول → يُرجع redirect حسب الدور |
| `logout` | GET | — | إنهاء الجلسة → redirect لصفحة الدخول |
| `forgot_password` | POST | email (أو رقم الجوال) | توليد OTP مكون من 6 أرقام (صلاحية 15 دقيقة) |
| `verify_code` | POST | code | التحقق من OTP المُرسل |
| `reset_password` | POST | newPassword, confirmPassword | تعيين كلمة مرور جديدة بعد التحقق |

**ملاحظات:**
- كلمة المرور: 8 أحرف كحد أدنى، تُشفّر بـ `bcrypt`
- رقم الجوال: يقبل صيغ `05x`, `+966`, `00966` ويُنظّف تلقائياً
- OTP يُخزّن في الـ session (ليس في قاعدة البيانات)

---

## BookingController

**المسار:** `controllers/BookingController.php?action=...`
**الوصول:** مريض فقط (`is_patient()`)

| Action | Method | الوصف |
|---|---|---|
| `specializations` | GET | جلب التخصصات النشطة (id, name, icon) |
| `doctors` | GET | جلب الأطباء — اختياري: `?spec=اسم_التخصص` |
| `slots` | GET | جلب المواعيد المتاحة: `?doctor_id=X&date=YYYY-MM-DD` |
| `triage` | POST | **محرك الفرز الذكي** — يرسل الأعراض ويستقبل التصنيف + الموعد المقترح |
| `book` | POST | تأكيد الحجز (ذكي أو عادي) |
| `emergency_book` | POST | حجز طوارئ فوري (يبحث عن طبيب طوارئ → طب عام كـ fallback) |

### تفاصيل `triage` (POST — JSON Body)

```json
{
  "symptoms": ["ألم شديد في الصدر", "صعوبة في التنفس"],
  "pain_level": 8,
  "duration": "أقل من 24 ساعة",
  "conditions": ["أمراض قلب", "سكري"],
  "notes": "ملاحظات اختيارية",
  "emergency": false
}
```

**الاستجابة:**
```json
{
  "success": true,
  "message": "تم التصنيف.",
  "triage": {
    "priority": "Critical",
    "specialty": "طب طوارئ",
    "wait_time": "فوري — توجه للطوارئ الآن",
    "reasoning": "أعراض حرجة تستدعي تدخلاً طبياً فورياً.",
    "confidence": 0.95,
    "source": "ai",
    "ai_summary": "ملخص للطبيب (لا يراه المريض)",
    "ai_reasoning": "تفصيل سبب الاختيار"
  },
  "slot": {
    "doctor_id": 1,
    "doctor_name": "عبدالعزيز الصالح",
    "date": "2026-05-01",
    "time": "09:30",
    "reasoning": "تم اختيار أقرب موعد لأولوية حرجة",
    "rescheduled": 1
  }
}
```

### تفاصيل `book` (POST — JSON Body)

```json
{
  "doctor_id": 1,
  "date": "2026-05-01",
  "time": "09:30",
  "visit_type": "In-person",
  "priority": "Critical",
  "booking_mode": "smart",
  "symptoms": ["..."],
  "pain_level": 8,
  "duration": "...",
  "conditions": ["..."],
  "notes": "...",
  "ai_reasoning": "...",
  "ai_summary": "...",
  "ai_confidence": 0.95,
  "ai_specialty": "طب طوارئ"
}
```

### فحوصات الحجز (3 مستويات)
1. ❌ الوقت محجوز عند هذا الطبيب
2. ❌ المريض لديه موعد بنفس اليوم ونفس الوقت عند أي طبيب
3. ❌ المريض لديه موعد عند نفس الطبيب في نفس اليوم

---

## PatientController

**المسار:** `controllers/PatientController.php?action=...`
**الوصول:** مريض فقط

| Action | Method | الوصف |
|---|---|---|
| `dashboard` | GET | إحصائيات + المواعيد القادمة (5) |
| `profile` | GET | الملف الشخصي + الأمراض المزمنة |
| `appointments` | GET | جميع المواعيد مع معلومات الطبيب والأولوية |
| `medical_records` | GET | السجلات الطبية + أعراض + فحوصات + أدوية |
| `prescriptions` | GET | جميع الوصفات الطبية |
| `bills` | GET | الفواتير + بنود كل فاتورة |
| `update_profile` | POST | phone, weight, height, blood_type |
| `upload_avatar` | POST | FormData (field: `avatar`) — JPG/PNG/WebP ≤ 3MB |
| `cancel_appointment` | POST | JSON body: `{ "appointment_id": X }` |

---

## DoctorController

**المسار:** `controllers/DoctorController.php?action=...`
**الوصول:** طبيب فقط — **27 action** (أكبر controller)

### لوحة التحكم والمواعيد

| Action | Method | الوصف |
|---|---|---|
| `dashboard` | GET | إحصائيات اليوم + المواعيد القادمة (5) مرتّبة بالأولوية |
| `today_queue` | GET | قائمة مرضى اليوم مرتّبة: Critical → Medium → Routine |
| `appointments` | GET | المواعيد — فلترة: `?status=X&date=Y&type=smart` |
| `appointment_detail` | GET | تفاصيل كاملة: `?id=X` (موعد + سجل + أمراض مزمنة + triage) |

### العلاج والسجلات

| Action | Method | الوصف |
|---|---|---|
| `save_treatment` | POST | حفظ التشخيص + أعراض + فحوصات + أدوية + متابعة |
| `update_status` | POST | تحديث حالة الموعد (Pending/Confirmed/Completed/Cancelled/Transferred) |

### المرضى والتقارير

| Action | Method | الوصف |
|---|---|---|
| `patients` | GET | قائمة مرضى الطبيب (الذين أتمّوا زيارة) |
| `reports` | GET | تقرير 30 يوم: إجمالي، مكتملة، ملغية، حرجة، متوسط وقت معاينة |

### الملف الشخصي

| Action | Method | الوصف |
|---|---|---|
| `profile` | GET | الملف + الجدول + المهارات + الإحصائيات |
| `update_profile` | POST | bio, consultation_fee, experience_years, first_name, last_name, phone, email |
| `upload_avatar` | POST | FormData (field: `avatar`) — JPG/PNG/WebP/GIF ≤ 3MB |

### الجدول (Schedule)

| Action | Method | الوصف |
|---|---|---|
| `get_schedule` | GET | جلب جدول الدوام (مقسّم حسب اليوم والشفت) |
| `save_schedule` | POST | حفظ جدول كامل (JSON body مع days + consultation_duration) |
| `update_slot_duration` | POST | تحديث مدة المعاينة لجميع الشفتات |

### المهارات

| Action | Method | الوصف |
|---|---|---|
| `get_skills` | GET | جلب مهارات الطبيب |
| `save_skills` | POST | حفظ/تحديث المهارات (JSON body: `{ "skills": [...] }`) |

### التحويلات (Referrals)

| Action | Method | الوصف |
|---|---|---|
| `create_referral` | POST | إنشاء تحويل: appointment_id, to_doctor_id, reason, clinical_summary, priority |
| `get_referrals` | GET | جلب التحويلات: `?type=sent` أو `?type=received` |
| `doctors` | GET | قائمة جميع الأطباء النشطين (لنافذة التحويل) |

### النداءات العاجلة (Summon & Alerts)

| Action | Method | الوصف |
|---|---|---|
| `get_available_doctors` | GET | الأطباء المداومين **الآن** (يفحص الشفت الحالي) |
| `summon_doctor` | POST | إرسال نداء عاجل لطبيب/أطباء آخرين |
| `check_alerts` | GET | جلب التنبيهات غير المقروءة (polling كل 8 ثوانٍ) |
| `get_all_alerts` | GET | جلب آخر 20 تنبيه (مقروء + غير مقروء) |
| `mark_alert_read` | POST | تحديد تنبيه واحد كمقروء |
| `mark_all_read` | POST | تحديد جميع التنبيهات كمقروءة |

---

## AdminController

**المسار:** `controllers/AdminController.php?action=...`
**الوصول:** مشرف فقط

### لوحة التحكم

| Action | Method | الوصف |
|---|---|---|
| `dashboard` | GET | إحصائيات شاملة + رسوم بيانية + أداء الأطباء + آخر الحجوزات |

### إدارة الأطباء

| Action | Method | الوصف |
|---|---|---|
| `doctors` | GET | قائمة جميع الأطباء مع عدد مواعيد اليوم |
| `get_doctor` | GET | تفاصيل طبيب واحد + الجدول: `?id=X` |
| `add_doctor` | POST | إضافة طبيب جديد (بيانات + جدول + صورة اختياري) |
| `update_doctor` | POST | تحديث بيانات الطبيب |
| `save_doctor_schedule` | POST | حفظ جدول طبيب: `?doctor_id=X` |
| `upload_doctor_avatar` | POST | رفع صورة طبيب: `?doctor_id=X` |
| `toggle_doctor` | POST | تفعيل/تعطيل حساب الطبيب |

### إدارة المرضى

| Action | Method | الوصف |
|---|---|---|
| `patients` | GET | قائمة جميع المرضى مع إحصائياتهم |
| `toggle_patient` | POST | تفعيل/تعطيل حساب المريض |

### التخصصات والتقارير

| Action | Method | الوصف |
|---|---|---|
| `specializations` | GET | قائمة جميع التخصصات |
| `reports` | GET | تقارير شاملة: `?period=today/week/month/quarter/year` |

### الإعدادات والصلاحيات

| Action | Method | الوصف |
|---|---|---|
| `get_settings` | GET | جلب إعدادات النظام (اسم المستشفى، AI، Telehealth، إلخ) |
| `save_settings` | POST | حفظ الإعدادات المحدّثة |
| `user_permissions` | GET | قائمة جميع المستخدمين مع أدوارهم |
| `update_permission` | POST | تغيير دور مستخدم: user_id, role_id |

---

## NotificationController

**المسار:** `controllers/NotificationController.php?action=...`
**الوصول:** مريض فقط

| Action | Method | الوصف |
|---|---|---|
| `list` | GET | آخر 50 إشعار للمريض |
| `mark_read` | POST | تحديد كمقروء: `{ "notification_id": X }` أو الكل إذا بدون ID |
| `unread_count` | GET | عدد الإشعارات غير المقروءة |

---

## 🛣️ توجيه API عبر .htaccess

يتم تحويل المسارات الافتراضية عبر `mod_rewrite`:

```apache
RewriteRule ^api/auth/login$      controllers/AuthController.php?action=login [QSA,L]
RewriteRule ^api/auth/register$   controllers/AuthController.php?action=register [QSA,L]
RewriteRule ^api/auth/logout$     controllers/AuthController.php?action=logout [QSA,L]
RewriteRule ^api/patient(.*)$     controllers/PatientController.php$1 [QSA,L]
RewriteRule ^api/doctor(.*)$      controllers/DoctorController.php$1 [QSA,L]
RewriteRule ^api/booking(.*)$     controllers/BookingController.php$1 [QSA,L]
RewriteRule ^api/admin(.*)$       controllers/AdminController.php$1 [QSA,L]
```

> 💡 الصفحات تستخدم المسار المباشر (`../controllers/X.php?action=Y`) وليس المسار المُعاد كتابته.
