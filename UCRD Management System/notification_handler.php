<?php
/**
 * Notification Handler
 * 
 * This script handles sending notifications to users based on their preferences.
 * It supports various notification types and delivery methods (in-app and email).
 */

// Include database connection
require_once 'db.php';

class NotificationHandler {
    private $conn;
    
    /**
     * Constructor
     * 
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Create a notification
     * 
     * @param string $type Notification type (new_publication, project_status_change, new_researcher)
     * @param int $entityId ID of the related entity (publication, project, researcher)
     * @param string $message Notification message
     * @param array $additionalData Optional additional data for the notification
     * @return bool Success status
     */
    public function createNotification($type, $entityId, $message, $additionalData = []) {
        try {
            // Insert notification into log
            $stmt = $this->conn->prepare("INSERT INTO notification_log 
                (type, entity_id, message, additional_data, created_at) 
                VALUES (?, ?, ?, ?, NOW())");
            
            $jsonData = !empty($additionalData) ? json_encode($additionalData) : null;
            $stmt->bind_param("siss", $type, $entityId, $message, $jsonData);
            
            if (!$stmt->execute()) {
                error_log("Failed to create notification: " . $this->conn->error);
                return false;
            }
            
            $notificationId = $this->conn->insert_id;
            
            // Get users who should receive this notification
            $users = $this->getUsersForNotification($type);
            
            // Send notifications to eligible users
            foreach ($users as $user) {
                // Send email if user has email notifications enabled
                if ($user['email_notifications']) {
                    $this->sendEmail($user, $type, $message, $additionalData);
                }
                
                // Here you would also handle in-app notifications if implemented
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in createNotification: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get users who should receive a specific notification type
     * 
     * @param string $type Notification type
     * @return array Array of user data
     */
    private function getUsersForNotification($type) {
        $users = [];
        
        try {
            $sql = "SELECT u.id, u.name, u.email, np.* 
                    FROM users u
                    JOIN notification_preferences np ON u.id = np.user_id
                    WHERE np.{$type} = 1";
            
            $result = $this->conn->query($sql);
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
            }
        } catch (Exception $e) {
            error_log("Error in getUsersForNotification: " . $e->getMessage());
        }
        
        return $users;
    }
    
    /**
     * Send an email notification
     * 
     * @param array $user User data
     * @param string $type Notification type
     * @param string $message Notification message
     * @param array $additionalData Additional data for the notification
     * @return bool Success status
     */
    private function sendEmail($user, $type, $message, $additionalData) {
        // Skip if user has daily digest enabled - those will be sent by a scheduled task
        if ($user['daily_digest']) {
            return true;
        }
        
        try {
            // Get the email template
            $stmt = $this->conn->prepare("SELECT * FROM email_templates WHERE template_name = ? AND is_active = 1");
            $stmt->bind_param("s", $type);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                error_log("Email template not found for type: $type");
                return false;
            }
            
            $template = $result->fetch_assoc();
            
            // Parse template and replace placeholders
            $subject = $this->parsePlaceholders($template['subject'], $user, $additionalData);
            $body = $this->parsePlaceholders($template['body'], $user, $additionalData);
            
            // In a production environment, you would use a proper email library/service
            // For demonstration, we'll log the email instead
            error_log("EMAIL TO: {$user['email']} | SUBJECT: $subject | MESSAGE: " . strip_tags($body));
            
            // In a real application, use PHPMailer, Symfony Mailer, or a service like SendGrid
            // Example with PHPMailer would go here
            
            return true;
        } catch (Exception $e) {
            error_log("Error in sendEmail: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Parse and replace placeholders in templates
     * 
     * @param string $content Content with placeholders
     * @param array $user User data
     * @param array $additionalData Additional data for the notification
     * @return string Processed content
     */
    private function parsePlaceholders($content, $user, $additionalData) {
        // Replace user-related placeholders
        $content = str_replace('{USER_NAME}', $user['name'], $content);
        $content = str_replace('{USER_EMAIL}', $user['email'], $content);
        
        // Replace additional data placeholders
        foreach ($additionalData as $key => $value) {
            $content = str_replace('{' . strtoupper($key) . '}', $value, $content);
        }
        
        // Add current date/time
        $content = str_replace('{CURRENT_DATE}', date('F j, Y'), $content);
        $content = str_replace('{CURRENT_TIME}', date('g:i A'), $content);
        
        return $content;
    }
    
    /**
     * Send daily digest emails to users who have enabled this option
     * This method would be called by a scheduled cron job
     * 
     * @return bool Success status
     */
    public function sendDailyDigest() {
        try {
            // Get users who have daily digest enabled
            $sql = "SELECT u.id, u.name, u.email, np.* 
                    FROM users u
                    JOIN notification_preferences np ON u.id = np.user_id
                    WHERE np.daily_digest = 1 AND np.email_notifications = 1";
            
            $result = $this->conn->query($sql);
            
            if ($result->num_rows === 0) {
                return true; // No users with daily digest
            }
            
            // Get notifications from the last 24 hours
            $yesterday = date('Y-m-d H:i:s', strtotime('-24 hours'));
            $notifSql = "SELECT * FROM notification_log WHERE created_at >= '$yesterday' ORDER BY created_at DESC";
            $notifResult = $this->conn->query($notifSql);
            
            if ($notifResult->num_rows === 0) {
                return true; // No notifications to send
            }
            
            // Organize notifications by type
            $notifications = [];
            while ($row = $notifResult->fetch_assoc()) {
                $notifications[$row['type']][] = $row;
            }
            
            // Get the daily digest template
            $stmt = $this->conn->prepare("SELECT * FROM email_templates WHERE template_name = 'daily_digest' AND is_active = 1");
            $stmt->execute();
            $templateResult = $stmt->get_result();
            
            if ($templateResult->num_rows === 0) {
                error_log("Daily digest template not found");
                return false;
            }
            
            $template = $templateResult->fetch_assoc();
            
            // Send digest to each user
            while ($user = $result->fetch_assoc()) {
                // Filter notifications based on user preferences
                $userNotifications = [];
                
                foreach ($notifications as $type => $typeNotifs) {
                    if ($user[$type]) {
                        $userNotifications[$type] = $typeNotifs;
                    }
                }
                
                if (empty($userNotifications)) {
                    continue; // Skip if no relevant notifications for this user
                }
                
                // Build digest content
                $digestContent = $this->buildDigestContent($userNotifications);
                
                // Parse template
                $additionalData = ['DIGEST_CONTENT' => $digestContent];
                $subject = $this->parsePlaceholders($template['subject'], $user, $additionalData);
                $body = $this->parsePlaceholders($template['body'], $user, $additionalData);
                
                // Send email (log it for demo)
                error_log("DAILY DIGEST TO: {$user['email']} | SUBJECT: $subject | CONTENT COUNT: " . 
                    array_sum(array_map('count', $userNotifications)));
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in sendDailyDigest: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Build HTML content for the digest email
     * 
     * @param array $notifications Notifications organized by type
     * @return string HTML content
     */
    private function buildDigestContent($notifications) {
        $content = '<div style="font-family: Arial, sans-serif;">';
        
        foreach ($notifications as $type => $typeNotifs) {
            // Format notification type for display
            $typeTitle = ucwords(str_replace('_', ' ', $type));
            $content .= "<h3>$typeTitle Updates</h3>";
            $content .= '<ul style="padding-left: 20px;">';
            
            foreach ($typeNotifs as $notif) {
                $content .= "<li><strong>" . date('g:i A', strtotime($notif['created_at'])) . "</strong>: " . 
                    htmlspecialchars($notif['message']) . "</li>";
            }
            
            $content .= '</ul>';
        }
        
        $content .= '</div>';
        return $content;
    }
}

// Create an instance for use in other files
$notificationHandler = new NotificationHandler($conn);

// Example usage:
// $notificationHandler->createNotification(
//     'new_publication', 
//     123, 
//     'New publication "Advanced Research Methods" has been added', 
//     ['TITLE' => 'Advanced Research Methods', 'AUTHOR' => 'Dr. Smith']
// );
?> 