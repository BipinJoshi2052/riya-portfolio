-- Riya Portfolio database schema (MySQL / MariaDB — cPanel compatible)
-- Import this via cPanel's phpMyAdmin, or `mysql -u user -p dbname < schema.sql`

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS page_views (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    page VARCHAR(255) NOT NULL DEFAULT '/',
    user_agent VARCHAR(512) NULL,
    referrer VARCHAR(512) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_ip_created (ip_address, created_at),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cache of IP -> resolved location so we only ever call the geolocation
-- APIs once per unique visitor IP, no matter how many page views they rack up.
CREATE TABLE IF NOT EXISTS ip_locations (
    ip_address VARCHAR(45) NOT NULL PRIMARY KEY,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    display_name VARCHAR(512) NULL,
    city VARCHAR(150) NULL,
    region VARCHAR(150) NULL,
    country VARCHAR(150) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending', -- pending | resolved | failed | private
    resolved_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL,
    subject VARCHAR(255) NULL,
    message TEXT NOT NULL,
    ip_address VARCHAR(45) NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
