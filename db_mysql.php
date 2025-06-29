<?php
// MySQL Database Configuration for Hosting
// Replace the SQLite configuration with this for hosting

// Database configuration
$host = 'localhost'; // Your MySQL host
$dbname = 'ucrd_management'; // Your database name
$username = 'your_username'; // Your MySQL username
$password = 'your_password'; // Your MySQL password

try {
    // Create a new PDO instance for MySQL
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if they don't exist
    $sql = "CREATE TABLE IF NOT EXISTS Supervisor (
        Supervisor_ID INT AUTO_INCREMENT PRIMARY KEY,
        Name VARCHAR(255) NOT NULL,
        Department VARCHAR(255) NOT NULL,
        Email VARCHAR(255) NOT NULL,
        Phone VARCHAR(50) NOT NULL,
        ORCID VARCHAR(255) UNIQUE
    )";
    $conn->exec($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS Researcher (
        Researcher_ID INT AUTO_INCREMENT PRIMARY KEY,
        Name VARCHAR(255) NOT NULL,
        Email VARCHAR(255) NOT NULL,
        Phone VARCHAR(50) NOT NULL,
        Department VARCHAR(255) NOT NULL,
        Enrollment_Year INT NOT NULL,
        Supervisor_ID INT,
        ORCID VARCHAR(255) UNIQUE,
        FOREIGN KEY (Supervisor_ID) REFERENCES Supervisor(Supervisor_ID) ON DELETE SET NULL
    )";
    $conn->exec($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS Project (
        Project_ID INT AUTO_INCREMENT PRIMARY KEY,
        Title VARCHAR(500) NOT NULL,
        Researcher_ID INT,
        Supervisor_ID INT,
        Start_Date DATE NOT NULL,
        End_Date DATE,
        Status VARCHAR(100) NOT NULL,
        FOREIGN KEY (Researcher_ID) REFERENCES Researcher(Researcher_ID) ON DELETE SET NULL,
        FOREIGN KEY (Supervisor_ID) REFERENCES Supervisor(Supervisor_ID) ON DELETE SET NULL
    )";
    $conn->exec($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS Publication (
        Publication_ID INT AUTO_INCREMENT PRIMARY KEY,
        Title VARCHAR(500) NOT NULL,
        Researcher_ID INT,
        Supervisor_ID INT,
        Date_Published DATE NOT NULL,
        DOI VARCHAR(255) UNIQUE,
        FOREIGN KEY (Researcher_ID) REFERENCES Researcher(Researcher_ID) ON DELETE SET NULL,
        FOREIGN KEY (Supervisor_ID) REFERENCES Supervisor(Supervisor_ID) ON DELETE SET NULL
    )";
    $conn->exec($sql);
    
    // Create notification preferences table
    $sql = "CREATE TABLE IF NOT EXISTS notification_preferences (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        new_publication BOOLEAN DEFAULT TRUE,
        project_status_change BOOLEAN DEFAULT TRUE,
        new_researcher BOOLEAN DEFAULT FALSE,
        new_supervisor BOOLEAN DEFAULT FALSE,
        daily_summary BOOLEAN DEFAULT FALSE,
        weekly_summary BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?> 