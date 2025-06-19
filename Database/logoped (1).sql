-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 19, 2025 at 02:57 AM
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
-- Database: `logoped`
--

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`id`, `name`) VALUES
(3, '1 А Клас'),
(1, '1 Б Клас'),
(2, '1 В Клас'),
(7, '2 А Клас'),
(8, '2 Б Клас'),
(9, '2 В Клас'),
(5, '3 А Клас'),
(6, '3 Б Клас'),
(4, '3 В Клас'),
(10, '4 А Клас'),
(11, '4 Б Клас'),
(12, '4 В Клас'),
(13, '5 А Клас'),
(14, '5 Б Клас'),
(15, '5 В Клас'),
(16, '6 А Клас'),
(17, '6 Б Клас'),
(18, '6 В Клас'),
(19, '7 А Клас'),
(20, '7 Б Клас'),
(21, '7 В Клас'),
(22, '8 А Клас'),
(23, '8 Б Клас'),
(24, '8 В Клас');

-- --------------------------------------------------------

--
-- Table structure for table `examinations`
--

CREATE TABLE `examinations` (
  `id` int(10) UNSIGNED NOT NULL,
  `logoped_id` int(10) UNSIGNED NOT NULL,
  `student_id` int(10) UNSIGNED NOT NULL,
  `topic` varchar(255) NOT NULL,
  `exam_date` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `examinations`
--

INSERT INTO `examinations` (`id`, `logoped_id`, `student_id`, `topic`, `exam_date`, `notes`, `created_at`) VALUES
(25, 2, 13, 'Артикулация звук Р- Добър прогрес има нужда от наблюдение', '2025-06-17 08:34:53', '', '2025-06-17 05:34:53'),
(26, 2, 11, 'Артикулация звук З - Има нужда от повторение', '2025-06-17 08:36:21', '', '2025-06-17 05:36:21'),
(27, 2, 13, 'Артикулация звук з', '2025-06-17 09:06:07', '', '2025-06-17 06:06:07');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(10) UNSIGNED NOT NULL,
  `category` enum('articulation','placement','differentiation') NOT NULL,
  `title` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `category`, `title`, `filepath`, `created_at`) VALUES
(4, 'articulation', 'Артикулация на звук \'\'А\'\'', '685100643b127-6850ff7fb0469-логопед-Мерт.pdf', '2025-06-17 05:43:00'),
(6, 'placement', 'Постановка на звук И', '685100cf4dcaf-Упражнение от логопедично занятие за заекване.docx', '2025-06-17 05:44:47'),
(7, 'differentiation', 'Диф 1', '685100df776cf-Упражнение от логопедично занятие за заекване.docx', '2025-06-17 05:45:03');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `parent_name` varchar(100) NOT NULL,
  `parent_phone` varchar(20) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `doc1` varchar(255) DEFAULT NULL,
  `doc2` varchar(255) DEFAULT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `address`, `parent_name`, `parent_phone`, `profile_image`, `doc1`, `doc2`, `class_id`, `created_at`) VALUES
(11, 'Георги Тодоров Тодоров', 'Ковачевска 18', 'Kремена Петрова', '0898970897', '6850fd248d6a4-AW5A4201.jpg', '6850254937b6e-Jordan_Yankov.pdf', '682cbcfa1fd69-AR_Music_App_Description.pdf', 4, '2025-05-20 17:33:23'),
(12, 'Николета Маринова', 'Цар Освободител 5', 'Теодора Маринова', '0812361232', '6850fd6182558-21-Portland+Kids+Acting+Headshots-Michael+Verity+Photography.jpg', NULL, NULL, 15, '2025-06-17 05:30:09'),
(13, 'Андреа Маринова', 'Цар Освободител 5', 'Теодора Маринова', '047124174128', '6850fd89212a5-_DSC4354-Edit-2+-+Chloe+-+(c)+2021+Michael+Verity+Photography.+All+Rights+Reserved.jpg', '6850ff52b107c-КАРТА ЗА ОПРЕДЕЛЯНЕ НА ЛОГОПЕДИЧЕН СТАТУС - Мерт.docx', '6850ffe9d15db-6850ff7fb0469-логопед-Мерт (1).pdf', 4, '2025-06-17 05:30:49'),
(14, 'Михаил Стефанов', 'Братя Миладинови 87', 'Надя Стефанова', '07123461123', '6850fde4672d1-Tracy_Wright_Corvo_Photography_child_actor_headshot-5.jpg', NULL, NULL, 19, '2025-06-17 05:32:20'),
(15, 'Димитринка Христова', 'Македонска 11', 'Христо Димитров', '056768978987', '6850fe3a832ae-childrens_headshots_009-Resized.jpeg', NULL, NULL, 23, '2025-06-17 05:33:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('logoped','director') NOT NULL DEFAULT 'logoped',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'direktor', 'direktor@abv.bg', '$2y$10$MuXkk1G/AWWexbrL08/3bu0KYTp8J7WcmFQhadbD.jBTint8hlJfy', 'director', '2025-05-01 16:27:35'),
(2, 'Veselinova', 'veselina@abv.bg', '$2y$10$MuXkk1G/AWWexbrL08/3bu0KYTp8J7WcmFQhadbD.jBTint8hlJfy', 'logoped', '2025-05-02 13:21:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `examinations`
--
ALTER TABLE `examinations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_exams_logoped` (`logoped_id`),
  ADD KEY `fk_exams_student` (`student_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_students_class` (`class_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `examinations`
--
ALTER TABLE `examinations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `examinations`
--
ALTER TABLE `examinations`
  ADD CONSTRAINT `examinations_ibfk_1` FOREIGN KEY (`logoped_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exams_logoped` FOREIGN KEY (`logoped_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_exams_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_student_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  ADD CONSTRAINT `fk_students_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
