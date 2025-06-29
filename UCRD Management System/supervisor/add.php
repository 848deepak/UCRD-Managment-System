<?php
require_once '../db.php';
include '../header.php';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data
    $name = trim($_POST['name']);
    $department = trim($_POST['department']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $orcid = trim($_POST['orcid']);
    
    // Basic validation
    $errors = array();
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($department)) {
        $errors[] = "Department is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone is required";
    }
    
    // Check if ORCID already exists
    if (!empty($orcid)) {
        $stmt = $conn->prepare("SELECT Supervisor_ID FROM Supervisor WHERE ORCID = ?");
        $stmt->bindValue(1, $orcid);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            $errors[] = "ORCID already exists. Please use a unique ORCID.";
        }
    }
    
    // If no errors, insert data
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO Supervisor (Name, Department, Email, Phone, ORCID) VALUES (?, ?, ?, ?, ?)");
        $stmt->bindValue(1, $name);
        $stmt->bindValue(2, $department);
        $stmt->bindValue(3, $email);
        $stmt->bindValue(4, $phone);
        $stmt->bindValue(5, $orcid);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Supervisor added successfully!";
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
        <h2>Add Supervisor</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="view.php">Supervisors</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add New</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Supervisor Information</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label required">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="department" class="form-label required">Department</label>
                        <input type="text" class="form-control" id="department" name="department" required value="<?php echo isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''; ?>">
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
                        <label for="orcid" class="form-label">ORCID</label>
                        <input type="text" class="form-control" id="orcid" name="orcid" placeholder="e.g., 0000-0002-1825-0097" value="<?php echo isset($_POST['orcid']) ? htmlspecialchars($_POST['orcid']) : ''; ?>">
                        <div class="form-text">The unique identifier for the supervisor in the ORCID registry.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Add Supervisor</button>
                        <a href="view.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 