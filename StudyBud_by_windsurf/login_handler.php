<?php
session_start();
require_once 'config.php';

// Get form data
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

// Validate input
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Please fill in all fields';
    header('Location: login.php');
    exit();
}

// Get database connection
$conn = db_connect();

// Prepare and execute query
$stmt = $conn->prepare("SELECT id, name, email, password_hash FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($password, $user['password_hash'])) {
        // Login successful
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        
        // Redirect to dashboard
        header('Location: index.php');
        exit();
    }
}

// Login failed
$_SESSION['error'] = 'Invalid email or password';
header('Location: login.php');
exit();
?>
