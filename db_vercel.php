<?php
// Database configuration for Vercel deployment
// This handles the case when no external database is configured

// Check if we're on Vercel
$is_vercel = isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']);

if ($is_vercel) {
    // On Vercel, we need an external database
    // For now, create a mock connection to prevent errors
    
    try {
        // Create a mock PDO connection for demonstration
        $conn = new PDO("sqlite::memory:");
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create tables in memory
        $sql = "CREATE TABLE IF NOT EXISTS Supervisor (
            Supervisor_ID INTEGER PRIMARY KEY AUTOINCREMENT,
            Name TEXT NOT NULL,
            Department TEXT NOT NULL,
            Email TEXT NOT NULL,
            Phone TEXT NOT NULL,
            ORCID TEXT UNIQUE
        )";
        $conn->exec($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS Researcher (
            Researcher_ID INTEGER PRIMARY KEY AUTOINCREMENT,
            Name TEXT NOT NULL,
            Email TEXT NOT NULL,
            Phone TEXT NOT NULL,
            Department TEXT NOT NULL,
            Enrollment_Year INTEGER NOT NULL,
            Supervisor_ID INTEGER,
            ORCID TEXT UNIQUE,
            FOREIGN KEY (Supervisor_ID) REFERENCES Supervisor(Supervisor_ID) ON DELETE SET NULL
        )";
        $conn->exec($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS Project (
            Project_ID INTEGER PRIMARY KEY AUTOINCREMENT,
            Title TEXT NOT NULL,
            Researcher_ID INTEGER,
            Supervisor_ID INTEGER,
            Start_Date TEXT NOT NULL,
            End_Date TEXT,
            Status TEXT NOT NULL,
            FOREIGN KEY (Researcher_ID) REFERENCES Researcher(Researcher_ID) ON DELETE SET NULL,
            FOREIGN KEY (Supervisor_ID) REFERENCES Supervisor(Supervisor_ID) ON DELETE SET NULL
        )";
        $conn->exec($sql);
        
        $sql = "CREATE TABLE IF NOT EXISTS Publication (
            Publication_ID INTEGER PRIMARY KEY AUTOINCREMENT,
            Title TEXT NOT NULL,
            Researcher_ID INTEGER,
            Supervisor_ID INTEGER,
            Date_Published TEXT NOT NULL,
            DOI TEXT UNIQUE,
            FOREIGN KEY (Researcher_ID) REFERENCES Researcher(Researcher_ID) ON DELETE SET NULL,
            FOREIGN KEY (Supervisor_ID) REFERENCES Supervisor(Supervisor_ID) ON DELETE SET NULL
        )";
        $conn->exec($sql);
        
        // Insert some sample data for demonstration
        $conn->exec("INSERT OR IGNORE INTO Supervisor (Name, Department, Email, Phone, ORCID) VALUES ('Dr. John Smith', 'Computer Science', 'john.smith@university.edu', '123-456-7890', '0000-0001-2345-6789')");
        $conn->exec("INSERT OR IGNORE INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, Supervisor_ID, ORCID) VALUES ('Jane Doe', 'jane.doe@university.edu', '098-765-4321', 'Computer Science', 2023, 1, '0000-0002-3456-7890')");
        $conn->exec("INSERT OR IGNORE INTO Project (Title, Researcher_ID, Supervisor_ID, Start_Date, Status) VALUES ('AI Research Project', 1, 1, '2023-01-01', 'In Progress')");
        $conn->exec("INSERT OR IGNORE INTO Publication (Title, Researcher_ID, Supervisor_ID, Date_Published, DOI) VALUES ('Sample Publication', 1, 1, '2023-06-01', '10.1000/sample-2023-001')");
        
    } catch(PDOException $e) {
        // If database connection fails, create a simple error page
        die("Database connection failed on Vercel. Please configure an external database.");
    }
} else {
    // Use the regular database configuration
    require_once 'db.php';
}
?> 