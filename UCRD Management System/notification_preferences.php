<?php
/**
 * Notification Preferences Page
 * Allows users to manage their notification settings
 */

// Include database connection
require_once 'db.php';
session_start();

// Redirect to login page if not logged in (assuming you have a login system)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "You must be logged in to access this page";
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $newPublication = isset($_POST['new_publication']) ? 1 : 0;
    $projectStatusChange = isset($_POST['project_status_change']) ? 1 : 0;
    $newResearcher = isset($_POST['new_researcher']) ? 1 : 0;
    $emailNotifications = isset($_POST['email_notifications']) ? 1 : 0;
    $dailyDigest = isset($_POST['daily_digest']) ? 1 : 0;
    
    try {
        // Check if preferences already exist for the user
        $stmt = $pdo->prepare("SELECT id FROM notification_preferences WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() > 0) {
            // Update existing preferences
            $stmt = $pdo->prepare("
                UPDATE notification_preferences 
                SET new_publication = ?, 
                    project_status_change = ?, 
                    new_researcher = ?, 
                    email_notifications = ?, 
                    daily_digest = ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$newPublication, $projectStatusChange, $newResearcher, $emailNotifications, $dailyDigest, $userId]);
        } else {
            // Insert new preferences
            $stmt = $pdo->prepare("
                INSERT INTO notification_preferences 
                (user_id, new_publication, project_status_change, new_researcher, email_notifications, daily_digest) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $newPublication, $projectStatusChange, $newResearcher, $emailNotifications, $dailyDigest]);
        }
        
        $_SESSION['success'] = "Notification preferences updated successfully";
        header("Location: notification_preferences.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating notification preferences: " . $e->getMessage();
        header("Location: notification_preferences.php");
        exit;
    }
}

// Fetch current preferences
try {
    $stmt = $pdo->prepare("SELECT * FROM notification_preferences WHERE user_id = ?");
    $stmt->execute([$userId]);
    $preferences = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Set default values if no preferences found
    if (!$preferences) {
        $preferences = [
            'new_publication' => 1,
            'project_status_change' => 1,
            'new_researcher' => 1,
            'email_notifications' => 1,
            'daily_digest' => 0
        ];
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching notification preferences: " . $e->getMessage();
    $preferences = [
        'new_publication' => 1,
        'project_status_change' => 1,
        'new_researcher' => 1,
        'email_notifications' => 1,
        'daily_digest' => 0
    ];
}

// Get user info for display
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$user = $userResult->fetch_assoc();

// Include header
require_once 'header.php';
?>

<div class="container mt-4">
    <h2>Notification Preferences</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success" role="alert">
            <?php 
                echo $_SESSION['success']; 
                unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php 
                echo $_SESSION['error']; 
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Manage Your Notification Settings</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="notification_preferences.php">
                <h5 class="mb-3">Notification Types</h5>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="new_publication" name="new_publication" <?php echo $preferences['new_publication'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="new_publication">
                        New Publications
                        <small class="text-muted d-block">Get notified when new publications are added to the system</small>
                    </label>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="project_status_change" name="project_status_change" <?php echo $preferences['project_status_change'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="project_status_change">
                        Project Status Changes
                        <small class="text-muted d-block">Get notified when a project's status changes</small>
                    </label>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="new_researcher" name="new_researcher" <?php echo $preferences['new_researcher'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="new_researcher">
                        New Researchers
                        <small class="text-muted d-block">Get notified when new researchers are added to the system</small>
                    </label>
                </div>
                
                <hr class="my-4">
                
                <h5 class="mb-3">Delivery Methods</h5>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" <?php echo $preferences['email_notifications'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="email_notifications">
                        Email Notifications
                        <small class="text-muted d-block">Receive notifications via email</small>
                    </label>
                </div>
                
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="daily_digest" name="daily_digest" <?php echo $preferences['daily_digest'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="daily_digest">
                        Daily Digest
                        <small class="text-muted d-block">Receive a daily summary of all notifications instead of individual emails</small>
                    </label>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save Preferences</button>
                    <a href="index.php" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
require_once 'footer.php';
?> 