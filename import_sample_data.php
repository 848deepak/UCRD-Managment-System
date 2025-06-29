<?php
require_once 'db.php';

// Clear session message if any
session_start();
unset($_SESSION['message']);
unset($_SESSION['message_type']);

// Check if the form was submitted
$import_confirmed = isset($_POST['confirm_import']) && $_POST['confirm_import'] === 'yes';
$import_attempted = isset($_POST['import_data']);

// Function to get count of records in a table
function getRecordCount($conn, $table) {
    $sql = "SELECT COUNT(*) as count FROM $table";
    $result = $conn->query($sql);
    return $result->fetch(PDO::FETCH_ASSOC)['count'];
}

// Get current record counts
$counts = [
    'supervisors' => getRecordCount($conn, 'Supervisor'),
    'researchers' => getRecordCount($conn, 'Researcher'),
    'projects' => getRecordCount($conn, 'Project'),
    'publications' => getRecordCount($conn, 'Publication'),
];

// Import data if confirmed
$import_success = false;
$error_message = '';

if ($import_confirmed) {
    // Start transaction
    $conn->beginTransaction();
    
    try {
        // Read SQL file
        $sql_file = file_get_contents('sample_data.sql');
        
        // Split into individual queries
        $queries = explode(';', $sql_file);
        
        // Execute each query
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query) && strpos($query, '--') !== 0) {
                // Skip the "USE" statement as we're already connected to the correct DB
                if (strpos($query, 'USE') !== 0) {
                    $conn->query($query);
                }
            }
        }
        
        // Commit transaction
        $conn->commit();
        $import_success = true;
        
        // Update counts after import
        $counts = [
            'supervisors' => getRecordCount($conn, 'Supervisor'),
            'researchers' => getRecordCount($conn, 'Researcher'),
            'projects' => getRecordCount($conn, 'Project'),
            'publications' => getRecordCount($conn, 'Publication'),
        ];
        
    } catch (Exception $e) {
        // Rollback the transaction if any query fails
        $conn->rollBack();
        $error_message = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Sample Data - UCRD Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-database me-2"></i>Import Sample Data</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($import_attempted && $import_success): ?>
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle me-2"></i>Import Successful!</h5>
                                <p>Sample data has been imported successfully.</p>
                                <hr>
                                <p><strong>Current record counts:</strong></p>
                                <ul>
                                    <li>Supervisors: <?php echo $counts['supervisors']; ?></li>
                                    <li>Researchers: <?php echo $counts['researchers']; ?></li>
                                    <li>Projects: <?php echo $counts['projects']; ?></li>
                                    <li>Publications: <?php echo $counts['publications']; ?></li>
                                </ul>
                                <div class="mt-3">
                                    <a href="index.php" class="btn btn-primary">Return to Dashboard</a>
                                </div>
                            </div>
                        <?php elseif ($import_attempted && !$import_success): ?>
                            <div class="alert alert-danger">
                                <h5><i class="fas fa-exclamation-circle me-2"></i>Import Failed</h5>
                                <p>An error occurred during the import process: <?php echo $error_message; ?></p>
                                <div class="mt-3">
                                    <a href="import_sample_data.php" class="btn btn-primary">Try Again</a>
                                    <a href="index.php" class="btn btn-secondary ms-2">Return to Dashboard</a>
                                </div>
                            </div>
                        <?php elseif (!$import_confirmed): ?>
                            <p>This tool will import sample data into your UCRD Management System database.</p>
                            
                            <div class="alert alert-info">
                                <h5><i class="fas fa-info-circle me-2"></i>Current Record Counts</h5>
                                <ul class="mb-0">
                                    <li>Supervisors: <?php echo $counts['supervisors']; ?></li>
                                    <li>Researchers: <?php echo $counts['researchers']; ?></li>
                                    <li>Projects: <?php echo $counts['projects']; ?></li>
                                    <li>Publications: <?php echo $counts['publications']; ?></li>
                                </ul>
                            </div>
                            
                            <div class="alert alert-warning">
                                <h5><i class="fas fa-exclamation-triangle me-2"></i>Warning</h5>
                                <p>The sample data will be added to any existing data. If you have existing records with the same ORCID values, you may encounter errors.</p>
                            </div>
                            
                            <form method="post" action="">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="confirm_import" name="confirm_import" value="yes" required>
                                        <label class="form-check-label" for="confirm_import">
                                            I understand the risks and want to proceed with importing sample data
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" name="import_data" class="btn btn-primary">Import Sample Data</button>
                                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 