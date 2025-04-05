<?php
// User session security check file
session_start();

// Function to check if user is logged in and session is valid
function check_user_session() {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Check for session timeout
    if (isset($_SESSION['last_activity']) && isset($_SESSION['expire_time'])) {
        $inactive_time = time() - $_SESSION['last_activity'];
        
        if ($inactive_time > $_SESSION['expire_time']) {
            // Session expired, destroy it
            session_unset();
            session_destroy();
            return false;
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
    } else {
        // If session doesn't have timeout info, set it now
        $_SESSION['last_activity'] = time();
        $_SESSION['expire_time'] = 7200; // 2 hours in seconds
    }
    
    return true;
}

// Function to require login for protected pages
function require_login() {
    if (!check_user_session()) {
        // Determine if the request is AJAX or regular
        $is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        if ($is_ajax) {
            // For AJAX requests, return JSON response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Session expired or user not logged in',
                'redirect' => 'login.html'
            ]);
            exit;
        } else {
            // For regular requests, redirect to login page
            header("Location: login.html");
            exit;
        }
    }
    
    return true;
}
?>
