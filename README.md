# UCRD Management System

A comprehensive PHP-SQLite web application for managing university research center data, including researchers, supervisors, projects, and publications.

## Quick Start Guide

1. **Start the UCRD Management System**
   ```
   ./ucrdm
   ```
   This single command starts the PHP server automatically and shows the status of your database.

   Alternatively, you can use:
   ```
   php -S localhost:9000
   ```

2. **Access the application**
   - Open your web browser and go to: http://localhost:9000
   - Log in with default credentials:
     - Username: admin
     - Password: admin123

3. **Import Sample Data (Optional)**
   - After logging in, go to Tools → Database Backup in the navigation menu
   - Or navigate directly to: http://localhost:9000/import_sample_data.php
   - Check the confirmation box and click "Import Sample Data"
   - This will populate your database with sample supervisors, researchers, projects and publications

4. **Database Information**
   - This version uses SQLite instead of MySQL
   - The database file is stored at `ucrd_management.sqlite` in the root directory
   - No separate database server needed

## Features

- **Researchers Management**: Add, view, edit, and delete researcher records
- **Supervisors Management**: Add, view, edit, and delete supervisor records
- **Projects Management**: Add, view, edit, and delete project records
- **Publications Management**: Add, view, edit, and delete publication records
- **Search Functionality**: Search across all entities by various fields
- **CSV Export**: Export publication data to CSV format
- **PDF Export**: Generate PDF reports for publications
- **Data Visualization**: Charts and graphs for publication statistics
- **User Authentication**: Secure login system with session management
- **Responsive Design**: Built with Bootstrap for optimal viewing on all devices
- **Database Backup**: Create and download full database backups for data protection
- **Sample Data Import**: One-click population of the database with sample records

## Database Structure

The system uses a SQLite database with the following tables:

- **Researcher**: Stores researcher information including name, email, department, etc.
- **Supervisor**: Stores supervisor information including name, department, etc.
- **Project**: Stores project details with relationships to researchers and supervisors
- **Publication**: Stores publication records with DOI and relationships to researchers and supervisors

## Technical Details

- **Backend**: PHP 7.4+
- **Database**: SQLite (PDO)
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Libraries**: Chart.js (visualization), TCPDF (PDF generation), Font Awesome, jQuery
- **Security**: Password hashing, session management, prepared statements for database queries

## Setup Instructions

### Prerequisites

- PHP 7.4 or higher with SQLite support
- Web browser (Chrome, Firefox, Safari, Edge recommended)

### Installation

1. **Clone or Download the Repository**
   ```
   git clone https://github.com/yourusername/UCRD-Management-System.git
   ```
   Or download and extract the ZIP file

2. **Run the Application**
   ```
   cd UCRD-Management-System
   ./ucrdm
   ```

3. **Initialize with Sample Data (Optional)**
   - Navigate to http://localhost:9000/import_sample_data.php 
   - Check the confirmation box and click "Import Sample Data"

## Troubleshooting

- **Database Connection Issues**: If you see errors related to database connection, ensure SQLite is enabled in your PHP installation.
- **Session Warnings**: If you see warnings about sessions already being active, these are harmless notifications that don't affect functionality.
- **Permission Errors**: Make sure the directory has write permissions for the SQLite database file.
- **Port Already in Use**: If port 9000 is already in use, edit the `ucrdm` script and change the port number.

## Recent Updates

### Version 1.3 - April 2025
- Added `ucrdm` launcher script for one-command startup
- Switched from MySQL to SQLite for easier setup and portability
- Fixed PDO compatibility issues across all PHP files
- Enhanced session management to prevent duplicate session starts
- Added more detailed error reporting for database operations
- Simplified the sample data import process
- Updated SQL queries to use SQLite syntax (replaced YEAR() with strftime())

### Version 1.2 - May 2025
- Added database backup and restore functionality
- Created admin-only access to database management tools
- Enhanced security for sensitive operations

### Version 1.1 - April 2025
- Fixed PHP 8.4 compatibility issues with fputcsv() by adding required $escape parameter
- Fixed "headers already sent" warning in PDF and CSV export functionality
- Updated TCPDF library to properly declare properties and avoid dynamic property creation warnings
- Improved header handling in export functions to prevent output conflicts
- Enhanced error handling for file exports

## Credits

Developed as a full-stack web development project using PHP and SQLite. 