<?php
session_start();
require_once 'csrf_token.php';

// Generate a new CSRF token
generate_csrf_token();

// Return the token as JSON
echo json_encode([
    'csrf_token' => $_SESSION['csrf_token']
]);
?>
