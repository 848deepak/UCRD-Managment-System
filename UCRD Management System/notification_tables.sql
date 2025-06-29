-- Notification System Database Tables
-- Run this script to set up the required tables for the notification system

-- Table for user notification preferences
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    new_publication TINYINT(1) DEFAULT 1,
    project_status_change TINYINT(1) DEFAULT 1,
    new_researcher TINYINT(1) DEFAULT 1,
    email_notifications TINYINT(1) DEFAULT 1,
    daily_digest TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id)
);

-- Table for notification log
CREATE TABLE IF NOT EXISTS notification_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    entity_id INT,
    message TEXT NOT NULL,
    additional_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (type),
    INDEX (entity_id),
    INDEX (created_at)
);

-- Table for email templates
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (template_name)
);

-- Insert default email templates
INSERT INTO email_templates (template_name, subject, body)
VALUES 
('new_publication', 
 'New Publication: {TITLE}', 
 '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
    <h2 style="color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px;">New Publication Added</h2>
    <p>Hello {USER_NAME},</p>
    <p>{MESSAGE}</p>
    <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <p><strong>Title:</strong> {TITLE}</p>
        <p><strong>Author:</strong> {AUTHOR}</p>
        <p><strong>Date:</strong> {CURRENT_DATE}</p>
    </div>
    <p style="font-size: 12px; color: #777; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px;">
        This is an automated notification from the UCRD Management System.
    </p>
</div>'
),

('project_status_change', 
 'Project Status Update: {PROJECT_TITLE}', 
 '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
    <h2 style="color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px;">Project Status Updated</h2>
    <p>Hello {USER_NAME},</p>
    <p>{MESSAGE}</p>
    <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <p><strong>Project:</strong> {PROJECT_TITLE}</p>
        <p><strong>New Status:</strong> <span style="color: #0066cc; font-weight: bold;">{NEW_STATUS}</span></p>
        <p><strong>Previous Status:</strong> {PREVIOUS_STATUS}</p>
        <p><strong>Updated on:</strong> {CURRENT_DATE} at {CURRENT_TIME}</p>
    </div>
    <p style="font-size: 12px; color: #777; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px;">
        This is an automated notification from the UCRD Management System.
    </p>
</div>'
),

('new_researcher', 
 'New Researcher Added: {RESEARCHER_NAME}', 
 '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
    <h2 style="color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px;">New Researcher Added</h2>
    <p>Hello {USER_NAME},</p>
    <p>{MESSAGE}</p>
    <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <p><strong>Name:</strong> {RESEARCHER_NAME}</p>
        <p><strong>Department:</strong> {DEPARTMENT}</p>
        <p><strong>Added on:</strong> {CURRENT_DATE}</p>
    </div>
    <p style="font-size: 12px; color: #777; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px;">
        This is an automated notification from the UCRD Management System.
    </p>
</div>'
),

('daily_digest', 
 'UCRD System Daily Digest - {CURRENT_DATE}', 
 '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee; border-radius: 5px;">
    <h2 style="color: #333; border-bottom: 1px solid #eee; padding-bottom: 10px;">Your Daily UCRD Update</h2>
    <p>Hello {USER_NAME},</p>
    <p>Here\'s a summary of the activity in the UCRD Management System from the past 24 hours:</p>
    
    {DIGEST_CONTENT}
    
    <p style="margin-top: 20px;">
        You can manage your notification preferences by visiting the <a href="#" style="color: #0066cc;">notification preferences page</a>.
    </p>
    <p style="font-size: 12px; color: #777; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px;">
        This is an automated notification from the UCRD Management System.
    </p>
</div>'
);

-- Add a basic users table if not exists (usually you would already have this)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (email)
);

-- Add a test user if needed
INSERT INTO users (name, email, password, role)
VALUES ('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE id = id;

-- Add default notification preferences for existing users
INSERT INTO notification_preferences (user_id, new_publication, project_status_change, new_researcher, email_notifications, daily_digest)
SELECT id, 1, 1, 1, 1, 0 FROM users
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP; 