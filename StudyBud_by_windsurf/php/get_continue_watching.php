<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get continue watching videos
$sql = "SELECT 
            v.id,
            v.title,
            v.thumbnail,
            v.duration,
            uvp.current_time
        FROM user_video_progress uvp
        JOIN videos v ON uvp.video_id = v.id
        WHERE uvp.user_id = $user_id
        ORDER BY uvp.last_watched DESC
        LIMIT 6";

$result = $conn->query($sql);
$videos = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $videos[] = [
            'id' => $row['id'],
            'title' => htmlspecialchars($row['title']),
            'thumbnail' => htmlspecialchars($row['thumbnail']),
            'duration' => $row['duration'],
            'current_time' => $row['current_time']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($videos);
?>
