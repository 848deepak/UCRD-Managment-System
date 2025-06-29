<?php
require_once '../db.php';
session_start();

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Project ID not provided.";
    $_SESSION['message_type'] = "danger";
    header("Location: view.php");
    exit();
}

$id = $_GET['id'];

// Check if project exists
$stmt = $conn->prepare("SELECT p.*, r.Name as ResearcherName, s.Name as SupervisorName 
                        FROM Project p 
                        LEFT JOIN Researcher r ON p.Researcher_ID = r.Researcher_ID 
                        LEFT JOIN Supervisor s ON p.Supervisor_ID = s.Supervisor_ID 
                        WHERE p.Project_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Project not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: view.php");
    exit();
}

$project = $result->fetch_assoc();
$stmt->close();

// Handle delete confirmation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_delete'])) {
    
    $stmt = $conn->prepare("DELETE FROM Project WHERE Project_ID = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Project '{$project['Title']}' deleted successfully.";
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
        <h2>Delete Project</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="view.php">Projects</a></li>
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
                <p>Are you sure you want to delete the project <strong><?php echo htmlspecialchars($project['Title']); ?></strong>?</p>
                
                <div class="mt-4 mb-4">
                    <h5>Project Details</h5>
                    <table class="table table-bordered">
                        <tr>
                            <th>ID</th>
                            <td><?php echo $project['Project_ID']; ?></td>
                        </tr>
                        <tr>
                            <th>Title</th>
                            <td><?php echo htmlspecialchars($project['Title']); ?></td>
                        </tr>
                        <tr>
                            <th>Researcher</th>
                            <td><?php echo $project['ResearcherName'] ? htmlspecialchars($project['ResearcherName']) : 'Not assigned'; ?></td>
                        </tr>
                        <tr>
                            <th>Supervisor</th>
                            <td><?php echo $project['SupervisorName'] ? htmlspecialchars($project['SupervisorName']) : 'Not assigned'; ?></td>
                        </tr>
                        <tr>
                            <th>Start Date</th>
                            <td><?php echo date('M d, Y', strtotime($project['Start_Date'])); ?></td>
                        </tr>
                        <tr>
                            <th>End Date</th>
                            <td>
                                <?php 
                                if ($project['End_Date']) {
                                    echo date('M d, Y', strtotime($project['End_Date']));
                                } else {
                                    echo '<span class="text-muted">Ongoing</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php 
                                $statusClass = 'secondary';
                                switch ($project['Status']) {
                                    case 'Not Started':
                                        $statusClass = 'warning';
                                        break;
                                    case 'In Progress':
                                        $statusClass = 'info';
                                        break;
                                    case 'Completed':
                                        $statusClass = 'success';
                                        break;
                                    case 'On Hold':
                                        $statusClass = 'secondary';
                                        break;
                                    case 'Cancelled':
                                        $statusClass = 'danger';
                                        break;
                                }
                                ?>
                                <span class="badge bg-<?php echo $statusClass; ?>"><?php echo $project['Status']; ?></span>
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