<p align="center">
  <a href="./README.md">🌐 <strong>النسخة العربية</strong></a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2"/>
  <img src="https://img.shields.io/badge/MariaDB-10.4-003545?style=for-the-badge&logo=mariadb&logoColor=white" alt="MariaDB"/>
  <img src="https://img.shields.io/badge/AI-Groq%20%7C%20Gemini%20%7C%20OpenAI-FF6F00?style=for-the-badge&logo=openai&logoColor=white" alt="AI"/>
  <img src="https://img.shields.io/badge/License-Academic-blue?style=for-the-badge" alt="License"/>
</p>

<h1 align="center">🏥 Shifa+ (شفاء+)</h1>
<h3 align="center">AI-Powered Clinic Management System</h3>

<p align="center">
  <em>The first Arabic medical scheduling system that prioritizes appointments based on clinical urgency — not first-come, first-served.</em>
</p>

> ⚠️ **Disclaimer**: This project is for academic and educational purposes. It has not undergone clinical security auditing and should not be deployed in a production healthcare environment without professional review.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Key Features](#key-features)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Database](#database)
- [Documentation](#documentation)
- [Contributing](#contributing)
- [License](#license)

---

## Overview

**Shifa+** is a full-featured clinic management system built with vanilla PHP 8.2, designed for Arabic-speaking healthcare environments. Its core differentiator is an **AI-powered triage layer** that classifies patients by medical severity via Groq, Gemini, or OpenAI APIs, with a local rule-based fallback engine when APIs are unavailable.

| Traditional System | Shifa+ |
|---|---|
| First-come, first-served | Priority-based scheduling |
| Manual specialty selection | AI-driven specialty routing |
| Static appointment slots | Cascade rescheduling for emergencies |

---

## Key Features

- 🤖 **AI Medical Triage** — Symptom classification into three priorities (Critical / Medium / Routine) with automatic specialty routing
- 🔄 **Cascade Rescheduling** — Automatically reschedules lower-priority appointments when a critical case arrives
- 👨‍⚕️ **Multi-Role Dashboards** — Separate portals for Admin, Doctor, and Patient
- 🗓️ **Smart + Regular Booking** — Two modes: AI-driven or traditional manual selection
- 🚨 **Emergency Booking** — Instant emergency admission with on-duty doctor notifications
- 📋 **Complete Medical Records** — Diagnosis, prescriptions, lab tests, chronic diseases
- 🔀 **Doctor Referrals** — Referral system with clinical summary and priority levels
- 🔔 **Dual Notification System** — Patient notifications + real-time doctor summon alerts
- 🎯 **Advanced Scheduling** — Morning/evening shifts with customizable consultation durations

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.2 (Vanilla — no framework) |
| **Database** | MariaDB 10.4 — PDO with UTF-8 mb4 |
| **Web Server** | Apache + `mod_rewrite` |
| **AI Providers** | Groq (default), Google Gemini, OpenAI |
| **Default Model** | `llama-3.3-70b-versatile` with auto-fallback |
| **Frontend** | HTML5 + CSS3 + Vanilla JS + Bootstrap 5.3 RTL |
| **Authentication** | PHP Sessions + `password_hash()` (bcrypt) |

---

## Installation

### Prerequisites

- PHP ≥ 8.2 (`pdo_mysql`, `curl`, `json`, `mbstring`)
- MariaDB ≥ 10.4 / MySQL ≥ 8.0
- Apache with `mod_rewrite`
- AI API key (Groq, Gemini, or OpenAI)

### Quick Start

```bash
# 1. Clone the repository
git clone https://github.com/salman11169/hagz.git

# 2. Create the database and import schema
mysql -u root -p -e "CREATE DATABASE hagz_clinic_ai CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p hagz_clinic_ai < hagz_clinic_ai.sql

# 3. Configure database connection
#    Edit config/database.php (DB_HOST, DB_NAME, DB_USER, DB_PASS)

# 4. Configure AI API key
#    Edit config/ai.php and replace YOUR_API_KEY_HERE with your key
```

### Access the System

| Page | URL |
|---|---|
| Landing Page | `http://localhost/hagz/public/` |
| Login | `http://localhost/hagz/auth/login.php` |
| Register | `http://localhost/hagz/auth/signup.php` |

### Demo Credentials

| Role | Email | Password |
|---|---|---|
| Admin | `admin@hagz.com` | `password` |
| Doctor | `abdulaziz@hagz.sa` | `password` |

---

## Usage

### Roles & Portals

| Role | Capabilities |
|---|---|
| **Admin** | Full management: doctors, patients, reports, settings, permissions |
| **Doctor** | Patient queue, treatment, referrals, schedule, reports, doctor summon |
| **Patient** | Smart/regular booking, records, prescriptions, notifications, profile |

### Creating Users

| Role | How to Create |
|---|---|
| **Admin** | Auto-seeded when importing `hagz_clinic_ai.sql` |
| **Doctor** | Added by Admin via Dashboard → Manage Doctors → Add Doctor |
| **Patient** | Self-registers at [Sign Up](http://localhost/hagz/auth/signup.php) |

### Smart Booking Workflow

```
Patient selects symptoms → AI classifies (Critical/Medium/Routine)
    → Determines specialty → Picks best doctor & slot
    → Cascade rescheduling if needed → Notifies affected patients
```

---

## Project Structure

```
hagz/
├── .htaccess                    # Apache routing (RewriteBase /hagz/)
├── .gitignore                   # Git exclusions
├── hagz_clinic_ai.sql           # Database schema + seed data
├── logout.php                   # Logout handler
├── package.json                 # Dev tools (mermaid-cli)
│
├── config/
│   ├── database.php             # PDO connection (define constants)
│   └── ai.php                   # AI settings (provider, models, timeout)
│
├── includes/
│   ├── session.php              # Session management + RBAC guards
│   └── functions.php            # Helpers: sanitize, redirect, json_response, validate
│
├── services/
│   └── TriageAI.php             # Triage engine (AI + local fallback)
│
├── controllers/                 # 6 JSON API controllers
│   ├── AuthController.php       # Login/register, password recovery
│   ├── BookingController.php    # Smart/regular/emergency booking
│   ├── PatientController.php    # Patient dashboard + profile + records
│   ├── DoctorController.php     # 27 actions (largest controller)
│   ├── AdminController.php      # Management + reports + settings
│   └── NotificationController.php # Notifications + counter
│
├── public/                      # Public-facing pages (main entry point)
│   ├── index.php                # Landing page
│   └── 404.html                 # Error page
│
├── auth/                        # Authentication pages
│   ├── login.php
│   ├── signup.php
│   └── Reset_password.php
│
├── admin/                       # 7 admin pages
├── doctor/                      # 8 doctor pages
├── patient/                     # 6 pages + partials/
│
├── assets/
│   ├── css/                     # 18 CSS files
│   ├── js/                      # 3 JS files
│   └── img/avatars/             # User avatars
│
└── uploads/doctors/             # Doctor profile images
```

---

## Database

**20 relational tables** in `hagz_clinic_ai` (MariaDB 10.4, UTF-8 mb4):

| Table | Description |
|---|---|
| `users` | All users (doctors, patients, admins) |
| `roles` | Roles: Admin(1), Doctor(2), Patient(3), Receptionist(4) |
| `patients` | Patient profiles (DOB, gender, blood type, weight, height) |
| `doctors` | Doctor profiles (specialty, license, fee, bio) |
| `specializations` | 8 medical specialties with icons |
| `doctor_schedules` | Weekly schedule (morning/evening shifts, slot duration) |
| `doctor_skills` | Doctor skills and certifications |
| `doctor_alerts` | Urgent inter-doctor alerts (summon/emergency) |
| `appointments` | Appointments (smart/regular/emergency) with full lifecycle |
| `triage_logs` | AI triage records (JSON symptoms, priority, AI summary) |
| `medical_records` | Clinical records (diagnosis, follow-up) |
| `prescriptions` | Medication prescriptions |
| `record_lab_tests` | Laboratory test orders |
| `record_symptoms` | Documented symptoms with pain levels |
| `chronic_diseases` | Chronic diseases for risk assessment |
| `notifications` | Patient notifications |
| `billing` | Appointment invoices |
| `bill_items` | Itemized bill details |
| `referrals` | Doctor referrals (Pending → Accepted → Completed) |
| `system_settings` | Global system configuration |

---

## Documentation

> 📖 For detailed API documentation, database ERD, and triage engine internals, see the [`docs/`](./docs/) directory.

| Document | Description |
|---|---|
| [`docs/API.md`](./docs/API.md) | All endpoints (67 actions) with examples |
| [`docs/TRIAGE.md`](./docs/TRIAGE.md) | Triage engine: AI pipeline + fallback + cascade algorithm |
| [`docs/DATABASE.md`](./docs/DATABASE.md) | Database schema (20 tables) with relationships and indexes |

---

## Contributing

This is an academic initiative. To contribute:

1. Fork the repository
2. Create a branch: `git checkout -b feature/your-feature`
3. Follow existing code style (PHP, Arabic comments, RTL)
4. Test the fallback engine when modifying `TriageAI.php`
5. Submit a Pull Request

---

## References

The triage methodology draws inspiration from established clinical systems:

- **ESI** — Emergency Severity Index (AHRQ)
- **NHS 111** — UK Digital Triage
- **Ada Health** — AI-powered symptom assessment
- **Symptomate** — Symptom-to-specialty matching

---

## License

Academic project. Contact the repository owner for inquiries.

---

<p align="center">
  <strong>Shifa+</strong> — Because your health can't wait 🏥
</p>
