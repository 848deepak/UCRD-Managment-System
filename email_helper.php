<?php
/**
 * Email Helper Functions
 * Contains functions for sending emails from the UCRD Management System
 */

require_once 'email_config.php';

// Check if PHPMailer is installed
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Sends an email notification
 * 
 * @param string $to Recipient email address
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param array $attachments Optional array of file paths to attach
 * @return bool True if email was sent successfully, false otherwise
 */
function sendEmail($to, $subject, $body, $attachments = []) {
    // Check if email notifications are enabled
    if (!MAIL_ENABLED) {
        error_log("Email notifications are disabled in configuration.");
        return false;
    }
    
    // Check if PHPMailer is available
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer is not installed. Please run 'composer require phpmailer/phpmailer'");
        return false;
    }
    
    try {
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port       = MAIL_PORT;
        
        // Recipients
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>'], "\n", $body));
        
        // Attachments
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (file_exists($attachment)) {
                    $mail->addAttachment($attachment);
                }
            }
        }
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error sending email: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Notify users about a new publication
 * 
 * @param int $publication_id ID of the new publication
 * @return bool True if notifications were sent successfully
 */
function notifyNewPublication($publication_id) {
    global $conn;
    
    if (!NOTIFY_NEW_PUBLICATION) {
        return false;
    }
    
    // Get publication details
    $sql = "SELECT p.*, r.Name as ResearcherName, s.Name as SupervisorName 
            FROM Publication p 
            LEFT JOIN Researcher r ON p.Researcher_ID = r.Researcher_ID 
            LEFT JOIN Supervisor s ON p.Supervisor_ID = s.Supervisor_ID 
            WHERE p.Publication_ID = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $publication_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result || $result->num_rows === 0) {
        return false;
    }
    
    $publication = $result->fetch_assoc();
    
    // Get users to notify (admins, relevant researchers, supervisors)
    $usersToNotify = [];
    
    // Get system administrator
    $usersToNotify[] = [
        'email' => ADMIN_EMAIL,
        'name' => 'Administrator'
    ];
    
    // Get all users with notification preferences
    $sql = "SELECT u.user_id, u.username, u.email, np.new_publication 
            FROM users u 
            LEFT JOIN notification_preferences np ON u.user_id = np.user_id 
            WHERE np.new_publication = 1 OR np.new_publication IS NULL";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $usersToNotify[] = [
                'email' => $row['email'],
                'name' => $row['username']
            ];
        }
    }
    
    // Get the base URL
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $publicationUrl = $baseUrl . "/publication/edit.php?id=" . $publication_id;
    $preferencesUrl = $baseUrl . "/notification_preferences.php";
    
    // Send email to each recipient
    $successCount = 0;
    
    foreach ($usersToNotify as $user) {
        // Prepare email data
        $emailData = [
            'recipient_name' => $user['name'],
            'publication_title' => $publication['Title'],
            'researcher_name' => $publication['ResearcherName'],
            'supervisor_name' => $publication['SupervisorName'],
            'publication_date' => date('Y-m-d', strtotime($publication['Date_Published'])),
            'publication_doi' => !empty($publication['DOI']) ? $publication['DOI'] : 'N/A',
            'view_url' => $publicationUrl,
            'preferences_url' => $preferencesUrl
        ];
        
        // Get email template
        $emailBody = getEmailTemplate('new_publication', $emailData);
        
        if (!$emailBody) {
            continue;
        }
        
        // Send email
        $subject = "New Publication: " . $publication['Title'];
        if (sendEmail($user['email'], $subject, $emailBody)) {
            $successCount++;
            
            // Log the notification
            $sql = "INSERT INTO notification_log (user_id, notification_type, related_id, status) 
                    VALUES ((SELECT user_id FROM users WHERE email = ?), 'new_publication', ?, 'sent')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $user['email'], $publication_id);
            $stmt->execute();
        }
    }
    
    return $successCount > 0;
}

/**
 * Notify users about a project status change
 * 
 * @param int $project_id ID of the updated project
 * @param string $old_status Previous status
 * @param string $new_status New status
 * @return bool True if notifications were sent successfully
 */
function notifyProjectStatusChange($project_id, $old_status, $new_status) {
    global $conn;
    
    if (!NOTIFY_PROJECT_STATUS_CHANGE) {
        return false;
    }
    
    // Similar implementation as notifyNewPublication
    // This function would prepare and send emails about project status changes
    
    return true;
}

/**
 * Schedule a daily summary email to be sent
 * 
 * @return bool True if scheduling was successful
 */
function scheduleDailySummary() {
    // This function would record that a daily summary needs to be sent
    // Actual sending would be done by a cron job or scheduled task
    
    return true;
}

/**
 * Log email notification
 * 
 * @param int $user_id User ID
 * @param string $notification_type Type of notification
 * @param int $related_id ID of related entity (publication, project, etc.)
 * @param string $status Status of notification (sent, failed, etc.)
 * @return bool True if logged successfully
 */
function logNotification($user_id, $notification_type, $related_id, $status) {
    global $conn;
    
    $sql = "INSERT INTO notification_log (user_id, notification_type, related_id, status, created_at) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isis", $user_id, $notification_type, $related_id, $status);
    
    return $stmt->execute();
}

/**
 * Get user notification preferences
 * 
 * @param int $user_id User ID
 * @return array User notification preferences
 */
function getUserNotificationPreferences($user_id) {
    global $conn;
    
    // Default preferences (all enabled)
    $defaults = [
        'new_publication' => true,
        'project_status_change' => true,
        'new_researcher' => true,
        'new_supervisor' => true,
        'daily_summary' => true
    ];
    
    // Check if user has saved preferences
    $sql = "SELECT * FROM notification_preferences WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return [
            'new_publication' => (bool) $row['new_publication'],
            'project_status_change' => (bool) $row['project_status_change'],
            'new_researcher' => (bool) $row['new_researcher'],
            'new_supervisor' => (bool) $row['new_supervisor'],
            'daily_summary' => (bool) $row['daily_summary']
        ];
    }
    
    return $defaults;
} 