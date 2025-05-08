-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2025 at 09:10 AM
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
(5, 1, 'hello', 'hi', '2025-05-08 01:16:43', '2025-05-08 01:16:43');

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
(6, 20, 2, '2025-05-07 23:36:05', NULL, 'borrowed', NULL),
(7, 27, 3, '2025-05-08 00:14:36', NULL, 'borrowed', NULL),
(8, 27, 4, '2025-05-08 00:14:38', NULL, 'borrowed', NULL),
(9, 20, 2, '2025-05-08 01:02:27', NULL, 'borrowed', NULL),
(10, 20, 3, '2025-05-08 01:17:06', NULL, 'borrowed', NULL),
(13, 20, 3, '2025-05-08 06:55:56', NULL, 'borrowed', NULL),
(14, 20, 4, '2025-05-08 06:56:02', NULL, 'borrowed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` enum('available','borrowed','maintenance') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `name`, `quantity`, `status`) VALUES
(2, 'basketball', 0, 'available'),
(3, 'frisbee disc', 0, 'borrowed'),
(4, 'cone', 0, 'borrowed'),
(5, 'volleyball net', 0, 'borrowed'),
(6, 'baseball', 0, 'borrowed');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `joined_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`member_id`, `user_id`, `full_name`, `email`, `joined_date`) VALUES
(2, 3, 'rey', 'sinabiananrey@gmail.com', '2025-05-07'),
(3, 5, '', '', NULL),
(4, 20, 'User Name', 'user@example.com', '2025-05-08'),
(5, 21, 'Loy', '20212010@nbsc.edu.ph', '2025-05-08'),
(7, 25, 'hey', 'loy@gmail.com', '2025-05-08'),
(8, 26, 'yes', '20212140@nbsc.edu.ph', '2025-05-08'),
(9, 27, 'trisha', 'trishabasarte95@gmail.com', '2025-05-08'),
(10, 28, 'odon', '20221326@nbsc.edu.ph', '2025-05-08'),
(11, 29, 'real', 'realdjunodon@gmail.com', '2025-05-08');

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
(1, 'Taloy', '$2y$10$IYjcpO.SpqLGSSypbrjtCehNFVL/zvt18fWfMW3Ve31JjLpHmwZMm', 'admin', '', '', 0),
(3, 'taloy2', '$2y$10$F/Ym955aTPhG5MbPOcDLD.KFnwozLQhQxAXjqW4TYmjbbQLzAevJC', 'member', '', '', 0),
(5, 'triisha', '$2y$10$OtQ0iRAAuUbdE/YAGydJeuhFN4Jd5wFgDhKFHZjrWl8haCimB4Mpe', 'member', '', '', 0),
(10, 'jam', '$2y$10$HleHAFE01QIVMBiKydxlMe6XvbqCX3GE6JuQCpOYeSRlAvP/GnW2W', 'member', 'jampiodos@gmail.com', '', 0),
(11, 'realdjun', '$2y$10$Exp.AN4.tUggwc7cAUkQleWktZC.MA95MOMe06y6Oy5vFg6Erpe.O', 'member', 'maryannsarol6@gmail.com', '', 0),
(19, 'Gerlie', '$2y$10$5X.NWEXLa1fJN.mO.LJOju3lVxVXAqbMN6PPyZOhIogkVfvWjAPp2', 'member', 'gerlieidoanlicaosinabianan@gmail.com', 'c906b2611efd57cf964138b240c1b3a9', 0),
(20, 'Rey', '$2y$10$Af3w3cHq0TBlNcpXl0wrBOiYFODlaMavzhNnuNQ6/i3n0D0TXlgdy', 'member', 'sinabiananrey@gmail.com', '12c0232097bd5597382a87d0c8d298db', 0),
(21, 'Loy', '$2y$10$TVPjHklX0nRsbB/MAXhlbOjOFhmN82A0CXlKNzw3798PD5nxyXayy', 'admin', '20212010@nbsc.edu.ph', '', 0),
(25, 'hey', '$2y$10$dspvUVGOGW7dMxq0Rntcs.V9tBwC.HTPvl9YonQssBdjX4/9ohVnK', 'admin', 'loy@gmail.com', '', 0),
(26, 'yes', '$2y$10$jjT7egfrTusMORp8BJZ5s.GoiXxRLfJL3VnqFv9TAL9yWktRMXI3q', 'member', '20212140@nbsc.edu.ph', '', 0),
(27, 'trisha', '$2y$10$VkODXYPVgLByTXHmCbsVdeRpJL1a1O2mY3/odZUDSefIam1TvP8I.', 'member', 'trishabasarte95@gmail.com', '84093d84f6a99701b21d65621c3c5e17', 0),
(28, 'odon', '$2y$10$bhXdYtTlUgjJ71v5hEiLPepS1jxggVld9fvAWzThLNyJBtD2kHtXG', 'member', '20221326@nbsc.edu.ph', '3aba327a0e31282d78aa5444c03c411b', 0),
(29, 'real', '$2y$10$DMFMLv7dAGI7v66Ht69cyuSVnDQ2uo1chgLbJ3euoUsoEMueFmdgC', 'member', 'realdjunodon@gmail.com', '8ee10d4f212d805a4c692af88587a2e8', 0),
(31, 'Loren', '$2y$10$c0iU/XKP0hBSHVi5oZLwFOmFMDe1Eltz74JlfM5j5pvzRkPI82Gsa', 'member', '20212051@nbsc.edu.ph', 'c526ba046f2e142be9b2fec6b3ae2e8a', 0);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `borrow_transactions`
--
ALTER TABLE `borrow_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
