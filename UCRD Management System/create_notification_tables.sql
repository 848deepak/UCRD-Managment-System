-- Create notification_log table
CREATE TABLE IF NOT EXISTS notification_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL COMMENT 'Type of notification (e.g., new_publication, project_status_change)',
    entity_id INT NOT NULL COMMENT 'ID of the related entity (publication, project, researcher)',
    message TEXT NOT NULL COMMENT 'Notification message text',
    additional_data JSON DEFAULT NULL COMMENT 'Additional data in JSON format',
    created_at DATETIME NOT NULL COMMENT 'When the notification was created',
    INDEX idx_type (type),
    INDEX idx_entity_id (entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create notification_preferences table
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'ID of the user',
    new_publication TINYINT(1) DEFAULT 1 COMMENT 'Notify on new publications',
    project_status_change TINYINT(1) DEFAULT 1 COMMENT 'Notify on project status changes',
    new_researcher TINYINT(1) DEFAULT 1 COMMENT 'Notify on new researchers',
    email_notifications TINYINT(1) DEFAULT 1 COMMENT 'Send email notifications',
    daily_digest TINYINT(1) DEFAULT 0 COMMENT 'Send daily digest instead of immediate notifications',
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create email_templates table
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL COMMENT 'Template name/identifier',
    subject VARCHAR(255) NOT NULL COMMENT 'Email subject template',
    body TEXT NOT NULL COMMENT 'Email body template (HTML)',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'Template status',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default email templates
INSERT INTO email_templates (name, subject, body, status, created_at) VALUES
('default', 'UCRD Management System Notification', '<html><body><h2>Hello {{user_name}},</h2><p>{{message}}</p><p>This is an automated notification from the UCRD Management System.</p><p>Best regards,<br>UCRD Management System</p></body></html>', 'active', NOW()),
('new_publication', 'New Publication Added: {{title}}', '<html><body><h2>Hello {{user_name}},</h2><p>A new publication has been added to the UCRD Management System:</p><p><strong>Title:</strong> {{title}}<br><strong>Researcher:</strong> {{researcher}}<br><strong>Supervisor:</strong> {{supervisor}}</p><p>You can view the publication details in the system.</p><p>Best regards,<br>UCRD Management System</p></body></html>', 'active', NOW()),
('project_status_change', 'Project Status Update: {{title}}', '<html><body><h2>Hello {{user_name}},</h2><p>The status of a project has been updated in the UCRD Management System:</p><p><strong>Project:</strong> {{title}}<br><strong>Researcher:</strong> {{researcher}}<br><strong>Supervisor:</strong> {{supervisor}}<br><strong>Old Status:</strong> {{old_status}}<br><strong>New Status:</strong> {{new_status}}</p><p>You can view the project details in the system.</p><p>Best regards,<br>UCRD Management System</p></body></html>', 'active', NOW()),
('new_researcher', 'New Researcher Added: {{name}}', '<html><body><h2>Hello {{user_name}},</h2><p>A new researcher has been added to the UCRD Management System:</p><p><strong>Name:</strong> {{name}}<br><strong>Email:</strong> {{email}}<br><strong>Supervisor:</strong> {{supervisor}}</p><p>You can view the researcher details in the system.</p><p>Best regards,<br>UCRD Management System</p></body></html>', 'active', NOW()),
('daily_digest', 'UCRD Management System - Daily Digest ({{date_range}})', '<html><body><h2>Hello {{user_name}},</h2><p>Here is your daily digest of activity from the UCRD Management System for the period {{date_range}}.</p><p>There {{notification_count|pluralize:"was,were"}} {{notification_count}} notification{{notification_count|pluralize}} in the last 24 hours:</p><ul>{{notification_list}}</ul><p>You can view more details by logging into the system.</p><p>Best regards,<br>UCRD Management System</p></body></html>', 'active', NOW());

-- Create email_queue table
CREATE TABLE IF NOT EXISTS email_queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'ID of the recipient user',
    email VARCHAR(255) NOT NULL COMMENT 'Recipient email address',
    subject VARCHAR(255) NOT NULL COMMENT 'Email subject',
    body TEXT NOT NULL COMMENT 'Email body (HTML)',
    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending' COMMENT 'Email status',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    sent_at DATETIME DEFAULT NULL COMMENT 'When the email was sent',
    error_message TEXT DEFAULT NULL COMMENT 'Error message if sending failed',
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Make sure we have a users table (if it doesn't already exist)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'supervisor', 'researcher', 'viewer') NOT NULL DEFAULT 'viewer',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user if not exists
INSERT INTO users (name, email, password, role)
SELECT 'Admin User', 'admin@ucrd-system.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@ucrd-system.com' LIMIT 1);

-- Insert default notification preferences for existing users
INSERT INTO notification_preferences (user_id, new_publication, project_status_change, new_researcher, email_notifications, daily_digest)
SELECT id, 1, 1, 1, 1, 0
FROM users
WHERE NOT EXISTS (SELECT 1 FROM notification_preferences WHERE notification_preferences.user_id = users.id); 