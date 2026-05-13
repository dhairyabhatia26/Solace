-- Database: `solace`
CREATE DATABASE IF NOT EXISTS `solace`;
USE `solace`;

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','counselor','admin') NOT NULL DEFAULT 'student',
  `department` varchar(100) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `users`
-- Hash for password123 is used here
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `department`) VALUES
(1, 'Student Demo', 'student@solace.com', '$2y$10$19dplu6cZtEuiUDehlRAs.npOMu2aPf4xQD1PzXRGWEvWF4GDP1JG', 'student', 'Computer Science'),
(2, 'Counselor Demo', 'counselor@solace.com', '$2y$10$19dplu6cZtEuiUDehlRAs.npOMu2aPf4xQD1PzXRGWEvWF4GDP1JG', 'counselor', 'Student Wellness'),
(3, 'Admin Demo', 'admin@solace.com', '$2y$10$19dplu6cZtEuiUDehlRAs.npOMu2aPf4xQD1PzXRGWEvWF4GDP1JG', 'admin', 'Administration');

-- Table structure for table `wellness_cases`
CREATE TABLE `wellness_cases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `assigned_counselor_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `category` enum('academics','stress','anxiety','sleep','relationships','career','financial pressure','family','health','other') NOT NULL,
  `description` text NOT NULL,
  `support_mode` enum('anonymous','counselor callback','faculty mentor','resource recommendation only') NOT NULL,
  `urgency` enum('low','medium','high') NOT NULL DEFAULT 'low',
  `stress_score` int(11) DEFAULT NULL,
  `sleep_score` int(11) DEFAULT NULL,
  `academic_pressure_score` int(11) DEFAULT NULL,
  `status` enum('submitted','under review','assigned','in progress','resolved','closed') NOT NULL DEFAULT 'submitted',
  `severity` enum('low','moderate','high','critical') DEFAULT NULL,
  `escalation_flag` tinyint(1) NOT NULL DEFAULT 0,
  `ai_summary` text DEFAULT NULL,
  `ai_guidance` text DEFAULT NULL,
  `ai_risk_pattern` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`assigned_counselor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `wellness_cases`
INSERT INTO `wellness_cases` (`student_id`, `assigned_counselor_id`, `title`, `category`, `description`, `support_mode`, `urgency`, `stress_score`, `sleep_score`, `academic_pressure_score`, `status`, `severity`) VALUES
(1, 2, 'Struggling with upcoming exams', 'academics', 'I am feeling overwhelmed with the amount of syllabus left for the final exams. My sleep schedule is a mess.', 'counselor callback', 'high', 8, 3, 9, 'assigned', 'moderate'),
(1, NULL, 'Cannot sleep properly', 'sleep', 'Waking up multiple times at night. Feeling tired during lectures.', 'resource recommendation only', 'medium', 6, 2, 5, 'submitted', 'low'),
(1, 2, 'Unsure about placement options', 'career', 'I do not know if I should take up the job offer or go for higher studies. It is causing me a lot of stress.', 'counselor callback', 'medium', 7, 5, 4, 'in progress', 'moderate'),
(1, NULL, 'Unable to pay next semester fees', 'financial pressure', 'My family is facing financial difficulties and I am worried I will have to drop out.', 'counselor callback', 'high', 9, 4, 8, 'under review', 'high'),
(1, 2, 'Arguments with roommate', 'relationships', 'Constant arguments with my roommate are making it hard to focus on studies.', 'faculty mentor', 'low', 5, 6, 3, 'resolved', 'low'),
(1, NULL, 'Feeling completely burnt out', 'stress', 'I have no motivation left to attend classes or do assignments.', 'counselor callback', 'high', 10, 3, 9, 'submitted', 'critical'),
(1, 2, 'Homesick and lonely', 'family', 'Missing my family a lot. Hard to adjust to hostel life.', 'counselor callback', 'medium', 6, 6, 4, 'closed', 'low');

-- Table structure for table `case_notes`
CREATE TABLE `case_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `counselor_id` int(11) NOT NULL,
  `note_type` enum('internal','student_visible') NOT NULL DEFAULT 'internal',
  `note` text NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`case_id`) REFERENCES `wellness_cases`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`counselor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `resources`
CREATE TABLE `resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `resources`
INSERT INTO `resources` (`title`, `category`, `description`, `link`, `created_by`) VALUES
('Stress Management Guide', 'stress', 'A comprehensive guide on managing academic and personal stress.', '#', 3),
('Sleep Hygiene Checklist', 'sleep', 'Best practices for a good night\'s rest to improve focus.', '#', 3),
('Exam Preparation Support', 'academics', 'Tips and tricks to organize your study schedule effectively.', '#', 3),
('Anxiety Awareness', 'anxiety', 'Understanding anxiety symptoms and grounding techniques.', '#', 3),
('Career Uncertainty Support', 'career', 'Resources to help you navigate career choices and placement stress.', '#', 3);

-- Table structure for table `feedback`
CREATE TABLE `feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`case_id`) REFERENCES `wellness_cases`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `activity_logs`
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `settings`
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `theme_preference` enum('light','dark') NOT NULL DEFAULT 'light',
  `created_at` timestamp DEFAULT current_timestamp(),
  `updated_at` timestamp DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`user_id`, `theme_preference`) VALUES
(1, 'light'),
(2, 'light'),
(3, 'light');

-- Table structure for table `case_resources`
CREATE TABLE `case_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `case_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `recommended_by` int(11) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`case_id`) REFERENCES `wellness_cases`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`resource_id`) REFERENCES `resources`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`recommended_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;