<?php
require_once 'config.php';

// Test user data
$test_user = [
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123'  // This will be hashed
];

// Hash the password
$hashed_password = password_hash($test_user['password'], PASSWORD_DEFAULT);

// Insert test user
$sql = "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $test_user['name'], $test_user['email'], $hashed_password);

if ($stmt->execute()) {
    echo "Test user created successfully!";
    echo "<br>Username: " . $test_user['email'];
    echo "<br>Password: " . $test_user['password'];
} else {
    echo "Error creating test user: " . $conn->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
