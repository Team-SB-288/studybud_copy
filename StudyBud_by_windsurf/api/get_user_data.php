<?php
session_start();
header('Content-Type: application/json');
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user data
$sql = "SELECT id, name, email, phone_number, profile_picture, created_at FROM users WHERE id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Get enrolled courses
    $courses_sql = "SELECT c.*, uce.enrollment_date, uce.completion_percentage, cat.name as category_name
                    FROM user_course_enrollment uce
                    JOIN courses c ON uce.course_id = c.id
                    LEFT JOIN categories cat ON c.category_id = cat.id
                    WHERE uce.user_id = $user_id
                    ORDER BY uce.enrollment_date DESC";
    
    $courses_result = $conn->query($courses_sql);
    
    $enrolled_courses = [];
    if ($courses_result->num_rows > 0) {
        while($course = $courses_result->fetch_assoc()) {
            // Get video count for the course
            $video_count_sql = "SELECT COUNT(*) as count FROM videos WHERE course_id = " . $course['id'];
            $video_count_result = $conn->query($video_count_sql);
            $video_count = $video_count_result->fetch_assoc()['count'];
            
            $course['video_count'] = $video_count;
            $enrolled_courses[] = $course;
        }
    }
    
    // Get user achievements
    $achievements_sql = "SELECT * FROM user_achievements WHERE user_id = $user_id";
    $achievements_result = $conn->query($achievements_sql);
    
    $achievements = [];
    if ($achievements_result && $achievements_result->num_rows > 0) {
        while($achievement = $achievements_result->fetch_assoc()) {
            $achievements[] = $achievement;
        }
    }
    
    // Combine all data
    $response = [
        "status" => "success",
        "user" => $user,
        "enrolled_courses" => $enrolled_courses,
        "achievements" => $achievements
    ];
    
    echo json_encode($response);
} else {
    echo json_encode(["status" => "error", "message" => "User not found"]);
}

$conn->close();
?>
