-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2026 at 03:31 AM
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
-- Database: `clothing_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `action_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(150) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `added_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('superadmin','admin','regular') DEFAULT NULL,
  `status` enum('active','archived') DEFAULT 'active',
  `remember_selector` varchar(255) DEFAULT NULL,
  `remember_validator` varchar(255) DEFAULT NULL,
  `remember_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `status`, `remember_selector`, `remember_validator`, `remember_expiry`) VALUES
(4, 'Super', 'Admin', 'superadmin@gmail.com', '$2y$10$IuDWoddLMl4/P.aCQ4WyI.FY0Sx0BQs2dx0A6Rk8Tya7F1GkFlqdu', 'superadmin', 'active', NULL, NULL, NULL),
(6, 'Regular', 'User', 'user@gmail.com', '$2y$10$.h0ekI67Ee6VgGb8CpyiGO.5w40wWE9jxWhVs9upBsySx3wuR0o6.', 'regular', 'active', NULL, NULL, NULL),
(10, 'Marian', 'Dela Cruz', 'mau@gmail.com', '$2y$10$Z4mMI5HJJPXDeheMMclXmuwkn9EXd1JhLxGzXIoJEgkvbaoc/HwDS', 'admin', 'active', NULL, NULL, NULL),
(11, 'mau', 'wie', 'wie@gmail.com', '$2y$10$eS.DwAKSNL5cqHTymYzGS.g2rF4PyuZwN665wv/JmW1kRwEseyX2K', 'regular', 'archived', NULL, NULL, NULL),
(12, 'john vincent', 'herrera', 'john@gmail.com', '$2y$10$RMd3Pqmi51sxrJQNeAMtZOMOAsGPRlzZGOYEgTchS95ZPngJw3tg2', 'regular', 'archived', NULL, NULL, NULL),
(13, 'mico', 'ramos', 'micoramos95@gmail.com', '$2y$10$iO3xw5G2BVRSuJYVLaNpHeWAJSWhiDlp02mHlUx0m0exH8uc8d35W', 'regular', 'active', NULL, NULL, NULL),
(15, 'den', 'calilong', 'den@gmail.com', '$2y$10$.mekNopaDRGmZ0rznBefqeZ/6Ht3QsC98A60IoEfL41eYsqOH5idC', 'admin', 'active', NULL, NULL, NULL),
(16, 'ben', 'ngani', 'ben@gmail.com', '$2y$10$ymaJkR4a/XQB2XtgbhyAq.qjnNuY.9nHoQr//QyzcKjW5ajXwKQUm', 'superadmin', 'active', NULL, NULL, NULL),
(17, 'Benedict', 'Garis', 'benedictgaris@gmail.com', '$2y$10$rUngpMHKMccBeuJ8w20NJe7GJgwW1XxWW7zMI8dAXUIDlLTz4K85.', 'admin', 'active', NULL, NULL, NULL),
(18, 'gemma', 'manzon', 'gemma@gmail.com', '$2y$10$UT81vkevzx7WQHt8X5kceO/MY7zQ7mNWkBrOTeFzcXOgItz6p51ne', 'regular', 'active', NULL, NULL, NULL),
(19, 'recelyn', 'mabalay', 'recelyn@gmail.com', '$2y$10$JA1l5pKR.jDtfljgiIT9nOxkQF11c7IUc77C02w/RpLujv09iB7u.', 'regular', 'active', NULL, NULL, NULL),
(20, 'super', 'admin', 'super@gmail.com', '$2y$10$It269CXZpsAnhdwB0uBbneaqkXeScgqpRfkcx8KNC1/YHfo3jFt3.', 'superadmin', 'active', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_remember_selector` (`remember_selector`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
