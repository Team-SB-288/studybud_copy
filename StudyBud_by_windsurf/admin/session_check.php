<?php
// Admin session security check file
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Check for session timeout
if (isset($_SESSION['last_activity']) && isset($_SESSION['expire_time'])) {
    $inactive_time = time() - $_SESSION['last_activity'];
    
    if ($inactive_time > $_SESSION['expire_time']) {
        // Session expired, destroy it
        session_unset();
        session_destroy();
        header("Location: login.php?expired=1");
        exit;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
}
?>
