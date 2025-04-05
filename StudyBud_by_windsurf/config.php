<?php
require_once 'includes/db.php';

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to get URL
function get_url($path) {
    return 'http://' . $_SERVER['HTTP_HOST'] . '/' . str_replace('\\', '/', dirname($_SERVER['PHP_SELF'])) . '/' . $path;
}
?>
