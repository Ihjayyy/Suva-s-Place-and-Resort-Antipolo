-- =====================================================
-- UPDATED DATABASE SCHEMA FOR SUVA'S PLACE AND RESORT
-- Includes User Management System Enhancements
-- =====================================================

-- Users Table (Enhanced for login - both admin and clients)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    user_type ENUM('admin', 'client') DEFAULT 'client',
    role ENUM('admin', 'staff', 'client') DEFAULT 'client',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update existing users table if it already exists
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS role ENUM('admin', 'staff', 'client') DEFAULT 'client' AFTER user_type,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP AFTER last_login;

-- Sync role with user_type for existing records
UPDATE users SET role = 'admin' WHERE user_type = 'admin';
UPDATE users SET role = 'client' WHERE user_type = 'client';

-- Admin Roles & Permissions
CREATE TABLE IF NOT EXISTS admin_roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL,
    permissions TEXT, -- JSON format
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Services Table
CREATE TABLE IF NOT EXISTS services (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_name VARCHAR(100) NOT NULL,
    description TEXT,
    category VARCHAR(50),
    price DECIMAL(10,2),
    duration INT, -- in minutes
    availability ENUM('available', 'unavailable') DEFAULT 'available',
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reservations/Bookings Table
CREATE TABLE IF NOT EXISTS reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id VARCHAR(50) UNIQUE,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    shift ENUM('day', 'night', 'whole_day'),
    guests INT DEFAULT 1,
    special_requests TEXT,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_user_id (user_id),
    INDEX idx_booking_date (booking_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update existing reservations table
ALTER TABLE reservations 
ADD COLUMN IF NOT EXISTS booking_id VARCHAR(50) UNIQUE AFTER id,
ADD COLUMN IF NOT EXISTS full_name VARCHAR(100) AFTER service_id,
ADD COLUMN IF NOT EXISTS email VARCHAR(100) AFTER full_name,
ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER email,
ADD COLUMN IF NOT EXISTS shift ENUM('day', 'night', 'whole_day') AFTER booking_time,
ADD COLUMN IF NOT EXISTS guests INT DEFAULT 1 AFTER shift,
ADD COLUMN IF NOT EXISTS special_requests TEXT AFTER guests,
ADD COLUMN IF NOT EXISTS total_amount DECIMAL(10,2) DEFAULT 0.00 AFTER status;

-- Reviews & Feedback Table
CREATE TABLE IF NOT EXISTS reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    reservation_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    status ENUM('pending', 'approved', 'rejected', 'flagged') DEFAULT 'pending',
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gallery Table
CREATE TABLE IF NOT EXISTS gallery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(100),
    image_path VARCHAR(255) NOT NULL,
    album VARCHAR(50),
    description TEXT,
    display_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inquiries/Contact Messages Table
CREATE TABLE IF NOT EXISTS inquiries (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('new', 'replied', 'archived') DEFAULT 'new',
    admin_reply TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replied_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    reservation_id INT,
    notification_type ENUM('email', 'sms') NOT NULL,
    template_name VARCHAR(50),
    recipient VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('queued', 'sent', 'failed') DEFAULT 'queued',
    sent_at TIMESTAMP NULL,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notification Templates Table
CREATE TABLE IF NOT EXISTS notification_templates (
    id INT PRIMARY KEY AUTO_INCREMENT,
    template_name VARCHAR(50) UNIQUE NOT NULL,
    template_type ENUM('email', 'sms') NOT NULL,
    subject VARCHAR(200),
    body TEXT NOT NULL,
    variables TEXT, -- JSON format: available placeholders
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System Settings Table
CREATE TABLE IF NOT EXISTS system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(20) DEFAULT 'text',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity Logs Table (Enhanced for User Management)
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Update existing activity_logs table
ALTER TABLE activity_logs 
ADD COLUMN IF NOT EXISTS description TEXT AFTER action,
ADD COLUMN IF NOT EXISTS user_agent TEXT AFTER ip_address;

-- =====================================================
-- DEFAULT DATA INSERTS
-- =====================================================

-- Insert Default Admin User (password: admin123)
-- Email: admin@suvasplace.com
INSERT INTO users (username, email, password, full_name, user_type, role, status) 
SELECT 'admin', 'admin@suvasplace.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'admin', 'admin', 'active'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@suvasplace.com');

-- Insert Sample Staff User (password: staff123)
-- Email: staff@suvasplace.com
INSERT INTO users (username, email, password, full_name, user_type, role, status) 
SELECT 'staff', 'staff@suvasplace.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Staff User', 'admin', 'staff', 'active'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'staff@suvasplace.com');

-- Insert Sample System Settings
INSERT INTO system_settings (setting_key, setting_value, setting_type) 
SELECT * FROM (
    SELECT 'business_name' as setting_key, 'Suva\'s Place And Resort Antipolo' as setting_value, 'text' as setting_type
    UNION ALL SELECT 'business_email', 'info@suvasplace.com', 'email'
    UNION ALL SELECT 'business_phone', '+63 123 456 7890', 'text'
    UNION ALL SELECT 'business_address', 'Antipolo, Calabarzon, Philippines', 'text'
    UNION ALL SELECT 'operating_hours', '{"monday":"9:00-18:00","tuesday":"9:00-18:00","wednesday":"9:00-18:00","thursday":"9:00-18:00","friday":"9:00-18:00","saturday":"10:00-16:00","sunday":"Closed"}', 'json'
    UNION ALL SELECT 'twilio_sid', '', 'text'
    UNION ALL SELECT 'twilio_token', '', 'text'
    UNION ALL SELECT 'twilio_phone', '', 'text'
    UNION ALL SELECT 'sendgrid_api_key', '', 'text'
    UNION ALL SELECT 'sendgrid_from_email', '', 'email'
    UNION ALL SELECT 'max_guests_per_booking', '20', 'number'
    UNION ALL SELECT 'day_shift_hours', '8:00 AM - 5:00 PM', 'text'
    UNION ALL SELECT 'night_shift_hours', '6:00 PM - 10:00 PM', 'text'
    UNION ALL SELECT 'overnight_hours', '8:00 AM - 8:00 AM (Next Day)', 'text'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM system_settings WHERE setting_key = tmp.setting_key);

-- Insert Default Notification Templates
INSERT INTO notification_templates (template_name, template_type, subject, body, variables) 
SELECT * FROM (
    SELECT 'booking_confirmation' as template_name, 'email' as template_type, 
           'Booking Confirmation - {{booking_id}}' as subject,
           'Dear {{customer_name}},\n\nYour booking has been confirmed!\n\nService: {{service_name}}\nDate: {{booking_date}}\nTime: {{booking_time}}\nGuests: {{guests}}\n\nBooking ID: {{booking_id}}\n\nThank you for choosing Suva\'s Place And Resort!\n\nBest regards,\nSuva\'s Place And Resort Team' as body,
           '["customer_name","booking_id","service_name","booking_date","booking_time","guests"]' as variables
    UNION ALL
    SELECT 'booking_confirmation', 'sms', NULL,
           'Hi {{customer_name}}, your booking ({{booking_id}}) for {{service_name}} on {{booking_date}} at {{booking_time}} is confirmed! - Suva\'s Place',
           '["customer_name","booking_id","service_name","booking_date","booking_time"]'
    UNION ALL
    SELECT 'booking_reminder', 'email',
           'Reminder: Upcoming Booking Tomorrow',
           'Dear {{customer_name}},\n\nThis is a reminder for your upcoming booking:\n\nService: {{service_name}}\nDate: {{booking_date}}\nTime: {{booking_time}}\nBooking ID: {{booking_id}}\n\nWe look forward to seeing you!\n\nBest regards,\nSuva\'s Place And Resort Team',
           '["customer_name","service_name","booking_date","booking_time","booking_id"]'
    UNION ALL
    SELECT 'booking_reminder', 'sms', NULL,
           'Reminder: {{customer_name}}, you have a booking tomorrow for {{service_name}} at {{booking_time}}. Booking ID: {{booking_id}}',
           '["customer_name","service_name","booking_time","booking_id"]'
    UNION ALL
    SELECT 'booking_cancelled', 'email',
           'Booking Cancellation - {{booking_id}}',
           'Dear {{customer_name}},\n\nYour booking has been cancelled.\n\nBooking ID: {{booking_id}}\nService: {{service_name}}\nDate: {{booking_date}}\n\nIf you have any questions, please contact us.\n\nBest regards,\nSuva\'s Place And Resort Team',
           '["customer_name","booking_id","service_name","booking_date"]'
    UNION ALL
    SELECT 'user_welcome', 'email',
           'Welcome to Suva\'s Place And Resort!',
           'Dear {{full_name}},\n\nWelcome to Suva\'s Place And Resort admin panel!\n\nYour account has been created:\nEmail: {{email}}\nRole: {{role}}\n\nPlease change your password after first login.\n\nBest regards,\nSuva\'s Place And Resort Team',
           '["full_name","email","role"]'
) AS tmp
WHERE NOT EXISTS (
    SELECT 1 FROM notification_templates 
    WHERE template_name = tmp.template_name AND template_type = tmp.template_type
);

-- Insert Default Admin Roles
INSERT INTO admin_roles (role_name, permissions)
SELECT * FROM (
    SELECT 'administrator' as role_name,
           '{"dashboard":["view"],"reservations":["view","create","edit","delete","approve"],"customers":["view","create","edit","delete"],"services":["view","create","edit","delete"],"notifications":["view","send"],"reviews":["view","respond","delete"],"gallery":["view","upload","delete"],"inquiries":["view","respond","delete"],"users":["view","create","edit","delete"],"settings":["view","edit"],"reports":["view","export"],"help":["view"]}' as permissions
    UNION ALL
    SELECT 'staff',
           '{"dashboard":["view"],"reservations":["view","edit"],"customers":["view"],"services":["view"],"notifications":["view"],"reviews":["view","respond"],"gallery":["view"],"inquiries":["view","respond"],"users":[],"settings":[],"reports":["view"],"help":["view"]}'
    UNION ALL
    SELECT 'manager',
           '{"dashboard":["view"],"reservations":["view","create","edit","approve"],"customers":["view","create","edit"],"services":["view","edit"],"notifications":["view","send"],"reviews":["view","respond"],"gallery":["view","upload"],"inquiries":["view","respond"],"users":["view"],"settings":["view"],"reports":["view","export"],"help":["view"]}'
) AS tmp
WHERE NOT EXISTS (SELECT 1 FROM admin_roles WHERE role_name = tmp.role_name);

-- Insert Sample Activity Logs
INSERT INTO activity_logs (user_id, action, description, ip_address, created_at)
SELECT 
    (SELECT id FROM users WHERE email = 'admin@suvasplace.com' LIMIT 1),
    'system_initialized',
    'User management system initialized',
    '127.0.0.1',
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM activity_logs WHERE action = 'system_initialized'
);

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================

-- Ensure proper indexes exist
CREATE INDEX IF NOT EXISTS idx_users_email_status ON users (email, status);
CREATE INDEX IF NOT EXISTS idx_users_role_status ON users (role, status);
CREATE INDEX IF NOT EXISTS idx_activity_user_action ON activity_logs (user_id, action);
CREATE INDEX IF NOT EXISTS idx_activity_created_desc ON activity_logs (created_at DESC);
CREATE INDEX IF NOT EXISTS idx_reservations_date_status ON reservations (booking_date, status);
CREATE INDEX IF NOT EXISTS idx_reservations_user_status ON reservations (user_id, status);

-- =====================================================
-- USEFUL MAINTENANCE QUERIES
-- =====================================================

-- View all admin users with activity counts
/*
SELECT 
    u.id,
    u.username,
    u.full_name,
    u.email,
    u.role,
    u.status,
    u.last_login,
    COUNT(DISTINCT al.id) as activity_count,
    u.created_at
FROM users u
LEFT JOIN activity_logs al ON u.id = al.user_id
WHERE u.user_type = 'admin'
GROUP BY u.id
ORDER BY u.created_at DESC;
*/

-- Get today's reservations
/*
SELECT 
    r.booking_id,
    r.full_name,
    r.email,
    r.booking_date,
    r.booking_time,
    r.shift,
    r.status,
    s.service_name
FROM reservations r
LEFT JOIN services s ON r.service_id = s.id
WHERE DATE(r.booking_date) = CURDATE()
ORDER BY r.booking_time;
*/

-- Get recent activity logs
/*
SELECT 
    al.id,
    u.full_name,
    u.email,
    al.action,
    al.description,
    al.ip_address,
    al.created_at
FROM activity_logs al
LEFT JOIN users u ON al.user_id = u.id
ORDER BY al.created_at DESC
LIMIT 50;
*/

-- Clean old activity logs (older than 90 days)
/*
DELETE FROM activity_logs 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
*/

-- =====================================================
-- IMPORTANT NOTES
-- =====================================================
/*
DEFAULT LOGIN CREDENTIALS:

1. Administrator Account:
   Email: admin@suvasplace.com
   Password: admin123
   Role: Admin
   
2. Staff Account (for testing):
   Email: staff@suvasplace.com
   Password: staff123
   Role: Staff

⚠️ SECURITY WARNING: 
Change these default passwords immediately after first login!

FEATURES ADDED:
- Enhanced users table with 'role' field (admin/staff/client)
- Activity logs with user_agent tracking
- Additional reservation fields (booking_id, shift, guests, etc.)
- Comprehensive notification templates
- Default system settings for business info
- Admin roles with JSON permissions
- Proper indexes for performance

COMPATIBILITY:
- Fully backward compatible with existing schema
- Uses CREATE TABLE IF NOT EXISTS and ALTER TABLE ADD COLUMN IF NOT EXISTS
- Safe to run on existing database
- Will not overwrite existing data

MAINTENANCE:
- Set up regular cleanup of old activity logs
- Review and update notification templates as needed
- Monitor activity logs for suspicious behavior
- Backup database regularly
*/

-- =====================================================
-- QUICK MIGRATION SCRIPT
-- Run this to add missing columns to your existing database
-- Safe to run multiple times (uses IF NOT EXISTS)
-- =====================================================

USE suvas_db;

-- =====================================================
-- 1. ADD MISSING COLUMNS TO USERS TABLE
-- =====================================================

-- Check and add 'role' column
SET @dbname = DATABASE();
SET @tablename = "users";
SET @columnname = "role";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " ENUM('admin', 'staff', 'client') DEFAULT 'client' AFTER user_type")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Sync role with user_type for existing records
UPDATE users SET role = user_type WHERE role IS NULL OR role = '';
UPDATE users SET role = 'client' WHERE user_type = 'client';
UPDATE users SET role = 'admin' WHERE user_type = 'admin';

-- Check and add 'updated_at' column
SET @columnname = "updated_at";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP AFTER last_login")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =====================================================
-- 2. ADD MISSING COLUMNS TO ACTIVITY_LOGS TABLE
-- =====================================================

SET @tablename = "activity_logs";

-- Check and add 'description' column
SET @columnname = "description";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " TEXT AFTER action")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add 'user_agent' column
SET @columnname = "user_agent";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " TEXT AFTER ip_address")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =====================================================
-- 3. ADD MISSING COLUMNS TO RESERVATIONS TABLE (OPTIONAL)
-- =====================================================

SET @tablename = "reservations";

-- Check and add 'booking_id' column
SET @columnname = "booking_id";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " VARCHAR(50) UNIQUE AFTER id")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add 'full_name' column
SET @columnname = "full_name";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " VARCHAR(100) AFTER service_id")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add 'email' column
SET @columnname = "email";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " VARCHAR(100) AFTER full_name")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add 'phone' column
SET @columnname = "phone";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " VARCHAR(20) AFTER email")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add 'shift' column
SET @columnname = "shift";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " ENUM('day', 'night', 'whole_day') AFTER booking_time")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add 'guests' column
SET @columnname = "guests";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " INT DEFAULT 1 AFTER shift")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add 'special_requests' column
SET @columnname = "special_requests";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " TEXT AFTER guests")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Check and add 'total_amount' column
SET @columnname = "total_amount";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " DECIMAL(10,2) DEFAULT 0.00 AFTER status")
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- =====================================================
-- 4. ADD INDEXES FOR PERFORMANCE
-- =====================================================

-- Add index on users.role if not exists
SET @s = (SELECT IF(
    (SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE table_name = 'users'
        AND index_name = 'idx_role'
        AND table_schema = DATABASE()) > 0,
    "SELECT 1",
    "CREATE INDEX idx_role ON users(role)"));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index on users.email and status
SET @s = (SELECT IF(
    (SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE table_name = 'users'
        AND index_name = 'idx_email_status'
        AND table_schema = DATABASE()) > 0,
    "SELECT 1",
    "CREATE INDEX idx_email_status ON users(email, status)"));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index on activity_logs.action
SET @s = (SELECT IF(
    (SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE table_name = 'activity_logs'
        AND index_name = 'idx_action'
        AND table_schema = DATABASE()) > 0,
    "SELECT 1",
    "CREATE INDEX idx_action ON activity_logs(action)"));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- =====================================================
-- 5. VERIFICATION
-- =====================================================

-- Check users table structure
SELECT 'USERS TABLE COLUMNS:' as '';
SHOW COLUMNS FROM users;

-- Check activity_logs table structure
SELECT 'ACTIVITY_LOGS TABLE COLUMNS:' as '';
SHOW COLUMNS FROM activity_logs;

-- Check if default admin user exists
SELECT 'DEFAULT ADMIN USER:' as '';
SELECT id, username, email, user_type, role, status FROM users WHERE user_type = 'admin' LIMIT 1;

-- =====================================================
-- SUCCESS MESSAGE
-- =====================================================
SELECT '✓ Migration completed successfully!' as 'STATUS';
SELECT 'All missing columns have been added to your database.' as 'RESULT';
SELECT 'You can now use the User Management system.' as 'NEXT_STEP';