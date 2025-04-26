# UCRD Management System - Project Report

## Executive Summary

The UCRD Management System is a comprehensive PHP-MySQL web application designed to manage university research center data. The system provides functionalities for managing researchers, supervisors, projects, and publications, along with robust search, export, and visualization capabilities. This report outlines the project's objectives, implementation details, system architecture, and features.

## Project Objectives

1. Develop a user-friendly web-based system for managing university research center data
2. Implement CRUD operations for key entities: Researchers, Supervisors, Projects, and Publications
3. Create relationships between entities to represent the research ecosystem
4. Provide search and filtering capabilities across all entities
5. Implement data export functionality for reporting purposes
6. Include data visualization for insights on research output
7. Build a notification system for user alerts and email communications
8. Ensure data security and integrity throughout the system

## System Architecture

### Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, JavaScript, jQuery
- **Backend**: PHP 8.4
- **Database**: MySQL 5.7+
- **Libraries**: 
  - Chart.js for data visualization
  - TCPDF for PDF generation
  - Font Awesome for icons
  - Bootstrap for responsive design

### Database Schema

The system uses a relational database with the following key tables:

1. **Users**: Stores user authentication data
   - User_ID (PK)
   - Username
   - Password (hashed)
   - Email
   - Role

2. **Researcher**: Stores researcher information
   - Researcher_ID (PK)
   - Name
   - Email
   - Department
   - Specialization
   - Join_Date

3. **Supervisor**: Stores supervisor information
   - Supervisor_ID (PK)
   - Name
   - Email
   - Department
   - Position
   - Join_Date

4. **Project**: Stores project details
   - Project_ID (PK)
   - Title
   - Description
   - Start_Date
   - End_Date
   - Status
   - Funding_Amount
   - Researcher_ID (FK)
   - Supervisor_ID (FK)

5. **Publication**: Stores publication records
   - Publication_ID (PK)
   - Title
   - Abstract
   - Date_Published
   - DOI
   - Publication_Type
   - Researcher_ID (FK)
   - Supervisor_ID (FK)
   - Project_ID (FK)

6. **Notification_Log**: Tracks notifications sent to users
   - Notification_ID (PK)
   - User_ID (FK)
   - Type
   - Message
   - Created_At
   - Read_Status

7. **Notification_Preferences**: Stores user preferences for notifications
   - Preference_ID (PK)
   - User_ID (FK)
   - Type
   - Email_Enabled
   - Digest_Enabled

8. **Email_Queue**: Manages pending emails
   - Email_ID (PK)
   - Recipient
   - Subject
   - Body
   - Status
   - Scheduled_Time
   - Created_At

9. **Email_Templates**: Stores email notification templates
   - Template_ID (PK)
   - Name
   - Subject
   - Body
   - Variables
   - Created_At
   - Updated_At

## Implementation Details

### Core Modules

1. **Authentication System**
   - User login/logout functionality
   - Session management
   - Role-based access control
   - Password hashing for security

2. **Researcher Management**
   - Add, view, edit, and delete researchers
   - Search and filter researchers by various criteria
   - Researcher profile with related projects and publications

3. **Supervisor Management**
   - Add, view, edit, and delete supervisors
   - Search and filter supervisors by various criteria
   - Supervisor profile with supervised researchers, projects, and publications

4. **Project Management**
   - Add, view, edit, and delete projects
   - Associate projects with researchers and supervisors
   - Track project status and timeline
   - Search and filter projects by various criteria

5. **Publication Management**
   - Add, view, edit, and delete publications
   - Link publications to researchers, supervisors, and projects
   - DOI integration for external linking
   - Search and filter publications by various criteria
   - Export publications to CSV and PDF formats

6. **Dashboard and Visualization**
   - Overview of system statistics
   - Interactive charts showing publication trends
   - Distribution of projects by status
   - Researcher and supervisor productivity metrics

7. **Relationship Network**
   - Visual representation of connections between researchers, supervisors, and projects
   - Interactive network graph for exploring research collaborations

8. **Notification System**
   - User notification preferences management
   - Email notifications for important events
   - Daily digest option for collecting notifications
   - Customizable email templates

9. **Database Backup System**
   - On-demand database backup functionality
   - SQL file generation for complete database snapshot
   - Admin-only access for security
   - Detailed backup instructions

### Security Measures

1. **Authentication**
   - Secure password hashing
   - Session timeout
   - CSRF protection

2. **Data Validation**
   - Input sanitization
   - Form validation
   - Prepared statements for database queries

3. **Access Control**
   - Role-based permissions
   - Secure routing
   - Admin-only sections

4. **Error Handling**
   - Comprehensive error logging
   - User-friendly error messages
   - Graceful error recovery

## Feature Highlights

### Advanced Search Functionality
The system implements a comprehensive search system allowing users to search across all entities simultaneously. The global search feature indexes researchers, supervisors, projects, and publications, making it easy to find information across the database.

### Data Export
Users can export publication data in both CSV and PDF formats. The export functionality includes filtering options, allowing for customized exports based on search criteria. This feature is essential for reporting and data sharing.

### Interactive Dashboard
The dashboard provides a visual overview of the research center's activities through charts and statistics. It visualizes publication trends, project status distribution, and researcher/supervisor performance metrics, enabling data-driven decision making.

### Relationship Network
A unique feature of the system is the relationship network visualization, which displays the connections between researchers, supervisors, and projects. This network graph helps identify collaboration patterns and research clusters within the institution.

### Notification System
The notification system keeps users informed about important events through in-app notifications and email alerts. Users can customize their notification preferences, including opting for daily digest emails instead of individual notifications.

### Database Backup
The system includes a dedicated database backup functionality that allows administrators to create full database backups. These backups capture the complete database structure and data, ensuring data preservation and disaster recovery capabilities.

## Technical Challenges and Solutions

### Challenge 1: PHP Compatibility
As PHP evolves, maintaining compatibility with newer versions presents challenges. The system encountered deprecation warnings with functions like `fputcsv()` which required updates to include the new required `$escape` parameter.

**Solution**: Updated all instances of deprecated function calls to include required parameters and follow current best practices, ensuring compatibility with PHP 8.4.

### Challenge 2: Header Handling
The "headers already sent" warning occurred when attempting to set HTTP headers after output had already been sent to the browser, particularly affecting CSV and PDF exports.

**Solution**: Reorganized code to ensure all header() calls occur before any output is generated, including moving include statements for header.php after all potential header modifications.

### Challenge 3: Dynamic Property Creation
PHP 8.4 introduced a deprecation warning for dynamic property creation, affecting the TCPDF library used for PDF generation.

**Solution**: Refactored the TCPDF class to properly declare all properties, preventing runtime dynamic property creation and ensuring compatibility with strict property handling.

### Challenge 4: Database Relationships
Managing the complex relationships between researchers, supervisors, projects, and publications required careful database design to avoid redundancy while maintaining data integrity.

**Solution**: Implemented a normalized database schema with appropriate foreign key constraints and designed user interfaces that clearly represent these relationships.

## Future Enhancements

1. **API Development**
   - RESTful API for external system integration
   - API documentation and testing tools

2. **Advanced Analytics**
   - Predictive analytics for research trends
   - Researcher performance metrics
   - Funding allocation optimization

3. **Mobile Application**
   - Native mobile apps for iOS and Android
   - Mobile-optimized experience for on-the-go access

4. **Integration Capabilities**
   - Integration with academic databases (Scopus, Web of Science)
   - ORCID integration for researcher identification
   - Calendar system integration for project timelines

5. **Enhanced Backup System**
   - Automated scheduled backups
   - Cloud storage integration
   - Backup restoration interface

## Conclusion

The UCRD Management System successfully meets the objectives of creating a comprehensive research center data management solution. The system provides robust CRUD operations for all entities, advanced search capabilities, data export functionality, and visual insights through dashboards and relationship networks.

The notification system and email management features enhance user engagement, while the database backup functionality ensures data security. The system's modular architecture allows for future enhancements and expansion as the research center's needs evolve.

Technical challenges encountered during development were successfully addressed through careful refactoring and code optimization, resulting in a stable, maintainable, and future-proof application.

## Version History

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

### Version 1.0 - March 2025
- Initial release with core CRUD functionality
- Implemented researcher, supervisor, project, and publication modules
- Added search, filtering, and basic reporting features
- Created user authentication and access control
- Developed basic dashboard with visualization 