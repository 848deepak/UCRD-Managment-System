<?php
/**
 * Email Configuration File
 * Contains settings for email notifications in the UCRD Management System
 */

// Email server settings
define('MAIL_ENABLED', true); // Set to false to disable all email notifications
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'notifications@example.com');
define('MAIL_PASSWORD', 'your_password_here'); // In production, use environment variables
define('MAIL_FROM_ADDRESS', 'notifications@example.com');
define('MAIL_FROM_NAME', 'UCRD Management System');
define('MAIL_ENCRYPTION', 'tls'); // tls or ssl

// Administrator email for system notifications
define('ADMIN_EMAIL', 'admin@example.com');

// Notification types
define('NOTIFY_NEW_PUBLICATION', true);
define('NOTIFY_PROJECT_STATUS_CHANGE', true);
define('NOTIFY_NEW_RESEARCHER', true);
define('NOTIFY_NEW_SUPERVISOR', true);

/**
 * Gets user notification preferences from database
 * @param int $user_id The user ID to get preferences for
 * @return array User's notification preferences
 */
function getUserNotificationPreferences($user_id) {
    global $conn;
    
    // Default preferences if not set in database
    $preferences = [
        'new_publication' => true,
        'project_status_change' => true,
        'new_researcher' => false,
        'new_supervisor' => false,
        'daily_summary' => false,
        'weekly_summary' => true
    ];
    
    // Check if user has preferences saved
    $sql = "SELECT * FROM notification_preferences WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $preferences = [
            'new_publication' => (bool)$row['new_publication'],
            'project_status_change' => (bool)$row['project_status_change'],
            'new_researcher' => (bool)$row['new_researcher'],
            'new_supervisor' => (bool)$row['new_supervisor'],
            'daily_summary' => (bool)$row['daily_summary'],
            'weekly_summary' => (bool)$row['weekly_summary']
        ];
    }
    
    return $preferences;
}

/**
 * Gets email template from file
 * @param string $template_name The name of the template
 * @param array $data Data to be replaced in the template
 * @return string The parsed email template
 */
function getEmailTemplate($template_name, $data = []) {
    $template_path = __DIR__ . '/email_templates/' . $template_name . '.html';
    
    if (!file_exists($template_path)) {
        return false;
    }
    
    $template = file_get_contents($template_path);
    
    // Replace placeholder variables with actual data
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }
    
    return $template;
} 