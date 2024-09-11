-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2024 at 02:47 AM
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
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `accepter_id` int(11) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `from_time` datetime NOT NULL,
  `to_time` datetime NOT NULL,
  `description` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `accepter_id`, `requester_id`, `title`, `from_time`, `to_time`, `description`, `location`, `status`) VALUES
(2, 2, 1, 'FYP Discussion', '2024-09-04 12:25:00', '2024-09-04 01:25:00', 'FYP Discussion', 'PG079', 'Cancelled'),
(3, 2, 1, 'Activity 2', '2024-09-18 13:40:00', '2024-09-18 14:40:00', 'Nothing', 'PG078', 'Confirmed'),
(4, 1, 3, 'A Meeting with AA', '2024-09-11 10:45:00', '2024-09-11 11:00:00', 'This is a meeting with the academic advisor.', 'NF-059', 'Pending'),
(5, 3, 1, 'Second Meeting with Dr Leong', '2024-09-20 09:45:00', '2024-09-20 10:45:00', 'This is the second meeting', 'NF-059', 'Pending'),
(6, 2, 5, 'Admin meeting', '2024-09-18 09:00:00', '2024-09-18 10:00:00', 'Admin-lecturer meeting', 'N009', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(100) NOT NULL
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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reg_date` datetime NOT NULL,
  `role_id` int(11) NOT NULL,
  `faculty` varchar(20) NOT NULL,
  `contact_number` varchar(13) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `reg_date`, `role_id`, `faculty`, `contact_number`) VALUES
(1, 'LEE JUN KHANG', 'leejunkhang56@gmail.com', '$2y$10$fSEvnHL044lVdhGaJJ0gReUbZMRHogm39v4rBKobEj74p3Y3.izFy', '2024-08-31 06:06:10', 2, 'FICT', '601110983279'),
(2, 'LOH KOK MENG', 'kokmeng@gmail.com', '$2y$10$gFwOiuXzS9dtMl8kHMBfxeTkwoKxvWRaimhNulB9QxoWmZIhI0F7u', '2024-09-04 05:16:35', 1, 'FICT', '60157763167'),
(3, 'LEONG ZI QI', 'qiqileong@gmail.com', '$2y$10$R54uYs570j/aJ/DqR5n8cO5K71szCsnGwNCHhCpCauCWBLkGdYtgS', '2024-09-09 21:47:24', 1, 'FICT', '60127223279'),
(5, 'ADMIN', 'admin123@gmail.com', '$2y$10$cd7//lxtXO2hfVW9Pb4NAehVeL3LMYQu7uQDBI79s3wRCbPMtTWB2', '2024-09-11 01:35:09', 3, 'FICT', '60121116579');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `accepter_id` (`accepter_id`),
  ADD KEY `requester_id` (`requester_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_roles` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
