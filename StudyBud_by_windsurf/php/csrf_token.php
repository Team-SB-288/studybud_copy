<?php
// CSRF Protection System

// Generate a new CSRF token and store it in the session
function generate_csrf_token() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // Generate a random token
    $token = bin2hex(random_bytes(32));
    
    // Store token in session
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    
    return $token;
}

// Validate a CSRF token
function validate_csrf_token($token, $max_age = 3600) {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    // Check if token exists in session
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Check if token matches
    if ($_SESSION['csrf_token'] !== $token) {
        return false;
    }
    
    // Check if token has expired
    $token_age = time() - $_SESSION['csrf_token_time'];
    if ($token_age > $max_age) {
        // Token has expired, generate a new one
        generate_csrf_token();
        return false;
    }
    
    return true;
}

// Function to output a CSRF token field for forms
function csrf_token_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// Function to check CSRF token for form submissions
function check_csrf_token() {
    // For POST requests, check CSRF token
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !validate_csrf_token($_POST['csrf_token'])) {
            // CSRF token is invalid
            return false;
        }
    }
    return true;
}
?>
