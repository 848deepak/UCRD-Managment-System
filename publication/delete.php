<?php
require_once '../db.php';
session_start();

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Publication ID not provided.";
    $_SESSION['message_type'] = "danger";
    header("Location: view.php");
    exit();
}

$id = $_GET['id'];

// Check if publication exists
$stmt = $conn->prepare("SELECT p.*, r.Name as ResearcherName, s.Name as SupervisorName 
                        FROM Publication p 
                        LEFT JOIN Researcher r ON p.Researcher_ID = r.Researcher_ID 
                        LEFT JOIN Supervisor s ON p.Supervisor_ID = s.Supervisor_ID 
                        WHERE p.Publication_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Publication not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: view.php");
    exit();
}

$publication = $result->fetch_assoc();
$stmt->close();

// Handle delete confirmation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete'])) {
    
    $stmt = $conn->prepare("DELETE FROM Publication WHERE Publication_ID = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Publication '{$publication['Title']}' deleted successfully.";
        $_SESSION['message_type'] = "success";
        header("Location: view.php");
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
        header("Location: view.php");
        exit();
    }
    
    $stmt->close();
}

include '../header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h2>Delete Publication</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="view.php">Publications</a></li>
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
                <p>Are you sure you want to delete the publication <strong><?php echo htmlspecialchars($publication['Title']); ?></strong>?</p>
                
                <div class="mt-4 mb-4">
                    <h5>Publication Details</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>ID</th>
                            <td><?php echo $publication['Publication_ID']; ?></td>
                        </tr>
                        <tr>
                            <th>Title</th>
                            <td><?php echo htmlspecialchars($publication['Title']); ?></td>
                        </tr>
                        <tr>
                            <th>Researcher</th>
                            <td><?php echo $publication['ResearcherName'] ? htmlspecialchars($publication['ResearcherName']) : 'Not assigned'; ?></td>
                        </tr>
                        <tr>
                            <th>Supervisor</th>
                            <td><?php echo $publication['SupervisorName'] ? htmlspecialchars($publication['SupervisorName']) : 'Not assigned'; ?></td>
                        </tr>
                        <tr>
                            <th>Publication Date</th>
                            <td><?php echo date('M d, Y', strtotime($publication['Date_Published'])); ?></td>
                        </tr>
                        <tr>
                            <th>DOI</th>
                            <td>
                                <?php 
                                if (!empty($publication['DOI'])) {
                                    echo htmlspecialchars($publication['DOI']);
                                } else {
                                    echo '<span class="text-muted">Not available</span>';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-circle"></i> This action cannot be undone.
                </div>
                
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