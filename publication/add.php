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
    
    // Check if DOI already exists
    if (!empty($doi)) {
        $stmt = $conn->prepare("SELECT Publication_ID FROM Publication WHERE DOI = :doi");
        $stmt->bindParam(':doi', $doi);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "DOI already exists. Please use a unique DOI.";
        }
    }
    
    // If no errors, insert data
    if (empty($errors)) {
        try {
            if (empty($doi)) {
                $stmt = $conn->prepare("INSERT INTO Publication (Title, Researcher_ID, Supervisor_ID, Date_Published) VALUES (:title, :researcher_id, :supervisor_id, :date_published)");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':researcher_id', $researcher_id);
                $stmt->bindParam(':supervisor_id', $supervisor_id);
                $stmt->bindParam(':date_published', $date_published);
            } else {
                $stmt = $conn->prepare("INSERT INTO Publication (Title, Researcher_ID, Supervisor_ID, Date_Published, DOI) VALUES (:title, :researcher_id, :supervisor_id, :date_published, :doi)");
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':researcher_id', $researcher_id);
                $stmt->bindParam(':supervisor_id', $supervisor_id);
                $stmt->bindParam(':date_published', $date_published);
                $stmt->bindParam(':doi', $doi);
            }
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Publication added successfully!";
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
        <h2>Add Publication</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="view.php">Publications</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add New</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Publication Information</h4>
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
                        <label for="date_published" class="form-label required">Publication Date</label>
                        <input type="date" class="form-control" id="date_published" name="date_published" required value="<?php echo isset($_POST['date_published']) ? htmlspecialchars($_POST['date_published']) : date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="doi" class="form-label">DOI (Digital Object Identifier)</label>
                        <input type="text" class="form-control" id="doi" name="doi" placeholder="e.g., 10.1000/xyz123" value="<?php echo isset($_POST['doi']) ? htmlspecialchars($_POST['doi']) : ''; ?>">
                        <div class="form-text">The unique identifier for the publication (e.g., 10.1000/xyz123).</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Add Publication</button>
                        <a href="view.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 