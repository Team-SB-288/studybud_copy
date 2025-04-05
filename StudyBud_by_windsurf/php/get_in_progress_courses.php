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

// Get in-progress courses using prepared statement
try {
    $stmt = $conn->prepare("SELECT 
                            c.id, 
                            c.title, 
                            c.description, 
                            c.thumbnail as image,
                            (SELECT COUNT(*) FROM videos WHERE course_id = c.id) as video_count,
                            uce.completion_percentage
                        FROM courses c
                        JOIN user_course_enrollment uce ON c.id = uce.course_id
                        WHERE uce.user_id = ? AND uce.completion_percentage > 0 AND uce.completion_percentage < 100
                        ORDER BY uce.enrollment_date DESC
                        LIMIT 3");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        // Sanitize output data
        $courses[] = [
            'id' => $row['id'],
            'title' => htmlspecialchars($row['title']),
            'description' => htmlspecialchars($row['description']),
            'image' => htmlspecialchars($row['image'] ?: 'images/course-default.jpg'),
            'video_count' => (int)$row['video_count'],
            'completion_percentage' => (float)$row['completion_percentage']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'courses' => $courses
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving in-progress courses: ' . $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();
?>
