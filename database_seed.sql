-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 19, 2025 at 10:00 AM
-- Server version: 8.0.43
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

START TRANSACTION;

SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */
;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */
;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */
;
/*!40101 SET NAMES utf8mb4 */
;

--
-- Database: `web_event_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

CREATE TABLE `articles` (
    `id` int NOT NULL,
    `title` varchar(200) NOT NULL,
    `category` varchar(50) DEFAULT NULL,
    `summary` text,
    `content` text,
    `author` varchar(100) DEFAULT 'Admin',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO
    `articles` (
        `id`,
        `title`,
        `category`,
        `summary`,
        `content`,
        `author`,
        `created_at`
    )
VALUES (
        1,
        'Tips Sukses Kuliah',
        'Tips',
        'Cara membagi waktu antara kuliah dan organisasi.',
        '<p>Manajemen waktu adalah kunci sukses bagi mahasiswa...</p>',
        'Admin',
        NOW()
    ),
    (
        2,
        'Tech Trends 2025',
        'Teknologi',
        'Apa saja teknologi yang akan booming di tahun 2025?',
        '<p>AI dan Machine Learning terus berkembang...</p>',
        'Admin',
        NOW()
    ),
    (
        3,
        'Karir di Bidang IT',
        'Karir',
        'Persiapan menjajaki karir di dunia IT.',
        '<p>Mulai bangun portofolio sejak dini...</p>',
        'Admin',
        NOW()
    );

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
    `id` int NOT NULL,
    `title` varchar(200) NOT NULL,
    `description` text,
    `event_date` datetime NOT NULL,
    `location` varchar(100) DEFAULT NULL,
    `zoom_link` varchar(255) DEFAULT NULL,
    `category` varchar(50) DEFAULT NULL,
    `event_type` enum('offline', 'online') DEFAULT 'offline',
    `price` decimal(10, 2) DEFAULT '0.00',
    `poster` varchar(255) DEFAULT NULL,
    `user_id` int DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `participants` int DEFAULT '0',
    `is_reminder_sent` tinyint(1) DEFAULT '0',
    `certificate_link` text,
    `payment_info_bank` varchar(255) DEFAULT NULL,
    `payment_info_ewallet` varchar(255) DEFAULT NULL,
    `reminder_sent` tinyint(1) DEFAULT '0'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

--
-- Dumping data for table `events`
--

INSERT INTO
    `events` (
        `id`,
        `title`,
        `description`,
        `event_date`,
        `location`,
        `zoom_link`,
        `category`,
        `event_type`,
        `price`,
        `poster`,
        `user_id`,
        `created_at`,
        `participants`,
        `is_reminder_sent`,
        `certificate_link`,
        `payment_info_bank`,
        `payment_info_ewallet`,
        `reminder_sent`
    )
VALUES (
        1,
        'Seminar Teknologi Masa Depan',
        'Seminar mengenai perkembangan AI.',
        '2025-12-30 09:00:00',
        'Aula Utama',
        NULL,
        'Seminar',
        'offline',
        '50000.00',
        'default_poster.jpg',
        2,
        NOW(),
        1,
        0,
        NULL,
        'BCA 123',
        'Ovo 08123',
        0
    ),
    (
        2,
        'Workshop Web Development',
        'Belajar membuat website dari nol.',
        '2025-12-31 10:00:00',
        NULL,
        'https://zoom.us/test',
        'Workshop',
        'online',
        '0.00',
        'default_poster.jpg',
        2,
        NOW(),
        0,
        0,
        NULL,
        NULL,
        NULL,
        0
    ),
    (
        3,
        'Festival Musik Kampus',
        'Dimeriahkan oleh band-band lokal.',
        '2026-01-15 19:00:00',
        'Lapangan Parkir',
        NULL,
        'Hiburan',
        'offline',
        '25000.00',
        'default_poster.jpg',
        2,
        NOW(),
        0,
        0,
        NULL,
        'BCA 123',
        NULL,
        0
    );

-- --------------------------------------------------------

--
-- Table structure for table `event_registrations`
--

CREATE TABLE `event_registrations` (
    `id` int NOT NULL,
    `user_id` int NOT NULL,
    `event_id` int NOT NULL,
    `registered_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `payment_proof` varchar(255) DEFAULT NULL,
    `status` enum(
        'pending',
        'confirmed',
        'rejected'
    ) DEFAULT 'confirmed'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

--
-- Dumping data for table `event_registrations`
--

INSERT INTO
    `event_registrations` (
        `id`,
        `user_id`,
        `event_id`,
        `registered_at`,
        `payment_proof`,
        `status`
    )
VALUES (
        1,
        3,
        1,
        NOW(),
        'proof.jpg',
        'confirmed'
    );

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
    `id` int NOT NULL,
    `user_id` int NOT NULL,
    `message` text NOT NULL,
    `description` text,
    `is_read` tinyint(1) DEFAULT '0',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `link` varchar(255) DEFAULT NULL,
    `is_pushed` tinyint(1) DEFAULT '0'
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO
    `notifications` (
        `id`,
        `user_id`,
        `message`,
        `description`,
        `is_read`,
        `created_at`,
        `link`,
        `is_pushed`
    )
VALUES (
        1,
        3,
        'Selamat Datang',
        'Selamat datang di EventKampus.',
        0,
        NOW(),
        NULL,
        1
    );

-- --------------------------------------------------------

--
-- Table structure for table `participants`
--

CREATE TABLE `participants` (
    `id` int NOT NULL,
    `user_id` int DEFAULT NULL,
    `event_id` int DEFAULT NULL,
    `registered_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
    `id` int NOT NULL,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `campus` varchar(100) DEFAULT NULL,
    `password` varchar(255) DEFAULT NULL,
    `role` enum(
        'admin',
        'mahasiswa',
        'event_organizer'
    ) DEFAULT 'mahasiswa',
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `google_id` varchar(255) DEFAULT NULL,
    `photo` varchar(255) DEFAULT NULL,
    `google_refresh_token` text,
    `description` text,
    `is_verified` tinyint(1) DEFAULT '0',
    `verification_token` varchar(255) DEFAULT NULL,
    `notify_email` tinyint(1) DEFAULT '1',
    `notify_web` tinyint(1) DEFAULT '1',
    `reset_token` varchar(255) DEFAULT NULL,
    `reset_expires_at` datetime DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO
    `users` (
        `id`,
        `name`,
        `email`,
        `campus`,
        `password`,
        `role`,
        `created_at`,
        `is_verified`,
        `notify_email`,
        `notify_web`
    )
VALUES (
        1,
        'Super Admin',
        'admin@eventkampus.com',
        'Universitas Teknologi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin',
        NOW(),
        1,
        1,
        1
    ),
    (
        2,
        'Event Organizer',
        'eo@eventkampus.com',
        'Universitas Teknologi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'event_organizer',
        NOW(),
        1,
        1,
        1
    ),
    (
        3,
        'Mahasiswa Test',
        'mahasiswa@eventkampus.com',
        'Universitas Teknologi',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'mahasiswa',
        NOW(),
        1,
        1,
        1
    );

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_event_details`
-- (See below for the actual view)
--
CREATE TABLE `view_event_details` (
    `category` varchar(50),
    `created_at` timestamp,
    `current_participants` int,
    `description` text,
    `event_date` datetime,
    `event_id` int,
    `location` varchar(100),
    `organizer_id` int,
    `organizer_name` varchar(100),
    `organizer_photo` varchar(255),
    `poster` varchar(255),
    `price` decimal(10, 2),
    `title` varchar(200)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_event_stats`
-- (See below for the actual view)
--
CREATE TABLE `view_event_stats` (
    `event_id` int,
    `total_confirmed` decimal(23, 0),
    `total_pending` decimal(23, 0),
    `total_registrants` bigint,
    `total_rejected` decimal(23, 0)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_participant_list`
-- (See below for the actual view)
--
CREATE TABLE `view_participant_list` (
    `event_id` int,
    `payment_proof` varchar(255),
    `registered_at` timestamp,
    `registration_id` int,
    `registration_status` enum(
        'pending',
        'confirmed',
        'rejected'
    ),
    `user_campus` varchar(100),
    `user_email` varchar(100),
    `user_id` int,
    `user_name` varchar(100),
    `user_photo` varchar(255)
);

-- --------------------------------------------------------

--
-- Structure for view `view_event_details`
--
DROP TABLE IF EXISTS `view_event_details`;

CREATE ALGORITHM = UNDEFINED DEFINER = `root` @`localhost` SQL SECURITY DEFINER VIEW `view_event_details` AS
SELECT
    `e`.`id` AS `event_id`,
    `e`.`title` AS `title`,
    `e`.`category` AS `category`,
    `e`.`event_date` AS `event_date`,
    `e`.`location` AS `location`,
    `e`.`price` AS `price`,
    `e`.`poster` AS `poster`,
    `e`.`description` AS `description`,
    `e`.`participants` AS `current_participants`,
    `e`.`created_at` AS `created_at`,
    `u`.`id` AS `organizer_id`,
    `u`.`name` AS `organizer_name`,
    `u`.`photo` AS `organizer_photo`
FROM (
        `events` `e`
        join `users` `u` on ((`e`.`user_id` = `u`.`id`))
    );

-- --------------------------------------------------------

--
-- Structure for view `view_event_stats`
--
DROP TABLE IF EXISTS `view_event_stats`;

CREATE ALGORITHM = UNDEFINED DEFINER = `root` @`localhost` SQL SECURITY DEFINER VIEW `view_event_stats` AS
SELECT
    `event_registrations`.`event_id` AS `event_id`,
    count(0) AS `total_registrants`,
    sum(
        (
            case
                when (
                    `event_registrations`.`status` = 'confirmed'
                ) then 1
                else 0
            end
        )
    ) AS `total_confirmed`,
    sum(
        (
            case
                when (
                    `event_registrations`.`status` = 'pending'
                ) then 1
                else 0
            end
        )
    ) AS `total_pending`,
    sum(
        (
            case
                when (
                    `event_registrations`.`status` = 'rejected'
                ) then 1
                else 0
            end
        )
    ) AS `total_rejected`
FROM `event_registrations`
GROUP BY
    `event_registrations`.`event_id`;

-- --------------------------------------------------------

--
-- Structure for view `view_participant_list`
--
DROP TABLE IF EXISTS `view_participant_list`;

CREATE ALGORITHM = UNDEFINED DEFINER = `root` @`localhost` SQL SECURITY DEFINER VIEW `view_participant_list` AS
SELECT
    `r`.`id` AS `registration_id`,
    `r`.`event_id` AS `event_id`,
    `r`.`status` AS `registration_status`,
    `r`.`payment_proof` AS `payment_proof`,
    `r`.`registered_at` AS `registered_at`,
    `u`.`id` AS `user_id`,
    `u`.`name` AS `user_name`,
    `u`.`email` AS `user_email`,
    `u`.`campus` AS `user_campus`,
    `u`.`photo` AS `user_photo`
FROM (
        `event_registrations` `r`
        join `users` `u` on ((`r`.`user_id` = `u`.`id`))
    );

--
-- Indexes for dumped tables
--

--
-- Indexes for table `articles`
--
ALTER TABLE `articles` ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
ADD PRIMARY KEY (`id`),
ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `event_registrations`
--
ALTER TABLE `event_registrations`
ADD PRIMARY KEY (`id`),
ADD KEY `user_id` (`user_id`),
ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
ADD PRIMARY KEY (`id`),
ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `participants`
--
ALTER TABLE `participants`
ADD PRIMARY KEY (`id`),
ADD KEY `user_id` (`user_id`),
ADD KEY `event_id` (`event_id`);

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
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- AUTO_INCREMENT for table `event_registrations`
--
ALTER TABLE `event_registrations`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 2;

--
-- AUTO_INCREMENT for table `participants`
--
ALTER TABLE `participants` MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
MODIFY `id` int NOT NULL AUTO_INCREMENT,
AUTO_INCREMENT = 4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `events`
--
ALTER TABLE `events`
ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_registrations`
--
ALTER TABLE `event_registrations`
ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `participants`
--
ALTER TABLE `participants`
ADD CONSTRAINT `participants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
ADD CONSTRAINT `participants_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */
;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */
;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */
;