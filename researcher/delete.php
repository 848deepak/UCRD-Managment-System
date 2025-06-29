<?php
require_once '../db.php';
session_start();

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Researcher ID not provided.";
    $_SESSION['message_type'] = "danger";
    header("Location: view.php");
    exit();
}

$id = $_GET['id'];

// Check if researcher has associated projects or publications
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM Project WHERE Researcher_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$project_count = $result->fetch_assoc()['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM Publication WHERE Researcher_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$publication_count = $result->fetch_assoc()['count'];
$stmt->close();

// Check if researcher exists
$stmt = $conn->prepare("SELECT Name FROM Researcher WHERE Researcher_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Researcher not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: view.php");
    exit();
}

$researcher_name = $result->fetch_assoc()['Name'];
$stmt->close();

// Handle delete confirmation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete'])) {
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Update Project and Publication tables to remove researcher reference
        $stmt = $conn->prepare("UPDATE Project SET Researcher_ID = NULL WHERE Researcher_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $conn->prepare("UPDATE Publication SET Researcher_ID = NULL WHERE Researcher_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Delete the researcher
        $stmt = $conn->prepare("DELETE FROM Researcher WHERE Researcher_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        
        // Commit the transaction
        $conn->commit();
        
        $_SESSION['message'] = "Researcher '{$researcher_name}' deleted successfully.";
        $_SESSION['message_type'] = "success";
        header("Location: view.php");
        exit();
    } catch (Exception $e) {
        // Rollback the transaction if any query fails
        $conn->rollback();
        
        $_SESSION['message'] = "Error: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
        header("Location: view.php");
        exit();
    }
}

include '../header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h2>Delete Researcher</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="view.php">Researchers</a></li>
                <li class="breadcrumb-item active" aria-current="page">Delete</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h4><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h4>
            </div>
            <div class="card-body">
                <p>Are you sure you want to delete the researcher <strong><?php echo htmlspecialchars($researcher_name); ?></strong>?</p>
                
                <?php if ($project_count > 0 || $publication_count > 0): ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-circle"></i> Warning</h5>
                        <p>This researcher is currently associated with:</p>
                        <ul>
                            <?php if ($project_count > 0): ?>
                                <li><?php echo $project_count; ?> project<?php echo $project_count > 1 ? 's' : ''; ?></li>
                            <?php endif; ?>
                            
                            <?php if ($publication_count > 0): ?>
                                <li><?php echo $publication_count; ?> publication<?php echo $publication_count > 1 ? 's' : ''; ?></li>
                            <?php endif; ?>
                        </ul>
                        <p>Deleting this researcher will remove these associations, but the related items will not be deleted.</p>
                    </div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" method="post">
                    <div class="d-grid gap-2">
                        <button type="submit" name="confirm_delete" class="btn btn-danger">Confirm Delete</button>
                        <a href="view.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 