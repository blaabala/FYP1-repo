-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2025 at 02:11 AM
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
-- Database: `ams_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` bigint(11) NOT NULL,
  `user_id` bigint(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `user_id`, `username`, `department`) VALUES
(1, 5, 'ADMIN', 'ITISC');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` bigint(11) NOT NULL,
  `student_id` bigint(11) NOT NULL,
  `lecturer_id` bigint(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` enum('Confirmed','Rejected','Cancelled','Completed') DEFAULT 'Confirmed',
  `description` text DEFAULT NULL,
  `location` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `student_id`, `lecturer_id`, `title`, `start_datetime`, `end_datetime`, `status`, `description`, `location`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Nothing', '2025-04-29 14:00:00', '2025-04-29 14:30:00', 'Completed', 'testing123', 'PG078', '2025-04-28 14:04:35', '2025-04-28 15:31:13'),
(4, 2, 1, 'n', '2025-04-30 12:00:00', '2025-04-30 12:30:00', 'Rejected', 'n', 'n', '2025-04-28 15:04:06', '2025-04-29 01:02:20'),
(5, 2, 1, 'n', '2025-09-08 11:30:00', '2025-09-08 12:00:00', 'Confirmed', 'n', 'n', '2025-04-28 15:04:35', '2025-04-28 15:04:35'),
(7, 2, 2, 'Trying', '2025-05-02 13:30:00', '2025-05-02 14:00:00', 'Cancelled', '-', '-', '2025-05-01 05:14:55', '2025-05-02 02:25:20'),
(8, 2, 2, 'Short Meeting', '2025-05-02 13:30:00', '2025-05-02 14:00:00', 'Cancelled', 'just a short meeting', 'NF-059', '2025-05-02 03:14:31', '2025-05-02 03:25:26'),
(9, 10, 2, 'Short Meeting with Dr S', '2025-05-19 12:30:00', '2025-05-19 14:00:00', 'Confirmed', 'Short Meeting with Dr Soh', 'PG078', '2025-05-02 08:38:42', '2025-05-14 18:10:14'),
(10, 2, 2, 'Testing123', '2025-05-12 15:00:00', '2025-05-12 15:30:00', 'Completed', 'Testing123 meeting', 'NG-007', '2025-05-08 09:18:11', '2025-05-13 02:55:52'),
(11, 2, 2, 't', '2025-05-19 15:00:00', '2025-05-19 15:30:00', 'Confirmed', 't', 't', '2025-05-08 09:20:09', '2025-05-13 02:55:42'),
(12, 2, 1, 'Meeting with Supervis', '2025-05-13 04:00:00', '2025-05-13 04:30:00', 'Cancelled', '20-min meeting', 'PG-078', '2025-05-13 01:24:50', '2025-05-13 01:55:06'),
(13, 2, 2, 'testing', '2025-05-14 10:00:00', '2025-05-14 11:00:00', 'Cancelled', '-', '-', '2025-05-13 02:14:24', '2025-05-13 02:16:12'),
(14, 2, 2, 'testing1', '2025-05-13 07:00:00', '2025-05-13 07:30:00', 'Rejected', 'testing123', 'PG078', '2025-05-13 03:40:36', '2025-05-13 04:06:12'),
(15, 2, 1, 'test1', '2025-05-14 12:00:00', '2025-05-14 12:30:00', 'Cancelled', '---', 'n', '2025-05-13 04:01:24', '2025-05-13 04:02:34'),
(16, 20, 1, 'FYP Consultation', '2025-05-19 09:00:00', '2025-05-19 09:30:00', 'Confirmed', '30mins of FYP consultation', 'NG-013', '2025-05-14 14:51:12', '2025-05-14 14:51:12'),
(17, 6, 1, 'testin', '2025-05-16 13:00:00', '2025-05-16 13:30:00', 'Cancelled', '-', 'Nowhere', '2025-05-14 17:53:44', '2025-05-14 22:19:11'),
(19, 6, 1, 'test2', '2025-05-15 14:00:00', '2025-05-15 14:30:00', 'Confirmed', '-', 'Nowhere', '2025-05-14 18:19:18', '2025-05-14 18:19:18'),
(20, 10, 1, 'new meeting 123', '2025-05-19 01:30:00', '2025-05-19 02:00:00', 'Cancelled', 'nothing', 'NF-059', '2025-05-14 21:41:22', '2025-05-14 22:10:32'),
(21, 10, 1, 'New meeting with Dr Lee', '2025-05-19 09:30:00', '2025-05-19 10:00:00', 'Confirmed', 'new 30min-meeting with Dr Lee', 'NF-059', '2025-05-14 22:11:25', '2025-05-14 22:11:25');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_dates`
--

CREATE TABLE `blocked_dates` (
  `id` bigint(11) NOT NULL,
  `lecturer_id` bigint(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blocked_dates`
--

INSERT INTO `blocked_dates` (`id`, `lecturer_id`, `start_date`, `end_date`, `reason`, `created_at`) VALUES
(4, 2, '2025-05-05', '2025-05-09', NULL, '2025-05-01 15:20:24'),
(7, 2, '2025-05-12', '2025-05-12', 'testing', '2025-05-13 02:48:13'),
(9, 8, '2025-05-16', '2025-05-19', 'On leave', '2025-05-14 15:05:26');

-- --------------------------------------------------------

--
-- Table structure for table `lecturers`
--

CREATE TABLE `lecturers` (
  `id` bigint(11) NOT NULL,
  `user_id` bigint(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `faculty` varchar(100) NOT NULL,
  `department` varchar(100) NOT NULL,
  `designation` varchar(50) NOT NULL,
  `office_no` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturers`
--

INSERT INTO `lecturers` (`id`, `user_id`, `username`, `faculty`, `department`, `designation`, `office_no`) VALUES
(1, 4, 'LEE KOK LEONG', 'FICT', 'DCCT', 'Senior Lecturer', 'PG-078'),
(2, 7, 'MUHAMMAD MATTHEW SOH', 'FICT', 'DCS', 'Lecturer', 'NG-011'),
(3, 9, 'MR BEAN', 'FICT', 'DCCT', 'Senior Lecturer', 'NG-010'),
(8, 19, 'DR NG HUI FANG', 'FICT', 'DCCT', 'Lecturer', 'NG-013'),
(11, 26, 'CIK NUR ATHIRAH NABILA BINTI MOHD IDROS', 'FICT', 'DIS', 'Lecturer', 'PG-078');

-- --------------------------------------------------------

--
-- Table structure for table `lecturer_availability`
--

CREATE TABLE `lecturer_availability` (
  `id` int(11) NOT NULL,
  `lecturer_id` bigint(11) NOT NULL,
  `is_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `day_of_week` int(11) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturer_availability`
--

INSERT INTO `lecturer_availability` (`id`, `lecturer_id`, `is_recurring`, `day_of_week`, `start_time`, `end_time`, `start_date`, `end_date`, `start_datetime`, `end_datetime`, `created_at`, `updated_at`) VALUES
(3, 1, 1, 3, '14:00:00', '16:00:00', '2025-04-30', '2025-05-28', NULL, NULL, '2025-04-28 06:32:26', '2025-04-28 06:32:26'),
(4, 1, 1, 1, '09:00:00', '12:00:00', '2025-04-01', '2025-12-31', '2025-04-28 09:00:00', '2025-12-31 12:00:00', '2025-04-28 12:30:08', '2025-04-28 15:58:26'),
(7, 2, 0, NULL, NULL, NULL, NULL, NULL, '2025-05-02 11:30:00', '2025-05-02 12:00:00', '2025-05-01 15:33:38', '2025-05-01 15:34:30'),
(8, 2, 0, NULL, NULL, NULL, NULL, NULL, '2025-05-02 11:00:00', '2025-05-02 14:00:00', '2025-05-01 15:52:46', '2025-05-01 15:52:46'),
(9, 2, 1, 1, '15:00:00', '17:00:00', '2025-05-12', '2025-05-26', NULL, NULL, '2025-05-01 19:55:16', '2025-05-01 19:55:16'),
(11, 2, 1, 2, '15:00:00', '17:00:00', '2025-05-12', '2025-05-30', NULL, NULL, '2025-05-08 10:28:28', '2025-05-08 10:28:28'),
(12, 2, 0, NULL, NULL, NULL, NULL, NULL, '2025-05-13 12:30:00', '2025-05-13 13:00:00', '2025-05-13 03:51:23', '2025-05-13 03:51:23'),
(14, 1, 1, 5, '10:00:00', '12:00:00', '2025-05-13', '2025-05-31', NULL, NULL, '2025-05-13 03:58:25', '2025-05-13 03:58:25'),
(15, 8, 0, NULL, NULL, NULL, NULL, NULL, '2025-05-15 10:00:00', '2025-05-15 13:00:00', '2025-05-14 15:04:56', '2025-05-14 21:56:53'),
(17, 1, 0, NULL, NULL, NULL, NULL, NULL, '2025-05-15 08:30:00', '2025-05-15 10:30:00', '2025-05-14 22:18:30', '2025-05-14 22:18:30');

-- --------------------------------------------------------

--
-- Table structure for table `operating_hours`
--

CREATE TABLE `operating_hours` (
  `id` int(11) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operating_hours`
--

INSERT INTO `operating_hours` (`id`, `start_time`, `end_time`, `updated_at`) VALUES
(1, '08:00:00', '17:00:00', '2025-05-14 21:29:52');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(1) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `role_name`) VALUES
(1, 'lecturer'),
(2, 'student'),
(3, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(11) NOT NULL,
  `user_id` bigint(11) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `faculty` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `username`, `faculty`) VALUES
(2, 2, 'LEE JUN KHANG', 'FICT'),
(4, 6, 'VINCENT LOH', 'FICT'),
(6, 10, 'JORDAN TAN JUN HAO', 'FICT'),
(7, 16, 'TAN ZHI JUIN', 'FICT'),
(9, 20, 'NG JUN HAO', 'FICT');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role_id` int(1) NOT NULL,
  `reg_date` datetime NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_number` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `role_id`, `reg_date`, `password`, `contact_number`) VALUES
(2, 'LEE JUN KHANG', 'leejunkhang56@gmail.com', 2, '2025-04-28 01:13:47', '$2y$10$5BBsDqT/1ED12vGBCVT81.Mpy4EfBIXn1uaM67EO7.MROL/gaIIm.', '60123456789'),
(4, 'LEE KOK LEONG', 'kleong@gmail.com', 1, '2025-04-28 01:23:39', '$2y$10$eKPc9FZQJu19PkN/5M8RqeZzJNYonpUUsaUYEhruI4n9hAZxy61qm', '60123456789'),
(5, 'ADMIN1', 'admin1@gmail.com', 3, '2025-04-28 08:05:40', '$2y$10$DRBskwxgsf8mkF8TVZ7HMupqqb4Oa6fFsNmm6dxuDG/QRbQaL3L1G', '60123456784'),
(6, 'VINCENT LOH', 'vincent@gmail.com', 2, '2025-04-28 23:08:58', '$2y$10$4i9A2G5X4GilqTapHSMIB.zFZeATybPxD7ecP6vT.Oa5G0L2fv8ru', '60123456789'),
(7, 'MUHAMMAD MATTHEW', 'muhammad@gmail.com', 1, '2025-04-30 10:10:07', '$2y$10$ogsG7GoAHYCIabNn/PaEG.j0fEv.evTPK5nE8Z7sC9gX/t.deQPeS', '60123456789'),
(9, 'MR BEAN', 'bean@gmail.com', 1, '0000-00-00 00:00:00', '$2y$10$3c/vjGixv9lVwuqSTmAWQ.aaaBMrz.dk1/lFWH3ibKwqIwFfsQETS', '60123456789'),
(10, 'JORDAN TAN JUN HAO', 'tanjunhao@gmail.com', 2, '2025-05-02 16:19:37', '$2y$10$r3PLwByltwyJzmZrn43hdOmWgX.ExH2ggjCpVWRRTgAY2xwhfgXrK', '60123456789'),
(16, 'TAN ZHI JUIN', 'tzjuin@gmail.com', 2, '2025-05-03 02:08:08', '$2y$10$ZZqOwmWxSrmbngLRMYl12epj0PW0aUa8swzIFOZXWld1tRHGCc1GG', '60123456789'),
(19, 'DR NG HUI FANG', 'nhfang@gmail.com', 1, '2025-05-14 22:46:50', '$2y$10$/kUT0yjfwr/AfyTDUEK4DOMU5d8DJz57XaRaKPs62MfqPKHH5l5Jm', '60123456789'),
(20, 'NG JUN HAO', 'ngjunhao@gmail.com', 2, '2025-05-14 22:47:30', '$2y$10$NS/2Uv0WMHecOPgAa3uYk.6gxHGoUhpzwTpCl6aJKK53mduEwpZyu', '60123456789'),
(26, 'CIK NUR ATHIRAH NABILA BINTI MOHD IDROS', 'nurathirahn@utar.edu.my', 1, '2025-05-15 08:00:31', '$2y$10$j4r2xmP2lUu2woJX9u.b/.SsCiL1iGOrIjv0xNHVfSk7YjY8SPzma', '60123456789');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_admins_userid` (`user_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `blocked_dates`
--
ALTER TABLE `blocked_dates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lecturers_userid` (`user_id`);

--
-- Indexes for table `lecturer_availability`
--
ALTER TABLE `lecturer_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_lecturer_id` (`lecturer_id`) USING BTREE;

--
-- Indexes for table `operating_hours`
--
ALTER TABLE `operating_hours`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_students_userid` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `blocked_dates`
--
ALTER TABLE `blocked_dates`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `lecturers`
--
ALTER TABLE `lecturers`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `lecturer_availability`
--
ALTER TABLE `lecturer_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `operating_hours`
--
ALTER TABLE `operating_hours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(1) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `fk_admins_userid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blocked_dates`
--
ALTER TABLE `blocked_dates`
  ADD CONSTRAINT `blocked_dates_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lecturers`
--
ALTER TABLE `lecturers`
  ADD CONSTRAINT `fk_lecturers_userid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lecturer_availability`
--
ALTER TABLE `lecturer_availability`
  ADD CONSTRAINT `lecturer_availability_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_userid` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
