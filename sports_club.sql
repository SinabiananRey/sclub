-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2025 at 08:09 PM
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
-- Database: `sports_club`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `date_posted` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `admin_id`, `title`, `content`, `date_posted`, `created_at`) VALUES
(1, 1, 'hello', 'hi', '2025-05-07 13:27:54', '2025-05-07 13:29:16'),
(2, 1, 'YOU KNOW WHAT', 'IM TIIIIIRED', '2025-05-07 15:45:22', '2025-05-07 15:45:22'),
(3, 1, 'Try', 'woah', '2025-05-07 15:48:23', '2025-05-07 15:48:23'),
(4, 1, 'helloi', 'hiiii', '2025-05-08 01:01:16', '2025-05-08 01:01:16'),
(5, 1, 'hello', 'hi', '2025-05-08 01:16:43', '2025-05-08 01:16:43'),
(6, 1, 'I FIXED IT', 'HAHAHAHA', '2025-05-08 14:59:24', '2025-05-08 14:59:24');

-- --------------------------------------------------------

--
-- Table structure for table `borrow_transactions`
--

CREATE TABLE `borrow_transactions` (
  `transaction_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `borrow_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `return_date` timestamp NULL DEFAULT NULL,
  `status` enum('borrowed','returned') DEFAULT 'borrowed',
  `returned_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_transactions`
--

INSERT INTO `borrow_transactions` (`transaction_id`, `member_id`, `equipment_id`, `borrow_date`, `return_date`, `status`, `returned_date`) VALUES
(17, 32, 11, '2025-05-08 14:09:14', NULL, 'borrowed', NULL),
(18, 37, 11, '2025-05-08 15:13:54', NULL, 'borrowed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` enum('available','borrowed','maintenance') DEFAULT 'available',
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `name`, `quantity`, `status`, `stock`) VALUES
(11, 'disc', 0, 'available', 1),
(12, 'basketball', 0, 'available', 10),
(13, 'frisbee disc', 0, 'available', 10);

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `joined_date` date DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member') DEFAULT 'member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`member_id`, `user_id`, `full_name`, `email`, `joined_date`, `password`, `role`) VALUES
(14, 32, 'Kyle', 'trishabasarte95@gmail.com', '2025-05-08', '$2y$10$FHrVoOu8dV/6aYpfxBEWDeS8/Iua8RjyiQyEx6yOt5GZin1UHER3e', 'member'),
(18, 37, 'taloy', '20212010@nbsc.edu.ph', '2025-05-08', '$2y$10$HV79XSoL23m05lfaZ3WNaOHtE.0RagwIGOlKUzVNMQItJURQ4O4Mu', 'member'),
(39, 60, 'Gerlie Sinabianan', 'gerlieidoanlicaosinabianan@gmail.com', '2025-05-09', '$2y$10$cLn/M5f2dfQj4X7h2cgbVORmQpwOWxLiOO6tBSLniO.rme1ixujwW', 'member');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `club_name` varchar(255) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `borrowing_limit` int(11) NOT NULL DEFAULT 5
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `club_name`, `admin_email`, `borrowing_limit`) VALUES
(1, 'Sports Club', 'admin@example.com', 5);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','member') NOT NULL,
  `email` varchar(255) NOT NULL,
  `verification_code` varchar(255) NOT NULL,
  `verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `email`, `verification_code`, `verified`) VALUES
(1, 'Taloy', '$2y$10$IYjcpO.SpqLGSSypbrjtCehNFVL/zvt18fWfMW3Ve31JjLpHmwZMm', 'admin', 'sinabiananrey@gmail.com', '', 1),
(32, '', '$2y$10$FHrVoOu8dV/6aYpfxBEWDeS8/Iua8RjyiQyEx6yOt5GZin1UHER3e', 'member', 'trishabasarte95@gmail.com', '', 0),
(37, 'user5883', '$2y$10$HV79XSoL23m05lfaZ3WNaOHtE.0RagwIGOlKUzVNMQItJURQ4O4Mu', 'member', '20212010@nbsc.edu.ph', 'e24e58deae1c761e2a55a75d53a6e9d9', 0),
(43, 'user7848', '$2y$10$cAk2.23N7VJlYC6ABB8eBOZDbwy9B3BBnu4frtNCWjmXRwByePirW', 'member', '20221505@nbsc.edu.ph', '2b715e7b7d2bbc3f370d42822f88d6b5', 0),
(44, 'user9955', '$2y$10$GHKsoORUrkRBGJO8kfQtoe4KOySM2gUk/FIXoh8.4lQ5gdn29S0qa', 'member', '20221505@nbsc.edu.ph', 'dd4f102b6405493a234fc24415c1090e', 0),
(45, 'user8818', '$2y$10$tvn350OKTKt6wrJqgEDDzuEVH.yVUc.GUiaVA2Wz3NeTdMv/bjZFi', 'member', '20221505@nbsc.edu.ph', '0c9ab4e7ffa0c6132b4bb4d9cd1788e9', 0),
(46, 'user3850', '$2y$10$1rd9qTn3xr53yYAwQjKtLOUqjZIiZLCJPoJSQRjXFqmFr7lvL7Cpu', 'member', '20221505@nbsc.edu.ph', '8994b392ee8281a5f075061fa853b94a', 0),
(60, 'user2427', '$2y$10$cLn/M5f2dfQj4X7h2cgbVORmQpwOWxLiOO6tBSLniO.rme1ixujwW', 'member', 'gerlieidoanlicaosinabianan@gmail.com', '', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `borrow_transactions`
--
ALTER TABLE `borrow_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `borrow_transactions_ibfk_1` (`member_id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`equipment_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `borrow_transactions`
--
ALTER TABLE `borrow_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrow_transactions`
--
ALTER TABLE `borrow_transactions`
  ADD CONSTRAINT `borrow_transactions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`user_id`),
  ADD CONSTRAINT `borrow_transactions_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`);

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
