-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2024 at 05:26 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dr_cancer`
--

-- --------------------------------------------------------

--
-- Table structure for table `conversation`
--

CREATE TABLE `conversation` (
  `id` int(11) NOT NULL,
  `user1` int(11) DEFAULT NULL,
  `user2` int(11) DEFAULT NULL,
  `date_delete_form_user1` datetime DEFAULT NULL,
  `date_delete_form_user2` datetime DEFAULT NULL,
  `last_updated` datetime NOT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `conversation`
--

INSERT INTO `conversation` (`id`, `user1`, `user2`, `date_delete_form_user1`, `date_delete_form_user2`, `last_updated`, `date_added`) VALUES
(16, 2, 1, NULL, NULL, '2024-05-15 14:23:32', '2024-05-15 14:23:32');

-- --------------------------------------------------------

--
-- Table structure for table `dr_type`
--

CREATE TABLE `dr_type` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dr_type` enum('colon','lung') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `dr_type`
--

INSERT INTO `dr_type` (`id`, `user_id`, `dr_type`) VALUES
(1, 1, 'colon');

-- --------------------------------------------------------

--
-- Table structure for table `medicine_remm`
--

CREATE TABLE `medicine_remm` (
  `id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `descr` longtext NOT NULL,
  `morning` time NOT NULL,
  `afternoon` time NOT NULL,
  `evening` time NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `medicine_remm`
--

INSERT INTO `medicine_remm` (`id`, `quantity`, `descr`, `morning`, `afternoon`, `evening`, `user_id`) VALUES
(1, 3, '3', '01:07:15', '13:07:15', '21:07:15', 2),
(2, 3, '3', '01:07:15', '13:07:15', '21:07:15', 2);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `from` int(11) DEFAULT NULL,
  `to` int(11) DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 NOT NULL,
  `direction` enum('rtl','ltr') NOT NULL,
  `type` varchar(10) NOT NULL,
  `del_from` enum('0','1') NOT NULL DEFAULT '0',
  `del_to` enum('0','1') NOT NULL DEFAULT '0',
  `seen_it` enum('1','0') NOT NULL DEFAULT '0',
  `unique_` varchar(60) DEFAULT NULL,
  `date_added` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `conversation_id`, `from`, `to`, `message`, `direction`, `type`, `del_from`, `del_to`, `seen_it`, `unique_`, `date_added`) VALUES
(14, 16, 2, 1, '', 'rtl', '', '0', '0', '1', NULL, '2024-05-15 14:23:32'),
(18, 16, 1, 2, 'hello', 'ltr', '', '0', '0', '1', NULL, '2024-05-15 17:35:06'),
(19, 16, 2, 1, 'hello', 'ltr', '', '0', '0', '1', NULL, '2024-05-15 17:35:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_name` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
  `img` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `type` enum('doctor','user','patient') NOT NULL,
  `lat` varchar(255) NOT NULL,
  `lon` varchar(255) NOT NULL,
  `password` varchar(100) NOT NULL,
  `code` varchar(255) NOT NULL,
  `last_login_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_name`, `img`, `email`, `type`, `lat`, `lon`, `password`, `code`, `last_login_date`) VALUES
(1, 'asdd', 'mo', 'bemobashandy@gmail.com', 'doctor', '', '', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'bNCS1TADpvzEkDwzD8dJJYe8toaPcpfYA4YKfEeHA', '2024-05-17 17:12:13'),
(2, 'asdds', '', 'bemobashandy@gmail1.com', 'user', '21', '21', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'A9pLbBLLTbODUC7FdLzhS9OeptO4xKNmCPf7bBas', '2024-05-06 16:18:41');

-- --------------------------------------------------------

--
-- Table structure for table `verification`
--

CREATE TABLE `verification` (
  `id` int(11) NOT NULL,
  `code` varchar(300) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`id`) USING BTREE;

--
-- Indexes for table `dr_type`
--
ALTER TABLE `dr_type`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `medicine_remm`
--
ALTER TABLE `medicine_remm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`) USING BTREE,
  ADD UNIQUE KEY `unique_` (`unique_`) USING BTREE,
  ADD KEY `fk-messages-conversation` (`conversation_id`),
  ADD KEY `fk-messages-users1` (`from`),
  ADD KEY `fk-messages-users2` (`to`),
  ADD KEY `id` (`id`),
  ADD KEY `direction` (`direction`),
  ADD KEY `type` (`type`),
  ADD KEY `del_from` (`del_from`),
  ADD KEY `seen_it` (`seen_it`),
  ADD KEY `del_to` (`del_to`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- Indexes for table `verification`
--
ALTER TABLE `verification`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `conversation`
--
ALTER TABLE `conversation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `dr_type`
--
ALTER TABLE `dr_type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `medicine_remm`
--
ALTER TABLE `medicine_remm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `verification`
--
ALTER TABLE `verification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `dr_type`
--
ALTER TABLE `dr_type`
  ADD CONSTRAINT `dr_type_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `medicine_remm`
--
ALTER TABLE `medicine_remm`
  ADD CONSTRAINT `medicine_remm_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk-messages-conversation` FOREIGN KEY (`conversation_id`) REFERENCES `conversation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk-messages-users1` FOREIGN KEY (`from`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL,
  ADD CONSTRAINT `fk-messages-users2` FOREIGN KEY (`to`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
