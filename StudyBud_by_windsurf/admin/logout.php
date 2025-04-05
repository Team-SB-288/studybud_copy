<?php
session_start();

// Log the admin logout if admin_logs table exists
if (isset($_SESSION['admin_id'])) {
    require_once '../config.php';
    
    if ($conn->query("SHOW TABLES LIKE 'admin_logs'")->num_rows > 0) {
        $admin_id = $_SESSION['admin_id'];
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $logout_time = date('Y-m-d H:i:s');
        $status = "logout";
        
        $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, ip_address, user_agent, action_time, action_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $admin_id, $ip_address, $user_agent, $logout_time, $status);
        $stmt->execute();
        $stmt->close();
    }
    
    $conn->close();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
