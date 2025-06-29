<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    $_SESSION['error'] = "You don't have permission to access this page";
    header("Location: index.php");
    exit();
}

// Function to generate a database backup
function backupDatabase($host, $user, $pass, $dbname, $tables = '*') {
    // Connect to the database
    $link = mysqli_connect($host, $user, $pass, $dbname);
    if (mysqli_connect_errno()) {
        return "Failed to connect to MySQL: " . mysqli_connect_error();
    }

    // Get all tables if not specified
    if ($tables == '*') {
        $tables = array();
        $result = mysqli_query($link, 'SHOW TABLES');
        while ($row = mysqli_fetch_row($result)) {
            $tables[] = $row[0];
        }
    } else {
        $tables = is_array($tables) ? $tables : explode(',', $tables);
    }
    
    // Start the output buffer
    ob_start();
    
    // Add SQL header comment
    echo "-- UCRD Management System Database Backup\n";
    echo "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Database: " . $dbname . "\n\n";
    
    // Iterate through the tables
    foreach ($tables as $table) {
        $result = mysqli_query($link, 'SELECT * FROM ' . $table);
        $numFields = mysqli_num_fields($result);
        
        echo "-- Table structure for table `$table`\n";
        
        // Get create table syntax
        $row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE ' . $table));
        echo $row2[1] . ";\n\n";
        
        echo "-- Dumping data for table `$table`\n";
        
        // If table has data, create insert statements
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_row($result)) {
                echo "INSERT INTO `$table` VALUES(";
                for ($j=0; $j < $numFields; $j++) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n", "\\n", $row[$j]);
                    
                    if (isset($row[$j])) {
                        echo '"' . $row[$j] . '"';
                    } else {
                        echo 'NULL';
                    }
                    
                    if ($j < ($numFields-1)) {
                        echo ',';
                    }
                }
                echo ");\n";
            }
        }
        echo "\n\n";
    }
    
    $content = ob_get_clean();
    
    mysqli_close($link);
    return $content;
}

// Create backup if requested
if (isset($_POST['create_backup'])) {
    $backup = backupDatabase($servername, $username, $password, $dbname);
    
    // Set headers for file download
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="ucrd_backup_' . date('Y-m-d_H-i-s') . '.sql"');
    header('Content-Length: ' . strlen($backup));
    
    // Output the backup
    echo $backup;
    exit;
}

// Include header after any potential header() calls
include 'header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4">Database Backup</h2>
            
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Database Backup</li>
                </ol>
            </nav>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-database me-2"></i>Backup Database</h5>
                </div>
                <div class="card-body">
                    <p>Create a backup of the entire database. This backup can be used to restore the database in case of data loss.</p>
                    <p><strong>Note:</strong> The backup includes all tables, data, and structure.</p>
                    
                    <form method="post" action="">
                        <button type="submit" name="create_backup" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Generate & Download Backup
                        </button>
                    </form>
                </div>
                <div class="card-footer">
                    <div class="alert alert-info mb-0">
                        <h5><i class="fas fa-info-circle me-2"></i>About Database Backups</h5>
                        <p class="mb-0">Regular backups are essential for data protection. It's recommended to create backups:</p>
                        <ul class="mb-0">
                            <li>Before making significant changes to the database structure</li>
                            <li>On a regular schedule (daily/weekly/monthly)</li>
                            <li>Before system updates or migrations</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Restore Database</h5>
                </div>
                <div class="card-body">
                    <p>To restore a database backup, you will need to use your database management tool (e.g., phpMyAdmin) or contact your system administrator.</p>
                    <p><strong>Warning:</strong> Restoring a backup will overwrite any existing data in the database.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 