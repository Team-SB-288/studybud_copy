<?php
session_start();
require_once '../config.php';
require_once 'csrf_token.php';

// Check if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

// Handle login
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check CSRF token if it exists in session
    if (isset($_SESSION['admin_csrf_token'])) {
        check_admin_csrf_token();
    }
    // Get and sanitize input
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    
    // Check if admin table exists, if not create it
    $check_table = $conn->query("SHOW TABLES LIKE 'admins'");
    if ($check_table->num_rows == 0) {
        // Create admins table
        $sql = "CREATE TABLE admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->query($sql);
        
        // Insert default admin (this should be changed after first login)
        $default_password = password_hash('admin123', PASSWORD_DEFAULT);
        $sql = "INSERT INTO admins (username, password, name) VALUES ('admin', '$default_password', 'Administrator')";
        $conn->query($sql);
    }
    
    // Query for the admin
    $sql = "SELECT * FROM admins WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $admin['password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['name'];
            
            // Set session timeout (2 hours)
            $_SESSION['last_activity'] = time();
            $_SESSION['expire_time'] = 7200; // 2 hours in seconds
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Invalid username or password";
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyBud Admin - Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #4e73df;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>StudyBud Admin</h1>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" action="">
            <?php echo admin_csrf_token_field(); ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
    </div>
</body>
</html>
