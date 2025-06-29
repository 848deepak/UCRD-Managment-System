<?php
require_once '../db.php';
include '../header.php';

// Get all supervisors for dropdown
$stmt = $conn->prepare("SELECT Supervisor_ID, Name FROM Supervisor ORDER BY Name");
$stmt->execute();
$supervisors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $department = trim($_POST['department']);
    $enrollment_year = trim($_POST['enrollment_year']);
    $supervisor_id = !empty($_POST['supervisor_id']) ? $_POST['supervisor_id'] : null;
    $orcid = trim($_POST['orcid']);
    
    // Basic validation
    $errors = array();
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone is required";
    }
    
    if (empty($department)) {
        $errors[] = "Department is required";
    }
    
    if (empty($enrollment_year)) {
        $errors[] = "Enrollment year is required";
    } elseif (!is_numeric($enrollment_year) || $enrollment_year < 1900 || $enrollment_year > date('Y')) {
        $errors[] = "Invalid enrollment year";
    }
    
    // Check if ORCID already exists
    if (!empty($orcid)) {
        $stmt = $conn->prepare("SELECT Researcher_ID FROM Researcher WHERE ORCID = ?");
        $stmt->bindValue(1, $orcid);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $errors[] = "ORCID already exists. Please use a unique ORCID.";
        }
    }
    
    // If no errors, insert data
    if (empty($errors)) {
        if ($supervisor_id === null) {
            $stmt = $conn->prepare("INSERT INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, ORCID) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bindValue(1, $name);
            $stmt->bindValue(2, $email);
            $stmt->bindValue(3, $phone);
            $stmt->bindValue(4, $department);
            $stmt->bindValue(5, $enrollment_year, PDO::PARAM_INT);
            $stmt->bindValue(6, $orcid);
        } else {
            $stmt = $conn->prepare("INSERT INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, Supervisor_ID, ORCID) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bindValue(1, $name);
            $stmt->bindValue(2, $email);
            $stmt->bindValue(3, $phone);
            $stmt->bindValue(4, $department);
            $stmt->bindValue(5, $enrollment_year, PDO::PARAM_INT);
            $stmt->bindValue(6, $supervisor_id, PDO::PARAM_INT);
            $stmt->bindValue(7, $orcid);
        }
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Researcher added successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: view.php");
            exit();
        } else {
            $_SESSION['message'] = "Error: " . implode(", ", $stmt->errorInfo());
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
        <h2>Add Researcher</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="view.php">Researchers</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add New</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Researcher Information</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label required">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label required">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="department" class="form-label required">Department</label>
                        <input type="text" class="form-control" id="department" name="department" required value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="enrollment_year" class="form-label required">Enrollment Year</label>
                        <input type="number" class="form-control" id="enrollment_year" name="enrollment_year" min="1900" max="<?php echo date('Y'); ?>" required value="<?php echo isset($_POST['enrollment_year']) ? htmlspecialchars($_POST['enrollment_year']) : date('Y'); ?>">
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
                        <label for="orcid" class="form-label">ORCID</label>
                        <input type="text" class="form-control" id="orcid" name="orcid" placeholder="e.g., 0000-0002-1825-0097" value="<?php echo isset($_POST['orcid']) ? htmlspecialchars($_POST['orcid']) : ''; ?>">
                        <div class="form-text">The unique identifier for the researcher in the ORCID registry.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Add Researcher</button>
                        <a href="view.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 