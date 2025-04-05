<?php
session_start();
require_once 'db_connect.php';
require_once 'csrf_token.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'You must be logged in to update your profile'
    ]);
    exit;
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get form data
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';

// Validate data
if (empty($name)) {
    echo json_encode([
        'success' => false,
        'message' => 'Name is required'
    ]);
    exit;
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Valid email is required'
    ]);
    exit;
}

// Connect to database
$conn = db_connect();

// Check if bio column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'bio'");
$bio_exists = $result->num_rows > 0;

// Check if profile_picture column exists
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
$profile_picture_exists = $result->num_rows > 0;

// Add bio column if it doesn't exist
if (!$bio_exists) {
    $conn->query("ALTER TABLE users ADD COLUMN bio TEXT NULL");
    $bio_exists = true; // Column should exist now
}

// Add profile_picture column if it doesn't exist
if (!$profile_picture_exists) {
    $conn->query("ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255) NULL");
    $profile_picture_exists = true; // Column should exist now
}

// Check if email already exists for another user
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Email is already in use by another account'
    ]);
    exit;
}

// Handle profile picture upload
$profile_picture = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    try {
        // Create uploads directory if it doesn't exist
        $upload_dir = '../uploads/profile_pictures/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $file_name = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
        $target_file = $upload_dir . $file_name;
        
        // Move uploaded file
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
            $profile_picture = 'uploads/profile_pictures/' . $file_name;
        } else {
            throw new Exception('Failed to move uploaded file');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error uploading profile picture: ' . $e->getMessage(),
            'error_details' => error_get_last()
        ]);
        exit;
    }
}

// Update user profile in database
$query_parts = ["name = ?", "email = ?"];
$param_types = "ss";
$param_values = [$name, $email];

if ($bio_exists) {
    $query_parts[] = "bio = ?";
    $param_types .= "s";
    $param_values[] = $bio;
}

if ($profile_picture && $profile_picture_exists) {
    $query_parts[] = "profile_picture = ?";
    $param_types .= "s";
    $param_values[] = $profile_picture;
}

$query = "UPDATE users SET " . implode(", ", $query_parts) . " WHERE id = ?";
$param_types .= "i";
$param_values[] = $user_id;

$stmt = $conn->prepare($query);
$stmt->bind_param($param_types, ...$param_values);

$result = $stmt->execute();

if ($result) {
    // Return success response with updated data
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'data' => [
            'name' => $name,
            'email' => $email,
            'bio' => $bio,
            'profile_picture' => $profile_picture
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update profile: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close();
?>
