<?php
session_start();
require_once 'csrf_token.php';
require_once 'db_connect.php';

// Check CSRF token
if (!check_csrf_token()) {
    $_SESSION['reset_error'] = "Invalid request. Please try again.";
    header("Location: ../reset_password.php?token=" . $_SESSION['reset_token']);
    exit;
}

// Regenerate CSRF token for security
generate_csrf_token();

// Check if the reset token exists in session
if (!isset($_SESSION['reset_token']) || empty($_SESSION['reset_token'])) {
    $_SESSION['login_error'] = "Invalid or expired password reset session.";
    header("Location: ../login.php");
    exit;
}

$token = $_SESSION['reset_token'];

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate password
    if (!isset($_POST['password']) || trim($_POST['password']) === '') {
        $_SESSION['reset_error'] = "Password is required";
        header("Location: ../reset_password.php?token=$token");
        exit;
    }
    
    if (!isset($_POST['confirmPassword']) || trim($_POST['confirmPassword']) === '') {
        $_SESSION['reset_error'] = "Confirm password is required";
        header("Location: ../reset_password.php?token=$token");
        exit;
    }
    
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    
    // Check if passwords match
    if ($password !== $confirmPassword) {
        $_SESSION['reset_error'] = "Passwords do not match";
        header("Location: ../reset_password.php?token=$token");
        exit;
    }
    
    // Validate password strength
    if (strlen($password) < 8) {
        $_SESSION['reset_error'] = "Password must be at least 8 characters long";
        header("Location: ../reset_password.php?token=$token");
        exit;
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $_SESSION['reset_error'] = "Password must include at least one uppercase letter";
        header("Location: ../reset_password.php?token=$token");
        exit;
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $_SESSION['reset_error'] = "Password must include at least one number";
        header("Location: ../reset_password.php?token=$token");
        exit;
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $_SESSION['reset_error'] = "Password must include at least one special character";
        header("Location: ../reset_password.php?token=$token");
        exit;
    }
    
    try {
        // Connect to database
        $conn = db_connect();
        
        // Check if token exists and is valid
        $stmt = $conn->prepare("SELECT user_id, expires_at FROM password_reset_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $_SESSION['login_error'] = "Invalid or expired password reset link.";
            header("Location: ../login.php");
            exit;
        }
        
        $tokenData = $result->fetch_assoc();
        
        // Check if token has expired
        if (strtotime($tokenData['expires_at']) < time()) {
            // Delete expired token
            $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            
            $_SESSION['login_error'] = "Password reset link has expired. Please request a new one.";
            header("Location: ../login.php");
            exit;
        }
        
        $user_id = $tokenData['user_id'];
        
        // Hash the new password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Update user's password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        $stmt->execute();
        
        // Delete the used token
        $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        
        // Clear the reset token from session
        unset($_SESSION['reset_token']);
        
        // Set success message
        $_SESSION['login_success'] = "Your password has been reset successfully. You can now log in with your new password.";
        header("Location: ../login.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['reset_error'] = "An error occurred. Please try again later.";
        header("Location: ../reset_password.php?token=$token");
        exit;
    }
} else {
    // If not a POST request, redirect to the reset password page
    header("Location: ../reset_password.php?token=$token");
    exit;
}
?>
