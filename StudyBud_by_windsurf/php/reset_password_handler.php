<?php
session_start();
require_once 'csrf_token.php';
require_once 'db_connect.php';
require_once 'mailer.php';

// Check CSRF token
if (!check_csrf_token()) {
    $_SESSION['reset_error'] = "Invalid request. Please try again.";
    header("Location: ../forgot_password.php");
    exit;
}

// Regenerate CSRF token for security
generate_csrf_token();

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (!isset($_POST['email']) || trim($_POST['email']) === '') {
        $_SESSION['reset_error'] = "Email is required";
        header("Location: ../forgot_password.php");
        exit;
    }
    
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['reset_error'] = "Invalid email format";
        header("Location: ../forgot_password.php");
        exit;
    }
    
    try {
        // Connect to database
        $conn = db_connect();
        
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            // Don't reveal that the email doesn't exist for security reasons
            // Instead, show a generic success message
            $_SESSION['reset_message'] = "If your email exists in our system, you will receive a password reset link shortly.";
            header("Location: ../forgot_password.php");
            exit;
        }
        
        $user = $result->fetch_assoc();
        $user_id = $user['id'];
        
        // Generate a unique token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
        
        // Store token in database
        // First, delete any existing tokens for this user
        $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Insert new token
        $stmt = $conn->prepare("INSERT INTO password_reset_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $token, $expires);
        $stmt->execute();
        
        // Create the reset link
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF'])) . "/reset_password.php?token=" . $token;
        
        // Get user's name
        $stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $name_result = $stmt->get_result();
        $user_name = $name_result->fetch_assoc()['name'];
        
        // Create mailer instance
        $mailer = new Mailer();
        
        // Try to send email
        $email_result = $mailer->sendPasswordResetEmail($email, $user_name, $reset_link);
        
        // For demonstration purposes, also store the link in the session
        $_SESSION['reset_link'] = $reset_link;
        
        if ($email_result['status'] === 'success') {
            $_SESSION['reset_message'] = "<strong>Password reset email sent!</strong><br>Please check your inbox for instructions to reset your password.<br><br>";
            if (!$mailer->isConfigured()) {
                // If email is not configured, still show the link for testing
                $_SESSION['reset_message'] .= "<div class='demo-note'><strong>Note:</strong> Email sending is not fully configured. In a real application, the link would be emailed.<br><br><a href='$reset_link' class='reset-link'>Click here to reset your password</a></div>";
            }
        } else {
            // Email sending failed, show the link directly
            $_SESSION['reset_message'] = "<strong>Password reset link has been generated.</strong><br>" . $email_result['message'] . "<br><br><a href='$reset_link' class='reset-link'>Click here to reset your password</a>";
        }
        
        header("Location: ../forgot_password.php");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['reset_error'] = "An error occurred. Please try again later.";
        header("Location: ../forgot_password.php");
        exit;
    }
} else {
    // If not a POST request, redirect to the forgot password page
    header("Location: ../forgot_password.php");
    exit;
}
?>
