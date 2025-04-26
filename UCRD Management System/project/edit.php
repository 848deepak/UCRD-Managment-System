<?php
require_once '../db.php';
include '../header.php';

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Project ID not provided.";
    $_SESSION['message_type'] = "danger";
    header("Location: view.php");
    exit();
}

$id = $_GET['id'];

// Get all researchers for dropdown
$stmt = $conn->prepare("SELECT Researcher_ID, Name FROM Researcher ORDER BY Name");
$stmt->execute();
$researchers = $stmt->get_result();
$stmt->close();

// Get all supervisors for dropdown
$stmt = $conn->prepare("SELECT Supervisor_ID, Name FROM Supervisor ORDER BY Name");
$stmt->execute();
$supervisors = $stmt->get_result();
$stmt->close();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data
    $title = trim($_POST['title']);
    $researcher_id = !empty($_POST['researcher_id']) ? $_POST['researcher_id'] : null;
    $supervisor_id = !empty($_POST['supervisor_id']) ? $_POST['supervisor_id'] : null;
    $start_date = trim($_POST['start_date']);
    $end_date = !empty($_POST['end_date']) ? trim($_POST['end_date']) : null;
    $status = trim($_POST['status']);
    
    // Basic validation
    $errors = array();
    
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    
    if (empty($start_date)) {
        $errors[] = "Start date is required";
    }
    
    if (empty($status)) {
        $errors[] = "Status is required";
    }
    
    // Validate dates if both are provided
    if (!empty($start_date) && !empty($end_date) && strtotime($end_date) < strtotime($start_date)) {
        $errors[] = "End date cannot be earlier than start date";
    }
    
    // If no errors, update data
    if (empty($errors)) {
        if (empty($end_date)) {
            $stmt = $conn->prepare("UPDATE Project SET Title = ?, Researcher_ID = ?, Supervisor_ID = ?, Start_Date = ?, End_Date = NULL, Status = ? WHERE Project_ID = ?");
            $stmt->bind_param("siissi", $title, $researcher_id, $supervisor_id, $start_date, $status, $id);
        } else {
            $stmt = $conn->prepare("UPDATE Project SET Title = ?, Researcher_ID = ?, Supervisor_ID = ?, Start_Date = ?, End_Date = ?, Status = ? WHERE Project_ID = ?");
            $stmt->bind_param("siisssi", $title, $researcher_id, $supervisor_id, $start_date, $end_date, $status, $id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Project updated successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: view.php");
            exit();
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        
        $stmt->close();
    } else {
        // Display validation errors
        $_SESSION['message'] = "Please fix the following errors: " . implode(", ", $errors);
        $_SESSION['message_type'] = "danger";
    }
}

// Get project data
$stmt = $conn->prepare("SELECT * FROM Project WHERE Project_ID = ?");
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
?>

<div class="row">
    <div class="col-md-12">
        <h2>Edit Project</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="view.php">Projects</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Edit Project Information</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label required">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required value="<?php echo htmlspecialchars($project['Title']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="researcher_id" class="form-label">Researcher</label>
                        <select class="form-select" id="researcher_id" name="researcher_id">
                            <option value="">-- Select Researcher --</option>
                            <?php 
                            // Reset the result set pointer
                            $researchers->data_seek(0);
                            while ($researcher = $researchers->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $researcher['Researcher_ID']; ?>" <?php echo ($project['Researcher_ID'] == $researcher['Researcher_ID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($researcher['Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supervisor_id" class="form-label">Supervisor</label>
                        <select class="form-select" id="supervisor_id" name="supervisor_id">
                            <option value="">-- Select Supervisor --</option>
                            <?php 
                            // Reset the result set pointer
                            $supervisors->data_seek(0);
                            while ($supervisor = $supervisors->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $supervisor['Supervisor_ID']; ?>" <?php echo ($project['Supervisor_ID'] == $supervisor['Supervisor_ID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($supervisor['Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_date" class="form-label required">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required value="<?php echo $project['Start_Date']; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $project['End_Date'] ?? ''; ?>">
                        <div class="form-text">Leave blank if the project is ongoing.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label required">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">-- Select Status --</option>
                            <option value="Not Started" <?php echo ($project['Status'] == 'Not Started') ? 'selected' : ''; ?>>Not Started</option>
                            <option value="In Progress" <?php echo ($project['Status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                            <option value="Completed" <?php echo ($project['Status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="On Hold" <?php echo ($project['Status'] == 'On Hold') ? 'selected' : ''; ?>>On Hold</option>
                            <option value="Cancelled" <?php echo ($project['Status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Update Project</button>
                        <a href="view.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 