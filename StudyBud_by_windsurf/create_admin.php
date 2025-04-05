<?php
// Script to create an admin user
require_once 'config.php';

// Check if admin user already exists
$check_sql = "SELECT * FROM users WHERE email = 'admin@studybud.com'";
$result = $conn->query($check_sql);

if ($result->num_rows > 0) {
    echo "Admin user already exists!";
} else {
    // Create admin user
    $name = "Admin User";
    $email = "admin@studybud.com";
    $phone = "1234567890";
    $password = "admin123"; // Plain password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hashed password
    
    // Insert admin user into database
    $sql = "INSERT INTO users (name, email, phone_number, password, profile_picture) 
            VALUES ('$name', '$email', '$phone', '$hashed_password', 'images/default-avatar.png')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Admin user created successfully!<br>";
        echo "Username: admin@studybud.com<br>";
        echo "Password: admin123<br>";
        echo "<a href='admin/login.php'>Go to Admin Login</a>";
    } else {
        echo "Error creating admin user: " . $conn->error;
    }
}

$conn->close();
?>
