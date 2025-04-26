<?php
// Direct import script - bypasses the web interface

// Include database connection
require_once 'db.php';

echo "Starting direct import process...\n";

// Get current record counts
function getRecordCount($conn, $table) {
    $sql = "SELECT COUNT(*) as count FROM $table";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['count'];
}

// Display current counts
echo "Current record counts before import:\n";
echo "- Supervisors: " . getRecordCount($conn, 'Supervisor') . "\n";
echo "- Researchers: " . getRecordCount($conn, 'Researcher') . "\n";
echo "- Projects: " . getRecordCount($conn, 'Project') . "\n";
echo "- Publications: " . getRecordCount($conn, 'Publication') . "\n";

// Start transaction
$conn->begin_transaction();

try {
    echo "Beginning transaction...\n";
    
    // Individual INSERT statements for Supervisors
    echo "Importing supervisors...\n";
    $conn->query("INSERT INTO Supervisor (Name, Department, Email, Phone, ORCID) VALUES 
    ('Dr. Rajesh Kumar', 'Computer Science', 'rkumar@university.edu', '555-123-4567', '0000-0002-1825-0097')");
    
    $conn->query("INSERT INTO Supervisor (Name, Department, Email, Phone, ORCID) VALUES 
    ('Prof. Priya Sharma', 'Electrical Engineering', 'psharma@university.edu', '555-234-5678', '0000-0001-5432-1234')");
    
    $conn->query("INSERT INTO Supervisor (Name, Department, Email, Phone, ORCID) VALUES 
    ('Dr. Vikram Patel', 'Data Science', 'vpatel@university.edu', '555-345-6789', '0000-0003-9876-5432')");
    
    $conn->query("INSERT INTO Supervisor (Name, Department, Email, Phone, ORCID) VALUES 
    ('Prof. Neha Mehta', 'Artificial Intelligence', 'nmehta@university.edu', '555-456-7890', '0000-0002-6543-2109')");
    
    $conn->query("INSERT INTO Supervisor (Name, Department, Email, Phone, ORCID) VALUES 
    ('Dr. Arjun Singh', 'Cybersecurity', 'asingh@university.edu', '555-567-8901', '0000-0001-8765-4321')");
    
    // Get the actual supervisor IDs after insertion
    $supervisorQuery = "SELECT Supervisor_ID, Name FROM Supervisor";
    $supervisorResult = $conn->query($supervisorQuery);
    $supervisors = [];
    while ($row = $supervisorResult->fetch_assoc()) {
        $supervisors[$row['Name']] = $row['Supervisor_ID'];
    }
    
    // Individual INSERT statements for Researchers
    echo "Importing researchers...\n";
    $conn->query("INSERT INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, Supervisor_ID, ORCID) VALUES 
    ('Amit Verma', 'averma@university.edu', '555-987-6543', 'Computer Science', 2021, {$supervisors['Dr. Rajesh Kumar']}, '0000-0001-2345-6789')");
    
    $conn->query("INSERT INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, Supervisor_ID, ORCID) VALUES 
    ('Divya Gupta', 'dgupta@university.edu', '555-876-5432', 'Electrical Engineering', 2022, {$supervisors['Prof. Priya Sharma']}, '0000-0002-3456-7890')");
    
    $conn->query("INSERT INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, Supervisor_ID, ORCID) VALUES 
    ('Rahul Sharma', 'rsharma@university.edu', '555-765-4321', 'Data Science', 2020, {$supervisors['Dr. Vikram Patel']}, '0000-0003-4567-8901')");
    
    $conn->query("INSERT INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, Supervisor_ID, ORCID) VALUES 
    ('Meera Joshi', 'mjoshi@university.edu', '555-654-3210', 'Artificial Intelligence', 2023, {$supervisors['Prof. Neha Mehta']}, '0000-0001-5678-9012')");
    
    $conn->query("INSERT INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, Supervisor_ID, ORCID) VALUES 
    ('Sanjay Malhotra', 'smalhotra@university.edu', '555-543-2109', 'Cybersecurity', 2022, {$supervisors['Dr. Arjun Singh']}, '0000-0002-6789-0123')");
    
    $conn->query("INSERT INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, Supervisor_ID, ORCID) VALUES 
    ('Nisha Reddy', 'nreddy@university.edu', '555-432-1098', 'Computer Science', 2021, {$supervisors['Dr. Rajesh Kumar']}, '0000-0003-7890-1234')");
    
    $conn->query("INSERT INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, Supervisor_ID, ORCID) VALUES 
    ('Ravi Krishnan', 'rkrishnan@university.edu', '555-321-0987', 'Data Science', 2023, {$supervisors['Dr. Vikram Patel']}, '0000-0001-8901-2345')");
    
    $conn->query("INSERT INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, Supervisor_ID, ORCID) VALUES 
    ('Ananya Das', 'adas@university.edu', '555-210-9876', 'Artificial Intelligence', 2022, {$supervisors['Prof. Neha Mehta']}, '0000-0002-9012-3456')");
    
    // Get the actual researcher IDs after insertion
    $researcherQuery = "SELECT Researcher_ID, Name FROM Researcher";
    $researcherResult = $conn->query($researcherQuery);
    $researchers = [];
    while ($row = $researcherResult->fetch_assoc()) {
        $researchers[$row['Name']] = $row['Researcher_ID'];
    }
    
    // Projects - using the actual researcher IDs
    echo "Importing projects...\n";
    $conn->query("INSERT INTO Project (Title, Researcher_ID, Supervisor_ID, Start_Date, End_Date, Status) VALUES 
    ('Machine Learning for Climate Data Analysis', {$researchers['Amit Verma']}, {$supervisors['Dr. Rajesh Kumar']}, '2022-01-15', '2023-06-30', 'Completed')");
    
    $conn->query("INSERT INTO Project (Title, Researcher_ID, Supervisor_ID, Start_Date, End_Date, Status) VALUES 
    ('Next-Generation Wireless Communication Systems', {$researchers['Divya Gupta']}, {$supervisors['Prof. Priya Sharma']}, '2022-03-10', NULL, 'In Progress')");
    
    $conn->query("INSERT INTO Project (Title, Researcher_ID, Supervisor_ID, Start_Date, End_Date, Status) VALUES 
    ('Predictive Analytics for Healthcare Outcomes', {$researchers['Rahul Sharma']}, {$supervisors['Dr. Vikram Patel']}, '2021-09-01', '2023-08-15', 'Completed')");
    
    $conn->query("INSERT INTO Project (Title, Researcher_ID, Supervisor_ID, Start_Date, End_Date, Status) VALUES 
    ('Autonomous Vehicle Decision Systems', {$researchers['Meera Joshi']}, {$supervisors['Prof. Neha Mehta']}, '2023-02-20', NULL, 'In Progress')");
    
    $conn->query("INSERT INTO Project (Title, Researcher_ID, Supervisor_ID, Start_Date, End_Date, Status) VALUES 
    ('Blockchain Security Frameworks', {$researchers['Sanjay Malhotra']}, {$supervisors['Dr. Arjun Singh']}, '2022-11-05', NULL, 'In Progress')");
    
    // Publications - using the actual researcher IDs
    echo "Importing publications...\n";
    $conn->query("INSERT INTO Publication (Title, Researcher_ID, Supervisor_ID, Date_Published, DOI) VALUES 
    ('Advancements in Neural Network Architectures for Climate Prediction', {$researchers['Amit Verma']}, {$supervisors['Dr. Rajesh Kumar']}, '2023-05-15', '10.1234/journal.2023.001')");
    
    $conn->query("INSERT INTO Publication (Title, Researcher_ID, Supervisor_ID, Date_Published, DOI) VALUES 
    ('Performance Analysis of 6G Network Prototypes', {$researchers['Divya Gupta']}, {$supervisors['Prof. Priya Sharma']}, '2023-07-20', '10.1234/journal.2023.002')");
    
    $conn->query("INSERT INTO Publication (Title, Researcher_ID, Supervisor_ID, Date_Published, DOI) VALUES 
    ('Machine Learning Models for Early Disease Detection', {$researchers['Rahul Sharma']}, {$supervisors['Dr. Vikram Patel']}, '2022-11-30', '10.1234/journal.2022.003')");
    
    $conn->query("INSERT INTO Publication (Title, Researcher_ID, Supervisor_ID, Date_Published, DOI) VALUES 
    ('Ethical Frameworks for Autonomous Vehicle Decision Making', {$researchers['Meera Joshi']}, {$supervisors['Prof. Neha Mehta']}, '2023-08-10', '10.1234/journal.2023.004')");
    
    $conn->query("INSERT INTO Publication (Title, Researcher_ID, Supervisor_ID, Date_Published, DOI) VALUES 
    ('Securing Blockchain Applications Against Quantum Threats', {$researchers['Sanjay Malhotra']}, {$supervisors['Dr. Arjun Singh']}, '2023-03-25', '10.1234/journal.2023.005')");
    
    // Commit the transaction
    $conn->commit();
    echo "Transaction committed successfully!\n";
    
    // Display updated counts
    echo "Current record counts after import:\n";
    echo "- Supervisors: " . getRecordCount($conn, 'Supervisor') . "\n";
    echo "- Researchers: " . getRecordCount($conn, 'Researcher') . "\n";
    echo "- Projects: " . getRecordCount($conn, 'Project') . "\n";
    echo "- Publications: " . getRecordCount($conn, 'Publication') . "\n";
    
    echo "Import completed successfully. You can now visit the dashboard to see the data.\n";
    
} catch (Exception $e) {
    // Roll back the transaction if something went wrong
    $conn->rollback();
    echo "Error during import: " . $e->getMessage() . "\n";
}

// Close connection
$conn->close();
?> 