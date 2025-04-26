<?php
require_once '../db.php';
include '../header.php';

// Get all researchers for dropdown
$stmt = $conn->prepare("SELECT Researcher_ID, Name FROM Researcher ORDER BY Name");
$stmt->execute();
$researchers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all supervisors for dropdown
$stmt = $conn->prepare("SELECT Supervisor_ID, Name FROM Supervisor ORDER BY Name");
$stmt->execute();
$supervisors = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    
    // If no errors, insert data
    if (empty($errors)) {
        try {
            if (empty($end_date)) {
                $stmt = $conn->prepare("INSERT INTO Project (Title, Researcher_ID, Supervisor_ID, Start_Date, Status) VALUES (:title, :researcher_id, :supervisor_id, :start_date, :status)");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':researcher_id', $researcher_id);
                $stmt->bindParam(':supervisor_id', $supervisor_id);
                $stmt->bindParam(':start_date', $start_date);
                $stmt->bindParam(':status', $status);
            } else {
                $stmt = $conn->prepare("INSERT INTO Project (Title, Researcher_ID, Supervisor_ID, Start_Date, End_Date, Status) VALUES (:title, :researcher_id, :supervisor_id, :start_date, :end_date, :status)");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':researcher_id', $researcher_id);
                $stmt->bindParam(':supervisor_id', $supervisor_id);
                $stmt->bindParam(':start_date', $start_date);
                $stmt->bindParam(':end_date', $end_date);
                $stmt->bindParam(':status', $status);
            }
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Project added successfully!";
                $_SESSION['message_type'] = "success";
                header("Location: view.php");
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['message'] = "Error: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    } else {
        // Display validation errors
        $_SESSION['message'] = "Please fix the following errors: " . implode(", ", $errors);
        $_SESSION['message_type'] = "danger";
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h2>Add Project</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="view.php">Projects</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add New</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Project Information</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label required">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="researcher_id" class="form-label">Researcher</label>
                        <select class="form-select" id="researcher_id" name="researcher_id">
                            <option value="">-- Select Researcher --</option>
                            <?php foreach ($researchers as $researcher): ?>
                                <option value="<?php echo $researcher['Researcher_ID']; ?>" <?php echo (isset($_POST['researcher_id']) && $_POST['researcher_id'] == $researcher['Researcher_ID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($researcher['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="supervisor_id" class="form-label">Supervisor</label>
                        <select class="form-select" id="supervisor_id" name="supervisor_id">
                            <option value="">-- Select Supervisor --</option>
                            <?php foreach ($supervisors as $supervisor): ?>
                                <option value="<?php echo $supervisor['Supervisor_ID']; ?>" <?php echo (isset($_POST['supervisor_id']) && $_POST['supervisor_id'] == $supervisor['Supervisor_ID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($supervisor['Name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="start_date" class="form-label required">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>">
                        <div class="form-text">Leave blank if the project is ongoing.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label required">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="">-- Select Status --</option>
                            <option value="Not Started" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Not Started') ? 'selected' : ''; ?>>Not Started</option>
                            <option value="In Progress" <?php echo (isset($_POST['status']) && $_POST['status'] == 'In Progress') ? 'selected' : (!isset($_POST['status']) ? 'selected' : ''); ?>>In Progress</option>
                            <option value="Completed" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
                            <option value="On Hold" <?php echo (isset($_POST['status']) && $_POST['status'] == 'On Hold') ? 'selected' : ''; ?>>On Hold</option>
                            <option value="Cancelled" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Add Project</button>
                        <a href="view.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 