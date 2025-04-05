<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirmPassword'];

    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = 'Passwords do not match';
        header('Location: ../register.html');
        exit();
    }

    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $_SESSION['register_error'] = 'Email already registered';
        header('Location: ../register.html');
        exit();
    }

    // Handle profile picture upload
    $targetDir = "../uploads/profile_pictures/";
    $fileName = basename($_FILES["profilePicture"]["name"]);
    $targetFilePath = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

    // Allow certain file formats
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    
    if (in_array($fileType, $allowTypes)) {
        // Upload file to server
        if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $targetFilePath)) {
            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into database
            $sql = "INSERT INTO users (name, email, phone_number, password, profile_picture) 
                    VALUES ('$name', '$email', '$phone', '$hashedPassword', '$targetFilePath')";

            if ($conn->query($sql) === TRUE) {
                // Set session variables
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_profile_picture'] = $targetFilePath;

                // Redirect to home page
                header('Location: ../index.php');
                exit();
            } else {
                $_SESSION['register_error'] = 'Error creating account: ' . $conn->error;
                header('Location: ../register.html');
                exit();
            }
        } else {
            $_SESSION['register_error'] = 'Error uploading profile picture';
            header('Location: ../register.html');
            exit();
        }
    } else {
        $_SESSION['register_error'] = 'Only JPG, PNG, JPEG, and GIF files are allowed';
        header('Location: ../register.html');
        exit();
    }
}
?>
