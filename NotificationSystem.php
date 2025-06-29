<?php
/**
 * Notification System
 * Handles all notification-related functionality, including sending emails,
 * logging notifications, and processing notification preferences.
 */
class NotificationSystem {
    private $conn;
    
    /**
     * Constructor
     * @param mysqli $conn Database connection
     */
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    /**
     * Log a notification in the system
     * @param string $type Notification type (e.g., 'new_publication', 'project_status')
     * @param int $entityId ID of the related entity (publication, project, etc.)
     * @param string $message Notification message
     * @param array $additionalData Optional additional data in JSON format
     * @return int|bool The ID of the new notification or false on failure
     */
    public function logNotification($type, $entityId, $message, $additionalData = null) {
        $sql = "INSERT INTO notification_log (type, entity_id, message, additional_data, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->conn->prepare($sql);
        $jsonData = $additionalData ? json_encode($additionalData) : null;
        $stmt->bind_param("siss", $type, $entityId, $message, $jsonData);
        
        if ($stmt->execute()) {
            $id = $stmt->insert_id;
            $stmt->close();
            
            // Process notification based on user preferences
            $this->processNotificationPreferences($type, $entityId, $message, $jsonData);
            
            return $id;
        } else {
            $stmt->close();
            return false;
        }
    }
    
    /**
     * Process notification preferences for all users
     * @param string $type Notification type
     * @param int $entityId Entity ID
     * @param string $message Notification message
     * @param string $jsonData Additional data in JSON format
     */
    private function processNotificationPreferences($type, $entityId, $message, $jsonData) {
        // Get all users with their notification preferences
        $sql = "SELECT u.id, u.name, u.email, np.receive_new_publications, 
                np.receive_project_updates, np.receive_email_notifications 
                FROM users u
                JOIN notification_preferences np ON u.id = np.user_id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($user = $result->fetch_assoc()) {
            $shouldNotify = false;
            
            // Check if user wants this type of notification
            if ($type == 'new_publication' && $user['receive_new_publications'] == 1) {
                $shouldNotify = true;
            } elseif ($type == 'project_status' && $user['receive_project_updates'] == 1) {
                $shouldNotify = true;
            } elseif ($type == 'new_researcher') {
                // Assume all admins should be notified of new researchers
                $shouldNotify = true;
            }
            
            // If user should be notified and wants email notifications, queue an email
            if ($shouldNotify && $user['receive_email_notifications'] == 1) {
                $this->queueEmail($type, $user['email'], $user['name'], $message, $entityId, $jsonData);
            }
        }
        
        $stmt->close();
    }
    
    /**
     * Queue an email based on notification type
     * @param string $type Notification type
     * @param string $email Recipient email
     * @param string $name Recipient name
     * @param string $message Notification message
     * @param int $entityId Entity ID
     * @param string $jsonData Additional data
     * @return bool Success status
     */
    public function queueEmail($type, $email, $name, $message, $entityId, $jsonData) {
        // Get the appropriate email template
        $sql = "SELECT * FROM email_templates WHERE name = ? AND status = 'active'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $type);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return false;
        }
        
        $template = $result->fetch_assoc();
        $stmt->close();
        
        // Parse the additional data
        $data = json_decode($jsonData, true) ?: [];
        
        // Create the email subject and body
        $subject = $this->parseTemplate($template['subject'], $name, $message, $data);
        $body = $this->parseTemplate($template['body'], $name, $message, $data);
        
        // Add to email queue
        $sql = "INSERT INTO email_queue (email, subject, body, status, created_at) VALUES (?, ?, ?, 'pending', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $email, $subject, $body);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    /**
     * Parse email template by replacing placeholders with actual data
     * @param string $template Template with placeholders
     * @param string $name Recipient name
     * @param string $message Notification message
     * @param array $data Additional data
     * @return string Parsed template
     */
    private function parseTemplate($template, $name, $message, $data) {
        $replacements = [
            '{{name}}' => $name,
            '{{message}}' => $message,
            '{{date}}' => date('Y-m-d H:i:s'),
            '{{system_name}}' => 'UCRD Management System'
        ];
        
        // Add any additional data from JSON
        foreach ($data as $key => $value) {
            if (is_string($value) || is_numeric($value)) {
                $replacements['{{'.$key.'}}'] = $value;
            }
        }
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    /**
     * Send daily digest of notifications to users who prefer it
     * @return int Number of digests processed
     */
    public function sendDailyDigests() {
        // Get all users who have email notifications enabled
        $sql = "SELECT u.id, u.name, u.email FROM users u
                JOIN notification_preferences np ON u.id = np.user_id
                WHERE np.receive_email_notifications = 1 AND np.receive_daily_digest = 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $digestCount = 0;
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return 0;
        }
        
        while ($user = $result->fetch_assoc()) {
            // Get yesterday's notifications for this user
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $notifSql = "SELECT * FROM notification_log 
                        WHERE DATE(created_at) = ? 
                        ORDER BY created_at DESC";
            
            $notifStmt = $this->conn->prepare($notifSql);
            $notifStmt->bind_param("s", $yesterday);
            $notifStmt->execute();
            $notifResult = $notifStmt->get_result();
            
            if ($notifResult->num_rows > 0) {
                // Build digest email
                $digestBody = "<h2>Your Daily UCRD System Digest</h2>";
                $digestBody .= "<p>Hello " . htmlspecialchars($user['name']) . ",</p>";
                $digestBody .= "<p>Here's a summary of activities from " . $yesterday . ":</p>";
                $digestBody .= "<ul>";
                
                while ($notification = $notifResult->fetch_assoc()) {
                    $digestBody .= "<li>" . htmlspecialchars($notification['message']) . 
                                  " <small>(" . $notification['created_at'] . ")</small></li>";
                }
                
                $digestBody .= "</ul>";
                $digestBody .= "<p>Log in to the system for more details.</p>";
                $digestBody .= "<p>Regards,<br>UCRD Management System</p>";
                
                // Queue the digest email
                $sql = "INSERT INTO email_queue (email, subject, body, status, created_at) 
                        VALUES (?, ?, ?, 'pending', NOW())";
                $queueStmt = $this->conn->prepare($sql);
                $subject = "UCRD System Daily Digest - " . $yesterday;
                $queueStmt->bind_param("sss", $user['email'], $subject, $digestBody);
                $queueStmt->execute();
                $queueStmt->close();
                $digestCount++;
            }
            
            $notifStmt->close();
        }
        
        $stmt->close();
        return $digestCount;
    }
    
    /**
     * Create a notification for a new publication
     * @param int $publicationId Publication ID
     * @param string $title Publication title
     * @param string $researcher Researcher name
     * @param string $supervisor Supervisor name
     * @return bool Success status
     */
    public function notifyNewPublication($publicationId, $title, $researcher, $supervisor) {
        $message = "New publication added: '$title' by $researcher (Supervisor: $supervisor)";
        $additionalData = [
            'title' => $title,
            'researcher' => $researcher,
            'supervisor' => $supervisor,
            'url' => "publication/view.php?id=$publicationId"
        ];
        
        return $this->logNotification('new_publication', $publicationId, $message, $additionalData);
    }
    
    /**
     * Create a notification for a project status change
     * @param int $projectId Project ID
     * @param string $title Project title
     * @param string $oldStatus Previous status
     * @param string $newStatus New status
     * @return bool Success status
     */
    public function notifyProjectStatusChange($projectId, $title, $oldStatus, $newStatus) {
        $message = "Project '$title' status changed from '$oldStatus' to '$newStatus'";
        $additionalData = [
            'title' => $title,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'url' => "project/view.php?id=$projectId"
        ];
        
        return $this->logNotification('project_status', $projectId, $message, $additionalData);
    }
    
    /**
     * Create a notification for a new researcher
     * @param int $researcherId Researcher ID
     * @param string $name Researcher name
     * @param string $department Researcher department
     * @return bool Success status
     */
    public function notifyNewResearcher($researcherId, $name, $department) {
        $message = "New researcher added: $name ($department)";
        $additionalData = [
            'name' => $name,
            'department' => $department,
            'url' => "researcher/view.php?id=$researcherId"
        ];
        
        return $this->logNotification('new_researcher', $researcherId, $message, $additionalData);
    }
} 