<?php
require_once '../db.php';
include '../header.php';

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Supervisor ID not provided.";
    $_SESSION['message_type'] = "danger";
    header("Location: view.php");
    exit();
}

$id = $_GET['id'];

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
    
    // Check if ORCID already exists with a different supervisor
    if (!empty($orcid)) {
        $stmt = $conn->prepare("SELECT Supervisor_ID FROM Supervisor WHERE ORCID = ? AND Supervisor_ID != ?");
        $stmt->bind_param("si", $orcid, $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "ORCID already exists. Please use a unique ORCID.";
        }
        $stmt->close();
    }
    
    // If no errors, update data
    if (empty($errors)) {
        if (empty($orcid)) {
            $stmt = $conn->prepare("UPDATE Supervisor SET Name = ?, Department = ?, Email = ?, Phone = ?, ORCID = NULL WHERE Supervisor_ID = ?");
            $stmt->bind_param("ssssi", $name, $department, $email, $phone, $id);
        } else {
            $stmt = $conn->prepare("UPDATE Supervisor SET Name = ?, Department = ?, Email = ?, Phone = ?, ORCID = ? WHERE Supervisor_ID = ?");
            $stmt->bind_param("sssssi", $name, $department, $email, $phone, $orcid, $id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Supervisor updated successfully!";
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

// Get supervisor data
$stmt = $conn->prepare("SELECT * FROM Supervisor WHERE Supervisor_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Supervisor not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: view.php");
    exit();
}

$supervisor = $result->fetch_assoc();
$stmt->close();
?>

<div class="row">
    <div class="col-md-12">
        <h2>Edit Supervisor</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="view.php">Supervisors</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Edit Supervisor Information</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label required">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($supervisor['Name']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="department" class="form-label required">Department</label>
                        <input type="text" class="form-control" id="department" name="department" required value="<?php echo htmlspecialchars($supervisor['Department']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($supervisor['Email']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label required">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" required value="<?php echo htmlspecialchars($supervisor['Phone']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="orcid" class="form-label">ORCID</label>
                        <input type="text" class="form-control" id="orcid" name="orcid" placeholder="e.g., 0000-0002-1825-0097" value="<?php echo htmlspecialchars($supervisor['ORCID'] ?? ''); ?>">
                        <div class="form-text">The unique identifier for the supervisor in the ORCID registry.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Update Supervisor</button>
                        <a href="view.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 