-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2026 at 03:24 AM
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

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `admin_id`, `user_id`, `item_id`, `action_type`, `description`, `created_at`) VALUES
(76, 17, 6, 14, 'Archive Item', 'Admin archived item: sketchers', '2026-03-25 06:28:45'),
(77, 17, 6, 16, 'Archive Item', 'Admin archived item: penshoppe', '2026-03-25 06:28:48'),
(78, 17, 6, 18, 'Archive Item', 'Admin archived item: Lee', '2026-03-25 06:28:52'),
(79, 17, 6, 19, 'Archive Item', 'Admin archived item: joggers', '2026-03-25 06:28:55'),
(80, 17, 6, 21, 'Archive Item', 'Admin archived item: short', '2026-03-25 06:28:58'),
(81, 17, 18, 24, 'Archive Item', 'Admin archived item: mango', '2026-03-25 07:52:14'),
(82, NULL, 18, 25, 'Submit Item (Pending)', 'Regular user submitted a new item for approval: short', '2026-03-25 07:53:06'),
(83, 17, 18, 25, 'Approve Item', 'Admin approved item: short', '2026-03-25 07:53:42'),
(84, 17, 18, 25, 'Archive Item', 'Admin archived item: short', '2026-03-25 07:53:54'),
(85, 17, 6, NULL, 'Restore User', 'Admin performed Restore User on User ID: 6', '2026-05-13 14:45:00'),
(86, 17, 18, 24, 'Restore Item', 'Admin restored item: mango', '2026-05-13 14:45:25'),
(87, NULL, 18, 26, 'Submit Item (Pending)', 'Regular user submitted a new item for approval: joggers', '2026-05-13 14:47:10'),
(88, 17, 18, 26, 'Approve Item', 'Admin approved item: joggers', '2026-05-13 14:47:54'),
(89, 17, 17, 23, 'Update Item', 'Admin updated details of item: mango', '2026-05-13 14:48:08'),
(90, 17, 10, 22, 'Update Item', 'Admin updated details of item: short', '2026-05-13 14:48:15'),
(91, 17, 19, NULL, 'Reset User Password', 'Admin reset password of User: recelyn mabalay', '2026-05-13 14:49:23'),
(92, 17, 11, NULL, 'Archive User', 'Admin performed Archive User on User ID: 11', '2026-05-13 14:51:52'),
(93, 17, 12, NULL, 'Archive User', 'Admin performed Archive User on User ID: 12', '2026-05-13 14:51:54');

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

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `item_name`, `category`, `size`, `color`, `price`, `quantity`, `photo`, `status`, `added_by`, `approved_by`, `created_at`) VALUES
(2, 'Dress', 'Dress', 'M', 'Black', 290.00, 1, '1772111824_img.png', '', 6, 5, '2026-02-26 13:17:04'),
(3, 'Dress', 'T-Shirt', 'M', 'Black', 230.00, 1, '1772178368_img.png', '', 6, 10, '2026-02-27 07:46:08'),
(4, 'short', 'Pants', 'M', 'Black', 1121.00, 1, '1772180696_img.png', NULL, 6, NULL, '2026-02-27 08:24:56'),
(5, 'short', 'Dress', 'M', 'Black', 12.00, 1, '1772185249_img.png', '', 6, 10, '2026-02-27 09:40:49'),
(6, 'short', 'Pants', 'M', 'Black', 1211.00, 1, '1772185768_d7d003ba-64ae-4ee4-ad0b-1c8e7f2ffbb4.jpg', '', 6, 10, '2026-02-27 09:49:28'),
(7, 'shortsss', 'Pants', 'M', 'Black', 12.00, 1, '1772186037_d7d003ba-64ae-4ee4-ad0b-1c8e7f2ffbb4.jpg', '', 10, NULL, '2026-02-27 09:53:57'),
(8, 'short', 'Pants', 'M', 'Black', 121.00, 11, '1772186191_d7d003ba-64ae-4ee4-ad0b-1c8e7f2ffbb4.jpg', 'rejected', 6, 10, '2026-02-27 09:56:31'),
(9, 'joggers', 'Shoes', 'L', 'Byellow', 22.00, 233, '1772188203_img.png', 'archived', 6, 10, '2026-02-27 09:57:54'),
(10, 'jogger', 'T-Shirt', 'M', 'Black', 121.00, 1, '1772186374_3a5f5b04-98e4-412d-8a61-f434725de0a8.jpg', 'rejected', 6, 10, '2026-02-27 09:59:34'),
(11, 'short', 'T-Shirt', 'M', 'Black', 1.00, 26, '1772186399_99ad0631-e3cc-4d74-822b-63d15e75d9ec.jpg', 'archived', 6, 10, '2026-02-27 09:59:59'),
(12, 'short', 'T-Shirt', 'M', 'Blacks', 11.00, 1, '1772190987_644b2d50-8d6d-458c-b021-33a349ccaa39.jpg', 'archived', 10, NULL, '2026-02-27 11:16:27'),
(13, 'sketchers', 'Shoes', 'M', 'Black', 22.00, 1, '1772191155_02fd8ca5-1b19-41a9-ac2b-bd341ddb471c.jpg', 'active', 6, NULL, '2026-02-27 11:19:15'),
(14, 'sketchers', 'Shoes', 'M', 'Black', 11.00, 1, '1772191390_644b2d50-8d6d-458c-b021-33a349ccaa39.jpg', 'archived', 6, 10, '2026-02-27 11:23:10'),
(15, 'adidas', 'Shoes', 'M', 'Black', 234.00, 1, '1772191475_d7d003ba-64ae-4ee4-ad0b-1c8e7f2ffbb4.jpg', 'rejected', 6, 10, '2026-02-27 11:24:35'),
(16, 'penshoppe', 'Shirt', 'L', 'Black', 11111.00, 1, '1772250734_3a5f5b04-98e4-412d-8a61-f434725de0a8 (1).jpg', 'archived', 6, 10, '2026-02-28 03:52:14'),
(17, 'jogger', 'Pants', 'M', 'Black', 12.00, 1, '1772258016_d7d003ba-64ae-4ee4-ad0b-1c8e7f2ffbb4.jpg', 'rejected', 6, 10, '2026-02-28 05:53:36'),
(18, 'Lee', 'Shirt', 'M', 'Black', 12.00, 1, '1772259354_d7d003ba-64ae-4ee4-ad0b-1c8e7f2ffbb4.jpg', 'archived', 6, 10, '2026-02-28 06:15:54'),
(19, 'joggers', 'Accessories', 'XL', 'Blue', 111.00, 1, '1772270776_644b2d50-8d6d-458c-b021-33a349ccaa39.jpg', 'archived', 6, 17, '2026-02-28 09:26:16'),
(20, 'jogger', 'Hat', 'XL', 'Blacks', 1.00, 1, '1772270797_644b2d50-8d6d-458c-b021-33a349ccaa39.jpg', 'rejected', 6, 10, '2026-02-28 09:26:37'),
(21, 'short', 'Skirt', 'M', 'Black', 11.00, 1, '1772270818_644b2d50-8d6d-458c-b021-33a349ccaa39.jpg', 'archived', 6, 10, '2026-02-28 09:26:58'),
(22, 'short', 'T-Shirt', 'XL', 'Blue', 88.00, 1, '1778683695_77121-dark.gif', 'approved', 10, NULL, '2026-02-28 10:15:55'),
(23, 'mango', 'Jeans', 'm', 'Blacks', 2.00, 1, '1778683688_36802-alx.gif', 'approved', 17, NULL, '2026-03-24 04:04:33'),
(24, 'mango', 'Shirt', 'L', 'Black', 222.00, 2, '1774419591_9000-best-meme.png', 'approved', 18, 17, '2026-03-25 06:19:51'),
(25, 'short', 'Jeans', 'M', 'Black', 2.00, 5, '1774425186_1acc118f-9c19-4995-bd83-46a3f8d3f22e.jpg', 'archived', 18, 17, '2026-03-25 07:53:06'),
(26, 'joggers', 'Hat', 'L', 'Blacks', 11.00, 1, '1778683630_58830-huhuhu.gif', 'approved', 18, 17, '2026-05-13 14:47:10');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

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
