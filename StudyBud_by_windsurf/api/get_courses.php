<?php
header('Content-Type: application/json');
require_once '../config.php';

// Get all courses with category information
$sql = "SELECT c.*, cat.name as category_name, 
        (SELECT COUNT(*) FROM videos WHERE course_id = c.id) as video_count
        FROM courses c
        LEFT JOIN categories cat ON c.category_id = cat.id
        ORDER BY c.created_at DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $courses = [];
    while($row = $result->fetch_assoc()) {
        // Get average rating for the course
        $rating_sql = "SELECT AVG(rating) as avg_rating FROM course_ratings WHERE course_id = " . $row['id'];
        $rating_result = $conn->query($rating_sql);
        $rating_row = $rating_result->fetch_assoc();
        
        $row['rating'] = $rating_row['avg_rating'] ? round($rating_row['avg_rating'], 1) : 0;
        
        $courses[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $courses]);
} else {
    echo json_encode(["status" => "error", "message" => "No courses found"]);
}

$conn->close();
?>
