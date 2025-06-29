<?php
require_once '../db.php';
include '../header.php';

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Publication ID not provided.";
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
    $date_published = trim($_POST['date_published']);
    $doi = !empty($_POST['doi']) ? trim($_POST['doi']) : null;
    
    // Basic validation
    $errors = array();
    
    if (empty($title)) {
        $errors[] = "Title is required";
    }
    
    if (empty($date_published)) {
        $errors[] = "Publication date is required";
    }
    
    // Check if DOI already exists with a different publication
    if (!empty($doi)) {
        $stmt = $conn->prepare("SELECT Publication_ID FROM Publication WHERE DOI = ? AND Publication_ID != ?");
        $stmt->bind_param("si", $doi, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "DOI already exists. Please use a unique DOI.";
        }
        $stmt->close();
    }
    
    // If no errors, update data
    if (empty($errors)) {
        if (empty($doi)) {
            $stmt = $conn->prepare("UPDATE Publication SET Title = ?, Researcher_ID = ?, Supervisor_ID = ?, Date_Published = ?, DOI = NULL WHERE Publication_ID = ?");
            $stmt->bind_param("siisi", $title, $researcher_id, $supervisor_id, $date_published, $id);
        } else {
            $stmt = $conn->prepare("UPDATE Publication SET Title = ?, Researcher_ID = ?, Supervisor_ID = ?, Date_Published = ?, DOI = ? WHERE Publication_ID = ?");
            $stmt->bind_param("siissi", $title, $researcher_id, $supervisor_id, $date_published, $doi, $id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Publication updated successfully!";
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

// Get publication data
$stmt = $conn->prepare("SELECT * FROM Publication WHERE Publication_ID = ?");
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
?>

<div class="row">
    <div class="col-md-12">
        <h2>Edit Publication</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="view.php">Publications</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Edit Publication Information</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" method="post">
                    <div class="mb-3">
                        <label for="title" class="form-label required">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required value="<?php echo htmlspecialchars($publication['Title']); ?>">
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
                                <option value="<?php echo $researcher['Researcher_ID']; ?>" <?php echo ($publication['Researcher_ID'] == $researcher['Researcher_ID']) ? 'selected' : ''; ?>>
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
                                <option value="<?php echo $supervisor['Supervisor_ID']; ?>" <?php echo ($publication['Supervisor_ID'] == $supervisor['Supervisor_ID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($supervisor['Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="date_published" class="form-label required">Publication Date</label>
                        <input type="date" class="form-control" id="date_published" name="date_published" required value="<?php echo $publication['Date_Published']; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="doi" class="form-label">DOI (Digital Object Identifier)</label>
                        <input type="text" class="form-control" id="doi" name="doi" placeholder="e.g., 10.1000/xyz123" value="<?php echo htmlspecialchars($publication['DOI'] ?? ''); ?>">
                        <div class="form-text">The unique identifier for the publication (e.g., 10.1000/xyz123).</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Update Publication</button>
                        <a href="view.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 