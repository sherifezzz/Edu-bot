-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2024 at 02:46 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chatbot_login`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password`) VALUES
(1, 'ahmed', '$2y$10$gokBqDcxuwZQsVrodvUnFeclsJtbtIsOjzkC/Af9TK/ItBvJIE6Be'),
(2, 'sherif', '$2y$10$e.ddsFFHnVvVKZkLsd1KBuqKBaIf.pRmruF9qinHcmSnQjBgMZPkG');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `semester` enum('1','2') NOT NULL,
  `level_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `semester`, `level_id`) VALUES
(9, 'English', '1', 1),
(10, 'Introduction to Information Technology', '1', 1),
(11, 'Introduction to Cumputer Tecnology', '1', 1),
(12, 'Math 1', '1', 1),
(13, 'Physics', '1', 1),
(14, 'Programming One', '1', 1),
(15, 'Arabic Language (IT)', '2', 1),
(16, 'Creative and Scientific Thinking (IT)', '2', 1),
(17, 'Introduction to Web Technology', '2', 1),
(18, 'Mathematics (2)', '2', 1),
(19, 'Physics (2)', '2', 1),
(20, 'Probability and Statistics', '2', 1),
(21, 'Programming Techniques (2)', '2', 1),
(22, 'Automata Models', '1', 2),
(23, 'Discrete Mathematics', '1', 2),
(24, 'Electronics', '1', 2),
(25, 'Programming Techniques (3)', '1', 2),
(26, 'Report Writing and Presentation Skills (IT)', '1', 2),
(27, 'Systems and Operations Research', '1', 2),
(28, 'Algorithms and Data Structures', '2', 2),
(29, 'Computer Organization (1)', '2', 2),
(30, 'Human Rights and Ethics IT', '2', 2),
(31, 'Information Economy', '2', 2),
(32, 'Numerical Methods', '2', 2),
(33, 'Operating Systems', '2', 2),
(34, 'Software Engineering (1)', '2', 2),
(35, 'Artificial Intelligence', '1', 3),
(36, 'Computer Graphics', '1', 3),
(37, 'Computer Networks (1)', '1', 3),
(38, 'Computer Organization (2)', '1', 3),
(39, 'Database System', '1', 3),
(40, 'Software Engineering (2)', '1', 3),
(41, 'Computer Networks (2)', '2', 3),
(42, 'Information Ethics', '2', 3),
(43, 'Intelligent Databases', '2', 3),
(44, 'Language Engineering', '2', 3),
(45, 'Microprocessors and Interfacing', '2', 3),
(46, 'Human Computer Interaction', '1', 4),
(47, 'Modeling and Simulation', '1', 4),
(48, 'Multimedia and Virtual Reality', '1', 4),
(49, 'Three Dimensional Graphics', '1', 4),
(50, 'Web Engineering 2', '1', 4),
(51, 'Information Assurance and Security', '2', 4),
(52, 'Integrated Information Systems', '2', 4),
(53, 'Mobile and Sensor Networks', '2', 4),
(54, 'Neural Network', '2', 4),
(55, 'Web Engineering 3', '2', 4);

-- --------------------------------------------------------

--
-- Table structure for table `course_professor`
--

CREATE TABLE `course_professor` (
  `professor_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_professor`
--

INSERT INTO `course_professor` (`professor_id`, `course_id`) VALUES
(22, 35),
(22, 36),
(24, 50),
(24, 51),
(25, 46),
(25, 47),
(25, 48);

-- --------------------------------------------------------

--
-- Table structure for table `levels`
--

CREATE TABLE `levels` (
  `level_id` int(11) NOT NULL,
  `level_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `levels`
--

INSERT INTO `levels` (`level_id`, `level_name`) VALUES
(1, 'Level One'),
(2, 'Level Two'),
(3, 'Level Three'),
(4, 'Level Four');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `log_type` enum('insert','update','delete') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `table_name` enum('professors','courses','students') NOT NULL,
  `row_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `log_type`, `timestamp`, `table_name`, `row_id`, `admin_id`) VALUES
(1, 'update', '2024-05-19 15:33:06', 'courses', 0, 1),
(2, 'update', '2024-05-19 15:53:01', 'courses', 0, 1),
(3, 'update', '2024-05-19 15:54:08', 'courses', 1, 1),
(4, 'delete', '2024-05-19 15:54:28', 'courses', 1, 1),
(5, 'insert', '2024-05-19 15:59:01', 'courses', 2, 1),
(6, 'update', '2024-05-19 15:59:13', 'courses', 2, 1),
(7, 'insert', '2024-05-19 16:01:21', 'courses', 3, 1),
(8, 'update', '2024-05-19 16:01:33', 'courses', 3, 1),
(9, 'insert', '2024-05-19 16:14:33', 'courses', 4, 1),
(10, 'update', '2024-05-19 16:14:46', 'courses', 3, 1),
(11, 'update', '2024-05-19 16:19:36', 'courses', 3, 1),
(12, 'insert', '2024-05-19 17:52:27', 'courses', 5, 1),
(13, 'update', '2024-05-19 17:52:43', 'courses', 5, 1),
(14, 'insert', '2024-05-19 22:31:36', 'courses', 6, 1),
(15, 'insert', '2024-05-19 22:47:52', 'professors', 19, 2),
(16, 'delete', '2024-05-19 22:49:04', 'professors', 16, 2),
(17, 'delete', '2024-05-19 22:49:06', 'professors', 17, 2),
(18, 'insert', '2024-05-20 00:00:17', 'professors', 21, 2),
(19, 'delete', '2024-05-20 00:08:24', 'courses', 2, 2),
(20, 'insert', '2024-05-20 00:28:21', 'courses', 7, 1),
(21, 'delete', '2024-05-20 00:38:28', 'courses', 7, 1),
(22, 'insert', '2024-05-20 00:38:44', 'courses', 8, 1),
(23, 'delete', '2024-05-20 00:50:45', 'courses', 8, 2),
(24, 'delete', '2024-05-20 01:15:49', 'professors', 12, 2),
(25, 'delete', '2024-05-20 01:15:49', 'professors', 18, 2),
(26, 'delete', '2024-05-20 01:15:50', 'professors', 19, 2),
(27, 'delete', '2024-05-20 01:15:51', 'professors', 21, 2),
(28, 'insert', '2024-05-20 14:11:46', 'professors', 22, 2),
(29, 'insert', '2024-05-21 16:45:36', 'courses', 56, 2),
(30, 'delete', '2024-05-21 16:45:43', 'courses', 56, 2),
(31, 'insert', '2024-05-22 18:38:58', 'courses', 57, 2),
(32, 'delete', '2024-05-22 18:39:23', 'courses', 57, 2),
(33, 'insert', '2024-05-22 18:40:13', 'professors', 24, 2),
(34, 'insert', '2024-05-27 17:53:39', 'professors', 25, 2);

-- --------------------------------------------------------

--
-- Table structure for table `professors`
--

CREATE TABLE `professors` (
  `professor_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `professors`
--

INSERT INTO `professors` (`professor_id`, `first_name`, `last_name`, `username`, `email`, `password`) VALUES
(22, 'Sherif', 'Ezz', '2001829', 'shefezz@gmail.com', '$2y$10$QA3tQKrcaogcfJUYCQNxNuWUj/3RjYpDSBn/0IH7lgZBhE/swCwZu'),
(23, 'mo', 'ezz', '123', 'moezz@gmail.com', '$2y$10$hOrrCMQXP8tWZ9s17qauMe/520E.sESo5f41vAotW7aaCbmFcNoHi'),
(24, 'ahmed', 'samir', '1212', 'samir@gmail.com', '$2y$10$ZYl2bQYyrwL2uww6bG/uCey9hrxb.GaX3A33XLwT.wnRUYWoZsrba'),
(25, 'ahmed', 'khaled', '1515', 'sherifezz515@gmail.com', '$2y$10$P9uaaSboFedIkc7Xj5pBaeBYDqZGxxh4rj0GEQS0DOomwyKn.5p3a');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `level_id` int(11) DEFAULT NULL,
  `semester` enum('1','2') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `username`, `email`, `password`, `level_id`, `semester`) VALUES
(8, 'sherif', 'ezz', '2001829', 'sherif@uni.com', '$2y$10$ngmoxa9giZACcRjqH4zFB.o0lPqsSfNNmAgmRCR7DMX/tAixRpS2q', 4, '2'),
(9, 'ahmed', 'khaled', '2001737', 'abofyad203@gmail.com', '$2y$10$Xzbr3vE6B11pli9sBfU52eSHE2I3dMsFCLiuEbI0X/sZVlzxhbeSG', 3, '1'),
(10, 'ahmed', 'samir', '2001653', 'xamanoj885@haikido.com', '$2y$10$Ug1S94h2o3.peLv0ub8z0OTCCt3c5mo58DyqmbhyltnH5JWANEMZ.', 2, '2'),
(11, 'mina', 'medhat', '1122', 'mmm@gmail.com', '$2y$10$oxukZPfXb6QB5.sENrh6feSidYqAA0Hd3IS2Nwcz5kJ59dZMxrDxa', 1, '1'),
(12, 'sherif', 'ezz', '123', 'sherifezz515@gmail.com', '$2y$10$e7AX6v.sni3cUkEDBUvTKeIKJXxgJNcjdiKztN8YKJis6sVM07biO', 4, '2'),
(13, 'alaa', 'ibrahim', '1313', 'hello@gmail.com', '$2y$10$.8nnXXeFaFK55HKTYll3QupSKhrrc9oYwza60zXYh85sj7KL93M4m', 2, '1');

-- --------------------------------------------------------

--
-- Table structure for table `students_courses`
--

CREATE TABLE `students_courses` (
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students_courses`
--

INSERT INTO `students_courses` (`student_id`, `course_id`) VALUES
(12, 48),
(12, 51);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL,
  `task_type` enum('Quiz','Assignment') NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `note` varchar(500) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `professor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`task_id`, `task_type`, `start_date`, `end_date`, `note`, `course_id`, `professor_id`) VALUES
(3, 'Quiz', '2024-05-28 00:00:00', '2024-05-28 00:00:00', 'from chapter one to two', 48, 25),
(11, 'Assignment', '2024-06-24 00:00:00', '2024-06-28 00:00:00', '', 47, 25),
(12, 'Quiz', '2024-06-25 00:00:00', '2024-06-26 00:00:00', '', 46, 25);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `level_id` (`level_id`);

--
-- Indexes for table `course_professor`
--
ALTER TABLE `course_professor`
  ADD PRIMARY KEY (`professor_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `levels`
--
ALTER TABLE `levels`
  ADD PRIMARY KEY (`level_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `professors`
--
ALTER TABLE `professors`
  ADD PRIMARY KEY (`professor_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `level_id` (`level_id`);

--
-- Indexes for table `students_courses`
--
ALTER TABLE `students_courses`
  ADD PRIMARY KEY (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `professor_id` (`professor_id`),
  ADD KEY `course_id` (`course_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `levels`
--
ALTER TABLE `levels`
  MODIFY `level_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `professors`
--
ALTER TABLE `professors`
  MODIFY `professor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`level_id`) REFERENCES `levels` (`level_id`);

--
-- Constraints for table `course_professor`
--
ALTER TABLE `course_professor`
  ADD CONSTRAINT `course_professor_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`professor_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_professor_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`admin_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`level_id`) REFERENCES `levels` (`level_id`);

--
-- Constraints for table `students_courses`
--
ALTER TABLE `students_courses`
  ADD CONSTRAINT `students_courses_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `students_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `professors` (`professor_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
