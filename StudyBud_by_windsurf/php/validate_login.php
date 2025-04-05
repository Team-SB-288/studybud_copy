<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // Query to fetch user data
    $sql = "SELECT id, name, email, password, profile_picture FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_profile_picture'] = $user['profile_picture'];
            
            // Redirect to home page
            header('Location: ../index.php');
            exit();
        }
    }
    
    // If login fails
    $_SESSION['login_error'] = 'Invalid email or password';
    header('Location: ../login.html');
    exit();
}
?>
