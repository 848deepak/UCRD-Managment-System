<?php
require_once '../db.php';
include '../header.php';

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Researcher ID not provided.";
    $_SESSION['message_type'] = "danger";
    header("Location: view.php");
    exit();
}

$id = $_GET['id'];

// Get all supervisors for dropdown
$stmt = $conn->prepare("SELECT Supervisor_ID, Name FROM Supervisor ORDER BY Name");
$stmt->execute();
$supervisors = $stmt->get_result();
$stmt->close();

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
    
    // Check if ORCID already exists with a different researcher
    if (!empty($orcid)) {
        $stmt = $conn->prepare("SELECT Researcher_ID FROM Researcher WHERE ORCID = ? AND Researcher_ID != ?");
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
        if ($supervisor_id === null) {
            if (empty($orcid)) {
                $stmt = $conn->prepare("UPDATE Researcher SET Name = ?, Email = ?, Phone = ?, Department = ?, Enrollment_Year = ?, Supervisor_ID = NULL, ORCID = NULL WHERE Researcher_ID = ?");
                $stmt->bind_param("ssssis", $name, $email, $phone, $department, $enrollment_year, $id);
            } else {
                $stmt = $conn->prepare("UPDATE Researcher SET Name = ?, Email = ?, Phone = ?, Department = ?, Enrollment_Year = ?, Supervisor_ID = NULL, ORCID = ? WHERE Researcher_ID = ?");
                $stmt->bind_param("ssssis", $name, $email, $phone, $department, $enrollment_year, $orcid, $id);
            }
        } else {
            if (empty($orcid)) {
                $stmt = $conn->prepare("UPDATE Researcher SET Name = ?, Email = ?, Phone = ?, Department = ?, Enrollment_Year = ?, Supervisor_ID = ?, ORCID = NULL WHERE Researcher_ID = ?");
                $stmt->bind_param("ssssiii", $name, $email, $phone, $department, $enrollment_year, $supervisor_id, $id);
            } else {
                $stmt = $conn->prepare("UPDATE Researcher SET Name = ?, Email = ?, Phone = ?, Department = ?, Enrollment_Year = ?, Supervisor_ID = ?, ORCID = ? WHERE Researcher_ID = ?");
                $stmt->bind_param("ssssiiis", $name, $email, $phone, $department, $enrollment_year, $supervisor_id, $orcid, $id);
            }
        }
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Researcher updated successfully!";
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

// Get researcher data
$stmt = $conn->prepare("SELECT * FROM Researcher WHERE Researcher_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Researcher not found.";
    $_SESSION['message_type'] = "danger";
    header("Location: view.php");
    exit();
}

$researcher = $result->fetch_assoc();
$stmt->close();
?>

<div class="row">
    <div class="col-md-12">
        <h2>Edit Researcher</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="view.php">Researchers</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <div class="card">
            <div class="card-header">
                <h4>Edit Researcher Information</h4>
            </div>
            <div class="card-body">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" method="post">
                    <div class="mb-3">
                        <label for="name" class="form-label required">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($researcher['Name']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label required">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($researcher['Email']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label required">Phone</label>
                        <input type="text" class="form-control" id="phone" name="phone" required value="<?php echo htmlspecialchars($researcher['Phone']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="department" class="form-label required">Department</label>
                        <input type="text" class="form-control" id="department" name="department" required value="<?php echo htmlspecialchars($researcher['Department']); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="enrollment_year" class="form-label required">Enrollment Year</label>
                        <input type="number" class="form-control" id="enrollment_year" name="enrollment_year" min="1900" max="<?php echo date('Y'); ?>" required value="<?php echo $researcher['Enrollment_Year']; ?>">
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
                                <option value="<?php echo $supervisor['Supervisor_ID']; ?>" 
                                    <?php echo ($researcher['Supervisor_ID'] == $supervisor['Supervisor_ID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($supervisor['Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="orcid" class="form-label">ORCID</label>
                        <input type="text" class="form-control" id="orcid" name="orcid" placeholder="e.g., 0000-0002-1825-0097" value="<?php echo htmlspecialchars($researcher['ORCID'] ?? ''); ?>">
                        <div class="form-text">The unique identifier for the researcher in the ORCID registry.</div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">Update Researcher</button>
                        <a href="view.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?> 