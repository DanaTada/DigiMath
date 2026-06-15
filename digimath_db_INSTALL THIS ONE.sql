-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2026 at 01:56 AM
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
-- Table structure for table `ai_prompts`
--

CREATE TABLE `ai_prompts` (
  `id` int(11) NOT NULL,
  `grade_min` int(11) NOT NULL,
  `grade_max` int(11) NOT NULL,
  `prompt` text NOT NULL,
  `temperature` float DEFAULT 0.7,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ai_prompts`
--

INSERT INTO `ai_prompts` (`id`, `grade_min`, `grade_max`, `prompt`, `temperature`, `created_at`) VALUES
(1, 1, 4, 'Tu esi draudzīgs un pacietīgs 1.-4. klases matemātikas skolotājs. \r\nPaskaidro latviski ļoti vienkārši, ar piemēriem. \r\nLieto vienkāršus vārdus. \r\nEsi iecietīgs un uzmundrinošs.', 0.8, '2026-06-15 23:44:53'),
(2, 5, 6, 'Tu esi matemātikas skolotājs 5.-6. klasei.\r\nPaskaidro latviski saprotami, bet var izmantot precīzākus terminus.\r\nSoli pa solim izskaidro risinājumu.\r\nEsi atbalstošs un skaidrs.', 0.7, '2026-06-15 23:44:54'),
(3, 7, 9, 'Tu esi matemātikas skolotājs 7.-9. klasei.\r\nPaskaidro latviski precīzi un profesionāli.\r\nParādi risinājuma gaitu.\r\nJa ir kļūda, paskaidro kur un kāpēc.\r\nEsi strukturēts un loģisks.', 0.6, '2026-06-15 23:44:54');

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
(12, 'admin', 'admin', 0),
(13, 'admin1', 'admin1', 0),
(14, 'admin2', 'admin2', 0),
(15, 'admin3', 'admin3', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_info`
--

CREATE TABLE `user_info` (
  `user_ID` int(30) UNSIGNED NOT NULL,
  `grade` varchar(10) NOT NULL,
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
(12, '9', '1,2,17,18,35,33,3,23', 'Admin Skola', 'admin@admin.lv', 'admin', 'Admin Uzvards'),
(13, '9', '', 'Admin1', 'admin@admin.lv', 'admin1', 'admin1'),
(14, '3', '3,4,5', 'Admin  2', 'admin2@admin.lv', 'admin2', 'admin2'),
(15, '1', '', 'Skola1', 'email1@gamil.com', 'admin3', 'T');

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
-- Indexes for table `ai_prompts`
--
ALTER TABLE `ai_prompts`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `ai_prompts`
--
ALTER TABLE `ai_prompts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_ID` int(30) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `uzd`
--
ALTER TABLE `uzd`
  MODIFY `uzd_id` int(30) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_info`
--
ALTER TABLE `user_info`
  ADD CONSTRAINT `user_id` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
