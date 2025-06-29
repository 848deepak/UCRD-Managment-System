<?php
// Simple hello world test for Vercel PHP

header('Content-Type: text/html');

echo "<!DOCTYPE html>";
echo "<html>";
echo "<head>";
echo "<title>Hello from Vercel PHP</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 40px; background: #f0f0f0; }";
echo ".container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo ".success { color: #28a745; font-weight: bold; }";
echo ".info { color: #17a2b8; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🎉 Hello from Vercel PHP!</h1>";
echo "<p class='success'>✅ PHP is working correctly on Vercel!</p>";
echo "<p class='info'>PHP Version: " . phpversion() . "</p>";
echo "<p class='info'>Current Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p class='info'>Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A') . "</p>";
echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<p>If you can see this page, PHP is working correctly on Vercel.</p>";
echo "<p>Now try accessing the main application:</p>";
echo "<p><a href='/'>Go to UCRD Management System</a></p>";
echo "</div>";
echo "</body>";
echo "</html>";
?> 