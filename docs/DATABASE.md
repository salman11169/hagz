# 🗄️ Shifa+ — مخطط قاعدة البيانات

> `hagz_clinic_ai` — MariaDB 10.4 — UTF-8 mb4 — **20 جدول**

---

## 📊 مخطط العلاقات (ERD)

```
                         ┌──────────┐
                         │  roles   │
                         │  PK: id  │
                         └────┬─────┘
                              │ 1:N
                    ┌─────────┴──────────┐
                    │      users         │
                    │  PK: id            │
                    │  FK: role_id       │
                    │  UQ: email, phone  │
                    └──┬──────────────┬──┘
                       │ 1:1         │ 1:1
              ┌────────┴──┐    ┌─────┴──────────┐
              │ patients  │    │    doctors      │
              │ PK: id    │    │  PK: id         │
              │ FK: user_id│   │  FK: user_id    │
              └──┬────────┘    │  FK: spec_id    │
                 │             └──┬──────────┬───┘
                 │                │          │
                 │    ┌───────────┤     ┌────┴───────────┐
                 │    │           │     │ doctor_schedules│
                 │    │           │     │ doctor_skills   │
                 │    │           │     │ doctor_alerts   │
                 │    │           │     └────────────────┘
                 │    │           │
              ┌──┴────┴───────────┴──┐
              │    appointments      │
              │  PK: id              │
              │  FK: patient_id      │
              │  FK: doctor_id       │
              └──┬────────┬──────┬───┘
                 │        │      │
    ┌────────────┤   ┌────┤   ┌──┴──────────┐
    │            │   │    │   │ triage_logs  │
    │ billing    │   │    │   └─────────────┘
    │ └─bill_items   │    │
    │            │   │    │
    │ medical_records│    │ referrals
    │ ├─record_symptoms   │
    │ ├─record_lab_tests  │
    │ └─prescriptions     │
    │                     │
    │ notifications       │
    │ chronic_diseases    │
    └─────────────────────┘

              ┌──────────────────┐
              │ specializations  │
              └──────────────────┘
              ┌──────────────────┐
              │ system_settings  │
              └──────────────────┘
```

---

## 📋 تفاصيل الجداول

### users
المستخدمون — جميع الأدوار في جدول واحد.

| العمود | النوع | القيود | الوصف |
|---|---|---|---|
| `id` | INT | PK, AUTO | المعرّف |
| `role_id` | INT | FK → roles | الدور |
| `first_name` | VARCHAR(100) | NOT NULL | الاسم الأول |
| `last_name` | VARCHAR(100) | NOT NULL | اسم العائلة |
| `email` | VARCHAR(150) | UNIQUE, NOT NULL | البريد |
| `phone` | VARCHAR(20) | UNIQUE, NOT NULL | الجوال |
| `password_hash` | VARCHAR(255) | NOT NULL | bcrypt hash |
| `is_active` | TINYINT(1) | DEFAULT 1 | الحالة |
| `created_at` | TIMESTAMP | DEFAULT NOW | تاريخ الإنشاء |

### roles

| id | name |
|---|---|
| 1 | Admin |
| 2 | Doctor |
| 3 | Patient |
| 4 | Receptionist |

### patients

| العمود | النوع | الوصف |
|---|---|---|
| `id` | INT PK | المعرّف |
| `user_id` | INT FK UNIQUE | ربط المستخدم |
| `date_of_birth` | DATE | تاريخ الميلاد |
| `gender` | ENUM('Male','Female') | الجنس |
| `blood_type` | VARCHAR(5) | فصيلة الدم |
| `weight` | DECIMAL(5,2) | الوزن (كغ) |
| `height` | DECIMAL(5,2) | الطول (سم) |
| `avatar_path` | VARCHAR(255) | مسار الصورة |

### doctors

| العمود | النوع | الوصف |
|---|---|---|
| `id` | INT PK | المعرّف |
| `user_id` | INT FK UNIQUE | ربط المستخدم |
| `specialization_id` | INT FK | التخصص |
| `license_number` | VARCHAR(100) UNIQUE | رقم الترخيص |
| `experience_years` | INT | سنوات الخبرة |
| `consultation_fee` | DECIMAL(10,2) | رسوم المعاينة |
| `bio` | TEXT | السيرة الذاتية |
| `avatar_path` | VARCHAR(255) | مسار الصورة |

### specializations
8 تخصصات مبذورة:

| id | name | icon |
|---|---|---|
| 1 | طب عام | bx-plus-medical |
| 2 | طب طوارئ | bx-first-aid |
| 3 | طب باطني | bx-heart |
| 4 | جراحة | bx-plus-circle |
| 5 | أطفال | bx-child |
| 6 | عظام | bx-body |
| 7 | أعصاب | bx-brain |
| 8 | نساء وولادة | bx-female-sign |

### appointments

| العمود | النوع | الوصف |
|---|---|---|
| `status` | ENUM | Pending, Confirmed, Completed, Cancelled, Transferred |
| `visit_type` | ENUM | In-person, Telehealth |
| `booking_type` | ENUM | smart, regular, emergency |
| `consultation_start_time` | TIMESTAMP | بداية المعاينة |
| `consultation_end_time` | TIMESTAMP | نهاية المعاينة |

### triage_logs

| العمود | النوع | الوصف |
|---|---|---|
| `raw_symptoms_input` | LONGTEXT JSON | الأعراض كـ JSON (مع فحص `json_valid`) |
| `ai_predicted_priority` | ENUM | Routine, Medium, Critical |
| `algorithm_confidence_score` | DECIMAL(5,2) | نسبة الثقة (0-100) |
| `ai_summary` | TEXT | ملخص AI — **للطبيب فقط، لا يراه المريض** |
| `ai_reasoning` | TEXT | تفصيل سبب القرار |
| `scheduled_date` / `scheduled_time` | DATE / TIME | الموعد المقترح من AI |

### doctor_schedules

| العمود | النوع | الوصف |
|---|---|---|
| `day_of_week` | TINYINT | 0=الأحد → 6=السبت |
| `shift_number` | TINYINT | 1=صباحي، 2=مسائي |
| `slot_duration_min` | SMALLINT | مدة كل موعد بالدقائق |
| `max_patients` | SMALLINT | حد أقصى (NULL = تلقائي) |

**UNIQUE KEY:** `(doctor_id, day_of_week, shift_number)`

### doctor_alerts

| العمود | النوع | الوصف |
|---|---|---|
| `alert_type` | ENUM | summon (استدعاء), emergency |
| `from_doctor_id` | INT FK | NULL يعني النظام |
| `to_doctor_id` | INT FK | الطبيب المستهدف |

### referrals

| العمود | النوع | الوصف |
|---|---|---|
| `priority` | ENUM | Routine, Urgent, Emergency |
| `status` | ENUM | Pending, Accepted, Declined, Completed |
| `clinical_summary` | TEXT | ملخص الحالة للاستشاري |

### notifications

| العمود | النوع | الوصف |
|---|---|---|
| `user_id` | INT FK | أي مستخدم |
| `patient_id` | INT FK | المريض |
| `type` | VARCHAR(50) | نوع الإشعار (rescheduled, etc.) |
| `action_url` | VARCHAR(255) | رابط الإجراء (اختياري) |

### system_settings

جدول صف واحد (`id=1`) يحوي إعدادات النظام:

| الإعداد | القيمة الافتراضية |
|---|---|
| `hospital_name` | مستشفى شفاء+ |
| `ai_triage_enabled` | 1 (مفعّل) |
| `critical_pain_threshold` | 8 |
| `default_consultation_minutes` | 30 |
| `allow_telehealth` | 1 |
| `patient_reminder_hours` | 24 |

---

## 🔑 الفهارس الرئيسية

| الجدول | الفهرس | النوع |
|---|---|---|
| `appointments` | `idx_doctor_date(doctor_id, appointment_date)` | INDEX |
| `appointments` | `idx_status(status)` | INDEX |
| `doctor_schedules` | `idx_doctor_day(doctor_id, day_of_week)` | INDEX |
| `notifications` | `idx_user_read(user_id, is_read)` | INDEX |
| `users` | `email`, `phone` | UNIQUE |
| `doctors` | `license_number` | UNIQUE |
