<?php
// Railway PostgreSQL Database Configuration
// This will work with Railway's built-in PostgreSQL database

// Get database connection details from environment variables
$host = $_ENV['DB_HOST'] ?? $_ENV['PGHOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? $_ENV['PGDATABASE'] ?? 'railway';
$username = $_ENV['DB_USER'] ?? $_ENV['PGUSER'] ?? 'postgres';
$password = $_ENV['DB_PASS'] ?? $_ENV['PGPASSWORD'] ?? '';

// Alternative: Use Railway's DATABASE_URL
$database_url = $_ENV['DATABASE_URL'] ?? null;

try {
    if ($database_url) {
        // Parse DATABASE_URL
        $url = parse_url($database_url);
        $host = $url['host'] ?? $host;
        $port = $url['port'] ?? 5432;
        $dbname = ltrim($url['path'] ?? '/railway', '/');
        $username = $url['user'] ?? $username;
        $password = $url['pass'] ?? $password;
        
        $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    } else {
        // Use individual environment variables
        $conn = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    }
    
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if they don't exist (PostgreSQL syntax)
    $sql = "CREATE TABLE IF NOT EXISTS Supervisor (
        Supervisor_ID SERIAL PRIMARY KEY,
        Name VARCHAR(255) NOT NULL,
        Department VARCHAR(255) NOT NULL,
        Email VARCHAR(255) NOT NULL,
        Phone VARCHAR(50) NOT NULL,
        ORCID VARCHAR(255) UNIQUE
    )";
    $conn->exec($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS Researcher (
        Researcher_ID SERIAL PRIMARY KEY,
        Name VARCHAR(255) NOT NULL,
        Email VARCHAR(255) NOT NULL,
        Phone VARCHAR(50) NOT NULL,
        Department VARCHAR(255) NOT NULL,
        Enrollment_Year INTEGER NOT NULL,
        Supervisor_ID INTEGER,
        ORCID VARCHAR(255) UNIQUE,
        FOREIGN KEY (Supervisor_ID) REFERENCES Supervisor(Supervisor_ID) ON DELETE SET NULL
    )";
    $conn->exec($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS Project (
        Project_ID SERIAL PRIMARY KEY,
        Title VARCHAR(500) NOT NULL,
        Researcher_ID INTEGER,
        Supervisor_ID INTEGER,
        Start_Date DATE NOT NULL,
        End_Date DATE,
        Status VARCHAR(100) NOT NULL,
        FOREIGN KEY (Researcher_ID) REFERENCES Researcher(Researcher_ID) ON DELETE SET NULL,
        FOREIGN KEY (Supervisor_ID) REFERENCES Supervisor(Supervisor_ID) ON DELETE SET NULL
    )";
    $conn->exec($sql);
    
    $sql = "CREATE TABLE IF NOT EXISTS Publication (
        Publication_ID SERIAL PRIMARY KEY,
        Title VARCHAR(500) NOT NULL,
        Researcher_ID INTEGER,
        Supervisor_ID INTEGER,
        Date_Published DATE NOT NULL,
        DOI VARCHAR(255) UNIQUE,
        FOREIGN KEY (Researcher_ID) REFERENCES Researcher(Researcher_ID) ON DELETE SET NULL,
        FOREIGN KEY (Supervisor_ID) REFERENCES Supervisor(Supervisor_ID) ON DELETE SET NULL
    )";
    $conn->exec($sql);
    
    // Create notification preferences table
    $sql = "CREATE TABLE IF NOT EXISTS notification_preferences (
        id SERIAL PRIMARY KEY,
        user_id INTEGER NOT NULL,
        new_publication BOOLEAN DEFAULT TRUE,
        project_status_change BOOLEAN DEFAULT TRUE,
        new_researcher BOOLEAN DEFAULT FALSE,
        new_supervisor BOOLEAN DEFAULT FALSE,
        daily_summary BOOLEAN DEFAULT FALSE,
        weekly_summary BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    
    // Insert sample data if tables are empty
    $stmt = $conn->query("SELECT COUNT(*) FROM Supervisor");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO Supervisor (Name, Department, Email, Phone, ORCID) VALUES ('Dr. John Smith', 'Computer Science', 'john.smith@university.edu', '123-456-7890', '0000-0001-2345-6789')");
        $conn->exec("INSERT INTO Researcher (Name, Email, Phone, Department, Enrollment_Year, Supervisor_ID, ORCID) VALUES ('Jane Doe', 'jane.doe@university.edu', '098-765-4321', 'Computer Science', 2023, 1, '0000-0002-3456-7890')");
        $conn->exec("INSERT INTO Project (Title, Researcher_ID, Supervisor_ID, Start_Date, Status) VALUES ('AI Research Project', 1, 1, '2023-01-01', 'In Progress')");
        $conn->exec("INSERT INTO Publication (Title, Researcher_ID, Supervisor_ID, Date_Published, DOI) VALUES ('Sample Publication', 1, 1, '2023-06-01', '10.1000/sample-2023-001')");
    }
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?> 