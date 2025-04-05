<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Use prepared statements for all database queries
try {
    // Get courses enrolled count
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_course_enrollment WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $courses_enrolled = $stmt->get_result()->fetch_assoc()['count'];
    $stmt->close();
    
    // Get total hours learned (sum of video durations watched)
    $stmt = $conn->prepare("SELECT SUM(v.duration) as total_hours 
                           FROM user_video_progress uvp 
                           JOIN videos v ON uvp.video_id = v.id 
                           WHERE uvp.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $hours_learned = $result->fetch_assoc()['total_hours'];
    $hours_learned = $hours_learned ? round($hours_learned / 3600, 1) : 0; // Convert seconds to hours
    $stmt->close();
    
    // Get certificates count (placeholder - implement actual certificate logic)
    $certificates = 0;
    
    // Get average completion rate across all enrolled courses
    $stmt = $conn->prepare("SELECT AVG(completion_percentage) as avg_completion 
                           FROM user_course_enrollment 
                           WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $completion_rate = $result->fetch_assoc()['avg_completion'];
    $completion_rate = $completion_rate ? round($completion_rate) . '%' : '0%';
    $stmt->close();
    
    // Return user stats
    echo json_encode([
        'success' => true,
        'courses_enrolled' => $courses_enrolled,
        'hours_learned' => $hours_learned,
        'certificates' => $certificates,
        'completion_rate' => $completion_rate
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving user stats: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
