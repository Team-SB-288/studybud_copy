<?php
// Server configuration file
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Handle different routes
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case '/api/courses':
        handleCourses();
        break;
    case '/api/videos':
        handleVideos();
        break;
    case '/api/user-progress':
        handleUserProgress();
        break;
    default:
        // Serve static files
        $file = __DIR__ . $uri;
        if (file_exists($file)) {
            serveFile($file);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Not Found']);
        }
        break;
}

function serveFile($file) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $mime_types = [
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'php' => 'text/html',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];

    header('Content-Type: ' . ($mime_types[$ext] ?? 'application/octet-stream'));
    readfile($file);
}

function handleCourses() {
    require_once 'config.php';
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            // Use prepared statement for SELECT
            $stmt = $conn->prepare("SELECT * FROM courses ORDER BY created_at DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result) {
                $courses = [];
                while ($row = $result->fetch_assoc()) {
                    $courses[] = $row;
                }
                echo json_encode($courses);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Database error']);
            }
            $stmt->close();
            break;
            
        case 'POST':
            // Add new course using prepared statement
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($data['title']) || trim($data['title']) === '') {
                http_response_code(400);
                echo json_encode(['error' => 'Title is required']);
                return;
            }
            
            $stmt = $conn->prepare("INSERT INTO courses (title, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $data['title'], $data['description']);
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Course added successfully', 'id' => $conn->insert_id]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to add course: ' . $conn->error]);
            }
            $stmt->close();
            break;
    }
}

function handleVideos() {
    require_once 'config.php';
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            $course_id = $_GET['course_id'] ?? null;
            
            if ($course_id) {
                // Use prepared statement when filtering by course_id
                $stmt = $conn->prepare("SELECT * FROM videos WHERE course_id = ?");
                $stmt->bind_param("i", $course_id);
            } else {
                // Get all videos
                $stmt = $conn->prepare("SELECT * FROM videos");
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result) {
                $videos = [];
                while ($row = $result->fetch_assoc()) {
                    $videos[] = $row;
                }
                echo json_encode($videos);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Database error: ' . $conn->error]);
            }
            $stmt->close();
            break;
            
        case 'POST':
            // Add new video using prepared statement
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($data['course_id']) || !isset($data['title']) || !isset($data['video_url'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Course ID, title, and video URL are required']);
                return;
            }
            
            $stmt = $conn->prepare("INSERT INTO videos (course_id, title, description, video_url) 
                    VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", 
                $data['course_id'], 
                $data['title'], 
                $data['description'] ?? '', 
                $data['video_url']
            );
            
            if ($stmt->execute()) {
                echo json_encode(['message' => 'Video added successfully', 'id' => $conn->insert_id]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to add video: ' . $conn->error]);
            }
            $stmt->close();
            break;
    }
}

function handleUserProgress() {
    require_once 'config.php';
    session_start();
    
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        return;
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $user_id = $_SESSION['user_id'];
    
    switch ($method) {
        case 'GET':
            // Use prepared statement for user progress
            $stmt = $conn->prepare("SELECT v.*, uvp.`current_time`, uvp.last_watched 
                    FROM videos v 
                    JOIN user_video_progress uvp ON v.id = uvp.video_id 
                    WHERE uvp.user_id = ? 
                    ORDER BY uvp.last_watched DESC");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result) {
                $progress = [];
                while ($row = $result->fetch_assoc()) {
                    $progress[] = $row;
                }
                echo json_encode($progress);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Database error: ' . $conn->error]);
            }
            $stmt->close();
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate required fields
            if (!isset($data['video_id']) || !isset($data['current_time'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Video ID and current time are required']);
                return;
            }
            
            // First check if record exists
            $stmt = $conn->prepare("SELECT id FROM user_video_progress WHERE user_id = ? AND video_id = ?");
            $stmt->bind_param("ii", $user_id, $data['video_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update existing record
                $stmt = $conn->prepare("UPDATE user_video_progress SET `current_time` = ?, last_watched = NOW() WHERE user_id = ? AND video_id = ?");
                $stmt->bind_param("iii", $data['current_time'], $user_id, $data['video_id']);
            } else {
                // Insert new record
                $stmt = $conn->prepare("INSERT INTO user_video_progress (user_id, video_id, `current_time`, last_watched) VALUES (?, ?, ?, NOW())");
                $stmt->bind_param("iii", $user_id, $data['video_id'], $data['current_time']);
            }
            
            if ($stmt->execute()) {
                // Update course completion percentage
                updateCourseCompletion($conn, $user_id, $data['video_id']);
                echo json_encode(['message' => 'Progress updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update progress: ' . $conn->error]);
            }
            $stmt->close();
            break;
    }
}

// New function to update course completion percentage
function updateCourseCompletion($conn, $user_id, $video_id) {
    // Get course_id for this video
    $stmt = $conn->prepare("SELECT course_id FROM videos WHERE id = ?");
    $stmt->bind_param("i", $video_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $course_id = $result->fetch_assoc()['course_id'];
        
        // Count total videos in course
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM videos WHERE course_id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $total_videos = $stmt->get_result()->fetch_assoc()['total'];
        
        // Count watched videos (videos with progress)
        $stmt = $conn->prepare("SELECT COUNT(*) as watched FROM user_video_progress uvp 
                               JOIN videos v ON uvp.video_id = v.id 
                               WHERE uvp.user_id = ? AND v.course_id = ?");
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        $watched_videos = $stmt->get_result()->fetch_assoc()['watched'];
        
        // Calculate completion percentage
        $completion_percentage = ($watched_videos / $total_videos) * 100;
        
        // Update or insert into user_course_enrollment
        $stmt = $conn->prepare("SELECT id FROM user_course_enrollment WHERE user_id = ? AND course_id = ?");
        $stmt->bind_param("ii", $user_id, $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing record
            $stmt = $conn->prepare("UPDATE user_course_enrollment SET completion_percentage = ? WHERE user_id = ? AND course_id = ?");
            $stmt->bind_param("dii", $completion_percentage, $user_id, $course_id);
        } else {
            // Insert new record
            $stmt = $conn->prepare("INSERT INTO user_course_enrollment (user_id, course_id, completion_percentage) VALUES (?, ?, ?)");
            $stmt->bind_param("iid", $user_id, $course_id, $completion_percentage);
        }
        $stmt->execute();
    }
}
