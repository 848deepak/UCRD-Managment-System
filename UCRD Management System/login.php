<?php
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Define default admin user (in a real application, this would be stored in the database)
$admin_username = "admin";
$admin_password = password_hash("admin123", PASSWORD_DEFAULT); // Hashed password

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Simple validation
    $errors = array();
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, try to authenticate
    if (empty($errors)) {
        // Check against our hardcoded admin user
        if ($username === $admin_username && password_verify($password, $admin_password)) {
            // Authentication successful
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = $username;
            $_SESSION['user_role'] = 'admin';
            
            // Set success message
            $_SESSION['message'] = "Login successful. Welcome back!";
            $_SESSION['message_type'] = "success";
            
            // Redirect to dashboard
            header("Location: index.php");
            exit();
        } else {
            // Authentication failed
            $login_error = "Invalid username or password";
        }
    }
}

// Simple HTML header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UCRD Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-logo i {
            font-size: 60px;
            color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-logo">
                <i class="fas fa-university"></i>
                <h2 class="mt-3">UCRD Management System</h2>
                <p class="text-muted">Sign in to access the system</p>
            </div>
            
            <div class="card shadow">
                <div class="card-body p-4">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($login_error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $login_error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-text">Default login: admin / admin123</div>
                        </div>
                        
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i> Sign In
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="text-center mt-3 text-muted">
                <small>&copy; <?php echo date('Y'); ?> UCRD Management System</small>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 