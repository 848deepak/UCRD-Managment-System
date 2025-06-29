<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is not logged in and not on login page
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header("Location: " . str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1) . "login.php");
    exit();
}

// Function to display alert messages
function displayAlert() {
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">
                ' . $_SESSION['message'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                ' . $_SESSION['error'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['error']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UCRD Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>css/style.css">
    <!-- Custom JavaScript -->
    <script src="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>js/form-validation.js" defer></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>index.php">UCRD Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="researcherDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Researchers
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="researcherDropdown">
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>researcher/view.php">View Researchers</a></li>
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>researcher/add.php">Add Researcher</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="supervisorDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Supervisors
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="supervisorDropdown">
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>supervisor/view.php">View Supervisors</a></li>
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>supervisor/add.php">Add Supervisor</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="projectDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Projects
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="projectDropdown">
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>project/view.php">View Projects</a></li>
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>project/add.php">Add Project</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="publicationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Publications
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="publicationDropdown">
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>publication/view.php">View Publications</a></li>
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>publication/add.php">Add Publication</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="toolsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Tools
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="toolsDropdown">
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>dashboard.php">Advanced Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>relationships.php">Relationship Network</a></li>
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>backup.php">Database Backup</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>publication/view.php?export=csv">Export Publications (CSV)</a></li>
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>publication/view.php?export=pdf">Export Publications (PDF)</a></li>
                        </ul>
                    </li>
                </ul>
                
                <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Global Search Form -->
                <form class="d-flex me-3" method="GET" action="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>global_search.php">
                    <div class="input-group">
                        <input class="form-control" type="search" name="search" placeholder="Search..." aria-label="Search" required>
                        <button class="btn btn-light" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </form>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="<?php echo str_repeat("../", substr_count($_SERVER['PHP_SELF'], "/") - 1); ?>logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </li>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container">
        <?php displayAlert(); ?>
    </div>
</body>
</html> 