<?php
// Main Vercel PHP handler for UCRD Management System
// This handles all requests and serves the appropriate content

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the request path
$request_uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($request_uri, PHP_URL_PATH);

// Remove leading slash
$path = ltrim($path, '/');

// If accessing the root, serve the main application
if (empty($path)) {
    // Serve the main dashboard
    require_once '../index.php';
    exit;
}

// Check if the requested file exists
$requested_file = '../' . $path;

if (file_exists($requested_file) && is_file($requested_file)) {
    $extension = pathinfo($requested_file, PATHINFO_EXTENSION);
    
    // Handle PHP files
    if ($extension === 'php') {
        require_once $requested_file;
    }
    // Handle static files
    else {
        $mime_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'html' => 'text/html',
            'json' => 'application/json'
        ];
        
        $mime_type = $mime_types[$extension] ?? 'application/octet-stream';
        header('Content-Type: ' . $mime_type);
        readfile($requested_file);
    }
} else {
    // File not found, serve the main application
    require_once '../index.php';
}
?> 