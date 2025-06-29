<?php
// Simple test file to verify PHP is working on Vercel

header('Content-Type: text/html');

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>PHP Test - Vercel</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 40px; }";
echo ".success { color: green; }";
echo ".error { color: red; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<h1>PHP Test on Vercel</h1>";

// Test basic PHP functionality
echo "<h2>Basic PHP Test</h2>";
echo "<p class='success'>✅ PHP is working!</p>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current Time: " . date('Y-m-d H:i:s') . "</p>";

// Test database connection
echo "<h2>Database Test</h2>";
try {
    $conn = new PDO("sqlite::memory:");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p class='success'>✅ Database connection successful!</p>";
    
    // Test creating a table
    $sql = "CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)";
    $conn->exec($sql);
    echo "<p class='success'>✅ Table creation successful!</p>";
    
    // Test inserting data
    $sql = "INSERT INTO test (name) VALUES ('test')";
    $conn->exec($sql);
    echo "<p class='success'>✅ Data insertion successful!</p>";
    
} catch(PDOException $e) {
    echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test environment variables
echo "<h2>Environment Test</h2>";
echo "<p>VERCEL: " . ($_ENV['VERCEL'] ?? 'Not set') . "</p>";
echo "<p>PHP_VERSION: " . ($_ENV['PHP_VERSION'] ?? 'Not set') . "</p>";

echo "<h2>File System Test</h2>";
if (file_exists('../index.php')) {
    echo "<p class='success'>✅ index.php exists</p>";
} else {
    echo "<p class='error'>❌ index.php not found</p>";
}

if (file_exists('../db_vercel.php')) {
    echo "<p class='success'>✅ db_vercel.php exists</p>";
} else {
    echo "<p class='error'>❌ db_vercel.php not found</p>";
}

echo "<h2>Next Steps</h2>";
echo "<p>If all tests pass, your PHP environment is working correctly.</p>";
echo "<p><a href='/'>Go to main application</a></p>";

echo "</body>";
echo "</html>";
?> 