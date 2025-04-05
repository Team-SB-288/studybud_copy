<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Prevent multiple inclusions
if (!function_exists('get_current_user')) {
    function get_current_user() {
        if (!is_logged_in()) {
            return null;
        }
        
        global $conn;
        $user_id = $_SESSION['user_id'];
        
        $sql = "SELECT id, name, email, profile_picture FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            return $result->fetch_assoc();
        }
        return null;
    }
}

// Function to get URL
function get_url($path) {
    return 'http://' . $_SERVER['HTTP_HOST'] . '/' . str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])) . '/' . $path;
}

// Function to set flash message
function set_flash_message($type, $message) {
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

// Function to get and clear flash messages
function get_flash_messages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

// Function to redirect with flash message
function redirect_with_message($url, $type, $message) {
    set_flash_message($type, $message);
    header('Location: ' . $url);
    exit();
}

// Function to check CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to generate CSRF token
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
?>
