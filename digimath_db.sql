-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 06, 2026 at 10:58 PM
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
-- Database: `digimath_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai`
--

CREATE TABLE `ai` (
  `system_prompt` text NOT NULL,
  `temperature` float NOT NULL,
  `uzd_id` int(30) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai`
--

INSERT INTO `ai` (`system_prompt`, `temperature`, `uzd_id`) VALUES
('ggggggggggggggggggggggggg', 0.8, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_ID` int(30) UNSIGNED NOT NULL,
  `username` varchar(40) NOT NULL,
  `password` varchar(40) NOT NULL,
  `admin` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_ID`, `username`, `password`, `admin`) VALUES
(1, 'admin_d', 'admind', 1),
(2, 'admin_a', 'admina', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_info`
--

CREATE TABLE `user_info` (
  `user_ID` int(30) UNSIGNED NOT NULL,
  `grade` int(2) NOT NULL,
  `uzd_completed` varchar(100) NOT NULL,
  `School_name` varchar(100) NOT NULL,
  `email` varchar(90) NOT NULL,
  `username` varchar(40) NOT NULL,
  `actual_surname` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_info`
--

INSERT INTO `user_info` (`user_ID`, `grade`, `uzd_completed`, `School_name`, `email`, `username`, `actual_surname`) VALUES
(1, 4, 'gggggggg', 'hhhhhhhhhhh', 'hhhhhhhh', 'admin_d', 'DDDD');

-- --------------------------------------------------------

--
-- Table structure for table `uzd`
--

CREATE TABLE `uzd` (
  `uzd_id` int(30) UNSIGNED NOT NULL,
  `uzd_grade` int(2) NOT NULL,
  `uzd_text` text NOT NULL,
  `uzd_answer` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uzd`
--

INSERT INTO `uzd` (`uzd_id`, `uzd_grade`, `uzd_text`, `uzd_answer`) VALUES
(1, 6, 'ggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggg', '666666'),
(2, 8, 'hhhh', 'jj');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai`
--
ALTER TABLE `ai`
  ADD KEY `forein` (`uzd_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_ID`,`username`,`password`,`admin`);

--
-- Indexes for table `user_info`
--
ALTER TABLE `user_info`
  ADD KEY `user_id` (`user_ID`);

--
-- Indexes for table `uzd`
--
ALTER TABLE `uzd`
  ADD PRIMARY KEY (`uzd_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_ID` int(30) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `uzd`
--
ALTER TABLE `uzd`
  MODIFY `uzd_id` int(30) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai`
--
ALTER TABLE `ai`
  ADD CONSTRAINT `forein` FOREIGN KEY (`uzd_id`) REFERENCES `uzd` (`uzd_id`);

--
-- Constraints for table `user_info`
--
ALTER TABLE `user_info`
  ADD CONSTRAINT `user_id` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
