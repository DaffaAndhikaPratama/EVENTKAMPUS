ALTER TABLE notifications
ADD COLUMN type ENUM('email', 'push', 'web') DEFAULT 'web',
ADD COLUMN status ENUM('sent', 'failed', 'pending') DEFAULT 'sent';