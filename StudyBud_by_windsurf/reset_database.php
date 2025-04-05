<?php
// Script to reset the database and create a new admin user
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

// Drop the database if it exists
$sql = "DROP DATABASE IF EXISTS $database";
if ($conn->query($sql) === TRUE) {
    echo "<p>Database dropped successfully.</p>";
} else {
    echo "<p>Error dropping database: " . $conn->error . "</p>";
}

// Create the database again
$sql = "CREATE DATABASE $database";
if ($conn->query($sql) === TRUE) {
    echo "<p>Database created successfully.</p>";
    
    // Select the newly created database
    $conn->select_db($database);
    
    // Create users table
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        phone_number VARCHAR(15) NOT NULL,
        password VARCHAR(255) NOT NULL,
        profile_picture VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Create courses table
    $sql = "CREATE TABLE courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        thumbnail VARCHAR(255),
        category_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Create videos table
    $sql = "CREATE TABLE videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        video_url VARCHAR(255) NOT NULL,
        thumbnail VARCHAR(255),
        duration INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id)
    )";
    $conn->query($sql);
    
    // Create user_video_progress table
    $sql = "CREATE TABLE user_video_progress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        video_id INT,
        `current_time` INT,
        last_watched TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (video_id) REFERENCES videos(id)
    )";
    $conn->query($sql);
    
    // Create categories table
    $sql = "CREATE TABLE categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
    
    // Create books table
    $sql = "CREATE TABLE books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(100),
        description TEXT,
        category_id INT,
        thumbnail VARCHAR(255),
        file_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )";
    $conn->query($sql);
    
    // Create user_course_enrollment table
    $sql = "CREATE TABLE user_course_enrollment (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        course_id INT,
        enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completion_percentage FLOAT DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (course_id) REFERENCES courses(id)
    )";
    $conn->query($sql);
    
    // Create new admin user
    $name = "StudyBud Admin";
    $email = "admin@studybud.com";
    $phone = "1234567890";
    $password = "Admin@123"; // Plain password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hashed password
    
    // Insert admin user into database
    $sql = "INSERT INTO users (name, email, phone_number, password, profile_picture) 
            VALUES ('$name', '$email', '$phone', '$hashed_password', 'images/default-avatar.png')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<div style='background-color: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
        echo "<h3>Database Reset Successful!</h3>";
        echo "<p>New admin user created with the following credentials:</p>";
        echo "<ul>";
        echo "<li><strong>Username/Email:</strong> admin@studybud.com</li>";
        echo "<li><strong>Password:</strong> Admin@123</li>";
        echo "</ul>";
        echo "<p><a href='admin/login.php' style='background-color: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Go to Admin Login</a></p>";
        echo "</div>";
    } else {
        echo "<p>Error creating admin user: " . $conn->error . "</p>";
    }
} else {
    echo "<p>Error creating database: " . $conn->error . "</p>";
}

$conn->close();
?>
