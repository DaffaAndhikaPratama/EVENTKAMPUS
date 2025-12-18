-- Database Export (Manual Seed)
-- Database: web_event_db

SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for users
-- ----------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `role` enum(
        'admin',
        'event_organizer',
        'mahasiswa'
    ) NOT NULL,
    `campus` varchar(100) DEFAULT NULL,
    `is_verified` tinyint(1) DEFAULT 0,
    `verification_token` varchar(255) DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ----------------------------
-- Records of users
-- Password for all is: 123456 (hashed)
-- ----------------------------
INSERT INTO
    `users` (
        `id`,
        `name`,
        `email`,
        `password`,
        `role`,
        `campus`,
        `is_verified`,
        `verification_token`,
        `created_at`
    )
VALUES (
        1,
        'Super Admin',
        'admin@gmail.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin',
        '-',
        1,
        NULL,
        '2024-01-01 00:00:00'
    ),
    (
        2,
        'Event Organizer Utama',
        'eo@gmail.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'event_organizer',
        'UNSIKA',
        1,
        NULL,
        '2024-01-02 10:00:00'
    ),
    (
        3,
        'Mahasiswa Teladan',
        'mhs@gmail.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'mahasiswa',
        'UNSIKA',
        1,
        NULL,
        '2024-01-03 12:00:00'
    ),
    (
        4,
        'Budi Santoso',
        'budi@gmail.com',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'mahasiswa',
        'UI',
        1,
        NULL,
        '2024-01-05 09:30:00'
    );

-- ----------------------------
-- Table structure for events
-- ----------------------------
CREATE TABLE IF NOT EXISTS `events` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `title` varchar(255) NOT NULL,
    `category` varchar(50) NOT NULL,
    `event_type` enum('online', 'offline') NOT NULL,
    `event_date` datetime NOT NULL,
    `location` varchar(255) DEFAULT NULL,
    `zoom_link` varchar(255) DEFAULT NULL,
    `price` decimal(10, 2) DEFAULT 0.00,
    `description` text NOT NULL,
    `poster` varchar(255) DEFAULT NULL,
    `payment_info_bank` varchar(100) DEFAULT NULL,
    `payment_info_ewallet` varchar(100) DEFAULT NULL,
    `participants` int(11) DEFAULT 0,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    `certificate_link` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `events_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ----------------------------
-- Records of events
-- ----------------------------
INSERT INTO
    `events` (
        `id`,
        `user_id`,
        `title`,
        `category`,
        `event_type`,
        `event_date`,
        `location`,
        `zoom_link`,
        `price`,
        `description`,
        `poster`,
        `payment_info_bank`,
        `payment_info_ewallet`,
        `participants`,
        `created_at`,
        `certificate_link`
    )
VALUES (
        1,
        2,
        'Seminar Nasional Teknologi 2024',
        'Seminar',
        'offline',
        '2024-12-20 09:00:00',
        'Aula Kampus A',
        NULL,
        50000.00,
        'Seminar membahas masa depan AI.',
        'poster1.jpg',
        'BCA 1234567890',
        'DANA 081234567890',
        2,
        '2024-11-01 08:00:00',
        NULL
    ),
    (
        2,
        2,
        'Workshop Coding PHP Laravel',
        'Workshop',
        'online',
        '2024-12-25 13:00:00',
        NULL,
        'https://zoom.us/j/123456',
        0.00,
        'Belajar Laravel dari dasar.',
        'poster2.jpg',
        NULL,
        NULL,
        1,
        '2024-11-05 10:00:00',
        NULL
    ),
    (
        3,
        2,
        'Konser Amal Mahasiswa',
        'Hiburan',
        'offline',
        '2024-12-30 19:00:00',
        'Lapangan Parkir Utama',
        NULL,
        25000.00,
        'Konser penggalangan dana.',
        NULL,
        'BRI 0987654321',
        NULL,
        0,
        '2024-11-10 15:00:00',
        NULL
    );

-- ----------------------------
-- Table structure for event_registrations
-- ----------------------------
CREATE TABLE IF NOT EXISTS `event_registrations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `event_id` int(11) NOT NULL,
    `proof_payment` varchar(255) DEFAULT NULL,
    `status` enum(
        'pending',
        'confirmed',
        'rejected'
    ) DEFAULT 'pending',
    `registered_at` timestamp NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `event_id` (`event_id`),
    CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ----------------------------
-- Records of event_registrations
-- ----------------------------
INSERT INTO
    `event_registrations` (
        `id`,
        `user_id`,
        `event_id`,
        `proof_payment`,
        `status`,
        `registered_at`
    )
VALUES (
        1,
        3,
        1,
        'bukti1.jpg',
        'confirmed',
        '2024-11-02 09:00:00'
    ),
    (
        2,
        4,
        1,
        'bukti2.jpg',
        'pending',
        '2024-11-03 14:00:00'
    ),
    (
        3,
        3,
        2,
        NULL,
        'confirmed',
        '2024-11-06 11:00:00'
    );

-- ----------------------------
-- Table structure for notifications
-- ----------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `message` text NOT NULL,
    `description` text DEFAULT NULL,
    `link` varchar(255) DEFAULT NULL,
    `is_read` tinyint(1) DEFAULT 0,
    `created_at` timestamp NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- ----------------------------
-- Records of notifications
-- ----------------------------
INSERT INTO
    `notifications` (
        `id`,
        `user_id`,
        `message`,
        `description`,
        `link`,
        `is_read`,
        `created_at`
    )
VALUES (
        1,
        3,
        'Pendaftaran Diterima',
        'Selamat, pendaftaran Anda di Seminar Nasional telah diterima.',
        'dashboard.php',
        0,
        '2024-11-02 09:05:00'
    ),
    (
        2,
        2,
        'Peserta Baru',
        'Ada peserta baru mendaftar di event Seminar Nasional.',
        'detail_event.php?id=1',
        0,
        '2024-11-03 14:05:00'
    );

SET FOREIGN_KEY_CHECKS = 1;