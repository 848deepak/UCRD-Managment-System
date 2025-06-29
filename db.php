<?php
// Database file path
$db_path = __DIR__ . '/ucrd_management.sqlite';

try {
    // Create a new PDO instance for SQLite
    $conn = new PDO("sqlite:$db_path");
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if they don't exist
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
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?> 