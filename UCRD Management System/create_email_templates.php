<?php
/**
 * Email Templates Setup Script
 * Creates the required database table and inserts default email templates
 */

// Include database connection
require_once 'db.php';

// Create email_templates table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS email_templates (
    id INT(11) NOT NULL AUTO_INCREMENT,
    template_name VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY template_name (template_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Email templates table created or already exists.<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
    exit;
}

// Array of default templates
$templates = [
    [
        'name' => 'default',
        'subject' => 'UCRD Management System - {{notification_type}} Notification',
        'body' => '<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4a6da7; color: white; padding: 10px 20px; }
        .content { padding: 20px; border: 1px solid #ddd; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>UCRD Management System</h2>
        </div>
        <div class="content">
            <p>Hello {{user_name}},</p>
            <p>{{message}}</p>
            <p>Thank you for using the UCRD Management System.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from the UCRD Management System. Please do not reply to this email.</p>
            <p>Notification sent on: {{date}}</p>
        </div>
    </div>
</body>
</html>'
    ],
    [
        'name' => 'new_publication',
        'subject' => 'New Publication: {{entity_title}}',
        'body' => '<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4a6da7; color: white; padding: 10px 20px; }
        .content { padding: 20px; border: 1px solid #ddd; }
        .publication { background-color: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #4a6da7; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Publication Added</h2>
        </div>
        <div class="content">
            <p>Hello {{user_name}},</p>
            <p>A new publication has been added to the UCRD Management System:</p>
            <div class="publication">
                <h3>{{entity_title}}</h3>
                <p>{{message}}</p>
            </div>
            <p>You can view the full details by logging into the system.</p>
            <p>Thank you for using the UCRD Management System.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from the UCRD Management System. Please do not reply to this email.</p>
            <p>Notification sent on: {{date}}</p>
        </div>
    </div>
</body>
</html>'
    ],
    [
        'name' => 'project_status_change',
        'subject' => 'Project Status Update: {{entity_title}}',
        'body' => '<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4a6da7; color: white; padding: 10px 20px; }
        .content { padding: 20px; border: 1px solid #ddd; }
        .project { background-color: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #f0ad4e; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Project Status Update</h2>
        </div>
        <div class="content">
            <p>Hello {{user_name}},</p>
            <p>A project status has been updated in the UCRD Management System:</p>
            <div class="project">
                <h3>{{entity_title}}</h3>
                <p>{{message}}</p>
            </div>
            <p>You can view the full details by logging into the system.</p>
            <p>Thank you for using the UCRD Management System.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from the UCRD Management System. Please do not reply to this email.</p>
            <p>Notification sent on: {{date}}</p>
        </div>
    </div>
</body>
</html>'
    ],
    [
        'name' => 'new_researcher',
        'subject' => 'New Researcher Added: {{entity_title}}',
        'body' => '<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4a6da7; color: white; padding: 10px 20px; }
        .content { padding: 20px; border: 1px solid #ddd; }
        .researcher { background-color: #f9f9f9; padding: 15px; margin: 10px 0; border-left: 4px solid #5cb85c; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Researcher Added</h2>
        </div>
        <div class="content">
            <p>Hello {{user_name}},</p>
            <p>A new researcher has been added to the UCRD Management System:</p>
            <div class="researcher">
                <h3>{{entity_title}}</h3>
                <p>{{message}}</p>
            </div>
            <p>You can view the full details by logging into the system.</p>
            <p>Thank you for using the UCRD Management System.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from the UCRD Management System. Please do not reply to this email.</p>
            <p>Notification sent on: {{date}}</p>
        </div>
    </div>
</body>
</html>'
    ],
    [
        'name' => 'daily_digest',
        'subject' => 'UCRD Management System - Daily Notification Digest',
        'body' => '<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #4a6da7; color: white; padding: 10px 20px; }
        .content { padding: 20px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th { background-color: #f2f2f2; text-align: left; padding: 8px; }
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { font-size: 12px; color: #666; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Daily Notification Digest</h2>
        </div>
        <div class="content">
            <p>Hello {{user_name}},</p>
            <p>Here is your daily digest of notifications from the UCRD Management System:</p>
            
            {{digest_content}}
            
            <p>Thank you for using the UCRD Management System.</p>
        </div>
        <div class="footer">
            <p>This is an automated message from the UCRD Management System. Please do not reply to this email.</p>
            <p>Digest sent on: {{date}}</p>
        </div>
    </div>
</body>
</html>'
    ],
];

// Insert or update templates
$insertCount = 0;
$updateCount = 0;

foreach ($templates as $template) {
    // Check if template exists
    $stmt = $conn->prepare("SELECT id FROM email_templates WHERE template_name = ?");
    $stmt->bind_param("s", $template['name']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing template
        $templateId = $result->fetch_assoc()['id'];
        $stmt = $conn->prepare("UPDATE email_templates SET subject = ?, body = ? WHERE id = ?");
        $stmt->bind_param("ssi", $template['subject'], $template['body'], $templateId);
        
        if ($stmt->execute()) {
            $updateCount++;
        } else {
            echo "Error updating template '{$template['name']}': " . $conn->error . "<br>";
        }
    } else {
        // Insert new template
        $stmt = $conn->prepare("INSERT INTO email_templates (template_name, subject, body) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $template['name'], $template['subject'], $template['body']);
        
        if ($stmt->execute()) {
            $insertCount++;
        } else {
            echo "Error inserting template '{$template['name']}': " . $conn->error . "<br>";
        }
    }
}

echo "Email templates setup complete: $insertCount new templates inserted, $updateCount templates updated.<br>";

// Create notification_log table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS notification_log (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    related_id INT(11) NULL,
    status ENUM('pending', 'sent', 'read', 'failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX (user_id),
    INDEX (notification_type),
    INDEX (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Notification log table created or already exists.<br>";
} else {
    echo "Error creating notification log table: " . $conn->error . "<br>";
}

// Create notification_preferences table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    new_publication TINYINT(1) NOT NULL DEFAULT 1,
    project_status_change TINYINT(1) NOT NULL DEFAULT 1,
    new_researcher TINYINT(1) NOT NULL DEFAULT 1,
    email_notifications TINYINT(1) NOT NULL DEFAULT 1,
    daily_digest TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql) === TRUE) {
    echo "Notification preferences table created or already exists.<br>";
} else {
    echo "Error creating notification preferences table: " . $conn->error . "<br>";
}

// Check if users table exists, if not create a simple one for testing
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    if ($conn->query($sql) === TRUE) {
        echo "Users table created.<br>";
        
        // Insert a test user
        $name = "Test User";
        $email = "test@example.com";
        $password = password_hash("password", PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
        
        if ($stmt->execute()) {
            echo "Test user created with email: test@example.com and password: password<br>";
        } else {
            echo "Error creating test user: " . $conn->error . "<br>";
        }
    } else {
        echo "Error creating users table: " . $conn->error . "<br>";
    }
}

echo "Setup completed successfully!"; 