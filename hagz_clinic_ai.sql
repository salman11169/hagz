-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 12:15 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hagz_clinic_ai`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `referred_from_doctor_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Pending','Confirmed','Completed','Cancelled','Transferred') DEFAULT 'Pending',
  `visit_type` enum('In-person','Telehealth') DEFAULT 'In-person',
  `booking_type` enum('smart','regular','emergency') DEFAULT 'regular',
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `consultation_start_time` timestamp NULL DEFAULT NULL,
  `consultation_end_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

CREATE TABLE `billing` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `insurance_discount` decimal(10,2) DEFAULT 0.00,
  `net_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('Paid','Pending','Partial') DEFAULT 'Pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bill_items`
--

CREATE TABLE `bill_items` (
  `id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chronic_diseases`
--

CREATE TABLE `chronic_diseases` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `disease_name` varchar(255) NOT NULL,
  `diagnosed_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialization_id` int(11) NOT NULL,
  `license_number` varchar(100) NOT NULL,
  `experience_years` int(11) DEFAULT 0,
  `consultation_fee` decimal(10,2) DEFAULT 0.00,
  `bio` text DEFAULT NULL,
  `avatar_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `specialization_id`, `license_number`, `experience_years`, `consultation_fee`, `bio`, `avatar_path`) VALUES
(1, 2, 1, 'LIC-0002', 12, 150.00, 'طبيب متخصص ذو خبرة واسعة في مجاله.', NULL),
(2, 4, 4, 'LIC-0004', 15, 150.00, 'طبيب متخصص ذو خبرة واسعة في مجاله.', NULL),
(3, 6, 2, 'LIC-0006', 11, 150.00, 'طبيب متخصص ذو خبرة واسعة في مجاله.', NULL),
(4, 8, 7, 'LIC-0008', 14, 150.00, 'طبيب متخصص ذو خبرة واسعة في مجاله.', NULL),
(5, 5, 5, 'LIC-0005', 7, 150.00, 'طبيب متخصص ذو خبرة واسعة في مجاله.', NULL),
(6, 3, 3, 'LIC-0003', 9, 0.00, 'طبيب متخصص ذو خبرة واسعة في مجاله.', '/Hagz/uploads/doctors/doc_6_1773012459.jpg'),
(7, 9, 6, 'LIC-0009', 8, 150.00, 'طبيب متخصص ذو خبرة واسعة في مجاله.', NULL),
(8, 7, 8, 'LIC-0007', 6, 150.00, 'طبيب متخصص ذو خبرة واسعة في مجاله.', NULL),
(9, 11, 5, 'D15641', 3, 0.00, 'بيانات تجريبية', NULL),
(10, 14, 5, 'D234561', 3, 120.00, 'بيانات تجريبية', '/Hagz/uploads/doctors/doc_10_1773109950.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_alerts`
--

CREATE TABLE `doctor_alerts` (
  `id` int(11) NOT NULL,
  `from_doctor_id` int(11) DEFAULT NULL COMMENT 'NULL means system/emergency',
  `to_doctor_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `alert_type` enum('summon','emergency') NOT NULL DEFAULT 'summon',
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor_schedules`
--

CREATE TABLE `doctor_schedules` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` tinyint(4) NOT NULL COMMENT '0=Sunday, 6=Saturday',
  `shift_number` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=صباحي, 2=مسائي',
  `slot_duration_min` smallint(6) NOT NULL DEFAULT 30 COMMENT 'مدة كل موعد بالدقائق',
  `max_patients` smallint(6) DEFAULT NULL COMMENT 'حد أقصى للمرضى — NULL يعني تلقائي',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctor_schedules`
--

INSERT INTO `doctor_schedules` (`id`, `doctor_id`, `day_of_week`, `shift_number`, `slot_duration_min`, `max_patients`, `start_time`, `end_time`, `is_available`) VALUES
(1, 1, 0, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(4, 5, 0, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(5, 3, 0, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(6, 8, 0, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(7, 4, 0, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(8, 7, 0, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(9, 1, 1, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(12, 5, 1, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(13, 3, 1, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(14, 8, 1, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(15, 4, 1, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(16, 7, 1, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(17, 1, 2, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(20, 5, 2, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(21, 3, 2, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(22, 8, 2, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(23, 4, 2, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(24, 7, 2, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(25, 1, 3, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(28, 5, 3, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(29, 3, 3, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(30, 8, 3, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(31, 4, 3, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(32, 7, 3, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(33, 1, 4, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(36, 5, 4, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(37, 3, 4, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(38, 8, 4, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(39, 4, 4, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(40, 7, 4, 1, 30, NULL, '08:00:00', '16:00:00', 1),
(93, 6, 0, 1, 15, NULL, '09:00:00', '12:00:00', 1),
(94, 6, 0, 2, 15, NULL, '16:00:00', '23:00:00', 1),
(95, 6, 1, 1, 15, NULL, '08:00:00', '16:00:00', 1),
(96, 6, 1, 2, 15, NULL, '16:00:00', '23:00:00', 1),
(97, 6, 2, 1, 15, NULL, '08:00:00', '16:00:00', 1),
(98, 6, 3, 1, 15, NULL, '08:00:00', '16:00:00', 1),
(99, 6, 4, 1, 15, NULL, '08:00:00', '16:00:00', 1),
(100, 6, 5, 1, 15, NULL, '09:00:00', '21:00:00', 1),
(101, 6, 6, 1, 15, NULL, '08:00:00', '16:00:00', 0),
(102, 10, 0, 1, 30, NULL, '09:00:00', '23:00:00', 1),
(103, 10, 1, 1, 30, NULL, '09:00:00', '23:00:00', 1),
(104, 10, 2, 1, 30, NULL, '09:00:00', '23:00:00', 1),
(105, 10, 3, 1, 30, NULL, '09:00:00', '23:00:00', 1),
(106, 10, 4, 1, 30, NULL, '09:00:00', '23:00:00', 1),
(107, 10, 5, 1, 30, NULL, '09:00:00', '23:00:00', 0),
(108, 10, 6, 1, 30, NULL, '09:00:00', '23:00:00', 0),
(124, 2, 0, 1, 20, NULL, '08:00:00', '14:00:00', 1),
(125, 2, 0, 2, 20, NULL, '16:00:00', '02:00:00', 1),
(126, 2, 1, 1, 20, NULL, '08:00:00', '14:00:00', 1),
(127, 2, 1, 2, 20, NULL, '16:00:00', '02:00:00', 1),
(128, 2, 2, 1, 20, NULL, '08:00:00', '14:00:00', 1),
(129, 2, 2, 2, 20, NULL, '16:00:00', '02:00:00', 1),
(130, 2, 3, 1, 20, NULL, '08:00:00', '16:00:00', 1),
(131, 2, 4, 1, 20, NULL, '08:00:00', '16:00:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `doctor_skills`
--

CREATE TABLE `doctor_skills` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `skill` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctor_skills`
--

INSERT INTO `doctor_skills` (`id`, `doctor_id`, `skill`) VALUES
(1, 6, 'متخصص تغذية علاجية');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `doctor_notes` text DEFAULT NULL,
  `diagnosis` text DEFAULT NULL COMMENT 'تشخيص الطبيب',
  `next_follow_up_date` date DEFAULT NULL,
  `follow_up_notes` text DEFAULT NULL COMMENT 'ملاحظات المتابعة',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'أي مستخدم (طبيب أو مريض)',
  `patient_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `action_url` varchar(255) DEFAULT NULL COMMENT 'رابط الإجراء',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `blood_type` varchar(5) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL COMMENT 'in kg',
  `height` decimal(5,2) DEFAULT NULL COMMENT 'in cm',
  `avatar_path` varchar(255) DEFAULT NULL COMMENT 'Relative web path to profile picture'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `medication_name` varchar(255) NOT NULL,
  `dosage_strength` varchar(100) DEFAULT NULL COMMENT 'e.g., 500mg',
  `frequency` varchar(100) DEFAULT NULL COMMENT 'e.g., Twice daily',
  `timing` varchar(100) DEFAULT NULL COMMENT 'e.g., After meals',
  `duration_days` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `record_lab_tests`
--

CREATE TABLE `record_lab_tests` (
  `id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `test_name` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `result` text DEFAULT NULL,
  `status` enum('Pending','Completed') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `record_symptoms`
--

CREATE TABLE `record_symptoms` (
  `id` int(11) NOT NULL,
  `record_id` int(11) NOT NULL,
  `symptom_name` varchar(255) NOT NULL,
  `pain_level` tinyint(4) DEFAULT NULL COMMENT '1 to 10 scale',
  `duration` varchar(100) DEFAULT NULL COMMENT 'e.g., 2 days, 1 week',
  `condition_type` enum('Acute','Chronic') DEFAULT 'Acute'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `from_doctor_id` int(11) NOT NULL COMMENT 'الطبيب المحوِّل',
  `to_doctor_id` int(11) NOT NULL COMMENT 'الطبيب الاستشاري',
  `reason` text NOT NULL COMMENT 'سبب التحويل',
  `clinical_summary` text DEFAULT NULL COMMENT 'ملخص الحالة للاستشاري (يكتبه الطبيب المحوِّل)',
  `priority` enum('Routine','Urgent','Emergency') NOT NULL DEFAULT 'Routine',
  `status` enum('Pending','Accepted','Declined','Completed') NOT NULL DEFAULT 'Pending',
  `new_appointment_id` int(11) DEFAULT NULL COMMENT 'الموعد الجديد عند الاستشاري بعد القبول',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `responded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'Admin'),
(2, 'Doctor'),
(3, 'Patient'),
(4, 'Receptionist');

-- --------------------------------------------------------

--
-- Table structure for table `specializations`
--

CREATE TABLE `specializations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `specializations`
--

INSERT INTO `specializations` (`id`, `name`, `icon`, `status`) VALUES
(1, 'طب عام', 'bx-plus-medical', 'Active'),
(2, 'طب طوارئ', 'bx-first-aid', 'Active'),
(3, 'طب باطني', 'bx-heart', 'Active'),
(4, 'جراحة', 'bx-plus-circle', 'Active'),
(5, 'أطفال', 'bx-child', 'Active'),
(6, 'عظام', 'bx-body', 'Active'),
(7, 'أعصاب', 'bx-brain', 'Active'),
(8, 'نساء وولادة', 'bx-female-sign', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `hospital_name` varchar(255) DEFAULT 'Ù…Ø³ØªØ´ÙÙ‰ Ø´ÙØ§Ø¡+',
  `default_language` varchar(10) DEFAULT 'ar',
  `timezone` varchar(100) DEFAULT 'Asia/Riyadh',
  `maintenance_mode` tinyint(1) DEFAULT 0,
  `ai_triage_enabled` tinyint(1) DEFAULT 1,
  `critical_pain_threshold` tinyint(4) DEFAULT 8 COMMENT '1 to 10 scale',
  `urgent_duration_limit` varchar(50) DEFAULT '24 hours',
  `require_manual_triage_approval` tinyint(1) DEFAULT 0,
  `default_consultation_minutes` int(11) DEFAULT 30,
  `allow_telehealth` tinyint(1) DEFAULT 1,
  `patient_reminder_hours` int(11) DEFAULT 24
) ;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `hospital_name`, `default_language`, `timezone`, `maintenance_mode`, `ai_triage_enabled`, `critical_pain_threshold`, `urgent_duration_limit`, `require_manual_triage_approval`, `default_consultation_minutes`, `allow_telehealth`, `patient_reminder_hours`) VALUES
(1, 'مستشفى شفاء+', 'ar', 'Asia/Riyadh', 0, 1, 8, '24 hours', 0, 30, 1, 24);

-- --------------------------------------------------------

--
-- Table structure for table `triage_logs`
--

CREATE TABLE `triage_logs` (
  `id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `raw_symptoms_input` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'أعراض المريض كـ JSON' CHECK (json_valid(`raw_symptoms_input`)),
  `ai_predicted_priority` enum('Routine','Medium','Critical') NOT NULL,
  `ai_recommended_specialization` varchar(255) DEFAULT NULL,
  `algorithm_confidence_score` decimal(5,2) DEFAULT NULL COMMENT 'Percentage 0-100',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `specialty_predicted` varchar(100) DEFAULT NULL,
  `ai_reasoning` text DEFAULT NULL,
  `ai_summary` text DEFAULT NULL COMMENT 'ملخص AI — للطبيب فقط، لا يراه المريض',
  `scheduled_date` date DEFAULT NULL COMMENT 'التاريخ المقترح من AI',
  `scheduled_time` time DEFAULT NULL COMMENT 'الوقت المقترح من AI'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `first_name`, `last_name`, `email`, `phone`, `password_hash`, `is_active`, `created_at`) VALUES
(2, 2, 'عبدالعزيز', 'الصالح', 'abdulaziz@hagz.sa', '0501111111', '$2y$10$GxsNo6YoC.Vi4fHcqd7l2u1GCrtVvffg4jJLxv5dkkG/ER74GyDVG', 1, '2026-03-04 00:49:33'),
(3, 2, 'نورة', 'العتيبي', 'noura@hagz.sa', '0502222222', '$2y$10$GxsNo6YoC.Vi4fHcqd7l2u1GCrtVvffg4jJLxv5dkkG/ER74GyDVG', 1, '2026-03-04 00:49:33'),
(4, 2, 'فيصل', 'الحربي', 'faisal@hagz.sa', '0503333333', '$2y$10$GxsNo6YoC.Vi4fHcqd7l2u1GCrtVvffg4jJLxv5dkkG/ER74GyDVG', 1, '2026-03-04 00:49:33'),
(5, 2, 'منى', 'الزهراني', 'mona@hagz.sa', '0504444444', '$2y$10$GxsNo6YoC.Vi4fHcqd7l2u1GCrtVvffg4jJLxv5dkkG/ER74GyDVG', 1, '2026-03-04 00:49:33'),
(6, 2, 'خالد', 'العمر', 'khaled@hagz.sa', '0505555555', '$2y$10$GxsNo6YoC.Vi4fHcqd7l2u1GCrtVvffg4jJLxv5dkkG/ER74GyDVG', 1, '2026-03-04 00:49:33'),
(7, 2, 'سارة', 'البلوي', 'sara@hagz.sa', '0506666666', '$2y$10$GxsNo6YoC.Vi4fHcqd7l2u1GCrtVvffg4jJLxv5dkkG/ER74GyDVG', 1, '2026-03-04 00:49:33'),
(8, 2, 'محمد', 'الغامدي', 'mghamdi@hagz.sa', '0507777777', '$2y$10$GxsNo6YoC.Vi4fHcqd7l2u1GCrtVvffg4jJLxv5dkkG/ER74GyDVG', 1, '2026-03-04 00:49:33'),
(9, 2, 'ريم', 'السيف', 'reem@hagz.sa', '0508888888', '$2y$10$GxsNo6YoC.Vi4fHcqd7l2u1GCrtVvffg4jJLxv5dkkG/ER74GyDVG', 1, '2026-03-04 00:49:33'),
(10, 1, 'مدير', 'النظام', 'admin@hagz.com', '0500000001', '$2y$10$GxsNo6YoC.Vi4fHcqd7l2u1GCrtVvffg4jJLxv5dkkG/ER74GyDVG', 1, '2026-03-09 01:17:00'),
(11, 2, 'رهف', 'الغامدي', 'rahaf@gmail.com', '056783412', '$2y$10$GxsNo6YoC.Vi4fHcqd7l2u1GCrtVvffg4jJLxv5dkkG/ER74GyDVG', 1, '2026-03-10 00:32:01'),
(14, 2, 'رهف', 'الغامدية', 'rahaf1@gmail.com', '056783416', '$2y$10$GxsNo6YoC.Vi4fHcqd7l2u1GCrtVvffg4jJLxv5dkkG/ER74GyDVG', 1, '2026-03-10 01:24:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `referred_from_doctor_id` (`referred_from_doctor_id`),
  ADD KEY `idx_doctor_date` (`doctor_id`,`appointment_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `billing`
--
ALTER TABLE `billing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bill_id` (`bill_id`);

--
-- Indexes for table `chronic_diseases`
--
ALTER TABLE `chronic_diseases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `license_number` (`license_number`),
  ADD KEY `specialization_id` (`specialization_id`);

--
-- Indexes for table `doctor_alerts`
--
ALTER TABLE `doctor_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `from_doctor_id` (`from_doctor_id`),
  ADD KEY `to_doctor_id` (`to_doctor_id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_doctor_day_shift` (`doctor_id`,`day_of_week`,`shift_number`),
  ADD KEY `idx_doctor_day` (`doctor_id`,`day_of_week`);

--
-- Indexes for table `doctor_skills`
--
ALTER TABLE `doctor_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_doctor_skill` (`doctor_id`,`skill`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `record_id` (`record_id`);

--
-- Indexes for table `record_lab_tests`
--
ALTER TABLE `record_lab_tests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `record_id` (`record_id`);

--
-- Indexes for table `record_symptoms`
--
ALTER TABLE `record_symptoms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `record_id` (`record_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ref_appointment` (`appointment_id`),
  ADD KEY `fk_ref_from` (`from_doctor_id`),
  ADD KEY `fk_ref_to` (`to_doctor_id`),
  ADD KEY `fk_ref_new_appt` (`new_appointment_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `triage_logs`
--
ALTER TABLE `triage_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `billing`
--
ALTER TABLE `billing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bill_items`
--
ALTER TABLE `bill_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chronic_diseases`
--
ALTER TABLE `chronic_diseases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `doctor_alerts`
--
ALTER TABLE `doctor_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `doctor_skills`
--
ALTER TABLE `doctor_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `record_lab_tests`
--
ALTER TABLE `record_lab_tests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `record_symptoms`
--
ALTER TABLE `record_symptoms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `specializations`
--
ALTER TABLE `specializations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `triage_logs`
--
ALTER TABLE `triage_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`referred_from_doctor_id`) REFERENCES `doctors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `billing`
--
ALTER TABLE `billing`
  ADD CONSTRAINT `billing_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bill_items`
--
ALTER TABLE `bill_items`
  ADD CONSTRAINT `bill_items_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `billing` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chronic_diseases`
--
ALTER TABLE `chronic_diseases`
  ADD CONSTRAINT `chronic_diseases_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctors_ibfk_2` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`id`);

--
-- Constraints for table `doctor_alerts`
--
ALTER TABLE `doctor_alerts`
  ADD CONSTRAINT `doctor_alerts_ibfk_1` FOREIGN KEY (`from_doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctor_alerts_ibfk_2` FOREIGN KEY (`to_doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctor_alerts_ibfk_3` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  ADD CONSTRAINT `doctor_schedules_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_skills`
--
ALTER TABLE `doctor_skills`
  ADD CONSTRAINT `fk_skill_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `medical_records` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `record_lab_tests`
--
ALTER TABLE `record_lab_tests`
  ADD CONSTRAINT `record_lab_tests_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `medical_records` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `record_symptoms`
--
ALTER TABLE `record_symptoms`
  ADD CONSTRAINT `record_symptoms_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `medical_records` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `fk_ref_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ref_from` FOREIGN KEY (`from_doctor_id`) REFERENCES `doctors` (`id`),
  ADD CONSTRAINT `fk_ref_new_appt` FOREIGN KEY (`new_appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ref_to` FOREIGN KEY (`to_doctor_id`) REFERENCES `doctors` (`id`);

--
-- Constraints for table `triage_logs`
--
ALTER TABLE `triage_logs`
  ADD CONSTRAINT `triage_logs_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
