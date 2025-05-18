-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2025 at 12:30 PM
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
  `return_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `returned_date` timestamp NULL DEFAULT NULL,
  `notified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_transactions`
--

INSERT INTO `borrow_transactions` (`transaction_id`, `member_id`, `equipment_id`, `borrow_date`, `return_date`, `status`, `returned_date`, `notified`) VALUES
(91, 1030, 24, '2025-05-17 07:17:56', '2025-05-17', 'returned', '2025-05-17 07:18:54', 0),
(93, 1032, 25, '2025-05-17 07:21:37', '2025-05-17', 'returned', '2025-05-17 07:22:54', 0),
(94, 1033, 26, '2025-05-17 07:24:08', '2025-05-17', 'returned', '2025-05-17 11:33:05', 0),
(95, 1033, 25, '2025-05-17 08:19:15', '2025-05-17', 'returned', '2025-05-17 08:19:41', 0),
(96, 1034, 25, '2025-05-17 08:25:54', '2025-05-17', 'returned', '2025-05-17 08:26:18', 0),
(97, 1033, 26, '2025-05-17 11:36:58', '2025-05-17', 'returned', '2025-05-17 11:45:01', 0),
(98, 1033, 25, '2025-05-17 11:45:27', '2025-05-17', 'returned', '2025-05-17 11:45:43', 0),
(99, 1033, 26, '2025-05-17 11:53:32', '2025-05-17', 'returned', '2025-05-17 12:21:37', 0),
(100, 1033, 26, '2025-05-17 12:42:06', '2025-05-17', 'returned', '2025-05-17 12:42:51', 0),
(101, 1033, 24, '2025-05-18 02:34:45', '2025-05-18', 'returned', '2025-05-18 02:35:12', 0),
(102, 1032, 24, '2025-05-18 02:36:15', '2025-05-19', 'returned', '2025-05-18 02:36:44', 0),
(103, 1033, 28, '2025-05-18 05:13:32', '2025-05-19', 'returned', '2025-05-18 05:17:47', 0),
(105, 1033, 24, '2025-05-18 10:21:44', '2025-05-18', 'returned', '2025-05-18 10:26:05', 0);

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `equipment_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `status` enum('available','borrowed','maintenance') DEFAULT 'available',
  `stock` int(11) NOT NULL DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`equipment_id`, `name`, `quantity`, `status`, `stock`, `image_path`) VALUES
(24, 'basketball', 0, 'available', 15, 'uploads/1747465859_Basketball.png'),
(25, 'frisbee disc', 0, 'available', 14, 'uploads/1747465870_images (2).jpg'),
(26, 'volleyball', 0, 'available', 19, 'uploads/1747485634_images (3).jpg'),
(27, 'Cones', 0, 'available', 10, 'uploads/1747486127_images (4).jpg'),
(28, 'volleyball net', 0, 'available', 4, 'uploads/1747486250_images (5).jpg');

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
(68, 1030, 'Jam Piodos', '20221071@nbsc.edu.ph', '2025-05-17', '$2y$10$OfzF.Ma32kbptL2ASQyxcuQvPpghbDlVcDzIxBX4ahmTMd4YqCfNy', 'member'),
(70, 1032, 'Loren Dacol', '20212051@nbsc.edu.ph', '2025-05-17', '$2y$10$ubTHQPSQMX2T88EiqyxPcekw4efVjC/LD2VmJjkz4iy76NBI0PVCi', 'member'),
(71, 1033, 'Rey Sinabianan', '20212010@nbsc.edu.ph', '2025-05-17', '$2y$10$9Ry5qDIOxzA1WguxfFBW9uBc79eMPfwJJ9BZ9wVRQQmnLW3FU6LKO', 'member'),
(72, 1034, 'Joash RautRaut', '20212140@nbsc.edu.ph', '2025-05-17', '$2y$10$sURMOTUaX/94iu9NTuKQD./dLnSoBT0g1dhkv/oTNc1I3PCjUG5R6', 'member');

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
(1, 'Sports Club', 'sinabiananrey@gmail.com', 3);

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
  `verified` tinyint(1) DEFAULT 0,
  `profile_image` varchar(255) DEFAULT 'uploads/default-profile.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `email`, `verification_code`, `verified`, `profile_image`) VALUES
(1, 'Taloy', '$2y$10$IYjcpO.SpqLGSSypbrjtCehNFVL/zvt18fWfMW3Ve31JjLpHmwZMm', 'admin', 'sinabiananrey@gmail.com', '', 1, 'uploads/default-profile.png'),
(1027, 'user3517', '$2y$10$/MSqQ.MVrx5qIVp4vfvhoOrHXFXY1NMICO5sRx4gHKfTpRmpoZAca', 'member', '20221505@nbsc.edu.ph', '', 1, 'uploads/default-profile.png'),
(1030, 'user2524', '$2y$10$OfzF.Ma32kbptL2ASQyxcuQvPpghbDlVcDzIxBX4ahmTMd4YqCfNy', 'member', '20221071@nbsc.edu.ph', '', 1, 'uploads/default-profile.png'),
(1032, 'user1843', '$2y$10$ubTHQPSQMX2T88EiqyxPcekw4efVjC/LD2VmJjkz4iy76NBI0PVCi', 'member', '20212051@nbsc.edu.ph', '', 1, 'uploads/default-profile.png'),
(1033, 'user4012', '$2y$10$9Ry5qDIOxzA1WguxfFBW9uBc79eMPfwJJ9BZ9wVRQQmnLW3FU6LKO', 'member', '20212010@nbsc.edu.ph', '', 1, 'uploads/saraum.png'),
(1034, 'user4920', '$2y$10$sURMOTUaX/94iu9NTuKQD./dLnSoBT0g1dhkv/oTNc1I3PCjUG5R6', 'member', '20212140@nbsc.edu.ph', '', 1, 'uploads/default-profile.png');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `borrow_transactions`
--
ALTER TABLE `borrow_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `equipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1036;

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
