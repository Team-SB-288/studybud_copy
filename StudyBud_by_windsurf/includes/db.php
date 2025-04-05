<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$database = "studybud_windsurf";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    $conn->select_db($database);
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        profile_picture VARCHAR(255),
        bio TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql) === FALSE) {
        // If table exists, add missing columns
        $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NOT NULL";
        $conn->query($sql);
        
        $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255)";
        $conn->query($sql);
        
        $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS bio TEXT";
        $conn->query($sql);
    }
} else {
    die("Error creating database: " . $conn->error);
}

// Function to get database connection
function db_connect() {
    global $conn;
    return $conn;
}
?>
