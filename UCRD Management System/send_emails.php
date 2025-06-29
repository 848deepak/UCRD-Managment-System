<?php
/**
 * Email Processing Script
 * This script processes the email queue and sends pending emails.
 * It can be run manually or set up as a cron job.
 */

// Include database connection
require_once 'db.php';

// Function to send an email using PHP's mail function
function sendEmail($to, $subject, $body, $headers) {
    // You can replace this with a more robust email solution like PHPMailer if needed
    return mail($to, $subject, $body, $headers);
}

// Set headers for HTML emails
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: UCRD Management System <noreply@ucrd-system.com>" . "\r\n";

// Get pending emails from the queue
$sql = "SELECT id, email, subject, body FROM email_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT 50";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$sent = 0;
$failed = 0;

if ($result->num_rows > 0) {
    echo "Processing " . $result->num_rows . " pending emails...\n";
    
    while ($row = $result->fetch_assoc()) {
        $emailId = $row['id'];
        $to = $row['email'];
        $subject = $row['subject'];
        $body = $row['body'];
        
        // Try to send the email
        $success = sendEmail($to, $subject, $body, $headers);
        
        // Update the email status in the queue
        if ($success) {
            $updateSql = "UPDATE email_queue SET status = 'sent', sent_at = NOW() WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("i", $emailId);
            $updateStmt->execute();
            $updateStmt->close();
            $sent++;
            echo "✓ Email sent to: $to\n";
        } else {
            $error = error_get_last()['message'] ?? 'Unknown error';
            $updateSql = "UPDATE email_queue SET status = 'failed', error_message = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("si", $error, $emailId);
            $updateStmt->execute();
            $updateStmt->close();
            $failed++;
            echo "✗ Failed to send email to: $to (Error: $error)\n";
        }
        
        // Add a small delay to prevent overloading the mail server
        usleep(100000); // 0.1 seconds
    }
    
    echo "Email processing complete. Sent: $sent, Failed: $failed\n";
} else {
    echo "No pending emails in the queue.\n";
}

// Process daily digests if it's the right time (e.g., 8 AM)
$hour = (int)date('H');
if ($hour == 8) {
    echo "Processing daily digests...\n";
    // Include and call the NotificationSystem to process daily digests
    require_once 'NotificationSystem.php';
    $notificationSystem = new NotificationSystem($conn);
    $digestsProcessed = $notificationSystem->sendDailyDigests();
    echo "Daily digests processed: $digestsProcessed\n";
}

$conn->close();
echo "Email queue processing completed.\n";
?> 