-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 08, 2025 at 06:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `event_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `admin_email` varchar(100) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `admin_email`, `admin_password`, `created_at`) VALUES
(2, 'MCA', 'mca@gmail.com', '$2y$10$42.mXbk0ro8jnzJ.LASN4.P2C20KAgo07umQC15/nLQjz1AlC9lCe', '2025-06-24 12:33:35'),
(3, 'Mechanical', 'mechanical@gmail.com', '$2y$10$0YQB4DZZVEkJjyScRCZTMO2Qs.v2tiDmqYZ9pBnCzWwekt8jAy0Ue', '2025-07-05 10:16:54');

-- --------------------------------------------------------

--
-- Table structure for table `department_contacts`
--

CREATE TABLE `department_contacts` (
  `contact_id` int(11) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `student_email` varchar(100) NOT NULL,
  `department_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department_contacts`
--

INSERT INTO `department_contacts` (`contact_id`, `student_name`, `student_email`, `department_id`, `message`, `submitted_at`, `is_read`) VALUES
(1, 'Tejas Mahesh Bhide', 'tejasbhide21@gmail.com', 2, 'hello good website i loved it thank you.', '2025-07-01 22:01:17', 1),
(2, 'Manjula Shankar Swami', 'manjula27092002@gmail.com', 2, 'hey events are really good.', '2025-07-02 23:25:33', 1),
(3, 'Manjula Swami', 'a240286@famt.ac.in', 2, 'hey \r\nweb site is really cool i liked it.', '2025-07-02 23:40:20', 1),
(4, 'Niranjan Dipak Surve', 'dipasurve99@gmail.com', 2, 'I am enrolled for the Treasurer Hunt Event', '2025-07-04 14:10:43', 1);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `event_title` varchar(150) NOT NULL,
  `event_description` text NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `event_end_time` time NOT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `enrollment_deadline` datetime DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `event_title`, `event_description`, `event_date`, `event_time`, `event_end_time`, `max_participants`, `enrollment_deadline`, `department_id`, `created_at`, `notified`) VALUES
(7, 'java', 'java competition', '2025-06-24', '10:00:00', '13:00:00', NULL, '2025-06-23 23:00:00', 2, '2025-06-26 06:55:03', 0),
(8, 'web development competition', 'web development in html css js', '2025-06-28', '10:00:00', '13:00:00', 30, '2025-06-27 23:00:00', 2, '2025-06-26 09:30:58', 1),
(10, 'python', 'python competition', '2025-06-29', '10:00:00', '13:00:00', NULL, '2025-06-28 23:00:00', 2, '2025-06-26 10:02:26', 1),
(11, 'c#', 'c# .net', '2025-06-30', '09:00:00', '13:00:00', NULL, '2025-06-29 23:00:00', 2, '2025-06-26 10:36:55', 1),
(12, 'ppt', 'Power point presentation', '2025-06-26', '10:00:00', '20:40:00', NULL, '2025-06-25 23:00:00', 2, '2025-06-26 13:29:07', 0),
(13, 'Quiz competition', 'Quiz competition', '2025-06-28', '09:00:00', '13:00:00', NULL, '2025-06-27 23:00:00', 2, '2025-06-26 17:28:24', 1),
(14, 'Treasure Hunt', 'Treasure hunt in college premises.', '2025-07-05', '08:00:00', '13:00:00', NULL, '2025-07-04 23:55:00', 2, '2025-07-04 07:11:46', 1),
(15, 'Code It', 'C++ Coding competition', '2025-07-07', '09:00:00', '12:00:00', 1, '2025-07-06 23:55:00', 2, '2025-07-04 12:43:36', 1);

-- --------------------------------------------------------

--
-- Table structure for table `event_enrollments`
--

CREATE TABLE `event_enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_enrollments`
--

INSERT INTO `event_enrollments` (`enrollment_id`, `student_id`, `event_id`, `enrolled_at`) VALUES
(14, 2, 8, '2025-06-26 16:52:47'),
(15, 2, 10, '2025-06-26 16:52:48'),
(17, 3, 8, '2025-06-26 17:26:51'),
(18, 3, 11, '2025-06-26 17:26:52'),
(19, 3, 10, '2025-06-26 17:26:53'),
(21, 2, 11, '2025-06-29 13:04:43'),
(22, 4, 14, '2025-07-04 08:31:28'),
(23, 2, 14, '2025-07-04 11:55:54'),
(24, 3, 14, '2025-07-04 12:09:46'),
(25, 5, 14, '2025-07-04 12:39:15'),
(26, 5, 15, '2025-07-04 12:48:02');

-- --------------------------------------------------------

--
-- Table structure for table `event_reports`
--

CREATE TABLE `event_reports` (
  `report_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `report_details` text NOT NULL,
  `pdf_file_path` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_reports`
--

INSERT INTO `event_reports` (`report_id`, `event_id`, `report_details`, `pdf_file_path`, `submitted_at`) VALUES
(1, 10, 'Event has been completed successfully.\r\nIt is a great success.\r\nI would like to thank all the organisers who helped to achieve remarkable success in this event.', NULL, '2025-07-03 15:28:54');

-- --------------------------------------------------------

--
-- Table structure for table `event_suggestions`
--

CREATE TABLE `event_suggestions` (
  `suggestion_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `description` text NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `status` enum('Under Review','Accepted','Rejected') DEFAULT 'Under Review',
  `admin_remarks` text DEFAULT NULL,
  `submitted_at` datetime DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_suggestions`
--

INSERT INTO `event_suggestions` (`suggestion_id`, `student_id`, `department_id`, `title`, `event_type`, `description`, `preferred_date`, `status`, `admin_remarks`, `submitted_at`, `reviewed_at`) VALUES
(3, 2, 2, 'Python Coder', '', 'Python coding only.', '0000-00-00', 'Under Review', NULL, '2025-07-06 01:06:51', NULL),
(4, 6, 3, 'Car Racing', '', 'Car Racing', '0000-00-00', 'Under Review', NULL, '2025-07-06 01:24:29', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `feedback_text` text NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `full_name`, `email`, `password`, `department_id`, `created_at`) VALUES
(2, 'Tejas M. Bhide', 'tejas@gmail.com', '$2y$10$fVM2rqUE/C6VW2h9XjtZoeGN9EteAzz4E9TkLMea7CkMhoEru0Z9C', 2, '2025-06-24 12:48:03'),
(3, 'Tejas Bhide', 'bhidetejas111@gmail.com', '$2y$10$0VicCj5e4doRjU8CcfRJmO2G/b9JK5kGP9dDmVnRg2E4KnJrG0XjK', 2, '2025-06-26 17:24:31'),
(4, 'Niranjan Dipak Surve', 'dipasurve99@gmail.com', '$2y$10$lkO0tI2yLLG30IMvRY1NLur8ROuaWpAO53lnUoRgX183gb6W1c/Ji', 2, '2025-07-04 08:30:35'),
(5, 'Manjula Shankar Swami', 'manjula27092002@gmail.com', '$2y$10$VC4UrYhz2RfcfCRTdYAL9.olcKay5sm8wNcTW3/TWs1j3HXTQjeWW', 2, '2025-07-04 12:37:17'),
(6, 'Tejas Bhide', 'tejasbhide21@gmail.com', '$2y$10$x51BtF8R1rrIjnq2ktS9NO1UKm4Bbupo5WfzL4XNqoOVWsuYH8Ii2', 3, '2025-07-05 19:52:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`),
  ADD UNIQUE KEY `admin_email` (`admin_email`);

--
-- Indexes for table `department_contacts`
--
ALTER TABLE `department_contacts`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `event_enrollments`
--
ALTER TABLE `event_enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_reports`
--
ALTER TABLE `event_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_suggestions`
--
ALTER TABLE `event_suggestions`
  ADD PRIMARY KEY (`suggestion_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD UNIQUE KEY `student_id` (`student_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `department_contacts`
--
ALTER TABLE `department_contacts`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `event_enrollments`
--
ALTER TABLE `event_enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `event_reports`
--
ALTER TABLE `event_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `event_suggestions`
--
ALTER TABLE `event_suggestions`
  MODIFY `suggestion_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `department_contacts`
--
ALTER TABLE `department_contacts`
  ADD CONSTRAINT `department_contacts_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_enrollments`
--
ALTER TABLE `event_enrollments`
  ADD CONSTRAINT `event_enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_enrollments_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `event_reports`
--
ALTER TABLE `event_reports`
  ADD CONSTRAINT `event_reports_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `event_suggestions`
--
ALTER TABLE `event_suggestions`
  ADD CONSTRAINT `event_suggestions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `event_suggestions_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
