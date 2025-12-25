-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2025 at 10:13 AM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `obe_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `max_marks` int(11) DEFAULT 100,
  `weightage` decimal(5,2) DEFAULT 10.00,
  `assessment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`id`, `course_id`, `name`, `type`, `max_marks`, `weightage`, `assessment_date`, `created_at`) VALUES
(10, 130, 'Design and Analysis of Algorithm', 'Sessional 1', 10, '10.00', '2025-12-22', '2025-12-22 08:32:54'),
(11, 130, 'Design and Analysis of Algorithm', 'Mid Semester Examination', 30, '30.00', '2025-12-22', '2025-12-22 08:53:00');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(200) NOT NULL,
  `credits` int(11) DEFAULT 4,
  `semester` int(11) NOT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `academic_year` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `program_id`, `code`, `name`, `credits`, `semester`, `faculty_id`, `academic_year`, `created_at`) VALUES
(130, 1, 'CO206', 'Design and Analysis of Algorithm', 4, 4, 3, '2025', '2025-11-24 15:12:53'),
(131, 1, 'CO216', 'FLA', 3, 4, 3, '2025', '2025-12-21 16:27:05');

-- --------------------------------------------------------

--
-- Table structure for table `course_files`
--

CREATE TABLE `course_files` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `course_files`
--

INSERT INTO `course_files` (`id`, `course_id`, `file_type`, `file_name`, `file_path`, `file_size`, `uploaded_by`, `description`, `upload_date`) VALUES
(1, 130, 'Syllabus', '1764601508_report_ai.pdf', 'C:\\xampp\\htdocs\\obe-php\\includes/../uploads/course_files/1764601508_report_ai.pdf', 755936, 3, 'asvdsf', '2025-12-01 15:05:08');

-- --------------------------------------------------------

--
-- Table structure for table `course_outcomes`
--

CREATE TABLE `course_outcomes` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `bloom_level` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `course_outcomes`
--

INSERT INTO `course_outcomes` (`id`, `course_id`, `code`, `description`, `bloom_level`, `created_at`) VALUES
(137, 130, 'CO1', 'Argue the correctness of algorithms rigorously using appropriate proof techniques like invariants, induction etc.', 5, '2025-12-01 14:37:05'),
(138, 130, 'CO2', 'Analyze asymptotic worst-case performance of algorithms in terms of space and time.', 4, '2025-12-01 14:38:07'),
(139, 130, 'CO3', 'Identify and apply appropriate algorithm design paradigms like divide-and-conquer, greedy, dynamic programming for different problem situations.', 4, '2025-12-01 14:39:10'),
(140, 130, 'CO4', 'Analyze hardness of problems and use high level ideas of randomized and approximation algorithms to solve hard problems.', 2, '2025-12-01 14:40:07'),
(141, 130, 'CO5', 'Design and implement an effective combination of algorithms and data structures to solve practical problems.', 6, '2025-12-01 14:40:40'),
(144, 131, 'CO1', 'TDXCFYHGVJB', 2, '2025-12-21 16:28:42');

-- --------------------------------------------------------

--
-- Table structure for table `co_attainment`
--

CREATE TABLE `co_attainment` (
  `id` int(11) NOT NULL,
  `co_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `attainment_percentage` decimal(5,2) DEFAULT NULL,
  `target_threshold` decimal(5,2) DEFAULT 60.00,
  `is_attained` tinyint(1) DEFAULT 0,
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `co_po_mapping`
--

CREATE TABLE `co_po_mapping` (
  `id` int(11) NOT NULL,
  `co_id` int(11) NOT NULL,
  `outcome_type` enum('po','pso') NOT NULL DEFAULT 'po',
  `outcome_id` int(11) NOT NULL,
  `correlation_level` int(11) DEFAULT 2,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `co_po_mapping`
--

INSERT INTO `co_po_mapping` (`id`, `co_id`, `outcome_type`, `outcome_id`, `correlation_level`, `created_at`) VALUES
(16, 137, 'po', 17, 3, '2025-12-01 15:22:55'),
(17, 137, 'po', 18, 2, '2025-12-01 15:22:55'),
(18, 137, 'po', 21, 2, '2025-12-01 15:22:55'),
(19, 137, 'po', 28, 1, '2025-12-01 15:22:55'),
(20, 137, 'pso', 10, 1, '2025-12-01 15:22:55'),
(21, 138, 'po', 17, 3, '2025-12-01 15:22:55'),
(22, 138, 'po', 18, 3, '2025-12-01 15:22:55'),
(23, 138, 'po', 19, 2, '2025-12-01 15:22:55'),
(24, 138, 'po', 20, 3, '2025-12-01 15:22:55'),
(25, 138, 'po', 21, 3, '2025-12-01 15:22:55'),
(26, 138, 'po', 23, 1, '2025-12-01 15:22:55'),
(27, 138, 'po', 28, 1, '2025-12-01 15:22:55'),
(28, 138, 'pso', 10, 1, '2025-12-01 15:22:55'),
(29, 139, 'po', 17, 2, '2025-12-01 15:22:55'),
(30, 139, 'po', 18, 3, '2025-12-01 15:22:55'),
(31, 139, 'po', 19, 1, '2025-12-01 15:22:55'),
(32, 139, 'po', 20, 3, '2025-12-01 15:22:55'),
(33, 139, 'po', 21, 3, '2025-12-01 15:22:55'),
(34, 139, 'po', 23, 1, '2025-12-01 15:22:55'),
(35, 139, 'po', 27, 1, '2025-12-01 15:22:55'),
(36, 139, 'po', 28, 1, '2025-12-01 15:22:55'),
(37, 139, 'pso', 9, 1, '2025-12-01 15:22:55'),
(38, 139, 'pso', 10, 1, '2025-12-01 15:22:55'),
(39, 140, 'po', 17, 3, '2025-12-01 15:22:55'),
(40, 140, 'po', 18, 3, '2025-12-01 15:22:55'),
(41, 140, 'po', 19, 2, '2025-12-01 15:22:55'),
(42, 140, 'po', 20, 3, '2025-12-01 15:22:55'),
(43, 140, 'po', 21, 3, '2025-12-01 15:22:55'),
(44, 140, 'po', 27, 1, '2025-12-01 15:22:55'),
(45, 140, 'pso', 9, 1, '2025-12-01 15:22:55'),
(46, 140, 'pso', 10, 1, '2025-12-01 15:22:55'),
(47, 141, 'po', 17, 3, '2025-12-01 15:22:55'),
(48, 141, 'po', 18, 3, '2025-12-01 15:22:55'),
(49, 141, 'po', 19, 2, '2025-12-01 15:22:55'),
(50, 141, 'po', 20, 3, '2025-12-01 15:22:55'),
(51, 141, 'po', 21, 3, '2025-12-01 15:22:55'),
(52, 141, 'po', 27, 1, '2025-12-01 15:22:55'),
(53, 141, 'pso', 9, 1, '2025-12-01 15:22:55'),
(54, 141, 'pso', 10, 1, '2025-12-01 15:22:55');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `enrollment_date`) VALUES
(4, 4, 130, '2025-12-21 15:15:19'),
(5, 4, 131, '2025-12-21 16:29:52');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `duration` int(11) DEFAULT 4,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `code`, `name`, `department`, `duration`, `created_at`) VALUES
(1, 'CSE', 'Computer Science & Engineering', 'Engineering', 4, '2025-11-22 11:43:17');

-- --------------------------------------------------------

--
-- Table structure for table `program_outcomes`
--

CREATE TABLE `program_outcomes` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `program_outcomes`
--

INSERT INTO `program_outcomes` (`id`, `program_id`, `code`, `description`, `created_at`) VALUES
(17, 1, 'PO1', 'Apply the knowledge of mathematics, science, engineering fundamentals, and an engineering specialization to the solution of complex engineering problems (Engineering knowledge).', '2025-12-01 14:30:29'),
(18, 1, 'PO2', 'Identify, formulate, review research literature, and analyze complex engineering problems reaching substantiated conclusions using first principles of mathematics, natural sciences, and engineering sciences (Problem analysis).', '2025-12-01 14:30:29'),
(19, 1, 'PO3', 'Design solutions for complex engineering problems and design system components or processes that meet the specified needs with appropriate consideration for the public health and safety, and the cultural, societal, and environmental considerations (Design/development of solutions).', '2025-12-01 14:30:29'),
(20, 1, 'PO4', 'Use research-based knowledge and research methods including design of experiments, analysis and interpretation of data, and synthesis of the information to provide valid conclusions (Conduct investigations of complex problems).', '2025-12-01 14:30:29'),
(21, 1, 'PO5', 'Create, select, and apply appropriate techniques, resources, and modern engineering and IT tools including prediction and modeling to complex engineering activities with an understanding of the limitations (Modern tool usage).', '2025-12-01 14:30:29'),
(22, 1, 'PO6', 'Apply reasoning informed by the contextual knowledge to assess societal, health, safety, legal and cultural issues and the consequent responsibilities relevant to the professional engineering practice (The engineer and society).', '2025-12-01 14:30:29'),
(23, 1, 'PO7', 'Understand the impact of the professional engineering solutions in societal and environmental contexts, and demonstrate the knowledge of, and need for sustainable development (Environment and sustainability).', '2025-12-01 14:30:29'),
(24, 1, 'PO8', 'Apply ethical principles and commit to professional ethics and responsibilities and norms of the engineering practice (Ethics).', '2025-12-01 14:30:29'),
(25, 1, 'PO9', 'Function effectively as an individual, and as a member or leader in diverse teams, and in multidisciplinary settings (Individual and teamwork).', '2025-12-01 14:30:29'),
(26, 1, 'PO10', 'Communicate effectively on complex engineering activities with the engineering community and with society at large, such as, being able to comprehend and write effective reports and design documentation, make effective presentations, and give and receive clear instructions (Communication).', '2025-12-01 14:30:29'),
(27, 1, 'PO11', 'Demonstrate knowledge and understanding of the engineering and management principles and apply these to one’s own work, as a member and leader in a team, to manage projects and in multidisciplinary environments (Project management and finance).', '2025-12-01 14:30:29'),
(28, 1, 'PO12', 'Recognize the need for and have the preparation and ability to engage in independent and life-long learning in the broadest context of technological change (Life-long learning).', '2025-12-01 14:30:29');

-- --------------------------------------------------------

--
-- Table structure for table `program_specific_outcomes`
--

CREATE TABLE `program_specific_outcomes` (
  `id` int(11) NOT NULL,
  `program_id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `program_specific_outcomes`
--

INSERT INTO `program_specific_outcomes` (`id`, `program_id`, `code`, `description`, `created_at`) VALUES
(8, 1, 'PSO1', 'Design and develop computer applications using standard software engineering practices to solve real world problems.', '2025-12-01 15:19:05'),
(9, 1, 'PSO2', 'Apply the knowledge of hardware and software to design Computer-based Systems.', '2025-12-01 15:19:31'),
(10, 1, 'PSO3', 'To appreciate and address the issues in Computer Science.', '2025-12-01 15:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `question_papers`
--

CREATE TABLE `question_papers` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `exam_type` varchar(50) DEFAULT NULL,
  `total_marks` int(11) DEFAULT 100,
  `duration` int(11) DEFAULT 180,
  `academic_year` varchar(20) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `question_papers`
--

INSERT INTO `question_papers` (`id`, `course_id`, `title`, `exam_type`, `total_marks`, `duration`, `academic_year`, `semester`, `created_by`, `is_published`, `created_at`) VALUES
(12, 130, 'Design and Analysis of Algorithm', 'Sessional 1', 10, 30, '2025', 'Spring', 3, 0, '2025-12-22 08:32:54'),
(13, 130, 'Design and Analysis of Algorithm', 'Mid Semester Examination', 30, 90, '2025', 'Spring', 3, 0, '2025-12-22 08:53:00');

-- --------------------------------------------------------

--
-- Table structure for table `question_paper_questions`
--

CREATE TABLE `question_paper_questions` (
  `id` int(11) NOT NULL,
  `paper_id` int(11) NOT NULL,
  `co_id` int(11) DEFAULT NULL,
  `question_number` varchar(10) DEFAULT NULL,
  `question_text` text NOT NULL,
  `question_type` varchar(50) DEFAULT NULL,
  `marks` int(11) DEFAULT 1,
  `bloom_level` int(11) DEFAULT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options`)),
  `diagram_path` varchar(500) DEFAULT NULL,
  `display_order` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `question_paper_questions`
--

INSERT INTO `question_paper_questions` (`id`, `paper_id`, `co_id`, `question_number`, `question_text`, `question_type`, `marks`, `bloom_level`, `options`, `diagram_path`, `display_order`, `created_at`) VALUES
(12, 12, 139, NULL, 'zsrxdtcfyvgubhinjuon', 'Long Answer', 5, 4, NULL, NULL, 1, '2025-12-22 08:49:26'),
(14, 12, 139, NULL, 'zsrxdtcfyvgubhinjuon', 'Long Answer', 5, 4, NULL, NULL, 2, '2025-12-22 08:50:15');

-- --------------------------------------------------------

--
-- Table structure for table `student_marks`
--

CREATE TABLE `student_marks` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `assessment_id` int(11) NOT NULL,
  `marks_obtained` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `student_marks`
--

INSERT INTO `student_marks` (`id`, `enrollment_id`, `assessment_id`, `marks_obtained`, `created_at`) VALUES
(14, 4, 10, '8.00', '2025-12-22 08:48:40'),
(15, 4, 11, '25.00', '2025-12-22 08:55:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','faculty','student') NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `program` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `role`, `department`, `program`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$6vA4tIh1r9wkg3kiaUF9tOZSLmFFIUuqdXBr5vemApo1gClQ3eiTW', 'admin@obe.edu', 'System Admin', 'admin', 'Admin', '', 1, '2025-11-22 11:43:17'),
(3, 'bnath', '$2y$10$AOZyIO.MqYlqiVKmj4C98uY.5kMW23yrxfsT7fCIvql8.LPbtPYFC', 'teacher@obe.edu', 'b nath', 'faculty', 'computer science and engineering', '', 1, '2025-11-22 11:51:24'),
(4, 'CSB23201', '$2y$10$V.SlykHSgTf5Jrz93Iv4y.ucMPfiGCXgMr5.s5w/OD.wwWCZ/xzom', 'csb23201@tezu.ac.in', 'Papu Bora', 'student', 'computer science and engineering', '', 1, '2025-11-22 11:51:54'),
(5, 'CSB23202', '$2y$10$6a5P8rOHm1qbtndQBe5.E.6dxIaYuZoLj99VNvhvF0ZDcJh66ctXq', 'csb23202@tezu.ac.in', 'Debasish Newar', 'student', 'computer science and engineering', 'btech', 1, '2025-12-07 14:28:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `course_files`
--
ALTER TABLE `course_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `course_outcomes`
--
ALTER TABLE `course_outcomes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `co_po_mapping`
--
ALTER TABLE `co_po_mapping`
  ADD PRIMARY KEY (`id`),
  ADD KEY `co_id` (`co_id`),
  ADD KEY `po_id` (`outcome_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `program_outcomes`
--
ALTER TABLE `program_outcomes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `program_specific_outcomes`
--
ALTER TABLE `program_specific_outcomes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_id` (`program_id`);

--
-- Indexes for table `question_papers`
--
ALTER TABLE `question_papers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `question_paper_questions`
--
ALTER TABLE `question_paper_questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `paper_id` (`paper_id`),
  ADD KEY `co_id` (`co_id`);

--
-- Indexes for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enrollment_id` (`enrollment_id`),
  ADD KEY `assessment_id` (`assessment_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `course_files`
--
ALTER TABLE `course_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `course_outcomes`
--
ALTER TABLE `course_outcomes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT for table `co_po_mapping`
--
ALTER TABLE `co_po_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `program_outcomes`
--
ALTER TABLE `program_outcomes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `program_specific_outcomes`
--
ALTER TABLE `program_specific_outcomes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `question_papers`
--
ALTER TABLE `question_papers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `question_paper_questions`
--
ALTER TABLE `question_paper_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `student_marks`
--
ALTER TABLE `student_marks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `courses_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_files`
--
ALTER TABLE `course_files`
  ADD CONSTRAINT `course_files_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `course_files_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `course_outcomes`
--
ALTER TABLE `course_outcomes`
  ADD CONSTRAINT `course_outcomes_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `co_po_mapping`
--
ALTER TABLE `co_po_mapping`
  ADD CONSTRAINT `co_po_mapping_ibfk_1` FOREIGN KEY (`co_id`) REFERENCES `course_outcomes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `program_outcomes`
--
ALTER TABLE `program_outcomes`
  ADD CONSTRAINT `program_outcomes_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `program_specific_outcomes`
--
ALTER TABLE `program_specific_outcomes`
  ADD CONSTRAINT `program_specific_outcomes_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_papers`
--
ALTER TABLE `question_papers`
  ADD CONSTRAINT `question_papers_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_papers_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `question_paper_questions`
--
ALTER TABLE `question_paper_questions`
  ADD CONSTRAINT `question_paper_questions_ibfk_1` FOREIGN KEY (`paper_id`) REFERENCES `question_papers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `question_paper_questions_ibfk_2` FOREIGN KEY (`co_id`) REFERENCES `course_outcomes` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `student_marks`
--
ALTER TABLE `student_marks`
  ADD CONSTRAINT `student_marks_ibfk_1` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_marks_ibfk_2` FOREIGN KEY (`assessment_id`) REFERENCES `assessments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
