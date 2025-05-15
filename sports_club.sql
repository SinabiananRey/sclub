-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2025 at 01:07 AM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `admin_id`, `title`, `content`, `date_posted`, `created_at`, `image_path`) VALUES
(16, 1, '📣 Volleyball Tryouts Announcement 🏐', 'Are you ready to showcase your skills on the court? Join us for the upcoming Volleyball Team Tryouts!\r\n\r\n📅 Date:Girls team: jUNE 1-3\r\nboys team: JuNE 4-6\r\n🕘 Time: 10AM\r\n📍 Venue: NBSC COVERED COURT\r\n\r\nWe’re looking for passionate, dedicated, and talented players to be part of our official volleyball team. Whether you’re a seasoned player or a rising star, this is your chance to shine!\r\n\r\n✅ What to Bring:\r\n\r\nProper sports attire\r\n\r\nWater bottle\r\n\r\nExtra shirt/towel\r\n\r\nYour A-game!\r\n\r\n📌 Open to all year level\r\n\r\nDon’t miss this opportunity to be part of something great. Let’s serve, set, and spike our way to victory! 💪\r\n\r\nFor inquiries, contact: Loren Dacol | 09195730637', '2025-05-15 22:28:24', '2025-05-15 22:28:24', 'uploads/Yellow and Blue Modern Illustrative Volleyball Sport Tryout Poster.png'),
(17, 1, '🌟 Frisbee Team Tryouts Announcement 🥏', 'Think you’ve got what it takes to join the ultimate team?\r\nFrisbee Tryouts are here!\r\n\r\n📅 Date: MAY 23-24\r\n🕘 Time: ANYTIME\r\n📍 Location: Field\r\n\r\nWe’re calling all agile, strategic, and team-driven individuals to try out for our Ultimate Frisbee Team. No experience? No problem! All skill levels are welcome—just bring your energy and team spirit.\r\n\r\n🧢 What to Bring:\r\n\r\nSportswear and rubber shoes\r\n\r\nWater bottle\r\n\r\nTowel\r\n\r\nA positive attitude and willingness to learn\r\n\r\n📌 Open to all \r\n\r\nLet’s chase discs, dive for catches, and build a team that flies high! 💥\r\nSee you on the field!\r\n\r\nFor more info, contact: JOASH RAUTRAUT | 095511555151', '2025-05-15 22:40:14', '2025-05-15 22:40:14', 'uploads/Soccer Field Open Tryouts Flyer.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `borrow_transactions`
--

CREATE TABLE `borrow_transactions` (
  `transaction_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `borrow_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `return_date` datetime DEFAULT NULL,
  `status` enum('borrowed','returned') DEFAULT 'borrowed',
  `returned_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_transactions`
--

INSERT INTO `borrow_transactions` (`transaction_id`, `member_id`, `equipment_id`, `borrow_date`, `return_date`, `status`, `returned_date`) VALUES
(26, 1011, 12, '2025-05-11 13:39:40', '2025-05-11 00:00:00', 'returned', '2025-05-14 23:32:22'),
(27, 1011, 15, '2025-05-14 23:33:20', '2025-05-15 00:00:00', 'returned', '2025-05-15 11:58:01'),
(28, 1012, 15, '2025-05-15 11:38:02', '2025-05-16 00:00:00', 'returned', '2025-05-15 11:38:29'),
(30, 1011, 14, '2025-05-15 12:08:04', '2025-05-15 00:00:00', 'returned', '2025-05-15 12:08:10'),
(32, 1011, 12, '2025-05-15 12:19:53', '2025-05-15 00:00:00', 'returned', '2025-05-15 12:37:09'),
(33, 1011, 14, '2025-05-15 12:34:25', '2025-05-15 00:00:00', 'returned', '2025-05-15 12:37:07'),
(34, 1011, 14, '2025-05-15 12:37:33', '2025-05-15 00:00:00', 'returned', '2025-05-15 12:49:22'),
(35, 1011, 12, '2025-05-15 13:49:51', '2025-05-15 00:00:00', 'returned', '2025-05-15 13:55:01'),
(36, 1011, 12, '2025-05-15 22:17:43', '2025-05-16 00:00:00', 'borrowed', NULL);

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
(12, 'basketball', 0, 'available', 2),
(14, 'frisbee disc', 0, 'available', 9),
(15, 'volleyball net', 0, 'available', 2),
(18, 'Badminton', 0, 'available', 10);

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
(49, 1011, 'Rey Sinabianan', '20212010@nbsc.edu.ph', '2025-05-11', '$2y$10$vbvGnTtYv4Pvk8Eep5RWrefkV68cbC0qfKl2NuR2WiNQm39EhS30W', 'member'),
(50, 1012, 'Mary Ann Sarol', '20231791@nbsc.edu.ph', '2025-05-15', '$2y$10$TRam.EeB7w.UFF6ZSHGNjuB9mR.6LD6zGtvmhzTw1QZ6TPTkLSnra', 'member');

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
(1, 'Sports Club', 'sinabiananrey@gmail.com', 5);

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
(1011, 'user4098', '$2y$10$vbvGnTtYv4Pvk8Eep5RWrefkV68cbC0qfKl2NuR2WiNQm39EhS30W', 'member', '20212010@nbsc.edu.ph', '', 1),
(1012, 'user8873', '$2y$10$TRam.EeB7w.UFF6ZSHGNjuB9mR.6LD6zGtvmhzTw1QZ6TPTkLSnra', 'member', '20231791@nbsc.edu.ph', '', 1);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `borrow_transactions`
--
ALTER TABLE `borrow_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1015;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrow_transactions`
--
ALTER TABLE `borrow_transactions`
  ADD CONSTRAINT `borrow_transactions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrow_transactions_ibfk_2` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`equipment_id`);

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `fk_members_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
