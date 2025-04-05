<?php
header('Content-Type: application/json');
require_once '../config.php';

// Check if course_id is provided
if (!isset($_GET['course_id'])) {
    echo json_encode(["status" => "error", "message" => "Course ID is required"]);
    exit;
}

$course_id = $conn->real_escape_string($_GET['course_id']);

// Get all videos for the specified course
$sql = "SELECT * FROM videos WHERE course_id = $course_id ORDER BY id ASC";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $videos = [];
    while($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $videos]);
} else {
    echo json_encode(["status" => "error", "message" => "No videos found for this course"]);
}

$conn->close();
?>
