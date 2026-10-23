
CREATE DATABASE IF NOT EXISTS `studenthub_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `studenthub_db`;

CREATE TABLE IF NOT EXISTS `students` (
  `student_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(15) NOT NULL,
  `department` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `events` (
  `event_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `event_date` DATETIME NOT NULL,
  `location` VARCHAR(100) NOT NULL,
  `max_capacity` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `registrations` (
  `registration_id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT UNSIGNED NOT NULL,
  `event_id` INT UNSIGNED NOT NULL,
  `registration_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('CONFIRMED', 'CANCELLED', 'WAITLISTED') DEFAULT 'CONFIRMED',
  CONSTRAINT `fk_registrations_student`
    FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_registrations_event`
    FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `unique_student_event_registration` 
    UNIQUE (`student_id`, `event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `students` (`full_name`, `email`, `phone`, `department`) VALUES
('Aarav Sharma', 'aarav.sharma@example.com', '9876543210', 'Computer Engineering'),
('Priya Patel', 'priya.patel@example.com', '9876543211', 'Information Technology'),
('Rohan Mehta', 'rohan.mehta@example.com', '9876543212', 'Electronics');

INSERT INTO `events` (`title`, `category`, `event_date`, `location`, `max_capacity`) VALUES
('Web Dev Workshop 2026', 'Technical', '2026-11-15 10:00:00', 'Lab 3, IT Dept', 60),
('Annual Hackathon', 'Competition', '2026-12-01 09:00:00', 'Main Auditorium', 120),
('Cyber Security Seminar', 'Academic', '2026-11-20 14:00:00', 'Seminar Hall B', 80);

INSERT INTO `registrations` (`student_id`, `event_id`, `status`) VALUES
(1, 1, 'CONFIRMED'),
(1, 2, 'CONFIRMED'),
(2, 1, 'CONFIRMED'),
(3, 3, 'CONFIRMED');
