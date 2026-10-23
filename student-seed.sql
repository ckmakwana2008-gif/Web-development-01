-- ============================================================================
-- StudentHub Seed Dataset (Practical 11) - 20 Records
-- DBMS: MySQL / MariaDB
-- ============================================================================

USE `studenthub_db`;

TRUNCATE TABLE `students`;

INSERT INTO `students` (`full_name`, `email`, `phone`, `department`) VALUES
('Aarav Sharma', 'aarav.sharma@example.com', '9876543210', 'Computer Engineering'),
('Priya Patel', 'priya.patel@example.com', '9876543211', 'Information Technology'),
('Rohan Mehta', 'rohan.mehta@example.com', '9876543212', 'Electronics'),
('Ananya Gupta', 'ananya.gupta@example.com', '9876543213', 'Computer Engineering'),
('Vikram Singh', 'vikram.singh@example.com', '9876543214', 'Mechanical'),
('Sanya Verma', 'sanya.verma@example.com', '9876543215', 'Information Technology'),
('Karan Joshi', 'karan.joshi@example.com', '9876543216', 'Civil'),
('Neha Reddy', 'neha.reddy@example.com', '9876543217', 'Electronics'),
('Rahul Deshmukh', 'rahul.deshmukh@example.com', '9876543218', 'Computer Engineering'),
('Isha Kapoor', 'isha.kapoor@example.com', '9876543219', 'Information Technology'),
('Aditya Nair', 'aditya.nair@example.com', '9876543220', 'Mechanical'),
('Pooja Bhat', 'pooja.bhat@example.com', '9876543221', 'Civil'),
('Siddharth Rao', 'siddharth.rao@example.com', '9876543222', 'Computer Engineering'),
('Kavya Shah', 'kavya.shah@example.com', '9876543223', 'Information Technology'),
('Amit Kumar', 'amit.kumar@example.com', '9876543224', 'Electronics'),
('Riya Saxena', 'riya.saxena@example.com', '9876543225', 'Mechanical'),
('Manish Tiwari', 'manish.tiwari@example.com', '9876543226', 'Civil'),
('Tanvi Malhotra', 'tanvi.malhotra@example.com', '9876543227', 'Computer Engineering'),
('Varun Chaudhary', 'varun.chaudhary@example.com', '9876543228', 'Information Technology'),
('Meera Iyer', 'meera.iyer@example.com', '9876543229', 'Electronics');
