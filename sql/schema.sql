CREATE DATABASE IF NOT EXISTS attendance_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE attendance_db;

CREATE TABLE IF NOT EXISTS attendance (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id   VARCHAR(32)  NOT NULL,
    full_name     VARCHAR(100) NOT NULL,
    department    VARCHAR(80)  NOT NULL,
    phone         VARCHAR(20)  NULL,
    type          ENUM('check_in','check_out') NOT NULL,
    attendance_date DATE       NOT NULL,
    recorded_at   DATETIME     NOT NULL,   -- set by the server, never by the browser
    INDEX idx_date (attendance_date),
    INDEX idx_employee_date (employee_id, attendance_date, type)
) ENGINE=InnoDB;
